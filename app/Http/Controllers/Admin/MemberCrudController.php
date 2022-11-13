<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MemberRequest;
use App\Http\Requests\MemberRequestNoUpline;
use App\Http\Traits\MemberTrait;
use App\Models\Level;
use App\Models\Member;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
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
    use MemberTrait;

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
        $level = Level::find(1);
        $members = Member::all()->map(function($member){
            $member->name = $member->member_numb . ' - ' . $member->name;
            return $member;
        });
        $countMember = Member::count();
        $this->crud->setValidation(($countMember > 0) ? MemberRequest::class : MemberRequestNoUpline::class);
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
            'attributes' => [
                'disabled' => ($countMember > 0) ? false : true,
            ],
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
        $this->crud->removeField('level_id');
        $this->crud->removeField('level_name');
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
        $requests = request()->all();
        $this->crud->validateRequest();
        DB::beginTransaction();
        try {
            $checkMember = Member::where('member_numb', $requests['member_numb'])->first();
            if($checkMember){
                $requests['member_numb'] = $this->generateMemberNumber();
            }
            Member::create($requests);
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

}
