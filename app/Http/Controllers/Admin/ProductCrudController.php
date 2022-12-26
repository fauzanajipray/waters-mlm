<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * Class ProductCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductCrudController extends CrudController
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
        $this->crud->setModel(Product::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/product');
        $this->crud->setEntityNameStrings('product', 'products');
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
        $this->crud->column('name')->label('Name');
        $this->crud->column('model');
        $this->crud->column('capacity');
        $this->crud->column('price')->label('Netto Price')->prefix('Rp. ')->type('number_format');
        $this->crud->addColumn([
            'name' => 'type',
            'label' => 'Type',
            'type' => 'text',
            'searchLogic' => function($query, $column, $searchTerm) {
                $query->orWhere('type', 'like', '%'.$searchTerm.'%');
            },
            'wrapper' => [
                'element' => 'span',
                'class' => function($crud, $column, $entry, $related_key) {
                    if($entry->type == 'product'){
                        return 'badge badge-success';
                    }else{
                        return 'badge badge-warning';
                    }
                },
            ],
            'value' => function($entry) {
                if($entry->type == 'product'){
                    return 'Product';
                }else{
                    return 'Sparepart';
                }
            },
        ]);

        $this->crud->addFilter([
            'name' => 'type',
            'type' => 'dropdown',
            'label'=> 'Type'
        ], [
            'product' => 'Product',
            'sparepart' => 'Sparepart',
        ], function($value) {
            $this->crud->addClause('where', 'type', $value);
        });
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->crud->setValidation([
            'type' => 'required|in:product,sparepart',
            'name' => 'required|min:5|max:255',
            'price' => 'required|numeric',
            'capacity' => 'nullable|string|max:20',
            'model' => ['max:255', function($attribute, $value, $fail) {
                $name = request()->input('name');
                $type = request()->input('type');
                $model = request()->input('model');
                if ($type == 'product') {
                    if(strlen($model) < 5){
                        $fail('Model must be at least 5 characters.');
                    }
                    $product = Product::select(['name', 'model'])->where('name', $name)->where('model', $value)->first();
                    if($product){
                        $fail('Product with name '.$name.' and model '.$value.' already exists.');
                    }
                } else {
                    $sparepart = Product::select(['name'])->where('name', $name)->first();
                    if($sparepart){
                        $fail('Sparepart with name '.$name.' already exists.');
                    }
                }
            }],
        ]);
        $this->crud->addField([
            'name' => 'type',
            'label' => 'Type',
            'type' => 'select2_from_array',
            'options' => [
                'product' => 'Product',
                'sparepart' => 'Sparepart',
            ],
            'default' => 'product',
        ]);

        $this->crud->field('name')->label('Name');
        $this->crud->addField([
            'name' => 'model',
            'label' => 'Model',
            'type' => 'text',
            'dependencies' => ['type'],
        ]);
        $this->crud->field('capacity')->label('Capacity');
        $this->crud->field('price')->label('Netto Price')->type('number_format')->prefix('Rp. ');
        $this->crud->field('is_demokit')->label('Is Demokit');
        Widget::add()->type('script')->content(asset('assets/js/admin/form/product.js'));
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
        $this->crud->setValidation([
            'type' => 'nullable',
            'model' => ['required', 'min:5', 'max:255', function($attribute, $value, $fail) {
                $name = request()->input('name');
                $product = Product::find(request()->input('id'));
                if($product->name == $name && $product->model == $value){
                    return;
                }
                $product = Product::select(['name', 'model'])->where('name', $name)->where('model', $value)->first();
                if($product){
                    $fail('Product with name '.$name.' and model '.$value.' already exists.');
                }
            }],
        ]);
        $this->crud->modifyField('type', [
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);
    }

    /**
     * Define what happens when the Show operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-show-entries
     * @return void
     */
    protected function setupShowOperation()
    {
        $this->setupListOperation();
        $this->crud->column('is_demokit')->label('Is Demokit')->type('boolean');
        $this->crud->column('updated_at');
        $this->crud->column('created_at');
    }

    public function store(){
        $request = $this->crud->validateRequest();
        DB::beginTransaction();
        try {
            $product = new Product();
            $product->type = $request->input('type');
            $product->name = $request->input('name');
            $product->price = $request->input('price');
            if($request->type == 'product'){
                $product->model = $request->input('model');
                $product->capacity = $request->input('capacity');
                $product->is_demokit = $request->input('is_demokit');
            }
            $product->save();
            $branches = Branch::all();
            foreach ($branches as $branch) {
                $branchProduct = new BranchProduct();
                $branchProduct->branch_id = $branch->id;
                $branchProduct->product_id = $product->id;
                $branchProduct->save();
            }
            DB::commit();
            return redirect()->route('product.index');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function getProduct()
    {
        $id = request()->input('product_id');
        $product = Product::
            leftJoin('branch_products', 'branch_products.product_id', 'products.id')
            ->select(
                'products.*',
                'branch_products.additional_price',
                DB::raw('(products.price + branch_products.additional_price) AS netto_price')
            )
            ->where('products.id', $id)
            ->first();
        return response()->json($product);
    }
    
    public function getDemokitProducts()
    {
        $search_term = request()->input('q');
        $form = collect(request()->input('form'));
        $member_id = $form->where('name', 'member_id')->first();
        $branch_id = $form->where('name', 'branch_id')->first();
        if(!$member_id || !$branch_id){
            return response()->json([]);
        }
        if($search_term){
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('is_demokit', 1)
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }else{
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('is_demokit', 1)
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();   
        }
        $products->map(function ($stock) {
            $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity ;
            $stock->id = $stock->product_id;
            return $stock;
        });
        $productBought = [];
        $transactions = Transaction::with('transactionProducts')
            ->where('member_id', $member_id['value'])->where('type', 'Demokit')->get();
        foreach($transactions as $transaction){
            foreach($transaction->transactionProducts as $transactionProduct){
                $productBought[$transactionProduct->product_id] = $transactionProduct->product_id;
            }
        }
        $products = $products->filter(function($product) use ($productBought){
            return !isset($productBought[$product->id]);
        });
        return $products;
    }

    public function getDisplayProducts()
    {
        $search_term = request()->input('q');   
        $form = collect(request()->input('form'));
        $member_id = $form->where('name', 'member_id')->first();
        $branch_id = $form->where('name', 'branch_id')->first();
        if(!$member_id || !$branch_id){
            return response()->json([]);
        }

        if($search_term){
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }else{
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }

        $products->map(function ($stock) {
            if ($stock->type == 'sparepart'){
                $stock->name = $stock->name.' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity;
            } else {
                $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity;
            }
            $stock->id = $stock->product_id;
           return $stock;
        });

        // Filter Sudah Pernah Membeli
        $productBought = [];
        $transactions = Transaction::with('transactionProducts')
            ->where('member_id', $member_id['value'])->where('type', 'Normal')->where('status_paid', true)->get();
        foreach($transactions as $transaction){
            foreach($transaction->transactionProducts as $transactionProduct){
                $productBought[$transactionProduct->product_id] = $transactionProduct->product_id;
            }
        }
        $products = $products->filter(function($product) use ($productBought){
            return isset($productBought[$product->product_id]);
        });

        // Filter sudah beli display
        $productBoughtDisplay = [];
        $transactions = Transaction::with('transactionProducts')
            ->where('member_id', $member_id['value'])->where('type', 'Display')->get();
        foreach($transactions as $transaction){
            foreach($transaction->transactionProducts as $transactionProduct){
                $productBoughtDisplay[$transactionProduct->product_id] = $transactionProduct->product_id;
            }
        }
        $products = $products->filter(function($product) use ($productBoughtDisplay){
            return !isset($productBoughtDisplay[$product->product_id]);
        });
        return $products;
    }

    public function getBebasProducts()
    {
        $search_term = request()->input('q');
        $branch_id = collect(request()->form)->where('name', 'branch_id')->first();
        if(!$branch_id){
            return response()->json([]);
        }        
        if($search_term){
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'product')
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }else{
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'product')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }

        $products->map(function ($stock) {
            $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity ;
            $stock->id = $stock->product_id;
            return $stock;
        });

        return $products;
    }

    public function getProductsForFilter() 
    {
        $search_term = request()->input('q');
        if($search_term){
            $products = Product::where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->get();
        }else{
            $products = Product::get();
        }
        $products->map(function($product){
            $product->name = $product->name.' - '.$product->model. ' - '.$product->price;
            return $product;
        });

        return $products->pluck('name', 'id');
    }

    public function getProductForStock() {  
        $search_term = request()->input('q');
        $branch_id = request()->form[2];
        $origin_branch_id = request()->form[3];
        if($branch_id['name'] != 'branch_id' ) { 
            return response()->json([]);
        }
        if ($branch_id['value'] != 1) {
            if($origin_branch_id['name'] == 'origin_branch_id' ) { 
                if($search_term){
                    $stocks = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                        ->where('branch_id', $origin_branch_id['value'])
                        ->where('quantity', '>', 0)
                        ->where('name', 'like', '%'.$search_term.'%')
                        ->orWhere('model', 'like', '%'.$search_term.'%')
                        ->get();
                }else{
                    $stocks = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                        ->where('branch_id', $origin_branch_id['value'])
                        ->where('quantity', '>', 0)
                        ->get();
                }
                $stocks->map(function ($stock) {
                    $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->price) ;
                    return $stock;
                });

                return $stocks;
            } else {
                return response()->json([]);
            }
        } else {
            if($search_term){
                $products = Product::where('name', 'like', '%'.$search_term.'%')
                    ->orWhere('model', 'like', '%'.$search_term.'%')
                    ->get();
            }else{
                $products = Product::get();
            }
            $products->map(function($product){
                if ($product->type == 'sparepart'){
                    $product->name = $product->name.' - '.$product->price;
                } else {
                    $product->name = $product->name.' - '.$product->model. ' - '.$product->price;
                }
                return $product;
            });
            return $products;
        }
    }

    public function getProductStock(Request $request, $id, $branch_id)
    {
        $stocks = Stock::where('product_id', $id)
            ->where('branch_id', $branch_id)
            ->first();
        return $stocks->quantity ?? 0;
    }

    public function getProductTransaction() {
        $search_term = request()->input('q');
        $branch_id = collect(request()->form)->where('name', 'branch_id')->first();
        if(!$branch_id){
            return response()->json([]);
        }        
        if($search_term){
            $stocks = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'product')
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }else{
            $stocks = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    } 
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'product')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();
        }
        $stocks = $stocks->map(function ($stock) {
            $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity ;
            return $stock;
        });

        return $stocks;
    }

    public function getProductSparepartTransaction() {
        $search_term = request()->input('q');
        $branch_id = collect(request()->form)->where('name', 'branch_id')->first();
        if(!$branch_id){
            return response()->json([]);
        }        
        if($search_term){
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'sparepart')
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();   
        }else{
            $products = Stock::
                leftJoin(
                    DB::raw('(
                        SELECT `products`.*, `branch_products2`.`additional_price` AS `additional_price`
                        FROM `products`
                        LEFT JOIN (
                            SELECT * FROM branch_products WHERE `branch_id` = '.$branch_id['value'].'
                        ) AS `branch_products2` 
                        ON `products`.`id` = `branch_products2`.`product_id`
                    ) AS `products2`' ),
                    function($join) {
                        $join->on('products2.id', '=', 'stocks.product_id');
                    }
                )
                ->where('stocks.branch_id', $branch_id['value'])
                ->where('stocks.quantity', '>', 0)
                ->where('products2.type', 'sparepart')
                ->select(
                    'stocks.*',
                    'products2.name',
                    'products2.model',
                    'products2.price',
                    'products2.additional_price',
                    DB::raw('(products2.additional_price + products2.price) AS netto_price')
                )
                ->get();  
        }
        $products = $products->map(function ($stock) {
            $stock->name = $stock->name.' - '.number_format($stock->netto_price). ' - Stock : '.$stock->quantity ;
            $stock->id = $stock->product_id;
            return $stock;
        });
        return $products;
    }
}


