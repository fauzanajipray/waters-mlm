<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BranchProductRequest;
use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Member;
use App\Models\Product;
use App\Models\Stock;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Prologue\Alerts\Facades\Alert;

use function PHPUnit\Framework\isEmpty;

/**
 * Class BranchCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BranchCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
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
        CRUD::setModel(\App\Models\Branch::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/branch');
        CRUD::setEntityNameStrings('branch', 'branches');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        if(!backpack_user()->hasPermissionTo('Read Branch')){
            $this->crud->denyAccess(['list', 'show']);
        }
        if(!backpack_user()->hasPermissionTo('Create Branch')){
            $this->crud->denyAccess(['create']);
        }
        if(!backpack_user()->hasPermissionTo('Update Branch')){
            $this->crud->denyAccess(['update']);
        }
        if(!backpack_user()->hasPermissionTo('Delete Branch')){
            $this->crud->denyAccess(['delete']);
        }
        $this->crud->column('name');
        $this->crud->column('address');
        $this->crud->column('type');
        $this->crud->addColumn([
            'name' => 'member',
            'label' => 'Owner',
            'type' => 'relationship',
            'attribute' => 'name',
            'entity' => 'member',
            'model' => 'App\Models\Member',
            'wrapper' => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('member/' . $related_key . '/show');
                },
            ],
        ]);
        $this->crud->column('area');
        // $this->crud->addButtonFromModelFunction('line', 'add_stock', 'stockButton', 'beginning'); // TODO: add stock button
        if(backpack_user()->hasPermissionTo('Add Owner Branch')){
            $this->crud->addButtonFromModelFunction('line', 'add_owner', 'addOwnerButton', 'beginning');
        }
        $this->crud->addButtonFromModelFunction('line', 'deleteButton', 'deleteButton', 'end');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $branch = Branch::count();
        CRUD::setValidation(BranchRequest::class);
        $this->crud->field('name');
        $this->crud->addField([
            'name' => 'type',
            'type' => 'select_from_array',
            'options' => ($branch == 0) ? ['PUSAT' => 'PUSAT', 'CABANG' => 'CABANG', 'STOKIST' => 'STOKIST'] : ['CABANG' => 'CABANG', 'STOKIST' => 'STOKIST'],
            'allows_null' => false,
            'default' => 'CABANG',
        ]);
        $this->crud->field('address');
        $this->crud->addField([
            'name' => 'area_id',
            'type' => 'relationship',
            'attribute' => 'name',
            'entity' => 'area',
            'model' => 'App\Models\Area',
        ]);
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
        $this->crud->modifyField('type', [
            'attributes' => [
                'readonly' => 'readonly',
            ],
        ]);
    }

    protected function store(Request $request) {
        $requests = $request->all();
        $this->crud->validateRequest($requests);

        DB::beginTransaction();
        try {
            $branch = Branch::create($requests);
            foreach (Product::all() as $p) {
                BranchProduct::updateOrCreate([
                    'branch_id' => $branch->id,
                    'product_id' => $p->id,
                ], [
                    'branch_id' => $branch->id,
                    'product_id' => $p->id,
                    'additional_price' => 0,
                ]);
            }
            DB::commit();
            return redirect()->route('branch.index');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function memberNotExist(Request $request)
    {
        $search_term = $request->input('q');
        $member_type = request()->form[3];

        if($member_type['name'] != 'member_type') {
            $member_type = request()->form[4];
            if($member_type['name'] != 'member_type') {
                return response()->json([]);
            }
        }

        if($member_type['value'] == 'NSI') {
            return $this->memberExist($request);
        }

        if($search_term) {
            $branch = Branch::whereDoesntHave('member')
                ->where('name', 'like', '%'.$search_term.'%')
                ->type($member_type['value'])
                ->get();

        } else {
            $branch = Branch::whereDoesntHave('member')
                ->type($member_type['value'])
                ->get();
        }
        return $branch;
    }

    public function memberExist(Request $request)
    {
        $search_term = $request->input('q');

        if($search_term) {
            $branch = Branch::whereHas('member')
                ->where('name', 'like', '%'.$search_term.'%')
                ->get();

        } else {
            $branch = Branch::whereHas('member')
                ->get();
        }
        return $branch;
    }

    protected function setupUpdateRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/{id}/edit', [
            'as'        => $routeName.'.edit',
            'uses'      => $controller.'@edit',
            'operation' => 'update',
        ]);

        Route::put($segment.'/{id}', [
            'as'        => $routeName.'.update',
            'uses'      => $controller.'@update',
            'operation' => 'update',
        ]);

        Route::get($segment.'/{id}/addOwner', [
            'as'        => $routeName.'.addOwner',
            'uses'      => $controller.'@addOwner',
            'operation' => 'update',
        ]);
    }

    public function addOwner($id)
    {
        if(!backpack_user()->hasPermissionTo('Add Owner Branch')){
            abort(403);
        }
        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        // get the info for that entry

        $this->data['entry'] = $this->crud->getEntryWithLocale($id);
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = 'Add Owners';
        $this->data['id'] = $id;

        $this->crud->removeAllFields();
        $this->crud->addFields([
            [
                'name' => 'update_type',
                'type' => 'hidden',
                'value' => 'add_owner',
                'attributes' => [
                    'id' => 'update_type'
                ],
            ],
            [
                'name' => 'member_id',
                'label' => 'Member',
                'type' => 'select2_from_ajax',
                'entity' => 'member',
                'attribute' => 'text',
                'model' => 'App\Models\Member',
                'data_source' => url('members/not-branch-owner'),
            ]
        ]);

        $this->crud->setEntityNameStrings('owners', 'Owners');

        return view($this->crud->getEditView(), $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->crud->hasAccessOrFail('update');
        $update_type = $request->update_type;
        if($update_type == 'add_owner') {
            DB::beginTransaction();
            try {
                $member_id = $request->member_id;
                $branch_id = $request->id;
                $branch_type = Branch::find($branch_id)->type;
                $member = Member::find($member_id);
                $member->branch_id = $branch_id;
                $member->member_type = $branch_type;
                $member->save();
                DB::commit();
                return redirect()->route('branch.index');
            } catch (Exception $e) {
                DB::rollback();
                return redirect()->back()->with('error', $e->getMessage());
            }
        } else {
            $request = $this->crud->validateRequest();
            $this->crud->update($id, $request->all());
            return $this->crud->performSaveAction($id);
        }
    }

    public function getOriginBranch(Request $request)
    {
        $search_term = $request->input('q');
        $branch_id = request()->form[2];
        if($branch_id['name'] != 'branch_id') {
            return response()->json([]);
        }

        $branch_type = Branch::find($branch_id['value'])->type;
        if($branch_type == "STOKIST") {
            $branch_type = "CABANG";
        } else if($branch_type == "CABANG") {
            $branch_type = "PUSAT";
        } else if ($branch_type == "PUSAT") {
            $branch_type = null;
        } else {
            return response()->json([]);
        }

        if($search_term) {
            $branch = Branch::where('type', $branch_type)
                ->where('name', 'like', '%'.$search_term.'%')
                ->get();
        } else {
            $branch = Branch::where('type', $branch_type)
                ->get();
        }
        $branch->map(function($item) {
            if(isset($item->member)) $item->name = $item->name . ' | ' . $item->member->name;

            return $item;
        });
        return $branch;
    }

    public function show(Request $request, $id)
    {
        $this->crud->hasAccessOrFail('show');
        $this->setupListOperation();

        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['stocks'] = Stock::join('products', 'products.id', '=', 'stocks.product_id')
            ->where('branch_id', $id)
            ->where('quantity', '>', 0)
            ->select('stocks.*', 'products.*')
            ->get();
        return view('branch.show', $this->data);
    }

    public function getBranchesForFilter(Request $request)
    {
        $search_term = $request->input('q');

        if($search_term) {
            $branch = Branch::where('name', 'like', '%'.$search_term.'%')
                ->get();

        } else {
            $branch = Branch::get();
        }
        return $branch->pluck('name', 'id');
    }

    public function getBranches()
    {
        $search_term = request()->input('q');

        if($search_term) {
            $branch = Branch::where('name', 'like', '%'.$search_term.'%')
                ->get();
        } else {
            $branch = Branch::get();
        }

        $branch->map(function($item) {
            if(isset($item->member)) $item->name = $item->name . ' | ' . $item->member->name;
            return $item;
        });
        return $branch;
    }

    public function getBranchStock()
    {
        $search_term = request()->input('q');
        $form = collect(request()->form);
        $memberId = $form->where('name', 'member_id')->first();
        if(!$memberId) {
            return response()->json([]);
        }
        $memberId = $memberId['value'];
        $member = Member::with(['branch' => function ($query) {
            $query->join('areas', 'areas.id', '=', 'branches.area_id')
                ->select('branches.*', 'areas.name as area_name', 'areas.id as area_id');
        }])->find($memberId);
        if($search_term) {
            if($member->branch->type == 'STOKIST') {
                if($member->branch->area_id == 1) {
                    $branches = Branch::whereIn('type', ['CABANG', 'PUSAT'])
                    ->where('name', 'like', '%'.$search_term.'%')->get();
                } else {
                    $branches = Branch::where('type', 'CABANG')
                    ->where('name', 'like', '%'.$search_term.'%')->get();
                }
            } else if($member->branch->type == 'CABANG') {
                $branches = Branch::where('type', 'PUSAT')
                    ->where('name', 'like', '%'.$search_term.'%')
                    ->get();
            } else {
                return response()->json([]);
            }
        } else {
            if($member->branch->type == 'STOKIST') {
                if($member->branch->area_id == 1) {
                    $branches = Branch::whereIn('type', ['CABANG', 'PUSAT'])->get();
                } else {
                    $branches = Branch::where('type', 'CABANG')->get();
                }
            } else if($member->branch->type == 'CABANG') {
                $branches = Branch::where('type', 'PUSAT')
                    ->get();
            } else {
                return response()->json([]);
            }

        }

        $branches->map(function($item) {
            if(isset($item->member)) $item->name = $item->name . ' | ' . $item->member->name;
            return $item;
        });
        return $branches;
    }

    public function index()
    {
        $this->crud->hasAccessOrFail('list');
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        return view('vendor.backpack.crud.list_with_error_head', $this->data);
    }

    public function deleteBranch($id){
        DB::beginTransaction();
        try {
            $branch = Branch::with('member')->find($id);
            $stock = Stock::where('branch_id', $id)->get();

            if(isset($branch->member)) {
                throw new \Exception('You can\'t delete this branch because it has owner or member');
            } else if ($stock->count() > 0) {
                throw new \Exception('You can\'t delete this branch because it has stock');
            }
            $product = BranchProduct::where('branch_id', $id)->get();
            $product->each(function($item) {
                $item->delete();
            });
            $branch->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Branch deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
