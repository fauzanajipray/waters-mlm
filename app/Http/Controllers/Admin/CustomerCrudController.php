<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CustomerInlineCreateRequest;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Backpack\CRUD\app\Http\Controllers\CrudController;
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
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\InlineCreateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        $this->crud->setModel(\App\Models\Customer::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/customer');
        $this->crud->setEntityNameStrings('customer', 'customers');
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
        $this->crud->column('city');
        $this->crud->column('phone');
        $this->crud->column('member_id');
        $this->crud->column('is_member');
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
        $this->crud->setValidation(CustomerRequest::class);

        // $this->crud->field('member_id');
        $this->crud->addField([
            'name' => 'member_id',
            'type' => 'select2_from_ajax',
            'entity' => 'member',
            'attribute' => 'text',
            'data_source' => url('api/members/only-actived'),
            'delay' => 500
        ]);
        $this->crud->field('name');
        $this->crud->field('address');
        $this->crud->field('city');
        $this->crud->field('phone')->type('number');

       
        // Widget::add()->type('script')->content(asset('assets/js/admin/form/customer.js'));
    }

    // public function store(Request $request)
    // {   
    //     dd($request->all());
    // }

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
        $this->crud->removeField('is_member');
    }

    public function customerbyMemberID(Request $request){
        $search_term = $request->input('q');
        $memberIdForm = request()->form[3];
        if($search_term && $memberIdForm['name'] == 'member_id' && $memberIdForm['value']) {
            $customers = Customer::
                where('member_id', $memberIdForm['value'])
                ->where('is_member', "0")
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('phone', 'like', '%'.$search_term.'%')
                ->paginate(10);
            $customers->map(function($customer){
                $customer->name = $customer->name . ' - ' . $customer->phone;
                return $customer;
            });
        } else if (!$search_term && $memberIdForm['name'] == 'member_id' && $memberIdForm['value']){ 
            $customers = Customer::
                where('member_id', $memberIdForm['value'])
                ->where('is_member', "0")
                ->paginate(10);
            $customers->map(function($customer){
                $customer->name = $customer->name . ' - ' . $customer->phone;
                return $customer;
            });
        } else {
            $customers = Customer::
                where('is_member', "0")->paginate(10);
            $customers->map(function($customer){
                $customer->name = $customer->name . ' - ' . $customer->phone;
                return $customer;
            });
        }
        return $customers;
    }
}
