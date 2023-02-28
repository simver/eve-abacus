<?php

namespace App\domain;


use App\Acl\Ceve;
use App\Models\Type;
use App\Models\TypePrice;

class GetPrice
{
    public static function getPriceNeed(): void
    {
        $types = Type::query()
            ->where('price_need', Type::PRICE_NEED_YES)
            ->get()->toArray();

        // 获取ESI价格列表
//        $marketPrices = Esi::marketPrices();
//        $marketPricesMap = array_column($marketPrices, 'average_price', 'type_id');

        foreach ($types as $type) {
//            if (!empty($marketPricesMap[$type['type_id']])) {
                self::updateJitaPrice($type['type_id']);
//            }
        }

    }

    public static function updateJitaPrice(int $typeId): void
    {
        // 获取吉他价格
        $jitaPrice = Ceve::getJitaPrice($typeId);
        $attributes = [
            'type_id' => $typeId,
            'region_id' => $jitaPrice['regionId'],
            'system_id' => $jitaPrice['systemId'],
        ];
        $values = [
            'type_id' => $typeId,
            'region_id' => $jitaPrice['regionId'],
            'system_id' => $jitaPrice['systemId'],
            'buy_max' => $jitaPrice['buy']['max'],
            'buy_min' => $jitaPrice['buy']['min'],
            'buy_quantity' => $jitaPrice['buy']['volume'],
            'sell_max' => $jitaPrice['sell']['max'],
            'sell_min' => $jitaPrice['sell']['min'],
            'sell_quantity' => $jitaPrice['sell']['volume'],
        ];
        TypePrice::query()->updateOrCreate($attributes, $values);
    }
}
