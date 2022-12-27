<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BonusHistoryRequest;
use App\Models\BonusHistory;
use App\Models\Configuration;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class BonusHistoryCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BonusHistoryCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        $this->crud->setModel(\App\Models\BonusHistory::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/bonus-history');
        $this->crud->setEntityNameStrings('bonus history', 'bonus histories');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'label' => 'Member',
            'type' => 'relationship',
            'name' => 'member',
            'entity' => 'member',
            'attribute' => 'name',
            'model' => 'App\Models\Member',
            'wrapper' => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('member/'.$related_key.'/show');
                },
            ],
        ]);
        $this->crud->addColumn([
            'label' => 'Transaction',
            'type' => 'relationship',
            'name' => 'transaction',
            'entity' => 'transaction',
            'attribute' => 'code',
            'model' => "App\Models\Transaction",
            'pivot' => true,
            'wrapper' => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('transaction/'.$related_key.'/show');
                },
            ]
        ]); 
        $this->crud->column('level_id');
        $this->crud->column('bonus_type');
        $this->crud->column('bonus_percent');
        $this->crud->column('bonus')->value(function ($entry) {
            return "Rp. " . number_format($entry->bonus, 2, ',', '.');
        });
        $this->crud->addColumn([
            'name' => 'bonus_after_tax',
            'label' => 'Bonus After Tax',
            'type' => 'text',
            'value' => function ($entry) {
                if($entry->member->npwp){
                    $tax = Configuration::where('key', 'bonus_tax_percentage_npwp')->first()->value;
                } else {
                    $tax = Configuration::where('key', 'bonus_tax_percentage_non_npwp')->first()->value;
                }
                $bonusAfterTax = $entry->bonus - ($entry->bonus * (50/100) * $tax / 100);
                return "Rp. " . number_format($bonusAfterTax, 2, ',', '.');
            },
        ]);
        $this->crud->column('created_at');
        $this->crud->column('updated_at');

        $this->getFilter();
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
            // 'name' => 'required|min:2',
        ]);

        CRUD::field('member_id');
        CRUD::field('transaction_id');
        CRUD::field('level_id');
        CRUD::field('bonus_type');
        CRUD::field('bonus_percent');
        CRUD::field('bonus');

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */
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
    }

    private function getFilter(){
        $startDate = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');
        $this->crud->addFilter([
                'type' => 'date_range',
                'name' => 'created_at',
                'label'=> 'Date',
                'init_selection' => [
                    'from' => $startDate,
                    'to' => $endDate
                ]
            ], 
            false, 
            function($value) {
                $dates = json_decode($value);
                $this->crud->addClause('where', 'created_at', '>=', $dates->from);
                $this->crud->addClause('where', 'created_at', '<=', $dates->to . ' 23:59:59');
            },
            function() use ($startDate, $endDate) {
                $this->crud->addClause('where', 'created_at', '>=', $startDate . ' 00:00:00');
                $this->crud->addClause('where', 'created_at', '<=', $endDate . ' 23:59:59');
            },
        );

        $this->crud->addFilter([
                'name' => 'member_id',
                'type' => 'select2_ajax',
                'label'=> 'Member',
                'placeholder' => 'Pick a member',
                'method' => 'POST'
            ], 
            url('members/for-filter'), 
            function($value) {
                $this->crud->addClause('where', 'member_id', $value);
            }
        );
        // Filter Bonus Type, Default GM and OR 
        $this->crud->addFilter([
                'name' => 'bonus_type',
                'type' => 'select2_multiple',
                'label'=> 'Bonus Type',
                'init_selection'=> ['GM', 'OR', 'SS'],
            ],
            [ 'GM' => 'GM', 'OR' => 'OR', 'BP' => 'BP', 'SS' => 'SS'],
            function($value) {
                $this->crud->addClause('whereIn', 'bonus_type', json_decode($value));
            },
            function() {
                $this->crud->addClause('whereIn', 'bonus_type', ['GM', 'OR', 'SS']);
            },
        );
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        return view('vendor.backpack.crud.list_bonus', $this->data);
    }

    public function totalTransactions() 
    {
        $requests = collect(request()->all());
        $bonusType = null;
        $dateRange = null;
        $memberId = null;
        if ($requests->has('bonus_type')) {
            $bonusType = $requests->get('bonus_type');
            $bonusType = json_decode($requests->get('bonus_type'));
        }
        if ($requests->has('created_at')) {
            $dateRange = $requests->get('created_at');
            $dateRange = json_decode($dateRange);
        }
        if ($requests->has('member_id')) {
            $memberId = $requests->get('member_id');
        }
        $bonus = BonusHistory::
            leftJoin(
                DB::raw("( 
                    SELECT 
                        `members`.`id` as `id`,
                        `members`.`npwp` as `npwp`,
                        `configurations`.`value` as `tax_percentage`
                    from
                        `members`
                    LEFT JOIN
                        `configurations`
                    ON CASE
                        WHEN `members`.`npwp` = '' OR `members`.`npwp` = NULL THEN
                            `configurations`.`key` = 'bonus_tax_percentage_non_npwp'
                        ELSE
                            `configurations`.`key` = 'bonus_tax_percentage_npwp'
                        END
                    ) as `members`
                "),
                'bonus_histories.member_id', '=', 'members.id'
            )
            ->whereBonusType($bonusType)
            ->whereCreatedAt($dateRange)
            ->whereMember($memberId)
            ->select(
                DB::raw('SUM(bonus) as total_bonus'), 
                DB::raw('SUM(bonus - (bonus * (50/100) * (members.tax_percentage / 100) )) as total_bonus_after_tax'), 
                DB::raw('COUNT(*) as total_transaction')
            )
            ->first();
        return response()->json([
            'total_bonus' => 'Rp. ' . number_format($bonus->total_bonus, 2, ',', ','),
            'total_transactions' => $bonus->total_transaction,
            'total_bonus_after_tax' => 'Rp. ' . number_format($bonus->total_bonus_after_tax, 2, ',', ','),
        ]);
    }
}
