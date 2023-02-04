<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TransactionPaymentRequest;
use App\Http\Traits\TransactionPaymentTrait;
use App\Models\PaymentMethod;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\Widget;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
        if(!backpack_user()->hasPermissionTo('Create Payment Transaction')){
            $this->crud->denyAccess(['create']);
        }
        if(!backpack_user()->hasPermissionTo('Read Payment Transaction')){
            $this->crud->denyAccess(['list', 'show']);
        }
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

        $this->crud->column('code');
        $this->crud->addColumn([
            'name' => 'transaction_id',
            'label' => 'Transaction Code',
            'entity' => 'transaction',
            'attribute' => 'code',
            'wrapper' => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('transaction/'.$entry->transaction_id.'/show');
                },
            ],
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
        if ($transaction_id == 0) abort(404);
        $transaction = Transaction::findOrfail($transaction_id);
        $check = Transaction::with(['transactionPayments', 'transactionProducts'])->find($transaction_id);
        $totalPrice =  0;
        if ($transaction->type == 'Bebas Putus') {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                $discount = 0;
                if ($item->discount_percentage > 0) {
                    $discount = $item->price * $item->discount_percentage / 100;
                } else  {
                    $discount = $item->discount_amount;
                }
                return $item->price * $item->quantity - $discount;
            });
        } else if ($transaction->type == 'Demokit' || $transaction->type == 'Display') {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                return $item->price * $item->quantity - ($item->price * $item->quantity * $item->discount_percentage / 100);
            });
        } else {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                return $item->price * $item->quantity;
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
        if ($transaction->type == 'Bebas Putus') {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                $discount = 0;
                if ($item->discount_percentage > 0) {
                    $discount = $item->price * $item->discount_percentage / 100;
                } else  {
                    $discount = $item->discount_amount;
                }
                return $item->price * $item->quantity - $discount;
            });
        } else if ($transaction->type == 'Demokit' || $transaction->type == 'Display') {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                return $item->price * $item->quantity - ($item->price * $item->quantity * $item->discount_percentage / 100);
            });
        } else {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                return $item->price * $item->quantity;
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

    protected function generateCode() {
        $lastPayment = TransactionPayment::orderBy('id', 'desc')->first();
        $lastPaymentCode = $lastPayment->code ?? 'PY-000000-0000';
        $paymentCode = explode('-', $lastPaymentCode)[2] + 1;
        $paymentCode = 'PY-' . date('ymd') . '-' . str_pad($paymentCode, 4, '0', STR_PAD_LEFT);
        return $paymentCode;
    }

    public function store()
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
            $requests['code'] = $this->generateCode();
            $payment = TransactionPayment::create($requests);
            $transaction = Transaction::with(['transactionPayments', 'transactionProducts', 'member'])->find($requests['transaction_id']);

            /* Check Stock */
            $transactionProducts = $transaction->transactionProducts;
            foreach ($transactionProducts as $transactionProduct) {
                $product = Stock::where('product_id', $transactionProduct->product_id)
                    ->where('branch_id', $transaction->branch_id)
                    ->first();
                if(!$product) {
                    throw new \Exception('Stock '. $transactionProduct->name.' '. $transactionProduct->model .' is not enough');
                }
                if ($product->quantity < $transactionProduct->quantity) {
                    throw new \Exception('Stock '. $transactionProduct->name.' '. $transactionProduct->model .' is not enough');
                }
            }

            $totalPrice =  0;
            if ($transaction->type == 'Normal') {
                $totalPrice = $transaction->transactionProducts->sum(function($item){
                    return $item->price * $item->quantity;
                });
            } else if ($transaction->type == 'Bebas Putus'){
                $totalPrice = $transaction->transactionProducts->sum(function($item) use ($transaction){
                    $discount = 0;
                    if ($item->discount_percentage > 0) {
                        $discount = $item->price * $item->quantity * $item->discount_percentage / 100;
                    } else {
                        $discount = $item->discount_amount;
                    }
                    return $item->price * $item->quantity - $discount;
                });
            } else {
                $totalPrice = $transaction->transactionProducts->sum(function($item){
                    return $item->price * $item->quantity - ($item->price * $item->quantity * $item->discount_percentage / 100);
                });
            }
            if ($transaction->transactionPayments->sum('amount') == $totalPrice) {
                $transaction->status_paid = true;
                $transaction->save();
                $lastPaymentDate = $transaction->transactionPayments->sortByDesc('payment_date')->first()->payment_date;
                $this->calculateBonus($transaction, $transaction->member, $lastPaymentDate);
                /* Minus stock */
                foreach ($transactionProducts as $transactionProduct) {
                    $stock = Stock::where('product_id', $transactionProduct->product_id)
                        ->where('branch_id', $transaction->branch_id)
                        ->first();
                    $stock->quantity = $stock->quantity - $transactionProduct->quantity;
                    $stock->save();
                    /* Add stock history */
                    if($transaction->type != "Stock"){
                        $stockHistory = new StockHistory();
                        $stockHistory->type = 'sales';
                        $stockHistory->branch_id = $transaction->branch_id;
                        $stockHistory->sales_on = $transaction->id;
                        $stockHistory->product_id = $transactionProduct->product_id;
                        $stockHistory->quantity = $transactionProduct->quantity;
                        $stockHistory->save();
                    } else {
                        // out to branch
                        $stockHistory = new StockHistory();
                        $stockHistory->type = 'out';
                        $stockHistory->branch_id = $transaction->branch_id;
                        $stockHistory->sales_on = $transaction->id;
                        $stockHistory->product_id = $transactionProduct->product_id;
                        $stockHistory->quantity = $transactionProduct->quantity;
                        $stockHistory->out_to = $transaction->member->branch->id;
                        $stockHistory->save();

                        // in to member branch
                        $stockNew = Stock::where('product_id', $transactionProduct->product_id)
                            ->where('branch_id', $transaction->member->branch->id)->first();
                        if(!$stockNew) {
                            $stockNew = new Stock();
                            $stockNew->branch_id = $transaction->member->branch->id;
                            $stockNew->product_id = $transactionProduct->product_id;
                            $stockNew->quantity = $transactionProduct->quantity;
                        } else {
                            $stockNew->quantity = $stockNew->quantity + $transactionProduct->quantity;
                        }
                        $stockNew->save();

                        $stockHistoryIn = new StockHistory();
                        $stockHistoryIn->type = 'in';
                        $stockHistoryIn->branch_id = $transaction->member->branch->id;
                        $stockHistoryIn->sales_on = $transaction->id;
                        $stockHistoryIn->product_id = $transactionProduct->product_id;
                        $stockHistoryIn->quantity = $transactionProduct->quantity;
                        $stockHistoryIn->in_from = $transaction->branch_id;
                        $stockHistoryIn->save();
                    }
                }
            }
            DB::commit();
            return redirect($this->crud->route);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('transaction.list_payment', $this->data);
    }

    public function createByImport($requests)
    {
        // $transaction = Transaction::findOrfail($requests['transaction_id']);
        $transaction = Transaction::with(['transactionPayments', 'transactionProducts'])->findOrFail($requests['transaction_id']);
        $totalPrice =  0;
        if ($transaction->type == 'Bebas Putus') {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                $discount = 0;
                if ($item->discount_percentage > 0) {
                    $discount = $item->price * $item->discount_percentage / 100;
                } else  {
                    $discount = $item->discount_amount;
                }
                return $item->price * $item->quantity - $discount;
            });
        } else if ($transaction->type == 'Demokit' || $transaction->type == 'Display') {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                return $item->price * $item->quantity - ($item->price * $item->quantity * $item->discount_percentage / 100);
            });
        } else {
            $totalPrice = $transaction->transactionProducts->sum(function($item){
                return $item->price * $item->quantity;
            });
        }
        if ($transaction->transactionPayments->sum('amount') >= $totalPrice) {
            return throw new Exception('Transaction already paid');
        }
        $transactionBill = $totalPrice - $transaction->transactionPayments->sum('amount');
        if($transactionBill < $requests['amount']) {
            return throw new Exception('Transaction bill is less than payment amount. bill : ' . $transactionBill . ', payment : ' . $requests['amount']);
        }
        $paymentMethodCust = PaymentMethod::where('name', $requests['payment_method'])->first();

        if(!$paymentMethodCust) {
            return throw new Exception('Payment method '.$requests['payment_method']. ' transaction id' . $requests['transaction_id'] . ' not found', 400);
        }
        $requests['payment_method_id'] = $paymentMethodCust->id;

        // make validator
        $validator = Validator::make($requests, [
            'transaction_id' => 'required|exists:transactions,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_account_number' => 'nullable',
            'photo_url' => 'nullable',
            'type' => 'required|in:Full,Partial',
            'amount' => function ($attribute, $value, $fail) use ($transaction, $requests) {
                $typePayment = $requests['type'];
                if ($typePayment = 'Partial') {
                    $totalBill = $transaction->transactionProducts->sum(function($item){
                        return $item->price * $item->quantity;
                    }) - $transaction->transactionPayments->sum('amount');
                    if ($totalBill < $value) {
                        $fail('The amount must be less than the total bill');
                    }
                } else {
                    $total = $transaction->transactionProducts->sum(function ($item) {
                        return $item->price * $item->quantity;
                    });
                    if ($total != $value) {
                        $fail('The amount must be equal to the total transaction');
                    }
                }
            },
            'payment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return throw new Exception($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $requests['payment_name'] = $paymentMethodCust->name;
            unset($requests['payment_method']);
            // status paid
            $requests['code'] = $this->generateCode();
            $requests['created_at'] = $requests['payment_date'];
            $requests['updated_at'] = $requests['payment_date'];
            $payment = TransactionPayment::updateOrCreate(['id' => $requests['id']], $requests);
            $transaction = $transaction = Transaction::with(['transactionPayments', 'transactionProducts'])->find($requests['transaction_id']);
            /* Check Stock */
            $transactionProducts = $transaction->transactionProducts;
            foreach ($transactionProducts as $transactionProduct) {
                $product = Stock::where('product_id', $transactionProduct->product_id)
                    ->where('branch_id', $transaction->branch_id)
                    ->first();
                if(!$product) {
                    throw new \Exception('Stock '. $transactionProduct->name.' '. $transactionProduct->model .' is not enough');
                }
                if ($product->quantity < $transactionProduct->quantity) {
                    throw new \Exception('Stock '. $transactionProduct->name.' '. $transactionProduct->model .' is not enough');
                }
            }
            if ($transaction->transactionPayments->sum('amount') == $totalPrice) {
                $transaction->status_paid = true;
                $transaction->save();
                $lastPaymentDate = $transaction->transactionPayments->sortByDesc('payment_date')->first()->payment_date;
                $this->calculateBonus($transaction, $transaction->member, $lastPaymentDate);

                /* Minus stock */
                foreach ($transactionProducts as $transactionProduct) {
                    $stock = Stock::where('product_id', $transactionProduct->product_id)
                        ->where('branch_id', $transaction->branch_id)
                        ->first();
                    $stock->quantity = $stock->quantity - $transactionProduct->quantity;
                    $stock->save();

                    /* Add stock history */
                    if($transaction->type != "Stock"){
                        $stockHistory = new StockHistory();
                        $stockHistory->type = 'sales';
                        $stockHistory->branch_id = $transaction->branch_id;
                        $stockHistory->sales_on = $transaction->id;
                        $stockHistory->product_id = $transactionProduct->product_id;
                        $stockHistory->quantity = $transactionProduct->quantity;
                        $stockHistory->created_at = $lastPaymentDate;
                        $stockHistory->save();
                    } else {
                        // out to branch
                        $stockHistory = new StockHistory();
                        $stockHistory->type = 'out';
                        $stockHistory->branch_id = $transaction->branch_id;
                        $stockHistory->sales_on = $transaction->id;
                        $stockHistory->product_id = $transactionProduct->product_id;
                        $stockHistory->quantity = $transactionProduct->quantity;
                        $stockHistory->out_to = $transaction->member->branch->id;
                        $stockHistory->created_at = $lastPaymentDate;
                        $stockHistory->save();

                        // in to member branch
                        $stockNew = Stock::where('product_id', $transactionProduct->product_id)
                            ->where('branch_id', $transaction->member->branch->id)->first();
                        if(!$stockNew) {
                            $stockNew = new Stock();
                            $stockNew->branch_id = $transaction->member->branch->id;
                            $stockNew->product_id = $transactionProduct->product_id;
                            $stockNew->quantity = $transactionProduct->quantity;
                        } else {
                            $stockNew->quantity = $stockNew->quantity + $transactionProduct->quantity;
                        }
                        $stockNew->created_at = $lastPaymentDate;
                        $stockNew->save();

                        $stockHistoryIn = new StockHistory();
                        $stockHistoryIn->type = 'in';
                        $stockHistoryIn->branch_id = $transaction->member->branch->id;
                        $stockHistoryIn->sales_on = $transaction->id;
                        $stockHistoryIn->product_id = $transactionProduct->product_id;
                        $stockHistoryIn->quantity = $transactionProduct->quantity;
                        $stockHistoryIn->in_from = $transaction->branch_id;
                        $stockHistoryIn->created_at = $lastPaymentDate;
                        $stockHistoryIn->save();
                    }
                }
            }
            DB::commit();
            return ;
        } catch (Exception $e) {
            DB::rollBack();
            return throw new Exception($e->getMessage());
        }
    }
}
