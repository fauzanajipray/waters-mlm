<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'stocks';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    public function detailStockButton($crud) {
        if(backpack_user()->hasPermissionTo('Detail Stock Card')){
            if(isset($crud->startDate) && isset($crud->endDate)){
                return '<a class="btn btn-sm btn-link" href="'.backpack_url('stock-card/'. $this->id . '/detail?start_date='.$crud->startDate.'&end_date='.$crud->endDate).'"><i class="la la-eye"></i> Detail</a>';
            } else {
                return '<a class="btn btn-sm btn-link" href="'.backpack_url('stock-card/'. $this->id . '/detail').'"><i class="la la-eye"></i> Detail</a>';
            }
        } else {
            return '';
        }
    }

    public function addAdjustmentButton($crud) {
        if(backpack_user()->hasPermissionTo('Adjustment Stock Card')){
            return '<a class="btn btn-sm btn-link" href="'.backpack_url('stock-card/'. $this->id . '/adjustment/create').'"><i class="la la-plus"></i> Adjustment</a>';
        }
        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\Branch');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
