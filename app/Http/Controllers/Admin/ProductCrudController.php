<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
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
        $this->crud->column('price')->label('Price')->prefix('Rp. ')->type('number_format');
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
        $this->crud->field('price')->label('Price')->type('number_format')->prefix('Rp. ');
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
        $this->crud->column('updated_at');
        $this->crud->column('created_at');
    }
}
