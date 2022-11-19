<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\MemberRequest;
use App\Http\Requests\MemberRequestNoUpline;
use App\Http\Requests\MemberRequestUpdate;
use App\Http\Traits\MemberTrait;
use App\Models\Customer;
use App\Models\Level;
use App\Models\Member;
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
        $this->crud->column('member_numb')->label('Unique Number');
        $this->crud->column('id_card')->label('ID Card');
        $this->crud->column('name');
        $this->crud->addColumn([
            'name' => 'level_id',
            'label' => 'Level',
            'entity' => 'level',
            'attribute' => 'name' ,
            'model' => Level::class,
        ]);
        $this->crud->addColumn([
            'name' => 'member_type',
            'label' => 'Type',
            'allows_null' => false,
            'wrapper' => [
                'element' => 'span',
                'class' => function ($crud, $column, $entry, $related_key) {
                    switch ($entry->member_type) {
                        case 'DEFAULT':
                            return 'badge badge-primary';
                        case 'STOKIST':
                            return 'badge badge-success';
                        case 'CABANG':
                            return 'badge badge-warning';
                        case 'NSI':
                            return 'badge badge-danger';
                        default:
                            return 'badge badge-secondary';
                    }
                },
            ],
        ]);
        $this->crud->column('gender');
        $this->crud->column('phone');
        $this->crud->column('email');
        $this->crud->column('address');
        $this->crud->addColumn([
            'name' => 'expired_at',
            'label' => 'Expired At',
            'type' => 'datetime',
        ]);
        $this->crud->addColumn([
            'name' => 'photo_url',
            'label' => 'Photo Member',
            'type' => 'image',
            'prefix' => 'storage/',
        ]);

        // TODO : Add this buttons
        $this->crud->addButtonFromModelFunction('line', 'cardMember', 'cardMember', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'reportMember', 'reportMember', 'beginning');
        // $this->crud->addButtonFromModelFunction('line', 'addTransaction', 'addTransaction', 'beginning');
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
        $countMember = Member::count();
        $this->crud->setValidation(($countMember > 0) ? MemberRequest::class : MemberRequestNoUpline::class);
        $this->crud->addField([
            'name' => 'level_id',
            'type' => 'hidden',
            'value' => $level->id,
        ]);
        $this->crud->addField([
            'name' => 'upline_id',
            'type' => 'Upline',
            'type' => 'select2_from_ajax',
            'entity' => 'upline',
            'attribute' => 'text',
            'data_source' => url('members/only-actived'),
        ]);
        $this->crud->field('member_type')->label('Member Type')->type('select_from_array')->options([
            'DEFAULT' => 'DEFAULT',
            'NSI' => 'NSI',
            'STOKIST' => 'STOKIST',
            'CABANG' => 'CABANG',
        ])->allows_null(false);
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
        $this->crud->addField([
            'name' => 'id_card_type',
            'label' => 'ID Card Type',
            'type' => 'select_from_array',
            'options' => [
                'KTP' => 'KTP',
                'SIM' => 'SIM',
            ],
        ]);
        $this->crud->field('id_card')->label('ID Card')->type('number');
        $this->crud->field('name');
        $this->crud->addField([
            'name' => 'gender',
            'label' => 'Gender',
            'type' => 'select_from_array',
            'options' => ['M' => 'Male', 'F' => 'Female'],
        ]);
        $this->crud->field('dob')->label('Date of Birth')->type('date');
        $this->crud->field('phone');
        $this->crud->field('phone')->type('number');
        $this->crud->field('email')->type('email');
        $this->crud->field('address')->type('textarea');
        $this->crud->field('postal_code');
        $this->crud->addField([
            'name' => 'join_date',
            'label' => 'Join Date',
            'type' => 'date',
        ]);
        $this->crud->addField([
            'name' => 'photo_url',
            'label' => 'Photo Member',
            'type' => 'image',
            'upload' => true,
            'crop' => true,
            'prefix' => 'storage/',
            'aspect_ratio' => 0.66666666666666666666666666666667,
            'width' => 400,
            'height' => 600,
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
        $this->crud->setValidation(MemberRequestUpdate::class);
        $this->crud->removeField('member_numb');
        $this->crud->removeField('upline_id');
        $this->crud->addField([
            'name' => 'member_numb',
            'label' => 'No. Member',
            'type' => 'text',
            'attributes' => [
                'readonly' => 'readonly',
            ],
        ])->beforeField('id_card_type');
        $this->crud->addField([
            'name' => 'upline_name',
            'label' => 'Upline',
            'type' => 'text',
            'attributes' => [
                'disabled' => 'disabled'
            ],
        ])->beforeField('member_type');
        $this->crud->addField([
            'name' => 'upline_id',
            'type' => 'hidden',
            'attributes' => [
                'readonly' => 'disabled'
            ],
        ]);
        $this->crud->removeField('level_id');
        $this->crud->removeField('level_name');        
    }
    
    /**
     * Define what happens when the Show operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     * @return void
     */
    protected function setupShowOperation() 
    {
        $this->setupListOperation();
        $this->crud->removeColumn('photo_url');
        $this->crud->addColumn([
            'name' => 'dob',
            'label' => 'Date of Birth',
            'type' => 'date',
        ])->afterColumn('name');
        $this->crud->addColumn([
            'name' => 'postal_code',
            'label' => 'Postal Code',
        ])->afterColumn('dob');
        $this->crud->addColumn([
            'name' => 'join_date',
            'label' => 'Join Date',
            'type' => 'date',
        ]);
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
            $member = Member::create($requests);
            // Create Customer
            Customer::create([
                'name' => $requests['name'],
                'phone' => $requests['phone'],
                'email' => $requests['email'],
                'address' => $requests['address'],
                'postal_code' => $requests['postal_code'],
                'member_id' => $member->id,
            ]);
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

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');

        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['fields'] = $this->crud->getUpdateFields($id);
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['id'] = $id;

        $upline = $this->data['entry']->upline;
        if ($upline) {
            $this->crud->modifyField('upline_id', [
                'value' => optional($this->data['entry'])->upline_id,
            ]);
            $this->crud->modifyField('upline_name', [
                'value' => $upline->member_numb . ' - ' . $upline->name,
            ]);
        } else {
            $this->crud->modifyField('upline_name', [
                'type' => 'hidden',
            ]);
        }
        return view('crud::edit', $this->data);
    }
}
