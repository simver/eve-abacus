<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlueprintMaterial extends Model
{
    public $table      = 'blueprint_material';
    public $timestamps = false;
    public $guarded    = [];

    const ACTIVITY_TYPE_MANUFACTURING = 1;
    const ACTIVITY_TYPE_INVENTION     = 2;
}
