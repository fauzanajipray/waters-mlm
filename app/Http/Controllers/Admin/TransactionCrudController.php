<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Requests\TransactionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TransactionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TransactionCrudController extends CrudController
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
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/transaction');
        CRUD::setEntityNameStrings('Transaction', 'Transactions');

        $this->crud->transactions = [
            [
                'product_name' => 'Han Waters Satu',
                'member_name' => User::where('id', 1)->first()->name ?? null,
                'qty' => 1,
                'price' => 10000000,
                'total_price' => 10000000,
                'date' => '2 Jan 2022',
                'date_real' => '2022-01-02'
            ], 
            [
                'product_name' => 'Han Waters Satu',
                'member_name' => User::where('id', 1)->first()->name ?? null,
                'qty' => 2,
                'price' => 10000000,
                'total_price' => 20000000,
                'date' => '12 Aug 2022',
                'date_real' => '2022-08-12'
            ], 
            [
                'product_name' => 'Han Waters Dua',
                'member_name' => User::where('id', 2)->first()->name ?? null,
                'qty' => 2,
                'price' => 9000000,
                'total_price' => 18000000,
                'date' => '18 May 2022',
                'date_real' => '2022-05-18'
            ],
            [
                'product_name' => 'Han Waters Dua',
                'member_name' => User::where('id', 2)->first()->name ?? null,
                'qty' => 3,
                'price' => 9000000,
                'total_price' => 27000000,
                'date' => '17 June 2022',
                'date_real' => '2022-06-07'
            ]
        ];
    }

    public function getColumns(){
        CRUD::column('date')->label('Date')
        ->value(function($entry){
            return optional($this->crud->transactions[$entry->id - 1] ?? null)['date'] ?? null;
        });
        CRUD::column('product_name')->label('Product Name')
        ->value(function($entry){
            return optional($this->crud->transactions[$entry->id - 1] ?? null)['product_name'] ?? null;
        });
        CRUD::column('member_name')->label('Member Name')
        ->value(function($entry){
            return optional($this->crud->transactions[$entry->id - 1] ?? null)['member_name'] ?? null;
        });
        CRUD::column('qty')->label('Qty')->value(function($entry){
            return formatNumber(optional($this->crud->transactions[$entry->id - 1] ?? null)['qty'] ?? null);
        });
        CRUD::column('price')->label('Price')->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->transactions[$entry->id - 1] ?? null)['price'] ?? null);
        });
        CRUD::column('total_price')->label('Total Price')  ->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->transactions[$entry->id - 1] ?? null)['total_price'] ?? null);
        });
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->getColumns();
    }


    protected function setupShowOperation(){
        $this->crud->set('show.setFromDb', false);
        $this->getColumns();
        $this->crud->column($this->crud->model->getCreatedAtColumn())->type('datetime');
        $this->crud->column($this->crud->model->getUpdatedAtColumn())->type('datetime');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(TransactionRequest::class);
        CRUD::field('date')->type('fixed_date_picker');
        CRUD::field('product_name')->type('select2_from_array')->options(['Han Waters Satu' => 'Han Waters Satu', 'Han Waters Dua' => 'Han Waters Dua', 'Han Waters Tiga' => 'Han Waters Tiga', 'Han Waters Empat' => 'Han Waters Empat']);
        CRUD::field('member_name')->type('select2_from_array')->options(User::select('name')->get()->mapWithKeys(function($entry){
            return [$entry->name => $entry->name];
        }));
        CRUD::field('qty')->type('number_format');

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

    public function store()
    {
        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        return redirect($this->crud->route);
    }

    public function edit($id)
    {
        $this->crud->hasAccessOrFail('update');
        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        // get the info for that entry

        $this->data['entry'] = $this->crud->getEntryWithLocale($id);
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;
        $this->data['id'] = $id;

        $fields = ['product_name', 'member_name', 'qty'];

        foreach($fields as $field){
            $this->crud->modifyField($field, ['value' => optional($this->crud->transactions[$this->data['entry']->id - 1] ?? null)[$field] ?? null]);
        }

        $this->crud->modifyField('date', ['value' => optional($this->crud->transactions[$this->data['entry']->id - 1] ?? null)['date_real'] ?? null]);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    public function update()
    {
        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        return redirect($this->crud->route);
    }

    public function destroy($id)
    {
        return 1;
    }
}
