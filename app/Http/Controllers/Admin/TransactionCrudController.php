<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Requests\TransactionRequest;
use App\Models\Member;
use App\Models\Product;
use App\Models\Transaction;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\Facades\Alert;

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
        $this->crud->setModel(Transaction::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/transaction');
        $this->crud->setEntityNameStrings('transaction', 'transactions');

    }

    public function getColumns(){
        $this->crud->column('date')->label('Date')
        ->value(function($entry){
            return optional($this->crud->transactions[$entry->id - 1] ?? null)['date'] ?? null;
        });
        $this->crud->column('product_name')->label('Product Name')
        ->value(function($entry){
            return optional($this->crud->transactions[$entry->id - 1] ?? null)['product_name'] ?? null;
        });
        $this->crud->column('member_name')->label('Member Name')
        ->value(function($entry){
            return optional($this->crud->transactions[$entry->id - 1] ?? null)['member_name'] ?? null;
        });
        $this->crud->column('qty')->label('Qty')->value(function($entry){
            return formatNumber(optional($this->crud->transactions[$entry->id - 1] ?? null)['qty'] ?? null);
        });
        $this->crud->column('price')->label('Price')->value(function($entry){
            return 'Rp '. formatNumber(optional($this->crud->transactions[$entry->id - 1] ?? null)['price'] ?? null);
        });
        $this->crud->column('total_price')->label('Total Price')  ->value(function($entry){
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
        // $this->getColumns();
        $this->crud->viewAfterContent = ['image_preview_helper'];
        $this->crud->firstCellNonFlex = true;

        $this->crud->addColumns([
            'code',
            'transaction_date', 
            // 'status',
            'member_numb', 
            'member_name', 
            'product_name', 
            'qty_sold', 
            'unit_price', 
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
            'product_id',
            'product_model',
        ]);

        $this->crud->removeButton('update');
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
        // $users = User::with('member')->whereHas('member')->orderBy('name', 'ASC')->get();
        // $users = $users->map(function($item){
        //     $item->name = $item->member->member_numb . ' - ' . $item->name;
        //     return $item;
        // });

        $members = Member::select('id', 'member_numb', 'name')->orderBy('name', 'ASC')->get();
        $members = $members->map(function($item){
            $item->name = $item->member_numb . ' - ' . $item->name;
            return $item;
        });

        $this->crud->addField([
            'name' => 'transaction_date',
            'type' => 'date_picker',
            'label' => 'Date',
            'date_picker_option' => [
                'todayBtn' => 'linked',
                'format'   => 'dd-mm-yyyy',
                'language' => 'en'
            ],]);
        $this->crud->addField([
            'name' => 'product_id',
            'type' => 'select2_from_array',
            'label' => 'Product',
            'options' => $product->pluck('name', 'id')->toArray(),
            'allows_null' => false,
        ]);
        $this->crud->addField([
            'name' => 'member_id',
            'type' => 'select2_from_array',
            'label' => 'Member',
            'options' => $members->pluck('name', 'id')->toArray(),
            'allows_null' => false,
        ]);
        $this->crud->field('qty')->type('number_format');
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
    
    protected function generateCode() {
        $lastTransaction = Transaction::withTrashed()->orderBy('id', 'desc')->first();
        $lastTransactionCode = $lastTransaction->code ?? 'TRX-000000-0000';
        $transactionCode = explode('-', $lastTransactionCode)[2] + 1;
        $transactionCode = 'TRX-' . date('ymd') . '-' . str_pad($transactionCode, 4, '0', STR_PAD_LEFT);
        return $transactionCode;
    }

    public function store(Request $request)
    {
        $requests = $request->all();
        $this->crud->validateRequest($requests);
        $member = Member::find($requests['member_id']);
        $product = Product::find($requests['product_id']);
        DB::beginTransaction();
        try {
            $requests['code'] = $this->generateCode();
            $requests['id_card'] = $member->id_card;
            $requests['member_name'] = $member->name;
            $requests['member_numb'] = $member->member_numb;
            $requests['level_id'] = $member->level_id;
            $requests['product_name'] = $product->name;
            $requests['product_model'] = $product->model;
            $requests['qty_sold'] = $requests['qty'];
            $requests['unit_price'] = $product->price;
            $requests['total_price'] = $requests['qty'] * $product->price;
            $requests['created_by'] = backpack_user()->id;
            $requests['updated_by'] = backpack_user()->id;
            $requests['status'] = 'pending';
            $transaction = Transaction::create($requests);
            // show a success message
            Alert::success(trans('backpack::crud.insert_success'))->flash();
            // save the redirect choice for next time
            DB::commit();
            return redirect($this->crud->route);
        } catch (\Exception $e) {
            DB::rollback();
            Alert::error($e->getMessage())->flash();
            return redirect()->back();
        }
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

    public function create()
    {
        $this->crud->hasAccessOrFail('create');

        $this->data['crud'] = $this->crud;
        $this->data['fields'] = $this->crud->getCreateFields();
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->crud->modifyField('transaction_date', [
            'value' => date('Y-m-d'),
        ]);
        $this->crud->modifyField('qty', [
            'value' => 1,
        ]);
        return view('crud::create', $this->data);
    }

    public function update()
    {
        // show a success message
        Alert::success(trans('backpack::crud.update_success'))->flash();

        return redirect($this->crud->route);
    }
}
