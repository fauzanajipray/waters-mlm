<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use CrudTrait, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'branches';
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

    public function addOwnerButton()
    {
        $member =  $this->member;
        if (!$member) {
            return '<a href="' . route('branch.addOwner',$this->id) . '" class="btn btn-sm btn-link"><i class="la la-plus"></i> Add Owner</a>';
        }
    }

    public function stockButton(){
        return '<a href="'. backpack_url('stock/create') . '?branch_id=' . $this->id . '" class="btn btn-sm btn-link"><i class="la la-plus"></i> Stock</a>';
    }

    public function deleteButton(){
        return '<a href="'. backpack_url('branch') . '/' . $this->id . '/delete" class="btn btn-sm btn-link btn-delete-custom"><i class="la la-trash"></i> Delete</a>';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function member()
    {
        return $this->hasOne('App\Models\Member');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeType($query, $type)
    {
        if ($type == 'STOKIST') {
            return $query->where('type', 'STOKIST');
        } elseif ($type == 'CABANG') {
            return $query->where('type', 'CABANG');
        } elseif ($type == 'PUSAT') {
            return $query->where('type', 'PUSAT');
        } else {
            return $query;
        }
    }

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
