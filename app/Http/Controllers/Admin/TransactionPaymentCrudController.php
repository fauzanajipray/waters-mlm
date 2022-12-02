<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TransactionPaymentRequest;
use App\Http\Traits\TransactionPaymentTrait;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class TransactionPaymentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TransactionPaymentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use TransactionPaymentTrait;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        $this->crud->setModel(\App\Models\TransactionPayment::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/transaction-payment');
        $this->crud->setEntityNameStrings('transaction payment', 'transaction payments');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
                
        $this->crud->column('transaction_id')->wrapper([
            'href' => function ($crud, $column, $entry, $related_key) {
                return backpack_url('transaction/'.$entry->transaction_id.'/show');
            },
        ]);
        $this->crud->column('payment_date');
        $this->crud->column('payment_name');
        $this->crud->column('payment_account_number')->label('Account Number');
        $this->crud->column('amount')->type('number_format');
        $this->crud->column('type');
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
        $this->crud->setValidation(TransactionPaymentRequest::class);
        Transaction::findOrfail(request()->transaction_id);
        
        $this->crud->addField([
            'name' => 'payment_method_id',
            'label' => 'Method',
            'type' => 'select2_from_array',
            'options' =>  PaymentMethod::get()->pluck('name', 'id')->toArray(),
            'tab' => 'Payment',
        ]);
        $this->crud->addField([
            'name' => 'payment_account_number',
            'label' => 'Account Number',
            'type' => 'text',
            'tab' => 'Payment',
        ]);
        // Bukti Pembayaran
        $this->crud->addField([
            'name' => 'photo_url',
            'label' => 'Payment Proof',
            'type' => 'image',
            'upload' => true,
            'crop' => true,
            'prefix' => 'storage/',
            'tab' => 'Payment',
        ]);
        $this->crud->addField([
            'name' => 'type',
            'type' => 'select2_from_array',
            'options' => ['Partial' => 'Partial', 'Full' => 'Full'],
            'allows_null' => false,
            'default' => 'Full',
            'tab' => 'Payment',
        ]);
        $data = Transaction::with(['transactionPayments', 'transactionProducts'])->where('id', request()->transaction_id)->first();
        if($data->transactionPayments->count() > 0){
            $maxPrice = $data->transactionProducts->sum(function($item){
                return $item->price * $item->quantity;
            }) - $data->transactionPayments->sum('amount');
        }else{
            $maxPrice = $data->transactionProducts->sum(function($item){
                return $item->price * $item->quantity;
            });
        }

        $this->crud->addField([
            'name' => 'amount',
            'label' => 'Total Amount',
            'type' => 'number_format',
            'prefix' => 'Rp.',
            'attributes' => [
                'min' => 0,
                'max' => $maxPrice,
            ],
            'dependencies' => ['type'],
            'tab' => 'Payment',
        ]);

        $this->crud->addField([
            'name' => 'payment_date',
            'label' => 'Payment Date',
            'type' => 'datetime_picker',
            'value' => date('Y-m-d H:i:s'),
        ]);
                
        Widget::add()->type('script')->content(asset('assets/js/admin/form/payment_transaction.js'));
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

    public function create(Request $request)
    {
        $this->crud->hasAccessOrFail('create');
        $request = $request->all();
        $transaction_id = $request['transaction_id'] ?? 0;
        $transaction = Transaction::findOrfail($transaction_id);
        $check = Transaction::with(['transactionPayments', 'transactionProducts'])->find($transaction_id);
        $totalPrice =  0;
        if ($transaction->type == 'Normal') {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                return $item->price * $item->quantity;
            });
        } else {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                return $item->price * $item->quantity - ($item->price * $item->quantity * $item->discount_percentage / 100);
            });
        }
        if ($check->transactionPayments->sum('amount') >= $totalPrice) {
            return redirect()->back()->with('error', 'Transaction has been paid');
        }
        if($check->transactionPayments->count() > 0){
            $this->crud->modifyField('type', [
                'type' => 'text',
                'value' => 'Partial',
                'allows_null' => false,
                'attributes' => [
                    'readonly' => 'readonly',
                ],
            ]);
        }
        $this->data['crud'] = $this->crud;
        $this->data['fields'] = $this->crud->getCreateFields();
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->crud->addField([
            'name' => 'transaction_id',
            'type' => 'hidden',
            'value' => $transaction_id
        ]);
        $this->crud->addField([
            'name' => 'transaction_total',
            'type' => 'hidden',
            'value' => $transaction_id
        ]);
        $this->crud->addField([
            'name' => 'transaction_code',
            'value' => $transaction->code,
            'type' => 'text',
            'attributes' => [
                'disabled' => 'disabled'
            ],
        ])->beforeField('payment_method_id');
        $totalPrice =  0;
        if ($transaction->type == 'Normal') {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                return $item->price * $item->quantity;
            });
        } else {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                return $item->price * $item->quantity - ($item->price * $item->quantity * $item->discount_percentage / 100);
            });
        }
        $this->crud->addField([
            'name' => 'transaction_total',
            'value' => $totalPrice,
            'type' => 'number_format',
            'attributes' => [
                'readonly' => 'readonly'
            ],
            'prefix' => 'Rp.',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6'
            ]
        ])->afterField('transaction_code');
        $transactionBill = $totalPrice - $transaction->transactionPayments->sum('amount');
        $this->crud->addField([
            'name' => 'transaction_bill',
            'value' => $transactionBill,
            'type' => 'number_format',
            'attributes' => [
                'readonly' => 'readonly'
            ],
            'prefix' => 'Rp.',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6'
            ]
        ])->afterField('transaction_total');
        return view('crud::create', $this->data);
    }

    public function store(Request $request)
    {
        $requests = request()->all();
        if ($requests['type'] == 'Full') {
            $requests['amount'] = $requests['transaction_bill'];
        }
        $this->crud->validateRequest($requests);
        DB::beginTransaction();
        try {
            $paymentMethodCust = PaymentMethod::find($requests['payment_method_id']);
            $requests['payment_name'] = $paymentMethodCust->name;
            // status paid
            $payment = TransactionPayment::create($requests);
            $transaction = Transaction::with(['transactionPayments', 'transactionProducts', 'member'])->find($requests['transaction_id']);
            $totalPrice =  0;
            if ($transaction->type == 'Normal') {
                $totalPrice = $transaction->transactionProducts->sum(function($item){
                    return $item->price * $item->quantity;
                });
            } else {
                $totalPrice = $transaction->transactionProducts->sum(function($item){
                    return $item->price * $item->quantity - ($item->price * $item->quantity * $item->discount_percentage / 100);
                });
            }
            if ($transaction->transactionPayments->sum('amount') == $totalPrice) {
                // dd($transaction);
                $transaction->status_paid = true;
                $transaction->save();
                $lastPaymentDate = $transaction->transactionPayments->sortByDesc('payment_date')->first()->payment_date;
                $this->calculateBonus($transaction, $transaction->member, $lastPaymentDate);
                
            }
            // dd('test');
            DB::commit();
            return redirect($this->crud->route);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function index(){
        $this->crud->hasAccessOrFail('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('transaction.list_payment', $this->data);
    }
}
