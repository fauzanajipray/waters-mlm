<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Venturecraft\Revisionable\RevisionableTrait;

class Member extends Model
{
    use CrudTrait, SoftDeletes, RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'members';
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

    public function cardMember(){
        return '<a href="' . backpack_url('member') . '/' . $this->id . '/download-card-member" target="_blank" class="btn btn-sm btn-link"><i class="la la-download"></i> Card Member</a>';

    }

    public function reportMember(){
        return '<a href="' . backpack_url('member') . '/' . $this->id . '/report-member" class="btn btn-sm btn-link"><i class="la la-chart-bar"></i> Report Member</a>';
    }

    public function addTransaction(){
        return '<a href="' . backpack_url('transaction') . '/create?member_id=' . $this->id . '" class="btn btn-sm btn-link"><i class="la la-plus"></i> Add Transaction</a>';
    }

    function register(){
        return '<a href="'.backpack_url('member').'/register-form" class="btn btn-primary" data-style="zoom-in"><span class="ladda-label"><i class="la la-print"></i> Print Form Register</span></a>';
    }

    function line_register(){
        return '<a href="' . backpack_url('member') . '/' . $this->id . '/download-register" target="_blank" class="btn btn-sm btn-link"><i class="la la-print"></i> Register</a>';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function level(){
        return $this->belongsTo('App\Models\Level', 'level_id', 'id');
    }

    public function upline(){
        return $this->belongsTo('App\Models\Member', 'upline_id', 'id');
    }

    public function bonus(){
        return $this->hasMany('App\Models\BonusHistory', 'member_id', 'id');
    }
    
    public function transactions(){
        return $this->hasMany('App\Models\Transaction', 'member_id', 'id');
    }

    public function downlines(){
        return $this->hasMany('App\Models\Member', 'upline_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeActive() {
        return $this->where('expired_at', '>=', Carbon::now());
    }

    public function scopeIsNotActive() {
        return $this->where('expired_at', '<', date('Y-m-d'))->orWhere('expired_at', null);
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
