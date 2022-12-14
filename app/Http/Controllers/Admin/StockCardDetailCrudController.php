<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use App\Models\Stock;
use App\Models\StockHistory;
use App\Models\Transaction;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Carbon\Carbon;

/**
 * Class StockCardDetailCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StockCardDetailCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
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
        $stockID = request()->segment(2);
        $this->crud->stock = Stock::with(['product', 'branch' => function($query) {
            return $query->with('member');
        } ])->find($stockID);
        if (!$this->crud->stock) abort(404);
        $this->crud->setModel(StockHistory::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/stock-card/' . $stockID . '/detail');
        $this->crud->setEntityNameStrings('Stock Card Detail', 'Stock Card Detail');
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
            if (request()->get('start_date') && request()->get('end_date')) {
                $startDate = Carbon::parse(request()->get('start_date'))
                    ->startOfDay()
                    ->toDateTimeString();
                $endDate = Carbon::parse(request()->get('end_date'))
                    ->endOfDay()
                    ->toDateTimeString();
            } else {
                $startDate = Carbon::now()->startOfDay()->toDateTimeString();
                $endDate = Carbon::now()->endOfDay()->toDateTimeString();
            }
        }
        $this->crud->startDate = $startDate;
        $this->crud->endDate = $endDate;
        
        $this->crud->addClause('where', 'stock_histories.branch_id', $this->crud->stock->branch->id);
        $this->crud->addClause('where', 'stock_histories.product_id', $this->crud->stock->product->id);
        $this->crud->addClause('where', 'stock_histories.created_at', '>=', $startDate);
        $this->crud->addClause('where', 'stock_histories.created_at', '<=', $endDate);

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
            }
        );
        
        $this->crud->addColumn([ 'name' => 'created_at', 'label' => 'Date', 'type' => 'datetime' ]);
        $this->crud->addColumn([ 
            'name' => 'type', 
            'label' => 'Type', 
            'type' => 'text',
            'value' => function($entry) {
                switch ($entry->type) {
                    case 'in':
                        return 'Stock In';
                    case 'out':
                        return 'Stock Out';
                    case 'adjustment':
                        return 'Stock Adjustment';
                    case 'sales':
                        return 'Sales';
                    default:
                        return 'Stock In';
                }
            },
        ]);
        $this->crud->addColumn([ 
            'name' => 'quantity', 
            'label' => 'Quantity', 
            'type' => 'text',
            'value' => function($entry) {
                switch ($entry->type) {
                    case 'in':
                        return $entry->quantity;
                    case 'out':
                        return $entry->quantity * -1;
                    case 'adjustment':
                        return $entry->quantity;
                    case 'sales':
                        return $entry->quantity * -1;
                    default:
                        return $entry->quantity;
                }
            },
            'wrapper' => [
                'element' => 'span',
                'class' => function($crud, $column, $entry) {
                    switch ($entry->type) {
                        case 'in':
                            return 'text-success';
                        case 'out':
                            return 'text-danger';
                        case 'adjustment':
                            return 'text-black';
                        case 'sales':
                            return 'text-danger';
                        default:
                            return 'text-success';
                    }
                }
            ]
        ]);

        $this->crud->addColumn([
            'name' => 'branch',
            'label' => 'In/Out Branch',
            'type' => 'text',
            'value' => function($entry) {
                $branchID = null;
                if($entry->type == 'in') {
                    $branchID = $entry->in_from;
                } else if($entry->type == 'out') {
                    $branchID = $entry->out_to;
                } else {
                    $branchID = null;
                }
                if ($branchID) {
                    $branch = Branch::find($branchID);
                    return $branch->name;
                } else {
                    return '-';
                }
            },
        ]);

        $this->crud->addColumn([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'text',
            'value' => function($entry) {
                if($entry->type == 'adjustment') {
                    return $entry->descriptions;
                } else {
                    return '-';
                }
            },
        ]);
    
        $this->crud->addColumn([
            'name' => 'sales_on',
            'label' => 'Sales On',
            'type' => 'text',
            'value' => function($entry) {
                if($entry->sales_on) {
                    $sales = Transaction::find($entry->sales_on);
                    return $sales->code;
                } else {
                    return '-';
                }
            },
            'wrapper' => [
                'element' => 'a',
                'href' => function($crud, $column, $entry) {
                    if($entry->sales_on) {
                        $sales = Transaction::find($entry->sales_on);
                        return route('transaction.show', $sales->id);
                    } else {
                        return '#';
                    }
                },
                'target' => '_blank'
            ],
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

        $this->setupListOperation();
    }

    public function index() 
    {
        $this->crud->hasAccessOrFail('list');
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        return view('vendor.backpack.crud.list_stockdetail', $this->data);
    }

    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        $this->crud->applyUnappliedFilters();

        $start = (int) request()->input('start');
        $length = (int) request()->input('length');
        $search = request()->input('search');

        // if a search term was present
        if ($search && $search['value'] ?? false) {
            // filter the results accordingly
            $this->crud->applySearchTerm($search['value']);
        }
        // start the results according to the datatables pagination
        if ($start) {
            $this->crud->skip($start);
        }
        // limit the number of results according to the datatables pagination
        if ($length) {
            $this->crud->take($length);
        }
        // overwrite any order set in the setup() method with the datatables order
        $this->crud->applyDatatableOrder();

        $entries = $this->crud->getEntries();

        // if show entry count is disabled we use the "simplePagination" technique to move between pages.
        if ($this->crud->getOperationSetting('showEntryCount')) {
            $totalEntryCount = (int) (request()->get('totalEntryCount') ?: $this->crud->getTotalQueryCount());
            $filteredEntryCount = $this->crud->getFilteredQueryCount() ?? $totalEntryCount;
        } else {
            $totalEntryCount = $length;
            $filteredEntryCount = $entries->count() < $length ? 0 : $length + $start + 1;
        }

        // store the totalEntryCount in CrudPanel so that multiple blade files can access it
        $this->crud->setOperationSetting('totalEntryCount', $totalEntryCount);
        $response = $this->crud->getEntriesAsJsonForDatatables($entries, $totalEntryCount, $filteredEntryCount, $start);
        $response['period_name'] = Carbon::parse($this->crud->startDate)->format('Y F d') . ' - ' . Carbon::parse($this->crud->endDate)->format('Y F d');
        return $response;
    }
}
