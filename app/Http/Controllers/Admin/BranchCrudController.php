<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

/**
 * Class BranchCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BranchCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Branch::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/branch');
        CRUD::setEntityNameStrings('branch', 'branches');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->column('name');
        $this->crud->column('address');
        $this->crud->column('type');      
        $this->crud->addColumn([
            'name' => 'member',
            'label' => 'Owner',
            'type' => 'relationship',
            'attribute' => 'name',
            'entity' => 'member',
            'model' => 'App\Models\Member',
            'wrapper' => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('member/' . $related_key . '/show');
                },
            ],
        ]);
        // $this->crud->addButtonFromModelFunction('line', 'add_owner', 'addOwnerButton', 'end');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $branch = Branch::count();
        CRUD::setValidation(BranchRequest::class);
        $this->crud->field('name');
        $this->crud->addField([
            'name' => 'type',
            'type' => 'select_from_array',
            'options' => ($branch == 0) ? ['PUSAT' => 'PUSAT', 'CABANG' => 'CABANG', 'STOKIST' => 'STOKIST'] : ['CABANG' => 'CABANG', 'STOKIST' => 'STOKIST'],
            'allows_null' => false,
            'default' => 'CABANG',
        ]); 
        $this->crud->field('address');
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
    }

    public function memberNotExist(Request $request){
        
        $search_term = $request->input('q');
        $member_type = request()->form[3];
        
        if($member_type['name'] != 'member_type') {
            $member_type = request()->form[4];
            if($member_type['name'] != 'member_type') {
                return response()->json(['results' => []]);
            }
        }

        if($search_term) {
            $branch = Branch::whereDoesntHave('member')
                ->where('name', 'like', '%'.$search_term.'%')
                ->type($member_type['value'])
                ->get();

        } else {
            $branch = Branch::whereDoesntHave('member')
                ->type($member_type['value'])
                ->get();
        }
        return $branch;
    }

    public function memberExist(Request $request){
        
        $search_term = $request->input('q');

        if($search_term) {
            $branch = Branch::whereHas('member')
                ->where('name', 'like', '%'.$search_term.'%')
                ->get();

        } else {
            $branch = Branch::whereHas('member')
                ->get();
        }
        return $branch;
    }
}
