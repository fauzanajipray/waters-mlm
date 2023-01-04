<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Intervention\Image\ImageManagerStatic as Image;

class TransactionPayment extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'transaction_payments';
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

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function transaction(){
        return $this->belongsTo(Transaction::class, 'transaction_id');
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
