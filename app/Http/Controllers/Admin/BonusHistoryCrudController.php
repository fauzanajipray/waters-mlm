<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BonusHistoryRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class BonusHistoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BonusHistoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        $this->crud->setModel(\App\Models\BonusHistory::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/bonus-history');
        $this->crud->setEntityNameStrings('bonus history', 'bonus histories');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'label' => 'Member',
            'type' => 'relationship',
            'name' => 'member',
            'entity' => 'member',
            'attribute' => 'name',
            'model' => 'App\Models\Member',
            'wrapper' => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('member/'.$related_key.'/show');
                },
            ],
        ]);
        $this->crud->addColumn([
            'label' => 'Transaction',
            'type' => 'relationship',
            'name' => 'transaction',
            'entity' => 'transaction',
            'attribute' => 'code',
            'model' => "App\Models\Transaction",
            'pivot' => true,
            'wrapper' => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('transaction/'.$related_key.'/show');
                },
            ]
        ]); 
        $this->crud->column('level_id');
        $this->crud->column('bonus_type');
        $this->crud->column('bonus_percent');
        $this->crud->column('bonus')->value(function ($entry) {
            return "Rp. " . number_format($entry->bonus, 2);
        });
        $this->crud->column('created_at');
        $this->crud->column('updated_at');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - $this->crud->column('price')->type('number');
         * - $this->crud->addColumn(['name' => 'price', 'type' => 'number']); 
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
        $this->crud->setValidation([
            // 'name' => 'required|min:2',
        ]);

        CRUD::field('member_id');
        CRUD::field('transaction_id');
        CRUD::field('level_id');
        CRUD::field('bonus_type');
        CRUD::field('bonus_percent');
        CRUD::field('bonus');

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
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
}