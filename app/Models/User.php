<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CrudTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'member_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function cardMember(){
        return '<a href="' . backpack_url('member') . '/' . $this->id . '/download-card-member" target="_blank" class="btn btn-sm btn-link"><i class="la la-download"></i> Card Member</a>';

    }

    public function reportMember(){
        return '<a href="' . backpack_url('member') . '/' . $this->id . '/report-member" class="btn btn-sm btn-link"><i class="la la-chart-bar"></i> Report Member</a>';
    }

    public function registerMember(){
        if($this->member_id == null){
            return '<a href="' . backpack_url('member') . '/' . $this->id . '/create-member" class="btn btn-sm btn-link"><i class="la la-user-plus"></i> Register Member</a>';
        }else{
            return '<a href="#" class="btn btn-sm btn-link text-disable"><i class="la la-user-edit"></i> Already Registered</a>';
        }
    }
}
