<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use Backpack\CRUD\app\Http\Controllers\CrudController;
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
            'name' => 'required|min:5|max:255',
            'price' => 'required|numeric',
            'capacity' => 'nullable|string|max:20',
            'model' => ['required', 'min:5', 'max:255', function($attribute, $value, $fail) {
                $name = request()->input('name');
                $product = Product::select(['name', 'model'])->where('name', $name)->where('model', $value)->first();
                if($product){
                    $fail('Product with name '.$name.' and model '.$value.' already exists.');
                }
            }],
        ]);
        $this->crud->field('name')->label('Name');
        $this->crud->field('model')->label('Model');
        $this->crud->field('capacity')->label('Capacity');
        $this->crud->field('price')->label('Netto Price')->type('number_format')->prefix('Rp. ');
        $this->crud->field('is_demokit')->label('Is Demokit');
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

    public function getProduct()
    {
        $id = request()->input('product_id');
        $product = Product::find($id);
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
            $products = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                ->where('branch_id', $branch_id['value'])
                ->where('quantity', '>', 0)
                ->where('is_demokit', 1)
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->get();
        }else{
            $products = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                ->where('branch_id', $branch_id['value'])
                ->where('quantity', '>', 0)
                ->where('is_demokit', 1)
                ->get();
        }
        $products->map(function ($stock) {
            $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->price). ' - Stock : '.$stock->quantity ;
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
            $products = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                ->where('branch_id', $branch_id['value'])
                ->where('quantity', '>', 0)
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->get();
        }else{
            $products = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                ->where('branch_id', $branch_id['value'])
                ->where('quantity', '>', 0)
                ->get();
        }

        $products->map(function ($stock) {
            $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->price). ' - Stock : '.$stock->quantity ;
            return $stock;
        });

        /* Revision Display
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
            return isset($productBought[$product->id]);
        });

        // FIlter sudah beli display
        $productBought = [];
        $transactions = Transaction::with('transactionProducts')
            ->where('member_id', $member_id['value'])->where('type', 'Display')->get();
        foreach($transactions as $transaction){
            foreach($transaction->transactionProducts as $transactionProduct){
                $productBought[$transactionProduct->product_id] = $transactionProduct->product_id;
            }
        }
        $products = $products->filter(function($product) use ($productBought){
            return !isset($productBought[$product->id]);
        });
        */
        return $products;
    }

    public function getProducts()
    {
        $search_term = request()->input('q');
        $branch_id = collect(request()->form)->where('name', 'branch_id')->first();
        if(!$branch_id){
            return response()->json([]);
        }        
        if($search_term){
            $products = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                ->where('branch_id', $branch_id['value'])
                ->where('quantity', '>', 0)
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->get();
        }else{
            $products = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                ->where('branch_id', $branch_id['value'])
                ->where('quantity', '>', 0)
                ->get();
        }

        $products->map(function ($stock) {
            $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->price). ' - Stock : '.$stock->quantity ;
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
                $product->name = $product->name.' - '.$product->model. ' - '.$product->price;
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
            $stocks = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                ->where('branch_id', $branch_id['value'])
                ->where('quantity', '>', 0)
                ->where('name', 'like', '%'.$search_term.'%')
                ->orWhere('model', 'like', '%'.$search_term.'%')
                ->get();
        }else{
            $stocks = Stock::leftJoin('products', 'products.id', '=', 'stocks.product_id')
                ->where('branch_id', $branch_id['value'])
                ->where('quantity', '>', 0)
                ->get();
        }
        $stocks = $stocks->map(function ($stock) {
            $stock->name = $stock->name.' - '.$stock->model. ' - '.number_format($stock->price). ' - Stock : '.$stock->quantity ;
            return $stock;
        });

        return $stocks;
    }
}


