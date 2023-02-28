<?php
/**
 * Contract.php of eve_abacus.
 * Date: 2023-02-28
 */

namespace App\domain;

use App\Acl\Esi;
use App\Common\Universe;
use App\Models\Blueprint;
use App\Models\ContractItemCache;

class Contract
{
    public static function getForgeContracts(): \Generator
    {
        foreach (Esi::getContracts(Universe::FORGE) as $contracts) {
            foreach ($contracts as $contract) {
                yield $contract;
            }
        }
    }

    public static function updateContracts(): void
    {
        $now = time();
        ContractItemCache::query()->truncate();
        // 获取蓝图ID列表
        $blueprintIds = Blueprint::query()->pluck('type_id')->toArray();
        // 降低查库缓存
        $contractItemCacheLocal = [];
        foreach (self::getForgeContracts() as $contract) {
            if ($contract['type'] != 'item_exchange') continue;
            if ($contract['volume'] != 0.01) continue;
            // 要最少一天有效的订单（时区时间也加在误差时间内，实际为一天+8小时）
            if (strtotime($contract['date_expired']) - 86400 < $now) continue;
            $contractItems = Esi::getContractItems($contract['contract_id']);
            if (empty($contractItems)) continue;
            $contractItem = current($contractItems);
            if (empty($contractItem['type_id'])) continue;
            if (!in_array($contractItem['type_id'], $blueprintIds)) continue;
            if (!isset($contractItem['time_efficiency'])) {
                dump($contractItem);
                continue;
            };
            $model = [
                'type_id' => $contractItem['type_id'],
                'price' => $contract['price'],
                'is_blueprint_copy' => $contractItem['is_blueprint_copy'] ?? 0,
                'time_efficiency' => $contractItem['time_efficiency'],
                'material_efficiency' => $contractItem['material_efficiency'],
                'runs' => $contractItem['runs'] ?? 0,
            ];
            // 缓存检查
            if (empty($contractItemCacheLocal[$contractItem['type_id']])) {
                ContractItemCache::query()->create($model);
                $contractItemCacheLocal[$contractItem['type_id']] = $model;
            } else {
                $cache = $contractItemCacheLocal[$contractItem['type_id']];
                if ($cache['price'] > $contract['price']) {
                    ContractItemCache::query()
                        ->where('type_id', $contractItem['type_id'])->update($model);
                    $contractItemCacheLocal[$contractItem['type_id']] = $model;
                }
            }
        }
    }
}
