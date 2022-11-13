<?php
namespace App\Http\Traits;

use App\Http\Requests\MemberRequest;
use App\Models\BonusHistory;
use App\Models\Level;
use App\Models\Member;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Prologue\Alerts\Facades\Alert;

trait MemberTrait {
    public function downloadCardMember($id) {
        $member = Member::with('level')->where('id', $id)->firstOrFail();
        $imageUrl = ($member->photo_url) ? 'storage/'.$member->photo_url : 'images/profile.jpg';
        $title = "Card Member ($member->member_numb - $member->name)";
        $level = $member->level->name;
        $pdf = PDF::loadView('member.card_member_pdf', [
            'title' => $title, 
            'member' => $member,
            'imageUrl' => $imageUrl,
            'level' => $level,
        ]);
        return $pdf->download($title . ".pdf");
    }

    public function reportMember(Request $request, $id){
        $members = $this->getMemberInGroup($id);
        $dataMember = [];
        foreach ($members as $member) {
            // Count Bonus
            $BP = BonusHistory::where('member_id', $member['id'])
                ->where('bonus_type', 'BP')
                ->sum('bonus');
            $BS = BonusHistory::where('member_id', $member['id'])
                ->where('bonus_type', 'BS')
                ->sum('bonus');
            $OR = BonusHistory::where('member_id', $member['id'])
                ->where('bonus_type', 'OR')
                ->sum('bonus');
            $total = $BP + $BS + $OR;
            
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
                <p class="mb-0">B. Sponsor : <b>Rp. '.number_format($BS).'</b></p>
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
        ]);
    }

    protected function getMemberGroup($id){
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

    public function getMemberInGroup($id, $data = []){
        $mainMember = Member::with(['level:id,name'])->where('id', $id)->firstOrFail()->toArray();
        $downline = Member::where('upline_id', $mainMember['id'])->get()->toArray();
        $data[$mainMember['id']] = $mainMember;
        foreach($downline as $d){
            $data[$d['id']] = $d;
            $data = $this->getMemberInGroup($d['id'], $data);
        }
        return $data;
    }

    protected function setupModerateRoutes($segment, $routeName, $controller)
    {
        Route::get('user/{id}/'.$segment.'/create', [
            'as'        => $routeName.'.getCreateMember',
            'uses'      => $controller.'@getCreateMember',
            'operation' => 'createMember',
        ]);
        Route::post('user/{id}/'.$segment.'/create', [
            'as'        => $routeName.'.postCreateMember',
            'uses'      => $controller.'@postCreateMember',
            'operation' => 'createMember',
        ]);
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

    public function getCreateMember($id) 
    {
        $this->crud->setOperation('createMember');
        $this->data['crud'] = $this->crud;
        $this->data['title'] = 'Add '.$this->crud->entity_name;
        $this->data['member_numb'] = $this->generateMemberNumber();
        $this->data['level'] = Level::find(1);
        $this->data['user'] = User::where('id', $id)->firstOrFail();
        $this->data['upline'] = User::with('member')->where('id', backpack_user()->id)->firstOrFail()->member;
        $this->data['uplines'] = Member::select('name', 'id', 'member_numb')->get();
        return view('member.create-member', $this->data);
    }

    public function postCreateMember(Request $request, $id) 
    {
        $requests = $request->all();
        $validator = Validator::make($requests, (new MemberRequest)->rules());

        if ($validator->fails()) {
            Alert::error("Validation Error")->flash();
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            $checkMember = Member::where('member_numb', $requests['member_numb'])->first();
            if($checkMember){
                $requests['member_numb'] = $this->generateMemberNumber();
            }
            // Create Member
            $member = Member::create($requests);
            // Update User
            $user = User::where('id', $id)->firstOrFail();
            $user->update([
                'member_id' => $member->id,
                'name' => $requests['name']
            ]);
            Alert::success('Register Member success')->flash();
            DB::commit();
            return redirect()->route('member.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error, '.$e->getMessage())->flash();

            return redirect()->back()->withInput();
        }
    }
}

