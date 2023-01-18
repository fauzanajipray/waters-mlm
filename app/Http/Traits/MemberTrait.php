<?php
namespace App\Http\Traits;

use App\Models\BonusHistory;
use App\Models\Branch;
use App\Models\Configuration;
use App\Models\Member;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;

trait MemberTrait {
    public function downloadCardMember($id)
    {
        if(!backpack_user()->hasPermissionTo('Read Member')) {
            Alert::error('You don\'t have permission to access this page.')->flash();
            return redirect()->back();
        }
        $member = Member::with('level')->where('id', $id)->firstOrFail();
        $title = "Card Member ($member->member_numb - $member->name)";
        $level = $member->level->name;
        ($this->isActiveMember($member)) ? $expiredDate = Carbon::parse($member->expired_at)->format('d F Y') : $expiredDate = 'Expired';
        $pdf = PDF::loadView('member.card_member_pdf', [
            'title' => $title,
            'member' => $member,
            'level' => $level,
            'expiredDate' => $expiredDate,
        ]);
        return $pdf->stream($title . ".pdf");
    }

    public function reportMember(Request $request, $id)
    {
        if(!backpack_user()->hasPermissionTo('Read Member')) {
            Alert::error('You don\'t have permission to access this page.')->flash();
            return redirect()->back();
        }
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
        $uplineID = request()->input('upline_id') ?? 0;
        $memberType = request()->input('member_type') ?? 'PERSONAL';
        if($memberType == 'PERSONAL'){
            $branchID = request()->input('branch_office_id') ?? 0;
        } else {
            $branchID = request()->input('branch_id') ?? 0;
        }
        if($uplineID > 0){
            $upline = Member::where('id', $uplineID)->first();
            $uplineNumber = $upline->member_numb;
            $uplineNumber = explode('-', $uplineNumber);
            $uplineID = $uplineNumber[1];
        }
        $lastMember = Member::withTrashed()->orderBy('id', 'desc')->first();
        $lastMemberNumb = $lastMember->member_numb ?? '0-0-0';
        $memberNumb = explode('-', $lastMemberNumb)[1] + 1;
        $memberNumb = $branchID .'-'. str_pad($memberNumb, 1, '0', STR_PAD_LEFT) . '-'. str_pad($uplineID, 1, '0', STR_PAD_LEFT);
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

    function downloadFormRegister(){
        // $view = view('exports.pdf.print-letter-form-register', []);
        $registrationPayment = Configuration::where('key', 'activation_payment_amount')->first()->value;
        $pdf = Pdf::loadView('exports.pdf.print-letter-form-register-blank', [
            'payment' => $registrationPayment
        ]);

        // return view('exports.pdf.print-letter-form-register');
        // return $pdf->download('Surat Jalan '.$transaction->code.'.pdf');
        return $pdf->stream('form-registrasi-member.pdf');
    }

    function downloadFormLineRegister($id){
        $data = Member::with('upline')->where('id', $id)->first();
        $registrationPayment = Configuration::where('key', 'activation_payment_amount')->first()->value;

        $pdf = Pdf::loadView('exports.pdf.print-letter-form-register', [
            'data' => $data,
            'payment' => $registrationPayment,
        ]);

        // return view('exports.pdf.print-letter-form-register', [
        //     'data' => $data,
        //     'payment' => $registrationPayment,
        // ]);
        // return $pdf->download('Surat Jalan '.$transaction->code.'.pdf');
        return $pdf->stream('form-registrasi-member.pdf');
    }

    public function getMembersForFilter(Request $request)
    {
        $term = $request->input('term');
        if($term){
            $members = Member::where('name', 'like', '%'.$term.'%')->orWhere('member_numb', 'LIKE', '%'.$term.'%')->get();
        }else{
            $members = Member::all();
        }
        $members = $members->map(function($member){
            $member->name = $member->member_numb . ' - ' . $member->name;
            return $member;
        });
        $options = $members->pluck('name', 'id');
        return $options;
    }

    public function getBranchOwner(Request $request)
    {
        $term = $request->input('q');
        if($term){
            $branch = Branch::whereHas('member')->with(['member' => function ($query) use ($term) {
                return $query->where('name', 'like', '%'.$term.'%')
                    ->orWhere('member_numb', 'LIKE', '%'.$term.'%');
            }])->where('id', '!=', 1)->get();
        }else{
            $branch = Branch::whereHas('member')->with(['member'])->where('id', '!=', 1)->get();
        }
        $members = null;
        foreach($branch as $b){
            if(isset($b->member)){
                $members[$b->member->id] = $b->member;
                $members[$b->member->id]->name = $b->member->member_numb . ' - ' . $b->member->name . ' - ' . $b->name;
            }
        }
        $members = collect($members);
        return $members;
    }

    public function onlyNsi(Request $request){
        $search_term = $request->input('q');
        if($search_term) {
            $members = Member::active()->isNSI()->where(function ($query) use ($search_term) {
                $query->where('name', 'LIKE', '%'.$search_term.'%')
                    ->orWhere('member_numb', 'LIKE', '%'.$search_term.'%')
                    ->orWhere('id_card', 'LIKE', '%'.$search_term.'%');
                })
                ->paginate(10);
                $members->map(function($member) {
                    $text = $member->member_numb . ' - ' . $member->name;
                    $member->text = $text;
                    return $member;
                });
        } else {
            $members = Member::active()->isNSI()->paginate(10);
            $members->map(function($member) {
                $text = $member->member_numb . ' - ' . $member->name;
                $member->text = $text;
                return $member;
            });
        }
        return $members;
    }

    public function getMemberType(Request $request){
        $memberID = $request->input('member_id');
        if($memberID){
            $member = Member::find($memberID);
            $memberType = $member->member_type;
            return $memberType;
        }
        return null;
    }
}

