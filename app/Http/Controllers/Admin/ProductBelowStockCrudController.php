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
 * Class ProductBelowStockCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductBelowStockCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    // use ProductTrait;

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
        $this->crud->setModel(Product::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/product-below-stock');
        $this->crud->setEntityNameStrings('Product Stock Below Minimum', 'Product Stock Below Minimum');
        $this->crud->query = $this->customQuery();
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
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
        $this->crud->addColumn([
            'name' => 'min_stock_pusat',
            'label' => 'Min Stock Pusat',
            'type' => 'number',
        ]);

        $this->crud->addColumn([
            'name' => 'quantity',
            'type' => 'number',
            'label' => 'Stock Pusat',
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

    public function customQuery(){
        $query = Product::
            leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->select('products.*', DB::raw('IFNULL(stocks.quantity, 0) as quantity'))
            ->whereRaw('stocks.quantity < products.min_stock_pusat')
            ->orderBy(DB::raw('(products.min_stock_pusat - stocks.quantity)'), 'desc');
        return $query;
    }
}


