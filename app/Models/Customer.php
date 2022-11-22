<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Customer extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory, RevisionableTrait;

    protected $table = 'customers';
    protected $guarded = ['id'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    public function member(){
        return $this->belongsTo(Member::class, 'member_id');
    }


    public function deleteButton(){
        return '<a href="'. backpack_url('customer') . '/' . $this->id . '/delete" class="btn btn-sm btn-link btn-delete-customer"><i class="la la-trash"></i> Delete</a>';
    }
}
