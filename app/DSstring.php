<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DSstring extends Model
{
    protected $table = "DSstring";
    protected $fillable = [
        'first_name', 'last_name', 'age'
    ];
}
