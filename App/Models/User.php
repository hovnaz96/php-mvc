<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Example user model
 *
 * PHP version 7.0
 */
class User extends Eloquent
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'name', 'email', 'password', 'firebase_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
}
