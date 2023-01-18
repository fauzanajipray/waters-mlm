<?php

namespace App\Http\Controllers\Admin;

use App\Http\Traits\ProductTrait;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Product;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Support\Facades\DB;

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
    use ProductTrait;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        if(!backpack_user()->hasPermissionTo('Read Product')){
            $this->crud->denyAccess(['list', 'show']);
        }
        if(!backpack_user()->hasPermissionTo('Create Product')){
            $this->crud->denyAccess('create');
        }
        if(!backpack_user()->hasPermissionTo('Update Product')){
            $this->crud->denyAccess('update');
        }
        if(!backpack_user()->hasPermissionTo('Delete Product')){
            $this->crud->denyAccess('delete');
        }
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
        // dd(backpack_user());
        $this->crud->column('name')->label('Name');
        $this->crud->column('model');
        $this->crud->column('capacity')->label('Remarks');
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
        $this->crud->field('capacity')->label('Remarks');
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
            'model' => ['nullable', 'max:255', function($attribute, $value, $fail) {
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

    public function store()
    {
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
}


