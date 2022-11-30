<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Transaction extends Model
{
    use CrudTrait, SoftDeletes, RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'transactions';
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

    function letterRoad(){
        return '<a href="'. backpack_url('transaction') . '/' . $this->id . '/download-letter-road" class="btn btn-sm btn-link"><i class="la la-print"></i> Download</a>';
    }

    function invoice(){
        return '<a href="'. backpack_url('transaction') . '/' . $this->id . '/download-invoice" class="btn btn-sm btn-link"><i class="la la-print"></i> Invoice</a>';
    }

    function buttonAddPayment(){
        $status_paid = $this->status_paid;
        $payment = TransactionPayment::where('transaction_id', $this->id)->count();
        if($status_paid == 0 || $payment < 1){
            return '<a href="'. backpack_url('transaction-payment') . '/create?transaction_id=' . $this->id . '" class="btn btn-sm btn-link"><i class="la la-plus"></i> Add Payment</a>';
        }
    }
 
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function member() {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function transactionProducts() {
        return $this->hasMany(TransactionProduct::class, 'transaction_id');
    }
    
    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    
    public function transactionPayments(){
        return $this->hasMany(transactionPayment::class, 'transaction_id');
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
