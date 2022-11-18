<?php
namespace App\Http\Traits;

use App\Http\Requests\MemberRequest;
use App\Models\BonusHistory;
use App\Models\Level;
use App\Models\Member;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Prologue\Alerts\Facades\Alert;

trait MemberTrait {
    public function downloadCardMember($id) 
    {
        $member = Member::with('level')->where('id', $id)->firstOrFail();
        $imageUrl = ($member->photo_url) ? 'storage/'.$member->photo_url : 'images/2x3.png';
        $title = "Card Member ($member->member_numb - $member->name)";
        $level = $member->level->name;
        // expired date for human
        ($this->isActiveMember($member)) ? $expiredDate = Carbon::parse($member->expired_at)->format('d M Y') : $expiredDate = 'Expired';
        if($expiredDate == 'Expired') {
            Alert::error('Member Expired')->flash();
            return redirect()->back();
        } 
        $pdf = PDF::loadView('member.card_member_pdf', [
            'title' => $title, 
            'member' => $member,
            'imageUrl' => $imageUrl,
            'level' => $level,
            'expiredDate' => $expiredDate,
        ]);
        return $pdf->download($title . ".pdf");
    }

    public function reportMember(Request $request, $id)
    {
        $totalDownlineLevel = ($request->input('total_downline_level')) ? $request->input('total_downline_level') : 2;
        if($totalDownlineLevel <= 0) {
            Alert::error('Total Downline Level must be greater than 0')->flash();
            return redirect()->back();
        }
        if($request->input('month_year')) {
            $monthYear = $request->input('month_year');
            $monthYear = Carbon::parse($monthYear);
        } else {
            $monthYear = Carbon::now();
        }
        $members = $this->getMemberInGroup($id, $totalDownlineLevel);
        $dataMember = [];
        foreach ($members as $member) {
            // Count Bonus
            $BP = BonusHistory::where('member_id', $member['id'])
                ->where('bonus_type', 'BP')
                ->monthYear($monthYear)
                ->sum('bonus');
            $GM = BonusHistory::where('member_id', $member['id'])
                ->where('bonus_type', 'GM')
                ->monthYear($monthYear)
                ->sum('bonus');
            $OR = BonusHistory::where('member_id', $member['id'])
                ->where('bonus_type', 'OR')
                ->monthYear($monthYear)
                ->sum('bonus');
            $total = $BP + $GM + $OR;
            // $memberActive = ($this->isActiveMember($member)) ? 'Active' : 'Expired';

            
            $data = [
                'id' => $member['id'],
                'name' => $member['name'],
                'level' => $member['level']['name'],
                'url' => backpack_url('member/'.$member['id'].'/show'),
                'imageUrl' => ($member['photo_url']) ? backpack_url('storage/'.$member['photo_url']) : backpack_url('images/profile.jpg'),
                'parentId' => $member['upline_id'] ?? "",
                'height' => 175,
                '_directSubordinates' => 0,
                '_totalSubordinates' => 0,
                'member_numb' => $member['member_numb'],
                'total' => $total,
                'contents' => '<p class="mb-0">Total Bonus : <b>Rp. '.$total.'</b></p>
                <p class="mb-0" hidden>Total Omset : <b>Rp .-</b></p>
                <p class="mb-0" hidden>B. Omset : <b>Rp. -</b></p>
                <p class="mb-0">B. Pribadi : <b>Rp. '.number_format($BP).'</b></p>
                <p class="mb-0">Goldmine : <b>Rp. '.number_format($GM).'</b></p>
                <p class="mb-0">Overriding : <b>Rp. '.number_format($OR).'</b></p>'
            ];
            array_push($dataMember, $data);
        }
        $dataMember[0]['parentId'] = "";
        $title = "Report Member (".$dataMember[0]['member_numb']." - ".$dataMember[0]['name'].")";
        $user = User::where('id', 1)->firstOrFail();
        return view('member.report_member', [ 
            'title' => $title,
            'user' => $user,
            'dataMember' => json_encode($dataMember),
            'monthYear' => $monthYear,
            'totalDownlineLevel' => $totalDownlineLevel,
        ]);
    }

    protected function getMemberGroup($id)
    {
        $downlineInGroup = [];
        $mainMember = User::with('member')->where('id', $id)->firstOrFail()->member->toArray();
        // add to downlineGroup
        $downlineInGroup[$mainMember['id']] = $mainMember['member_numb'] . ' - ' . $mainMember['name'];
        // get downline
        $downline = Member::where('upline_id', $mainMember['id'])->get()->toArray();
        // add to downlineGroup
        foreach($downline as $d){
            $downlineInGroup[$d['id']] = $d['member_numb'] . ' - ' . $d['name'];
        }
        // get downline from downline
        foreach($downline as $d){
            $downline2 = Member::where('upline_id', $d['id'])->get()->toArray();
            foreach($downline2 as $d2){
                $downlineInGroup[$d2['id']] = $d2['member_numb'] . ' - ' . $d2['name'];
            }
        }
        return $downlineInGroup;
    }

    public function getMemberInGroup($id, $totalDownlineLevel, $data = [])
    {
        $mainMember = Member::with(['level:id,name'])->where('id', $id)->firstOrFail()->toArray();
        $downline = Member::where('upline_id', $mainMember['id'])->get()->toArray();
        $data[$mainMember['id']] = $mainMember;
        if($totalDownlineLevel > 0){
            foreach($downline as $d){
                $data[$d['id']] = $d;
                $members = $this->getMemberInGroup($d['id'], $totalDownlineLevel - 1, $data);
                $data = $members;
            }
        }
        return $data;
    }

    protected function generateMemberNumber()
    {
        $lastMember = Member::withTrashed()->orderBy('id', 'desc')->first();
        $lastMemberNumb = $lastMember->member_numb ?? 'M-000';
        $memberNumb = explode('-', $lastMemberNumb)[1] + 1;
        $memberNumb = 'M-' . str_pad($memberNumb, 3, '0', STR_PAD_LEFT);
        $check = Member::where('member_numb', $memberNumb)->first();
        if($check){
            $memberNumb = $this->generateMemberNumber();
        }
        return $memberNumb;
    }

    private function isActiveMember($member) 
    {
        if ($member->expired_at < Carbon::now()) {
            return false;
        }
        return true;
    }
}

