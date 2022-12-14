<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Requests\TransactionRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Level;
use App\Models\Member;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;

/**
 * Class TransactionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TransactionDisplayCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \App\Http\Traits\TransactionTrait;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        $this->crud->setModel(Transaction::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/transaction-display');
        $this->crud->setEntityNameStrings('display transaction', 'display transactions');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->viewAfterContent = ['image_preview_helper'];
        $this->crud->firstCellNonFlex = true;
        $this->crud->addColumns([
            'code',
            'transaction_date', 
            [
                'name' => 'member_numb',
                'label' => 'Unique Number',
            ], 
            'member_name',
            'total_price', 
            'id_card',
            'member_id',
            [
                'name' => 'level_id',
                'label' => 'Level',
                'entity' => 'level',
                'attribute' => 'name' ,
                'model' => Level::class,
            ],
        ]);

        $this->crud->addButtonFromModelFunction('line', 'letter_road', 'letterRoad', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'invoice', 'invoice', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'add_payment', 'buttonAddPayment', 'beginning');
        
        $this->crud->addClause('where', 'type', 'Display');
    }

    protected function setupShowOperation(){
        $this->setupListOperation();
        $this->crud->addColumns([
            [
                'name' => 'created_by',
                'label' => 'Created By',
                'entity' => 'createdBy',
                'attribute' => 'name' ,
                'model' => User::class,
            ],
            [
                'name' => 'updated_by',
                'label' => 'Updated By',
                'entity' => 'updatedBy',
                'attribute' => 'name' ,
                'model' => User::class,
            ],            
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setValidation(TransactionRequest::class);

        Widget::add()->type('script')->content(asset('assets/js/admin/form/transaction.js'));

        $this->crud->addField([
            'name' => 'transaction_date',
            'type' => 'datetime_picker',
            'label' => 'Date',
            'date_picker_options' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm-yyyy',
                'language' => 'en'
            ],
            'default' => date('d-m-Y H:i:s'),
        ]);
        
        $this->crud->addField([
            'name' => 'member_id',
            'type' => 'select2_from_ajax',
            'entity' => 'member',
            'attribute' => 'name',
            'data_source' => url('members/branch-owner'),
            'delay' => 500
        ]);
        
        /* Revision 1 
        $this->crud->addField([
            'name' => 'customer_id',
            'type' => 'relationship',
            'attribute' => 'name',
            'ajax' => true,
            'inline_create' => [
                'entity' => 'customer',
                'create_route' => route("customer-inline-create-save"),
                'modal_route' => route("customer-inline-create"),
                'modal_class' => 'modal-dialog modal-lg',
                'include_main_form_fields' => ['member_id'], // pass certain fields from the main form to the modal, get them with: request('main_form_fields')
            ],
            /// AJAX OPTIONAL
            'dependencies' => ['member_id', 'is_member'],
            'data_source' => url('customer/get-customer-by-member-id'),
            'placeholder' => 'Select a customer',
        ]);
        */
        
        $this->crud->addField([
            'name' => 'shipping_address',
            'type' => 'textarea',
            'label' => 'Shipping Address',
            'attributes' => [
                'rows' => 3,
            ],
            'dependencies' => ['customer_id']
        ]);

        $this->crud->addField([
            'name' => 'is_member',
            'type' => 'hidden',
            'label' => 'Member is customer',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-12'
            ], 
            'value' => 1,
        ]);

        $this->crud->field('shipping_notes');

        $this->crud->addFields([
            [
                'name' => 'branch_id',
                'label' => 'Branch',
                'type' => 'select2_from_ajax',
                'entity' => 'branch',
                'attribute' => 'name',
                'data_source' => url('branches/transaction-display'),
                'delay' => 500,
                'method' => 'POST',
                'tab' => 'Product',
                'include_all_form_fields' => true,
            ],
            [
                'name' => 'product_id',
                'type' => 'select2_from_ajax',
                'label' => 'Product',
                'entity' => 'product',
                'attribute' => 'name',
                'data_source' => url('product/get-display-products'),
                'wrapperAttributes' => [
                    'class' => 'form-group col-md-12'
                ],
                'dependencies' => ['member_id', 'branch_id'],
                'include_all_form_fields' => ['member_id'],
                'method' => 'POST',
                'tab' => 'Product',
            ],
            [
                'name' => 'quantity',
                'type' => 'number',
                'label' => 'Quantity',
                'allows_null' => false,
                'wrapperAttributes' => [
                    'class' => 'form-group col-md-6'
                ],
                'value' => 1,
                'tab' => 'Product',
                'attributes' => [
                    'readonly' => 'readonly',
                ]
            ],
            [
                'name' => 'discount_percentage',
                'type' => 'number',
                'label' => 'Discount',
                'allows_null' => false,
                'wrapperAttributes' => [
                    'class' => 'form-group col-md-6'
                ],
                'attributes' => [
                    'min' => 0,
                    'max' => 100,
                    'readonly' => 'readonly',
                ],
                'default' => 0,
                'hint' => 'Discount percentage for this product',
                'suffix' => '%',
                'default' => '50',
                'tab' => 'Product',
            ],
            [
                'name' => 'product_notes',
                'type' => 'textarea',
                'label' => 'Product Notes',
                'wrapperAttributes' => [
                    'class' => 'form-group col-md-12'
                ],
                'tab' => 'Product',
            ],
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
            'name' => 'code',
            'type' => 'text',
            'label' => 'Code',
            'attributes' => [
                'readonly' => 'readonly',
            ],
        ]);
        $this->setupCreateOperation();
        $this->crud->removeField('qty');
        $this->crud->addField([
            'name' => 'qty',
            'type' => 'number_format',
            'label' => 'Quantity',
        ]);
    }

    public function edit($id)
    {   
        $this->crud->hasAccessOrFail('update');

        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['fields'] = $this->crud->getUpdateFields($id);
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['id'] = $id;

        $this->crud->modifyField('qty', [
            'value' => optional($this->data['entry'])->qty_sold ?? 1,
        ]);
        $this->crud->modifyField('code', [
            'value' => optional($this->data['entry'])->code ?? $this->generateCode(),
        ]);
        return view('crud::edit', $this->data);
    }

    public function create(Request $request)
    {
        $this->crud->hasAccessOrFail('create');
        
        $this->data['crud'] = $this->crud;
        $this->data['fields'] = $this->crud->getCreateFields();
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->crud->modifyField('transaction_date', [
            'value' => date('Y-m-d H:i:s'),
        ]);
        if ($request->query('member_id')) {
            $this->crud->modifyField('member_id', [
                'value' => $request->query('member_id'),
            ]);
        }
        return view('crud::create', $this->data);
    }
    
    public function update()
    {
        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        return redirect($this->crud->route);
    }

    public function store(Request $request)
    {
        try {
            $requests = $request->all();
            $this->crud->validateRequest($requests);
            if(!isset($requests['products'])) {
                $products = [
                    [
                        'product_id' =>  $requests['product_id'],
                        'quantity' =>  $requests['quantity'],
                        'product_notes' =>  $requests['product_notes'],
                        'discount_percentage' =>  $requests['discount_percentage'] ,
                    ],
                ];
            } else {
                $products = $requests['products'];
            }
            
            foreach ($products as $key => $item) {
                for ($key2=$key; $key2 < count($products); $key2++) { 
                    if($item['product_id'] == $products[$key2]['product_id'] && $key != $key2){
                        $errors['products.'.$key2.'.product_id'] = 'Product '.$item['product_id'].' already taken';
                    }
                }
            }
            if ($requests['is_member'] == 1 && $requests['member_id']) {
                $customer = Customer::where('member_id', $requests['member_id'])->first();
                if ($customer) {
                    $requests['customer_id'] = strval($customer->id);
                } else {
                    $errors['customer_id'] = 'Customer not found';
                }
            } else if (!$requests['customer_id']) {
                $errors['customer_id'] = 'Customer is required';
            }
            if (isset($errors)) return redirect()->back()->withErrors($errors)->withInput();
            $member = Member::with(['upline' => function($query) {
                $query->with(['upline' => function($query) {
                    $query->with('level');
                }]);
            }])->find($requests['member_id']);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
        DB::beginTransaction();
        try {
            $totalPrice = 0;
            foreach ($products as $key => $item) {
                $product = Product::find($item['product_id']);
                $totalPrice += $product->price * $item['quantity'] - ($product->price * $item['quantity'] * $item['discount_percentage'] / 100);
            }
            $requests['code'] = $this->generateCode();
            $requests['id_card'] = $member->id_card;
            $requests['member_name'] = $member->name;
            $requests['member_numb'] = $member->member_numb;
            $requests['level_id'] = $member->level_id;
            $requests['total_price'] = $totalPrice;
            $requests['created_by'] = backpack_user()->id;
            $requests['updated_by'] = backpack_user()->id;
            $requests['type'] = 'Display';
            $requests['stock_from'] = Branch::find($requests['branch_id'])->name;

            $transaction = Transaction::create($requests);
            // Save Log Product Sold
            foreach ($products as $key => $item) {
                $product = Product::find($item['product_id']);
                $tp = TransactionProduct::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'model' => $product->model,
                    'price' => $product->price,
                    'capacity' => $product->capacity,
                    'quantity' => $item['quantity'],
                    'product_notes' => $item['product_notes'],
                    'discount_percentage' => $item['discount_percentage'],
                ]);
                $transactionProduct[] = $tp->toArray();
            }
            $requests['transaction_id'] = $transaction->id;
            Alert::success(trans('backpack::crud.insert_success'))->flash();
            DB::commit();
            return redirect($this->crud->route);
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error("Something when wrong")->flash();
            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }


    public function show($id)
    {
        $this->crud->hasAccessOrFail('show');

        $this->data['entry'] = Transaction::with('transactionPayments')->find($id);
        $this->data['crud'] = $this->crud;
        $this->data['products'] = TransactionProduct::where('transaction_id', $id)->get();
        return view('transaction.show', $this->data);
    }
}
