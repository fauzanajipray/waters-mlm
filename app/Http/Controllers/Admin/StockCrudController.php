<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StockRequest;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockHistory;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\Widget;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class StockCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StockCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        if(!backpack_user()->hasPermissionTo('Read Stock')){
            $this->crud->denyAccess(['list', 'show']);
        }
        if(!backpack_user()->hasPermissionTo('Create Stock')){
            $this->crud->denyAccess('create');
        }
        $this->crud->setModel(\App\Models\Stock::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/stock');
        $this->crud->setEntityNameStrings('stock', 'stocks');
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
            'name' => 'product_id',
            'label' => 'Product',
            'type' => 'select',
            'entity' => 'product',
            'attribute' => 'name',
            'model' => Product::class,
        ]);
        $this->crud->addColumn([
            'name' => 'product_model',
            'label' => 'Product Model',
            'type' => 'text',
            'entity' => 'product',
            'attribute' => 'model',
            'orderable'  => true,
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('product', function ($q) use ($column, $searchTerm) {
                    $q->where('model', 'like', '%'.$searchTerm.'%');
                });
            },
            'orderLogic' => function ($query, $column, $direction) {
                return $query->leftJoin('products', 'products.id', '=', 'stocks.product_id')
                    ->orderBy('products.model', $direction)
                    ->select('stocks.*');
            },
            'value' => function ($entry) {
                return $entry->product->model;
            }
        ]);
        $this->crud->column('branch_id');
        $this->crud->column('quantity');
        $this->crud->column('updated_at');

        $this->crud->addClause('where', 'quantity', '>', 0);
        $this->getFilter();
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setValidation(StockRequest::class);

        $request = request();
        if($request->has('branch_id')){
            $branch_id = $request->branch_id;
            // $this->crud->addField([
            //     'name' => 'branch_id',
            //     'label' => 'Branch',
            //     'type' => 'select2',
            //     'entity' => 'branch',
            //     'attribute' => 'name',
            //     'model' => "App\Models\Branch",
            //     'options'   => (function ($query) use ($branch_id) {
            //         return $query->where('id', $branch_id)->orderBy('name', 'DESC')->get();
            //     }),
            // ]);
        }else{
            $this->crud->addField([
                'name' => 'branch_id',
                'label' => 'Branch',
                'type' => 'select2',
                'entity' => 'branch',
                'attribute' => 'name',
                'model' => "App\Models\Branch",
                'options'   => (function ($query) {
                    $data = $query->where('id', '1')->with('member')->orderBy('name', 'DESC')->get();
                    $data->map(function($item){
                        if(isset($item->member)){
                            $item->name = $item->name . ' | ' . $item->member->name;
                        }
                        return $item;
                    });
                    return $data;
                }),
                'allows_null' => true,
            ]);
        }

        $this->crud->addField([
            'name' => 'origin_branch_id',
            'type' => 'select2_from_ajax',
            'label' => 'Origin Branch',
            'model' => "App\Models\Branch",
            'data_source' => url('branches/origin'),
            'placeholder' => 'Select a origin branch',
            'include_all_form_fields' => true,
            'dependencies' => ['branch_id'],
            'method' => 'POST',
            'tab' => 'Product'
        ]);

        $this->crud->addField([
            'name' => 'product_id',
            'type' => 'select2_from_ajax',
            'label' => 'Product',
            'model' => "App\Models\Product",
            'data_source' => url('product/for-stock'),
            'allows_null' => true,
            'method' => 'POST',
            'placeholder' => 'Select a product',
            'include_all_form_fields' => true,
            'dependencies' => ['branch_id', 'origin_branch_id'],
            'tab' => 'Product'
        ]);

        $this->crud->addField([
            'name' => 'product_stock',
            'label' => 'Product Stock',
            'type' => 'text',
            'tab' => 'Product',
            'attributes' => [
                'readonly' => 'readonly',
                'disabled' => 'disabled'
            ],
        ]);

        $this->crud->addField([
            'name' => 'quantity',
            'label' => 'Quantity',
            'type' => 'number',
            'tab' => 'Product'
        ]);

        $this->crud->addField([ 'name' => 'url', 'type' => 'hidden', 'value' => url(''), 'attributes' => ['disabled' => 'disabled'] ]);
        Widget::add()->type('script')->content(asset('assets/js/admin/form/stock.js'));
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

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     * @return void
     */
    protected function setupShowOperation()
    {
        $this->setupListOperation();
    }

    protected function store() {
        $request = request();
        $request = $this->crud->validateRequest();
        $data = $request->all();
        DB::beginTransaction();
        try {
            if($data['branch_id'] == 1) {
                $stock = Stock::where('product_id', $data['product_id'])->where('branch_id', $data['branch_id'])->first();
                if($stock){
                    $stock->quantity = $stock->quantity + $data['quantity'];
                    $stock->save();
                }else{
                    $stock = Stock::create($data);
                }
                StockHistory::create([
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'branch_id' => $data['branch_id'],
                    'type' => 'in',
                    'in_from' => null,
                ]);
            } else {
                $stock = Stock::where('product_id', $data['product_id'])->where('branch_id', $data['branch_id'])->first();
                if($stock){
                    $stock->quantity = $stock->quantity + $data['quantity'];
                    $stock->save();
                }else{
                    $stock = Stock::create($data);

                }
                $stockOrigin = Stock::where('product_id', $data['product_id'])->where('branch_id', $data['origin_branch_id'])->first();
                $stockOrigin->quantity = $stockOrigin->quantity - $data['quantity'];
                $stockOrigin->save();
                // stock out
                StockHistory::create([
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'branch_id' => $data['origin_branch_id'],
                    'type' => 'out',
                    'out_to' => $data['branch_id'],
                ]);
                // stock in
                StockHistory::create([
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'branch_id' => $data['branch_id'],
                    'type' => 'in',
                    'in_from' => $data['origin_branch_id'],
                ]);
            }
            DB::commit();
            return redirect()->route('stock.index');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
        return redirect()->route('stock.index');
    }

    protected function getFilter() {

        $this->crud->addFilter(
            [
                'name' => 'branch_id',
                'type' => 'select2_ajax',
                'label'=> 'Branch',
                'placeholder' => 'Pick a branch',
                'method' => 'POST'
            ],
            url('branches/for-filter'),
            function($value) {
                $this->crud->addClause('where', 'branch_id', $value);
            }
        );

        $this->crud->addFilter(
            [
                'name' => 'product_id',
                'type' => 'select2_ajax',
                'label'=> 'Product',
                'placeholder' => 'Pick a product',
                'method' => 'POST'
            ],
            url('product/for-filter'),
            function($value) {
                $this->crud->addClause('where', 'product_id', $value);
            }
        );
    }

    public function createByImport($requests) {
        $data = $requests;
        DB::beginTransaction();
        try {
            if($data['branch_id'] == 1) {
                $stock = Stock::where('product_id', $data['product_id'])->where('branch_id', $data['branch_id'])->first();
                if($stock){
                    $stock->quantity = $stock->quantity + $data['quantity'];
                    $stock->save();
                }else{
                    $stock = Stock::create($data);
                }
                StockHistory::create([
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'branch_id' => $data['branch_id'],
                    'type' => 'in',
                    'in_from' => null,
                    'created_at' => $data['created_at'],
                ]);
            } else {
                $stock = Stock::where('product_id', $data['product_id'])->where('branch_id', $data['branch_id'])->first();
                if($stock){
                    $stock->quantity = $stock->quantity + $data['quantity'];
                    $stock->save();
                }else{
                    $stock = Stock::create($data);

                }
                $stockOrigin = Stock::where('product_id', $data['product_id'])->where('branch_id', $data['origin_branch_id'])->first();
                $stockOrigin->quantity = $stockOrigin->quantity - $data['quantity'];
                $stockOrigin->save();
                // stock out
                StockHistory::create([
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'branch_id' => $data['origin_branch_id'],
                    'type' => 'out',
                    'out_to' => $data['branch_id'],
                    'created_at' => $data['created_at'],
                ]);
                // stock in
                StockHistory::create([
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'branch_id' => $data['branch_id'],
                    'type' => 'in',
                    'in_from' => $data['origin_branch_id'],
                    'created_at' => $data['created_at'],
                ]);
            }
            DB::commit();
            return ;
        } catch (\Exception $e) {
            DB::rollback();
            return throw new Exception($e->getMessage());
        }
    }
}
