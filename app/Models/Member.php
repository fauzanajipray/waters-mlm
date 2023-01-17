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
        return '<a href="' . backpack_url('member') . '/' . $this->id . '/download-card-member" target="_blank" class="btn btn-sm btn-link"><i class="la la-download"></i> Card</a>';
    }

    public function reportMember(){
        return '<a href="' . backpack_url('member') . '/' . $this->id . '/report-member" class="btn btn-sm btn-link"><i class="la la-chart-bar"></i> Downline</a>';
    }

    function register(){
        return '<a href="'.backpack_url('member').'/register-form" class="btn btn-primary" data-style="zoom-in"><span class="ladda-label"><i class="la la-print"></i> Print Form Pendaftaran</span></a>';
    }

    function line_register(){
        return '<a href="' . backpack_url('member') . '/' . $this->id . '/download-register" target="_blank" class="btn btn-sm btn-link"><i class="la la-print"></i> Form Pendaftaran</a>';
    }

    function checkIsActive(){
        if($this->expired_at >= Carbon::now() || $this->expired_at != null){
            return true;
        } else {
            return false;
        }
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

    public function branch(){
        return $this->belongsTo('App\Models\Branch', 'branch_id', 'id');
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

    public function scopeIsNSI() {
        return $this->where('member_type', 'NSI');
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

    public function getGenderAttribute($value){
        if($value == 'M'){
            return 'Laki-laki';
        } else {
            return 'Perempuan';
        }
    }

    public function setGenderAttribute($value){
        if($value == 'P'){
            return $this->attributes['gender'] = 'F';
        } else if ($value == 'L'){
            return $this->attributes['gender'] = 'M';
        } else {
            return $value;
        }
    }

    public function getNameAttribute($value){
        return Str::title($value);
    }

    public function setNameAttribute($value){
        return $this->attributes['name'] = Str::title($value);
    }
}
