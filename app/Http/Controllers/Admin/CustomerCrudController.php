<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CustomerInlineCreateRequest;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Http\Request;

/**
 * Class CustomerCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CustomerCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Customer::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/customer');
        CRUD::setEntityNameStrings('customer', 'customers');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('name');
        CRUD::column('address');
        CRUD::column('city');
        CRUD::column('no_hp');
        CRUD::column('member_id');
        CRUD::column('is_member');
        CRUD::column('created_at');
        CRUD::column('updated_at');

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
        CRUD::setValidation(CustomerRequest::class);

        CRUD::field('name');
        CRUD::field('address');
        CRUD::field('city');
        CRUD::field('no_hp');
        CRUD::field('member_id');
        CRUD::field('is_member');

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

    protected function setupInlineCreateOperation(){

        $this->crud->setValidation(CustomerInlineCreateRequest::class);
        // $this->crud->removeField('member_id');
        $this->crud->removeField('is_member');
        $this->crud->addField([
            'name' => 'member_id',
            'type' => 'number',
            'attributes' => [
                // 'value' => $member_id,
                // 'class' => 'd-none'
            ]
        ]);
    }

    public function customerbyMemberID(Request $request){
        $search_term = $request->q;
        $member_id = $request->member_id;
        if($search_term) {
            $customers = Customer::
                where('member_id', $member_id)
                // ->where('name', 'like', '%'.$search_term.'%')
                // ->orWhere('no_hp', 'like', '%'.$search_term.'%')
                ->paginate(10);
            $customers->map(function($customer){
                $customer->text = $customer->name . ' - ' . $customer->no_hp;
                return $customer;
            });
        } else{
            $customers = Customer::where('member_id', $member_id)->paginate(10);
        }
        
        return $customers;
    }

    // public function storeInlineCreate(){

    // }
}
