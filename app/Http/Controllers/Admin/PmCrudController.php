<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AreaRequest;
use App\Models\AreaManager;
use App\Models\Branch;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class AreaCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PmCrudController extends CrudController
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
        CRUD::setModel(AreaManager::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/area/pm');
        CRUD::setEntityNameStrings('PM Member', 'PM members');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('member_id');
        CRUD::column('area_id');
        CRUD::column('created_at');
        CRUD::column('updated_at');

        $this->crud->addClause('where', 'type', 'PM');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->addField([
            'name' => 'member_id',
            'type' => 'select2_from_ajax',
            'entity' => 'member',
            'attribute' => 'text',
            'data_source' => url('members/only-actived'),
            'delay' => 500
        ]);
        $this->crud->addField([
            'label' => 'Area',
            'type' => 'select2',
            'name' => 'area_id',
            'entity' => 'area',
            'attribute' => 'name',
            'model' => 'App\Models\Area',
        ]);
        $this->crud->addField([
            'name' => 'type',
            'type' => 'hidden',
            'value' => 'PM',
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
        $this->crud->addField([
            'name' => 'member_name',
            'type' => 'text',
            'label' => 'Member',
            'disabled' => 'disabled',
            'value' => $this->crud->getCurrentEntry()->member->name,
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);
        $this->crud->addField([
            'label' => 'Area',
            'type' => 'select2',
            'name' => 'area_id',
            'entity' => 'area',
            'attribute' => 'name',
            'model' => 'App\Models\Area',
        ]);
    }

}
