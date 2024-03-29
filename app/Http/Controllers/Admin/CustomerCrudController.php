<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CustomerInlineCreateRequest as InlineCreateRequest;
use App\Http\Requests\CustomerRequest as StoreRequest;
use App\Http\Requests\CustomerUpdateRequest as UpdateRequest;
use App\Models\Customer;
use App\Models\Member;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;

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
        if(!backpack_user()->hasPermissionTo('Create Customer')){
            $this->crud->denyAccess(['create']);
        }
        if(!backpack_user()->hasPermissionTo('Read Customer')){
            $this->crud->denyAccess(['list', 'show']);
        }
        if(!backpack_user()->hasPermissionTo('Update Customer')){
            $this->crud->denyAccess(['update']);
        }
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
        $this->crud->column('is_member')->value(function ($value) {
            return ($value->is_member) ? 'Yes' : 'No';
        });
        $this->crud->column('created_at');
        $this->crud->column('updated_at');

        $this->crud->addButtonFromModelFunction('line', 'deleteButton', 'deleteButton', 'end');

        Widget::add()->type('script')->content(asset('assets/js/admin/form/customer.js'));
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setValidation(StoreRequest::class);
        $this->crud->addField([
            'name' => 'member_id',
            'type' => 'select2_from_ajax',
            'entity' => 'member',
            'attribute' => 'text',
            'data_source' => url('members/only-actived'),
            'delay' => 500
        ]);
        $this->crud->field('name');
        $this->crud->field('address');
        $this->crud->field('city');
        $this->crud->field('phone')->type('number');

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
        $this->crud->setValidation(UpdateRequest::class);
        $this->crud->removeField('member_id');
        $customer = $this->crud->getCurrentEntry();
        $this->crud->addField([
            'name' => 'member_name',
            'type' => 'text',
            'label' => 'Member',
            'value' => $customer->member->name . ' - ' . $customer->member->phone,
            'attributes' => [
                'disabled' => 'disabled'
            ]
        ])->beforeField('name');
    }

    protected function setupInlineCreateOperation(){

        $this->crud->setValidation(InlineCreateRequest::class);
        $this->crud->removeField('member_id');
        $requestsMain = request('main_form_fields');
        $memberID = $requestsMain[0]['value'];

        $member = Member::where('id', $memberID)->first();

        $this->crud->addField([
            'name' => 'member_id',
            'type' => 'hidden',
            'value' => $memberID,
        ]);
        $this->crud->addField([
            'name' => 'member_name',
            'type' => 'text',
            'value' => $member->name . ' - ' . $member->phone,
            'attributes' => [
                'readonly' => 'readonly',
                'disabled' => 'disabled',
            ],
        ])->beforeField('name');
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

    public function deleteCustomer($id){
        DB::beginTransaction();
        try {
            $customer = Customer::with('transactions')
                ->where('id', $id)
                ->first();
            if($customer->is_member == 1){
                Alert::error('Customer is member');
                return redirect()->back()->with('error', 'Customer is member, not allowed to delete');
            }
            if($customer->transactions->count() > 0){
                Alert::error('Customer has transactions');
                return redirect()->back()->with('error', 'Customer has transactions, not allowed to delete');
            }
            $customer->delete();
            DB::commit();
            Alert::success('Customer deleted');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getCustomerIsMember(Request $request)
    {
        $customer = Customer::
            where('member_id', $request->member_id)
            ->where('is_member', '1')
            ->first();
        if ($customer) {
            return response()->json([
                'status' => true,
                'data' => $customer->address,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found!',
            ]);
        }
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        return view('vendor.backpack.crud.list_with_error_head', $this->data);
    }
}
