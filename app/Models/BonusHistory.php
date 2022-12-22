<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BonusHistory extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'bonus_histories';
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

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function transaction(){
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
    
    public function member(){
        return $this->belongsTo(Member::class, 'member_id');
    }
    
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeWhereBonusType($query, $bonusType)
    {
        if (request()->has('bonus_type')) {
            $query->whereIn('bonus_type', $bonusType);
        } else  {
            $query->whereIn('bonus_type', ['GM', 'OR']);
        }
        return $query; 
    }

    public function scopeWhereCreatedAt($query, $created_at)
    {
        if (request()->has('created_at')) {
            $query->whereBetween('created_at', [$created_at->from, $created_at->to]);
        } else {
            $startDate = Carbon::now()->firstOfMonth()->startOfDay()->format('Y-m-d H:i:s');
            $endDate = Carbon::now()->endOfDay()->format('Y-m-d H:i:s');
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        return $query;
    }

    public function scopeWhereMember($query, $memberID) {
        if (request()->has('member_id')) {
            $query->where('member_id', $memberID);
        }
        return $query;
    }

    public function scopeMonthYear($query, $monthYear)
    {
        return $query->whereMonth('created_at', $monthYear->month)
            ->whereYear('created_at', $monthYear->year);
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
