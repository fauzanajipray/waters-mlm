<?php

namespace App\Http\Controllers\Admin;

use App\Models\Stock;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class StockCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StockCardCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        if(!backpack_user()->hasPermissionTo('Read Stock Card')){
            $this->crud->denyAccess(['list', 'show']);
        }
        $this->crud->setModel(\App\Models\Stock::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/stock-card');
        $this->crud->setEntityNameStrings('stock card', 'stock cards');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $dateRange = request()->get('date_range');
        if ($dateRange) {
            $dateRange = json_decode($dateRange);
            $startDate = Carbon::parse($dateRange->from)
                ->startOfDay()
                ->toDateTimeString();
            $endDate = Carbon::parse($dateRange->to)
                ->endOfDay()
                ->toDateTimeString();
        } else {
            $startDate = Carbon::now()->startOfDay()->toDateTimeString();
            $endDate = Carbon::now()->endOfDay()->toDateTimeString();
        }
        $this->crud->startDate = $startDate;
        $this->crud->endDate = $endDate;
        $this->crud->query = $this->customQuery($startDate, $endDate);

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
                $this->crud->addClause('where', 'stocks.branch_id', $value);
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
                $this->crud->addClause('where', 'stocks.product_id', $value);
            }
        );

        $this->crud->addFilter(
            [
                'type' => 'date_range',
                'name' => 'date_range',
                'label'=> 'Date Range',
                'init_selection' => [
                    'from' => $startDate,
                    'to' => $endDate
                ]
            ],
            false,
            function($value) { }
        );

        $this->crud->addFilter([
            'name' => 'type',
            'type' => 'dropdown',
            'label'=> 'Product Type'
        ], [
            'product' => 'Product',
            'sparepart' => 'Sparepart',
        ], function($value) {
            $this->crud->addClause('where', 'products.type', $value);
        });

        $this->crud->addColumns([
            [
                'name' => 'product_name',
                'label' => 'Product Name',
                'type' => 'text',
                'orderable' => true,
                'orderLogic' => function ($query, $column, $columnDirection) {
                    $query->orderBy('products.name', $columnDirection);
                },
            ],
            [
                'name' => 'product_model',
                'label' => 'Product Model',
                'type' => 'text',
                'orderable' => true,
                'orderLogic' => function ($query, $column, $columnDirection) {
                    $query->orderBy('products.model', $columnDirection);
                },
            ],
            [
                'name' => 'product_type',
                'label' => 'Product Type',
                'type' => 'text',
                'orderable' => true,
                'orderLogic' => function ($query, $column, $columnDirection) {
                    $query->orderBy('products.type', $columnDirection);
                },
                'wrapper' => [
                    'element' => 'span',
                    'class' => function($crud, $column, $entry, $related_key) {
                        if($entry->product_type == 'product'){
                            return 'badge badge-success';
                        }else{
                            return 'badge badge-warning';
                        }
                    },
                ],
                'value' => function($entry) {
                    if($entry->product_type == 'product'){
                        return 'Product';
                    }else{
                        return 'Sparepart';
                    }
                },
            ],
            [
                'name' => 'branch_name',
                'label' => 'Branch Name',
                'type' => 'text',
                'orderable' => true,
                'orderLogic' => function ($query, $column, $columnDirection) {
                    $query->orderBy('branches.name', $columnDirection);
                },
            ],
            [
                'name' => 'initial_stock',
                'label' => 'Awal',
                'type' => 'number',
                'value' => function($entry) {
                    return $entry->initial_stock ?? 0;
                },
            ],
            [
                'name' => 'stock_in',
                'label' => 'In',
                'type' => 'number',
                'value' => function($entry) {
                    return $entry->stock_in ?? 0;
                },
            ],
            [
                'name' => 'stock_out',
                'label' => 'Out',
                'type' => 'number',
                'value' => function($entry) {
                    return $entry->stock_out ?? 0;
                },
            ],
            [
                'name' => 'stock_sales',
                'label' => 'Sales',
                'type' => 'number',
                'value' => function($entry) {
                    return $entry->stock_sales ?? 0;
                },
            ],
            [
                'name' => 'adjustment',
                'label' => 'Adjustment',
                'type' => 'number',
                'value' => function($entry) {
                    return $entry->adjustments_now ?? 0;
                },
            ],
            [
                'name' => 'final_stock',
                'label' => 'Akhir',
                'type' => 'number',
                'value' => function ($entry) {
                    return $entry->final_stock;
                }
            ],
            [
                'name' => 'start_date',
                'label' => 'Start Date',
                'type' => 'date',
                'value' => $startDate,
            ],
            [
                'name' => 'end_date',
                'label' => 'End Date',
                'type' => 'date',
                'value' => $endDate,
            ]
        ]);
        $this->crud->addButtonFromModelFunction('line', 'detail_stock', 'detailStockButton', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'add_adjustment', 'addAdjustmentButton', 'end');
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

    public function customQuery($startDate, $endDate){
        $startDateYesterdayEndOfDay = Carbon::parse($startDate)->endOfDay()->subDay()->toDateTimeString();
        $query = Stock::
            /* Now */
            leftJoin( // Stock In
                DB::raw("(
                    SELECT
                        stock_histories.product_id,
                        stock_histories.branch_id,
                        SUM(stock_histories.quantity) as quantity,
                        stock_histories.created_at
                    FROM stock_histories
                    WHERE stock_histories.created_at
                        BETWEEN '$startDate' AND '$endDate'
                    AND stock_histories.type = 'in'
                    GROUP BY stock_histories.product_id, stock_histories.branch_id
                ) as `stock_in_histories_now` "),
                function($join) {
                    $join->on('stocks.product_id', '=', 'stock_in_histories_now.product_id')
                        ->whereRaw('`stocks`.`branch_id` = `stock_in_histories_now`.`branch_id`');
                }
            )
            ->leftJoin( // Stock Out
                DB::raw("(
                    SELECT
                        stock_histories.product_id,
                        stock_histories.branch_id,
                        SUM(stock_histories.quantity) as quantity,
                        stock_histories.in_from,
                        stock_histories.created_at
                    FROM stock_histories
                    WHERE stock_histories.created_at
                        BETWEEN '$startDate' AND '$endDate'
                    AND stock_histories.type = 'out'
                    GROUP BY stock_histories.product_id, stock_histories.branch_id
                ) as `stock_out_histories_now` "),
                function($join) {
                    $join->on('stocks.product_id', '=', 'stock_out_histories_now.product_id')
                        ->whereRaw('`stocks`.`branch_id` = `stock_out_histories_now`.`branch_id`');
                }
            )
            ->leftJoin( // Sales
                DB::raw("(
                    SELECT
                        stock_histories.product_id,
                        stock_histories.branch_id,
                        SUM(stock_histories.quantity) as quantity,
                        stock_histories.created_at
                    FROM stock_histories
                    WHERE stock_histories.created_at
                        BETWEEN '$startDate' AND '$endDate'
                    AND stock_histories.type = 'sales'
                    GROUP BY stock_histories.product_id, stock_histories.branch_id
                ) as `transactions_now` "),
                function($join) {
                    $join->on('stocks.product_id', '=', 'transactions_now.product_id')
                        ->whereRaw('`stocks`.`branch_id` = `transactions_now`.`branch_id`');
                }
            )
            ->leftJoin( // Adjustment
                DB::raw("(
                    SELECT
                        stock_histories.product_id,
                        stock_histories.branch_id,
                        SUM(stock_histories.quantity) as quantity,
                        stock_histories.created_at
                    FROM stock_histories
                    WHERE stock_histories.created_at
                        BETWEEN '$startDate' AND '$endDate'
                    AND stock_histories.type = 'adjustment'
                    GROUP BY stock_histories.product_id, stock_histories.branch_id
                ) as `adjustments_now` "),
                function($join) {
                    $join->on('stocks.product_id', '=', 'adjustments_now.product_id')
                        ->whereRaw('`stocks`.`branch_id` = `adjustments_now`.`branch_id`');
                }
            )
            /* Yesterday */
            ->leftJoin( // Stock In
                DB::raw("(
                    SELECT
                        stock_histories.product_id,
                        stock_histories.branch_id,
                        SUM(stock_histories.quantity) as quantity,
                        stock_histories.in_from,
                        stock_histories.created_at
                    FROM stock_histories
                    WHERE stock_histories.created_at <= '$startDateYesterdayEndOfDay'
                    AND stock_histories.type = 'in'
                    GROUP BY stock_histories.product_id, stock_histories.branch_id
                ) as `stock_in_histories_yesterday` "),
                function($join) {
                    $join->on('stocks.product_id', '=', 'stock_in_histories_yesterday.product_id')
                        ->whereRaw('`stocks`.`branch_id` = `stock_in_histories_yesterday`.`branch_id`');
                }
            )
            ->leftJoin( // Stock Out
                DB::raw("(
                    SELECT
                        stock_histories.product_id,
                        stock_histories.branch_id,
                        SUM(stock_histories.quantity) as quantity,
                        stock_histories.in_from,
                        stock_histories.created_at
                    FROM stock_histories
                    WHERE stock_histories.created_at <= '$startDateYesterdayEndOfDay'
                    AND stock_histories.type = 'out'
                    GROUP BY stock_histories.product_id, stock_histories.branch_id
                ) as `stock_out_histories_yesterday` "),
                function($join) {
                    $join->on('stocks.product_id', '=', 'stock_out_histories_yesterday.product_id')
                        ->whereRaw('`stocks`.`branch_id` = `stock_out_histories_yesterday`.`branch_id`');
                }
            )
            ->leftJoin( // Sales Yesterday
                DB::raw("(
                    SELECT
                        stock_histories.product_id,
                        stock_histories.branch_id,
                        SUM(stock_histories.quantity) as quantity,
                        stock_histories.created_at
                    FROM stock_histories
                    WHERE stock_histories.created_at <= '$startDateYesterdayEndOfDay'
                    AND stock_histories.type = 'sales'
                    GROUP BY stock_histories.product_id, stock_histories.branch_id
                ) as `transactions_yesterday` "),
                function($join) {
                    $join->on('stocks.product_id', '=', 'transactions_yesterday.product_id')
                        ->whereRaw('`stocks`.`branch_id` = `transactions_yesterday`.`branch_id`');
                }
            )
            ->leftJoin( // Adjustment Yesterday
                DB::raw("(
                    SELECT
                        stock_histories.product_id,
                        stock_histories.branch_id,
                        SUM(stock_histories.quantity) as quantity,
                        stock_histories.created_at
                    FROM stock_histories
                    WHERE stock_histories.created_at <= '$startDateYesterdayEndOfDay'
                    AND stock_histories.type = 'adjustment'
                    GROUP BY stock_histories.product_id, stock_histories.branch_id
                ) as `adjustments_yesterday` "),
                function($join) {
                    $join->on('stocks.product_id', '=', 'adjustments_yesterday.product_id')
                        ->whereRaw('`stocks`.`branch_id` = `adjustments_yesterday`.`branch_id`');
                }
            )
            ->leftJoin('products', 'stocks.product_id', '=', 'products.id')
            ->leftJoin('branches', 'stocks.branch_id', '=', 'branches.id')
            ->select(
                'stocks.id',
                'stocks.product_id',
                'stocks.branch_id',
                'products.name as product_name',
                'products.model as product_model',
                'products.type as product_type',
                'branches.name as branch_name',
                /* Now */
                'stock_in_histories_now.quantity as stock_in',
                'stock_out_histories_now.quantity as stock_out',
                DB::raw('SUM(`transactions_now`.`quantity`) as `stock_sales`'),
                DB::raw('SUM(`adjustments_now`.`quantity`) as `adjustments_now`'),
                /* Yesterday */
                'stock_in_histories_yesterday.quantity as stock_in_yesterday',
                'stock_out_histories_yesterday.quantity as stock_out_yesterday',
                DB::raw('SUM(`transactions_yesterday`.`quantity`) as `stock_sales_yesterday`'),
                DB::raw('SUM(`adjustments_yesterday`.`quantity`) as `adjustments_yesterday`'),
                // Initial Stock
                DB::raw('(
                    IFNULL(`stock_in_histories_yesterday`.`quantity`, 0) -
                    IFNULL(`stock_out_histories_yesterday`.`quantity`, 0) -
                    IFNULL(SUM(`transactions_yesterday`.`quantity`), 0) +
                    IFNULL(SUM(`adjustments_yesterday`.`quantity`), 0)
                ) as `initial_stock`'),
                // Final Stock
                DB::raw('(
                    IFNULL(`stock_in_histories_yesterday`.`quantity`, 0) -
                    IFNULL(`stock_out_histories_yesterday`.`quantity`, 0) -
                    IFNULL(SUM(`transactions_yesterday`.`quantity`), 0) +
                    IFNULL(SUM(`adjustments_yesterday`.`quantity`), 0) +
                    IFNULL(`stock_in_histories_now`.`quantity`, 0) -
                    IFNULL(`stock_out_histories_now`.`quantity`, 0) -
                    IFNULL(SUM(`transactions_now`.`quantity`), 0) +
                    IFNULL(SUM(`adjustments_now`.`quantity`), 0)
                ) as `final_stock`'),
            )
            ->orderBy('branches.name', 'asc')
            ->groupBy('products.name', 'products.model', 'branches.name');
        return $query;
    }

}
