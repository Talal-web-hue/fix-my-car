<?php

namespace App\Models;
// use App\Models\Authenticatable\User;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    use HasFactory, Notifiable , HasApiTokens;


    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }


    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}