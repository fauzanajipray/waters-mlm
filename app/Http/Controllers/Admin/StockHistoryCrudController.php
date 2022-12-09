<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StockRequest;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockInHistory;
use App\Models\StockOutHistory;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\Widget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Termwind\Components\Raw;

/**
 * Class StockCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StockHistoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        $this->crud->setModel(\App\Models\Stock::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/stock-history');
        $this->crud->setEntityNameStrings('stock history', 'stock histories');
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

        $this->crud->query = $this->customQuery($startDate, $endDate);
        // dd($this->crud->query->get()->toArray());
        // dd($this->crud->query->toSql());
        
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
            function($value) { // if the filter is active
                $dates = json_decode($value);
                // $this->crud->addClause('where', 'stock_in_histories_now.created_at', '>=', $dates->from);
                // $this->crud->addClause('where', 'stock_in_histories_now.created_at', '<=', $dates->to . ' 23:59:59');
            },
            function() use($startDate, $endDate) { // if the filter is not active
                // $this->crud->addClause('where', 'stock_in_histories_now.created_at', '>=', $startDate);
                // $this->crud->addClause('where', 'stock_in_histories_now.created_at', '<=', $endDate . ' 23:59:59');
            }
        );


        $this->crud->addColumns([
            [
                'name' => 'product_name',
                'label' => 'Product Name',
                'type' => 'text',
            ],
            [
                'name' => 'product_model',
                'label' => 'Product Model',
                'type' => 'text',
            ],
            [
                'name' => 'branch_name',
                'label' => 'Branch Name',
                'type' => 'text',
            ],
            [
                'name' => 'initial_stock',
                'label' => 'Initial Stock',
                'type' => 'number',
                'value' => function($entry) {
                    $stockIn = $entry->stock_in_yesterday ?? 0;
                    $stockOut = $entry->stock_out_yesterday ?? 0;
                    return $stockIn - $stockOut;
                },
                    
            ],
            [
                'name' => 'stock_in',
                'label' => 'Stock In',
                'type' => 'number',
                'value' => function($entry) {
                    return $entry->stock_in ?? 0;
                },
            ],
            [
                'name' => 'stock_out',
                'label' => 'Stock Out',
                'type' => 'number',
                'value' => function($entry) {
                    return $entry->stock_out ?? 0;
                },
            ],
            [
                'name' => 'final_stock',
                'label' => 'Final Stock',
                'type' => 'number',
                'value' => function ($entry) {
                    $stockIn = $entry->stock_in_yesterday ?? 0;
                    $stockOut = $entry->stock_out_yesterday ?? 0;
                    $initialStock = $stockIn - $stockOut;
                    $finalStock = $initialStock + $entry->stock_in - $entry->stock_out;
                    return $finalStock;
                }
            ],
            // [
            //     'name' => 'stock_in_yesterday',
            //     'label' => 'Stock In Yesterday',
            //     'type' => 'number',
            // ],
            // [
            //     'name' => 'stock_out_yesterday',
            //     'label' => 'Stock Out Yesterday',
            //     'type' => 'number',
            // ]
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

    public function customQuery($startDate, $endDate){
        $startDateYesterdayEndOfDay = Carbon::parse($startDate)->endOfDay()->subDay()->toDateTimeString();
        return Stock::
            leftJoin(
                DB::raw("(
                    SELECT stock_in_histories.product_id, stock_in_histories.branch_origin, SUM(stock_in_histories.quantity) as quantity, stock_in_histories.created_at
                    FROM stock_in_histories 
                    WHERE stock_in_histories.created_at 
                    BETWEEN '$startDate' AND '$endDate'
                    GROUP BY stock_in_histories.product_id, stock_in_histories.branch_origin
                ) as `stock_in_histories_now` "),
                    function($join) {
                        $join->on('stocks.product_id', '=', 'stock_in_histories_now.product_id')
                            ->whereRaw('`stocks`.`branch_id` = `stock_in_histories_now`.`branch_origin`');
                    }
            )
            ->leftJoin(
                DB::raw("(
                    SELECT stock_out_histories.product_id, stock_out_histories.branch_origin, SUM(stock_out_histories.quantity) as quantity, stock_out_histories.created_at
                    FROM stock_out_histories 
                    WHERE stock_out_histories.created_at 
                    BETWEEN '$startDate' AND '$endDate'
                    GROUP BY stock_out_histories.product_id, stock_out_histories.branch_origin
                ) as `stock_out_histories_now`"),
                    function($join) {
                        $join->on('stocks.product_id', '=', 'stock_out_histories_now.product_id')
                            ->whereRaw('`stocks`.`branch_id` = `stock_out_histories_now`.`branch_origin`');
                    }
            )
            ->leftJoin(
                DB::raw("(
                    SELECT stock_in_histories.product_id, stock_in_histories.branch_origin, SUM(stock_in_histories.quantity) as quantity, stock_in_histories.created_at
                    FROM stock_in_histories 
                    WHERE stock_in_histories.created_at <= '$startDateYesterdayEndOfDay' 
                    GROUP BY stock_in_histories.product_id, stock_in_histories.branch_origin
                ) as `stock_in_histories_yesterday` "),
                    function($join) {
                        $join->on('stocks.product_id', '=', 'stock_in_histories_yesterday.product_id')
                            ->whereRaw('`stocks`.`branch_id` = `stock_in_histories_yesterday`.`branch_origin`');
                    }
            )
            ->leftJoin(
                DB::raw("(
                    SELECT stock_out_histories.product_id, stock_out_histories.branch_origin, SUM(stock_out_histories.quantity) as quantity, stock_out_histories.created_at
                    FROM stock_out_histories 
                    WHERE stock_out_histories.created_at <= '$startDateYesterdayEndOfDay'
                    GROUP BY stock_out_histories.product_id, stock_out_histories.branch_origin
                ) as `stock_out_histories_yesterday`"),
                    function($join) {
                        $join->on('stocks.product_id', '=', 'stock_out_histories_yesterday.product_id')
                            ->whereRaw('`stocks`.`branch_id` = `stock_out_histories_yesterday`.`branch_origin`');
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
                'branches.name as branch_name',
                // initial stock
                // DB::raw('(SUM(`stock_in_histories_yesterday`.`quantity`) - SUM(`stock_out_histories_yesterday`.`quantity`)) as `initial_stock`'),
                DB::raw('SUM(`stock_in_histories_now`.`quantity`) as `stock_in`'),
                DB::raw('SUM(`stock_out_histories_now`.`quantity`) as `stock_out`'),
                DB::raw('SUM(`stock_in_histories_yesterday`.`quantity`) as `stock_in_yesterday`'),
                DB::raw('SUM(`stock_out_histories_yesterday`.`quantity`) as `stock_out_yesterday`'),
                // final stock 
                // DB::raw('SUM(`stock_in_histories_yesterday`.`quantity`) - SUM(`stock_out_histories_yesterday`.`quantity`) + SUM(`stock_in_histories_now`.`quantity`) - SUM(`stock_out_histories_now`.`quantity`) as `final_stock`'),
            )
            ->orderBy('branches.name', 'asc')
            ->groupBy('products.name', 'products.model', 'branches.name');
    }

}
