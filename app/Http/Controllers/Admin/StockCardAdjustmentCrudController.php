<?php

namespace App\Http\Controllers\Admin;

use App\Models\Stock;
use App\Models\StockHistory;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class StockCardDetailCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StockCardAdjustmentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;

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
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/stock-card/' . $stockID . '/adjustment');
        $this->crud->setEntityNameStrings('Stock Adjustment', 'Stock Adjustment');
    }
    
    protected function setupCreateOperation(){
        if($this->crud->stock->product->type == 'sparepart'){
            $this->crud->stock->product->text = $this->crud->stock->product->name;
        } else {
            $this->crud->stock->product->text = $this->crud->stock->product->name . ' - ' . $this->crud->stock->product->model;
        }
        $this->crud->setValidation([
            'stock_id' => 'required',
            'branch' => 'required',
            'product' => 'required',
            'stock_before' => 'required',
            'quantity' => 'required|numeric|min:1',
            'descriptions' => 'required',
        ]);
        $this->crud->addFields([
            [
                'name' => 'stock_id',
                'type' => 'hidden',
                'value' => $this->crud->stock->id,
            ],
            [
                'name' => 'branch',
                'label' => 'Branch',
                'type' => 'text',
                'attributes' => [
                    'readonly' => 'readonly'
                ],
                'value' => $this->crud->stock->branch->name,
            ],
            [
                'name' => 'product',
                'label' => 'Product',
                'type' => 'text',
                'attributes' => [
                    'readonly' => 'readonly'
                ],
                'value' => $this->crud->stock->product->text,
            ],
            [
                'name' => 'stock_before',
                'label' => 'Stock Before',
                'type' => 'number',
                'attributes' => [
                    'readonly' => 'readonly'
                ],
                'value' => $this->crud->stock->quantity,
            ],
            [
                'name' => 'quantity',
                'label' => 'Adjustment Quantity',
                'type' => 'number',
                'attributes' => [
                    'min' => 0,
                ],
                'value' => 0,
            ],
            [
                'name' => 'descriptions',
                'label' => 'Descriptions',
                'type' => 'textarea',
            ]
        ]);
    }

    public function store() {
        $request = $this->crud->validateRequest();
        DB::beginTransaction();
        try {
            if($request->quantity > $this->crud->stock->quantity){
                $diff = $request->quantity - $this->crud->stock->quantity;
                $request->quantity = $diff;
            } else if($request->quantity < $this->crud->stock->quantity){
                $diff = $this->crud->stock->quantity - $request->quantity;
                $request->quantity = - (int) $diff;
            } 
            $stock = Stock::find($request->stock_id);
            $stock->quantity = $stock->quantity + $request->quantity;
            $stock->save();
            // adjustment stock history
            $stockHistory = new StockHistory;
            $stockHistory->type = 'adjustment';
            $stockHistory->product_id = $stock->product_id;
            $stockHistory->branch_id = $stock->branch_id;
            $stockHistory->quantity = $request->quantity;
            $stockHistory->descriptions = $request->descriptions;
            $stockHistory->adjustment_by = backpack_user()->id;
            $stockHistory->save();
            DB::commit();
            return redirect('/stock-card')->with('success', 'Stock Adjustment has been saved successfully!');
        } catch(Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        } 
    }
}
