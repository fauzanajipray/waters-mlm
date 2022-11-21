<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Requests\TransactionRequest;
use App\Models\Customer;
use App\Models\Level;
use App\Models\Member;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionProduct;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;
use Barryvdh\DomPDF\Facade\Pdf;

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
    use \App\Http\Traits\TransactionTrait;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        $this->crud->setModel(Transaction::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/transaction');
        $this->crud->setEntityNameStrings('transaction', 'transactions');
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

        $this->crud->removeButton('update');
        $this->crud->addButtonFromModelFunction('line', 'letter_road', 'letterRoad', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'invoice', 'invoice', 'beginning');

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

        $product = Product::select('id', 'name', 'model', 'price')->orderBy('name', 'ASC')->get();
        $product = $product->map(function($item){
            $item->name = $item->name . ' | ' . $item->model . ' | ' . 'Rp ' . formatNumber($item->price);
            return $item;
        });

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
        ]);
        $this->crud->addField([
            'name' => 'member_id',
            'type' => 'select2_from_ajax',
            'entity' => 'member',
            'attribute' => 'text',
            'data_source' => url('members/only-actived'),
            'delay' => 500
        ]);
        
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

        $this->crud->addField([
            'name' => 'is_member',
            'type' => 'checkbox',
            'label' => 'Member is customer',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-12'
            ], 
            'value' => 1,
        ]);

        $this->crud->addField([
            'name' => 'products',
            'label' => 'Products',
            'type' => 'repeatable',
            'fields' => [
                [
                    'name' => 'product_id',
                    'type' => 'select2_from_array',
                    'label' => 'Product',
                    'options' => $product->pluck('name', 'id')->toArray(),
                    'allows_null' => false,
                    'wrapperAttributes' => [
                        'class' => 'form-group col-md-6'
                    ],
                ],
                [
                    'name' => 'quantity',
                    'type' => 'number',
                    'label' => 'Quantity',
                    'allows_null' => false,
                    'wrapperAttributes' => [
                        'class' => 'form-group col-md-6'
                    ],
                ]
            ],
            // optional
            'new_item_label'  => 'Add Product', // customize the text of the button
            'init_rows' => 1, 
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
        $this->crud->modifyField('transaction_date', [
            'value' => optional($this->data['entry'])->transaction_date ?? date('Y-m-d'),
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
            $products = $requests['products'];
            $this->crud->validateRequest($requests);
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
                $totalPrice += $product->price * $item['quantity'];
            }
            $requests['code'] = $this->generateCode();
            $requests['id_card'] = $member->id_card;
            $requests['member_name'] = $member->name;
            $requests['member_numb'] = $member->member_numb;
            $requests['level_id'] = $member->level_id;
            $requests['total_price'] = $totalPrice;
            $requests['created_by'] = backpack_user()->id;
            $requests['updated_by'] = backpack_user()->id;

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
                ]);
                $transactionProduct[] = $tp->toArray();
            }
            $requests['transaction_id'] = $transaction->id;
            $this->calculateBonus($requests, $member);
            $this->levelUpMember($member->id);
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

        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['crud'] = $this->crud;
        $this->data['products'] = TransactionProduct::where('transaction_id', $id)->get();
        return view('transaction.show', $this->data);
    }
}
