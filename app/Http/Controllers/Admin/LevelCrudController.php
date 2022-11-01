<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LevelRequest;
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
        CRUD::setModel(\App\Models\Level::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/level');
        CRUD::setEntityNameStrings('level', 'levels');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('code');
        CRUD::column('name');
        CRUD::column('description');
        CRUD::column('minimum_downline');
        CRUD::column('minimum_sold_by_downline');
        CRUD::column('minimum_sold');
        CRUD::column('ordering_level');
        CRUD::column('bp_percentage');
        CRUD::column('bs_percentage');
        CRUD::column('or_percentage');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'code' => 'required|unique:levels',
            'name' => 'required',
            'description' => 'required',
            'minimum_downline' => 'required|numeric',
            'minimum_sold_by_downline' => 'required|numeric',
            'minimum_sold' => 'required|numeric',
            'ordering_level' => 'required|numeric',
            'bp_percentage' => 'required|numeric|between:0,100',
            'bs_percentage' => 'required|numeric|between:0,100',
            'or_percentage' => 'required|numeric|between:0,100',
        ]);

        CRUD::field('code');
        CRUD::field('name');
        CRUD::field('description');
        CRUD::field('minimum_downline');
        CRUD::field('minimum_sold_by_downline');
        CRUD::field('minimum_sold');
        CRUD::field('ordering_level');
        CRUD::field('bp_percentage')->type('number');
        CRUD::field('bs_percentage')->type('number');
        CRUD::field('or_percentage')->type('number');
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
        $this->crud->setValidation([
            'code' => [
                'required', 
                Rule::unique('levels')->ignore($this->crud->getCurrentEntryId())],
        ]);
    }
}
