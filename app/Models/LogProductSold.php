<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogProductSold extends Model
{
    protected $table = 'log_product_sold';
    protected $guarded = ['id'];
}
