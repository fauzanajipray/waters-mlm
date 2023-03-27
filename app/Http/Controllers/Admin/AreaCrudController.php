<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AreaRequest;
use App\Models\Area;
use App\Models\AreaManager;
use App\Models\Branch;
use App\Models\Member;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;

/**
 * Class AreaCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AreaCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Area::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/area');
        CRUD::setEntityNameStrings('area', 'areas');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        if(!backpack_user()->hasPermissionTo('Read Area')){
            $this->crud->denyAccess(['list', 'show']);
        }
        if(!backpack_user()->hasPermissionTo('Create Area')){
            $this->crud->denyAccess(['create']);
        }
        if(!backpack_user()->hasPermissionTo('Update Area')){
            $this->crud->denyAccess(['update']);
        }
        if(!backpack_user()->hasPermissionTo('Delete Area')){
            $this->crud->denyAccess(['delete']);
        }
        CRUD::column('name');
        CRUD::column('created_at');
        CRUD::column('updated_at');

    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(AreaRequest::class);

        CRUD::field('name');
        $this->crud->addField([
            'label' => 'LSI Members',
            'type' => 'select2_from_ajax_multiple',
            'name' => 'lsi_members',
            'entity' => 'lsiMembers',
            'attribute' => 'name',
            'model' => Member::class,
            'data_source' => url('members/only-actived'),
            'placeholder' => 'Select LSI Members',
            'minimum_input_length' => 0,
            'pivot' => true,
        ]);
        $this->crud->addField([
            'label' => 'PM Members',
            'type' => 'select2_from_ajax_multiple',
            'name' => 'pm_members',
            'entity' => 'pmMembers',
            'attribute' => 'name',
            'model' => Member::class,
            'data_source' => url('members/only-actived'),
            'placeholder' => 'Select LSI Members',
            'minimum_input_length' => 0,
            'pivot' => true,
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
        $this->crud->setValidation(AreaRequest::class);
        $this->setupCreateOperation();
    }

    protected function show($id)
    {
        $this->crud->hasAccessOrFail('show');
        $this->setupListOperation();

        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['datas'] = [
            'LSI' => [
                'name' => 'LSI',
                'entry' => $this->data['entry']->lsiMembers()->select('member_numb','name')->get(),
                'columns_name' => ['Member Numb','Name'],
                'columns' => ['member_numb', 'name'],
            ],
            'PM' => [
                'name' => 'PM',
                'entry' => $this->data['entry']->pmMembers()->select('member_numb','name')->get(),
                'columns_name' => ['Member Numb','Name'],
                'columns' => ['member_numb', 'name'],
            ],
            'Branches' => [
                'name' => 'Branches',
                'entry' => Branch::where('area_id', $id)->select('type', 'name', 'address')->get(),
                'columns_name' => ['Type', 'Name', 'Address'],
                'columns' => ['type', 'name', 'address'],
            ],
        ];
        return view('vendor.backpack.crud.show_data', $this->data);
    }

    public function update()
    {
        $this->crud->validateRequest();
        $requests = request()->all();
        DB::beginTransaction();
        try {
            $lsiMemberNow = $requests['lsi_members'];
            $pmMemberNow = $requests['pm_members'];
            $lsiMemberOld = AreaManager::where('area_id', $requests['id'])->where('type', 'lsi')->pluck('member_id')->toArray();
            $pmMemberOld = AreaManager::where('area_id', $requests['id'])->where('type', 'pm')->pluck('member_id')->toArray();
            $lsiMemberDelete = array_diff($lsiMemberOld, $lsiMemberNow);
            $pmMemberDelete = array_diff($pmMemberOld, $pmMemberNow);
            $lsiMemberAdd = array_diff($lsiMemberNow, $lsiMemberOld);
            $pmMemberAdd = array_diff($pmMemberNow, $pmMemberOld);
            foreach ($lsiMemberDelete as $memberId) {
                AreaManager::where('area_id', $requests['id'])->where('member_id', $memberId)->delete();
            }
            foreach ($pmMemberDelete as $memberId) {
                AreaManager::where('area_id', $requests['id'])->where('member_id', $memberId)->delete();
            }
            foreach ($lsiMemberAdd as $memberId) {
                AreaManager::create([
                    'area_id' => $requests['id'],
                    'member_id' => $memberId,
                    'type' => 'lsi',
                ]);
            }
            foreach ($pmMemberAdd as $memberId) {
                AreaManager::create([
                    'area_id' => $requests['id'],
                    'member_id' => $memberId,
                    'type' => 'pm',
                ]);
            }
            unset($requests['lsi_members']);
            unset($requests['pm_members']);
            $this->crud->update($requests['id'], $requests);
            
            DB::commit();
            return redirect()->route('area.index');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
