<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    public $table      = 'type';
    public $timestamps = false;
    public $guarded    = ['created_at', 'updated_at'];

    const PRICE_NEED_NO  = 0;
    const PRICE_NEED_YES = 1;

    public static function setPriceNeed(int $typeId)
    {
        $model = self::query()->where('type_id', $typeId)->first();
        if (!is_null($model) && $model->price_need != self::PRICE_NEED_YES) {
            $model->price_need = self::PRICE_NEED_YES;
            $model->save();
        }
    }
}
