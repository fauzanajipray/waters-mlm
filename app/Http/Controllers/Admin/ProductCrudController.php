<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Transaction;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

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
        $member_id = request()->input('form')[3];
        if($member_id['name'] != 'member_id'){
            return [];
        }
        if($search_term){
            $products = Product::where('is_demokit', 1)->where('name', 'like', '%'.$search_term.'%')->get();
        }else{
            $products = Product::where('is_demokit', 1)->get();
        }
        $products->map(function($product){
            $product->name = $product->name.' - '.$product->model. ' - '.$product->price;
            return $product;
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
        $member_id = request()->input('form')[3];
        if($member_id['name'] != 'member_id'){
            return [];
        }

        if($search_term){
            $products = Product::where('name', 'like', '%'.$search_term.'%')->get();
        }else{
            $products = Product::get();
        }
        $products->map(function($product){
            $product->name = $product->name.' - '.$product->model. ' - '.$product->price;
            return $product;
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

        return $products;
    }
}


