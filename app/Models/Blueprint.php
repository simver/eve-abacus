<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blueprint extends Model
{
    public $table      = 'blueprint';
    public $timestamps = false;
    public $guarded    = [];

    const FOR_SALE_NO  = 0;
    const FOR_SALE_YES = 1;

}
