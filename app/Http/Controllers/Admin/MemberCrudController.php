<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MemberRequest;
use App\Models\BonusHistory;
use App\Models\Level;
use App\Models\Member;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Prologue\Alerts\Facades\Alert;

/**
 * Class MemberCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MemberCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        $this->crud->setModel(\App\Models\Member::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/member');
        $this->crud->setEntityNameStrings('member', 'members');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->viewAfterContent = ['image_preview_helper'];
        $this->crud->firstCellNonFlex = true;
        $this->crud->column('member_numb')->label('No. Member');
        $this->crud->column('id_card')->label('ID Card');
        $this->crud->column('name');
        $this->crud->addColumn([
            'name' => 'level_id',
            'label' => 'Level',
            'entity' => 'level',
            'attribute' => 'name' ,
            'model' => Level::class,
        ]);
        $this->crud->column('gender');
        $this->crud->column('phone');
        $this->crud->column('email');
        $this->crud->column('address');
        $this->crud->addColumn([
            'name' => 'photo_url',
            'label' => 'Photo Member',
            'type' => 'image',
            'prefix' => 'storage/',
        ]);

        // TODO : Add this buttons
        $this->crud->addButtonFromModelFunction('line', 'cardMember', 'cardMember', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'reportMember', 'reportMember', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'addTransaction', 'addTransaction', 'beginning');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setValidation(MemberRequest::class);
        $level = Level::find(1);
        $members = Member::all()->map(function($member){
            $member->name = $member->member_numb . ' - ' . $member->name;
            return $member;
        });
        $this->crud->addField([
            'name' => 'level_id',
            'type' => 'hidden',
            'value' => $level->id,
        ]);
        $this->crud->addField([
            'name' => 'upline_id',
            'label' => 'Upline',
            'type' => 'select2_from_array',
            'options' => $members->pluck('name', 'id')->toArray(),
            'allows_null' => false,
        ]);
        $this->crud->addField([
            'name' => 'member_numb',
            'label' => 'No. Member',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly',
            ],
            'value' => $this->generateMemberNumber(),
        ]);
        $this->crud->addField([
            'name' => 'level_name',
            'label' => 'Level',
            'type' => 'text',
            'value' => $level->code.' - '.$level->name,
            'attributes' => [
                'placeholder' => 'Level',
                'disabled' => 'disabled'
            ],
        ]);

        // $this->crud->addField([
        //     'name' => 'level_id',
        //     'type' => 'select2',
        //     'label' => 'Level',
        //     'entity' => 'level',
        //     'attribute' => 'name',
        //     'model' => Level::class,
        //     'options'   => (function ($query) {
        //         return $query->orderBy('id', 'ASC')->get();
        //     }),
        // ]);

        $this->crud->field('id_card')->label('ID Card')->type('number');
        $this->crud->field('name');
        $this->crud->addField([
            'name' => 'gender',
            'label' => 'Gender',
            'type' => 'select_from_array',
            'options' => ['M' => 'Male', 'F' => 'Female'],
        ]);
        $this->crud->field('phone')->type('number');
        $this->crud->field('email')->type('email');
        $this->crud->field('address')->type('textarea');
        $this->crud->addField([
            'name' => 'photo_url',
            'label' => 'Photo Member',
            'type' => 'image',
            'upload' => true,
            'crop' => true,
            'aspect_ratio' => 1,
            'prefix' => 'storage/',
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        $this->crud->removeField('member_numb');
        $this->crud->addField([
            'name' => 'member_numb',
            'label' => 'No. Member',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly',
            ],
        ])->afterField('upline_id');
    }
    
    /**
     * Define what happens when the Show operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     * @return void
     */
    protected function setupShowOperation() {
        $this->setupListOperation();
        $this->crud->removeColumn('photo_url');
        $this->crud->addColumn([
            'name' => 'photo_url',
            'label' => 'Photo Member',
            'type' => 'image',
            'prefix' => 'storage/',
            'height' => '200px',
        ]);
        $this->crud->column('updated_at');
        $this->crud->column('created_at');
    }

    public function store()
    {
        $this->crud->validateRequest();
        $requests = request()->all();
        DB::beginTransaction();
        try {
            $checkMember = Member::where('member_numb', $requests['member_numb'])->first();
            if($checkMember){
                $requests['member_numb'] = $this->generateMemberNumber();
            }
            $member = Member::create($requests);
            DB::commit();
            Alert::success(trans('backpack::crud.insert_success'))->flash();
            return redirect($this->crud->route);
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error ', $e->getMessage())->flash();
            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }
    
    public function update()
    {   
        $this->crud->validateRequest();
        $requests = request()->all();
        $user = User::where('member_id', $requests['id'])->first();
        DB::beginTransaction();
        try {
            // Update Member 
            $member = Member::find($requests['id']);
            $member->update($requests);
            DB::commit();
            Alert::success('Member has been updated successfully')->flash();
            return redirect($this->crud->route);
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error ', $e->getMessage())->flash();
            return redirect()->back()->withInput();
        }
    }

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
