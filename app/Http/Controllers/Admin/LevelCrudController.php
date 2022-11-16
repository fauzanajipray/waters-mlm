<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LevelRequest;
use App\Http\Requests\LevelRequestUpdate;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Validation\Rule;

/**
 * Class LevelCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LevelCrudController extends CrudController
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
        $this->crud->setModel(\App\Models\Level::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/level');
        $this->crud->setEntityNameStrings('level', 'levels');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->column('code');
        $this->crud->column('name');
        $this->crud->column('description');
        $this->crud->column('minimum_downline');
        $this->crud->column('minimum_sold_by_downline');
        $this->crud->column('minimum_sold');
        $this->crud->column('ordering_level');
        $this->crud->column('bp_percentage')->label('BP Percentage');
        $this->crud->column('gm_percentage')->label('GM Percentage');
        $this->crud->column('or_percentage')->label('OR Percentage');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setValidation(LevelRequest::class);

        $this->crud->field('code');
        $this->crud->field('name');
        $this->crud->field('description');
        $this->crud->field('minimum_downline');
        $this->crud->field('minimum_sold_by_downline');
        $this->crud->field('minimum_sold');
        $this->crud->field('ordering_level');
        $this->crud->field('bp_percentage')->type('number')->label('BP Percentage');
        $this->crud->field('gm_percentage')->type('number')->label('GM Percentage');
        $this->crud->field('or_percentage')->type('number')->label('OR Percentage');
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->crud->setValidation(LevelRequestUpdate::class);
        $this->setupCreateOperation();
        $this->crud->setValidation([
            'code' => ['required', Rule::unique('levels')->ignore($this->crud->getCurrentEntryId())],
            'name' => ['required', Rule::unique('levels')->ignore($this->crud->getCurrentEntryId())],
        ]);
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
        $this->crud->addColumns(['updated_at', 'created_at']);
    }
}
