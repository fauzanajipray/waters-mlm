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

    public function setImageAttribute($value)
    {
        $attribute_name = "photo";
        $disk = "public";
        $destination_path = "uploads/image";
        
        // if the image was erased
        if ($value==null) {
            // delete the image from disk
            Storage::disk($disk)->delete($this->{$attribute_name});

            // set null in the database column
            $this->attributes[$attribute_name] = null;
        }

        // if a base64 was sent, store it in the db
        if (Str::startsWith($value, 'data:image'))
        {
            // 0. Make the image
            $image = Image::make($value)->encode('jpg', 90);

            // 1. Generate a filename.
            $filename = md5($value.time()).'.jpg';

            // 2. Store the image on disk.
            Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());

            // 3. Save the path to the database
            $this->attributes[$attribute_name] = $destination_path.'/'.$filename;

            return $this;
        }

        return null;
    }

    public function getImageAttribute($value)
    {
        return $value ? asset('storage/'.$value) : asset('storage/uploads/image/default.jpg');
    }

    

    public function setPhotoUrlAttribute($value)
    {
        $destination_path = "public/uploads/images";     
        $attribute_name = "photo_url";
        if(request()->{$attribute_name . '_change'}){
            // if the image was erased
            if ($value==null) {
                // delete the image from disk
                Storage::delete(Str::replaceFirst('storage/','public/', $this->{$attribute_name}));

                $this->attributes[$attribute_name] = null;
            }
        }

        // if a base64 was sent, store it in the db
        if (Str::startsWith($value, 'data:image'))
        {
            // 0. Make the image
            $image = Image::make($value)->encode('jpg', 75);

            // 1. Generate a filename.
            $filename = md5($value.time()).'.jpg';

            // 2. Store the image on disk.
            Storage::put($destination_path.'/'.$filename, $image->stream());

            // 3. Save the path to the database
            $this->attributes['photo_url'] = $destination_path.'/'.$filename;
        }
    }

    public function getPhotoUrlAttribute($value)
    {
        $url = Str::replaceFirst('public/','', $value);
        $value = $url;
        return $value ? $value : null;
    }
}
