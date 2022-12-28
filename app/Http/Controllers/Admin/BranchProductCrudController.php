<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BranchProductRequest;
use App\Models\Branch;
use App\Models\BranchProduct;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Support\Facades\DB;

/**
 * Class ProductCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BranchProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        $this->crud->setModel(\App\Models\BranchProduct::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/branch-product');
        $this->crud->setEntityNameStrings('branch product', 'branch products');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->query = $this->customQuery();
        $this->crud->addColumn([
            'name' => 'branch_name',
            'type' => 'text',
            'label' => 'Branch',
            'orderable' => true,
            'orderLogic' => function($query, $column, $columnDirection) {
                $query->orderBy('branches.name', $columnDirection);
            },
            'searchLogic' => function($query, $column, $searchTerm) {
                $query->orWhere('branches.name', 'like', '%'.$searchTerm.'%');
            },
        ]);
        $this->crud->addColumn([
            'name' => 'name',
            'type' => 'text',
            'label' => 'Product',
            'orderable' => true,
            'orderLogic' => function($query, $column, $columnDirection) {
                $query->orderBy('products.name', $columnDirection);
            },
        ]);
        $this->crud->addColumn([
            'name' => 'model',
            'type' => 'text',
            'label' => 'Product Model',
            'orderable' => true,
            'orderLogic' => function($query, $column, $columnDirection) {
                $query->orderBy('products.model', $columnDirection);
            },
            'searchLogic' => function($query, $column, $searchTerm) {
                $query->orWhere('products.model', 'like', '%'.$searchTerm.'%');
            },
        ]);
        $this->crud->addColumn([
            'name' => 'type',
            'label' => 'Type',
            'type' => 'text',
            'searchLogic' => function($query, $column, $searchTerm) {
                $query->orWhere('products.type', 'like', '%'.$searchTerm.'%');
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
            'orderable' => true,
            'orderLogic' => function($query, $column, $columnDirection) {
                $query->orderBy('products.type', $columnDirection);
            },
            'searchLogic' => function($query, $column, $searchTerm) {
                $query->orWhere('products.type', 'like', '%'.$searchTerm.'%');
            },
        ]);

        $this->crud->addColumn([
            'name' => 'price',
            'label' => 'Normal Price',
            'type' => 'number_format',
            'prefix' => 'Rp. ',
            'orderable' => true,
            'orderLogic' => function($query, $column, $columnDirection) {
                $query->orderBy('products.price', $columnDirection);
            },
            'searchLogic' => function($query, $column, $searchTerm) {
                $query->orWhere('products.price', 'like', '%'.$searchTerm.'%');
            },
        ]);
        $this->crud->addColumn([
            'name' => 'additional_price',
            'label' => 'Additional Price',
            'type' => 'number_format',
            'prefix' => 'Rp. ',
            'orderable' => true,
            'orderLogic' => function($query, $column, $columnDirection) {
                $query->orderBy('branch_products.additional_price', $columnDirection);
            },
            'searchLogic' => function($query, $column, $searchTerm) {
                $query->orWhere('branch_products.additional_price', 'like', '%'.$searchTerm.'%');
            },
        ]);
        $this->crud->addColumn([
            'name' => 'netto_price',
            'label' => 'Netto Price',
            'type' => 'number_format',
            'prefix' => 'Rp. ',
            'orderable' => true,
            'orderLogic' => function($query, $column, $columnDirection) {
                $query->orderBy(
                    DB::raw('(products.price + branch_products.additional_price)'),
                    $columnDirection
                );
            },
        ]);
        
        $this->filters();
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->crud->setValidation(BranchProductRequest::class);

        $this->crud->addField([
            'name' => 'branch_id',
            'type' => 'select2',
            'label' => 'Branch',
            'entity' => 'branch',
            'attribute' => 'name',
            'model' => "App\Models\Branch",
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);
        $this->crud->addField([
            'name' => 'product_id',
            'type' => 'select2',
            'label' => 'Product',
            'entity' => 'product',
            'attribute' => 'name',
            'model' => "App\Models\Product",
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);
        $this->crud->addField([
            'name' => 'normal_price',
            'type' => 'number_format',
            'label' => 'Normal Price',
            'attributes' => [
                'disabled' => 'disabled',
            ],
            'prefix' => 'Rp.',
            'value' => $this->crud->getCurrentEntry()->product->price,
            'default' => '0',
        ]);
        $this->crud->addField([
            'name' => 'additional_price',
            'type' => 'number_format',
            'label' => 'Additional Price', 
            'prefix' => 'Rp.',
            'default' => '0',
        ]);
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

    protected function filters()
    {
        $this->crud->addFilter([
            'name' => 'product_type',
            'type' => 'dropdown',
            'label'=> 'Product Type'
        ], [
            'product' => 'Product',
            'sparepart' => 'Sparepart',
        ], function($value) {
            $this->crud->addClause('where', 'products.type', $value);
        });

        $this->crud->addFilter([
            'name' => 'branch',
            'type' => 'select2',
            'label'=> 'Branch'
        ], function() {
            return Branch::where('id', '!=', 1)->get()->pluck('name', 'id')->toArray();
        }, function($value) {
            $this->crud->addClause('where', 'branch_products.branch_id', $value);
        });

        $this->crud->addClause('where', 'branch_products.branch_id', '!=', 1);
    }

    protected function customQuery() 
    {
        return BranchProduct::
            leftJoin('products', 'branch_products.product_id', '=', 'products.id')
            ->join('branches', 'branch_products.branch_id', '=', 'branches.id')
            ->orderBy('branch_products.created_at', 'desc')
            ->select(
                'branch_products.id',
                'branch_products.product_id',
                'branch_products.branch_id',
                'branch_products.additional_price', 
                'products.name',
                'products.model',
                'products.type',
                'products.capacity',
                'products.price',
                'products.type',
                'products.is_demokit', 
                'branches.name as branch_name', 
                DB::raw('(products.price + branch_products.additional_price) as netto_price'));
    }

}


