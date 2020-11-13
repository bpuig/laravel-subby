<?php

namespace Bpuig\Subby\Tests\Models;

use Bpuig\Subby\Traits\HasSubscriptions;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasSubscriptions;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

}
