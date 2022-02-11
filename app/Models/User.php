<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\HumanResource\Employee;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ADMIN_USER = 'true';
    const REGULAR_USER = 'false';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'admin',
        'employee_id',
        'fname',
        'lname',
        'positionName',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    

    public function isAdmin()
    {
        return $this->admin == User::ADMIN_USER;
    }

    
    public function employee(){
        return $this->belongsTo(employee::class);
    }

}
