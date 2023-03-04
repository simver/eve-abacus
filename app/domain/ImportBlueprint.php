<?php
/**
 * ImportBlueprint.php of eve_abacus.
 * Date: 2023-02-24
 */

namespace App\domain;

use App\Acl\Esi;
use App\Models\Blueprint;
use App\Models\BlueprintMaterial;
use App\Models\Type;

class ImportBlueprint
{
    /**
     * array:3 [
     * "activities" => array:4 [
     * "copying" => array:1 [
     * "time" => 480
     * ]
     * "manufacturing" => array:3 [
     * "materials" => array:1 [
     * 0 => array:2 [
     * "quantity" => 86
     * "typeID" => 38
     * ]
     * ]
     * "products" => array:1 [
     * 0 => array:2 [
     * "quantity" => 1
     * "typeID" => 165
     * ]
     * ]
     * "time" => 600
     * ]
     * "research_material" => array:1 [
     * "time" => 210
     * ]
     * "research_time" => array:1 [
     * "time" => 210
     * ]
     * ]
     * "blueprintTypeID" => 681
     * "maxProductionLimit" => 300
     * ]
     *
     * @param string $filepath
     * @return void
     */
    public static function import(string $filepath): void
    {
        // 获取ESI价格列表
        $marketPrices = Esi::marketPrices();
        $marketPricesMap = array_column($marketPrices, 'average_price', 'type_id');
        // 重置数据库标记字段
        Blueprint::query()->truncate();
        BlueprintMaterial::query()->truncate();
        Type::query()->update(['price_need' => Type::PRICE_NEED_NO]);
        $inventionTypeIds = [];
        // 读取文件更新
        foreach (yaml_parse_file($filepath) as $blueprint) {
            // 过滤已不发布蓝图
            $typeModel = Type::query()->where('type_id', $blueprint['blueprintTypeID'])
                ->first();
            if (is_null($typeModel) || $typeModel->published == 0) continue;
            // 蓝图基础信息准备
            if (!isset($blueprint['activities'])) continue;
            if (!isset($blueprint['activities']['manufacturing'])) continue;
            if (!isset($blueprint['activities']['manufacturing']['products'])) continue;
            $attributesB = ['type_id' => $blueprint['blueprintTypeID']];
            $valuesB = [
                'type_id' => $blueprint['blueprintTypeID'],
                'copying_time' => !empty($blueprint['activities']['copying']) && !empty($blueprint['activities']['copying']['time']) ?: 0,
                'manufacturing_time' => $blueprint['activities']['manufacturing']['time'],
                'product_type_id' => $blueprint['activities']['manufacturing']['products'][0]['typeID'],
                'product_quantity' => $blueprint['activities']['manufacturing']['products'][0]['quantity'],
            ];
            // 标记出售蓝图
            if (!empty($marketPricesMap[$blueprint['blueprintTypeID']])) {
                $valuesB['for_sale'] = Blueprint::FOR_SALE_YES;
                $valuesB['average_price'] = $marketPricesMap[$blueprint['blueprintTypeID']];
            }
            if (!empty($blueprint['activities']['invention'])
                && !empty($blueprint['activities']['invention']['materials'])) {
                $valuesB['invention_type_id'] = $blueprint['activities']['invention']['products'][0]['typeID'];
                $valuesB['invention_probability'] = $blueprint['activities']['invention']['products'][0]['probability'];
                $valuesB['invention_quantity'] = $blueprint['activities']['invention']['products'][0]['quantity'];
                // 记录研发图ID
                $inventionTypeIds[] = $valuesB['invention_type_id'];
            }
            // 蓝图基础信息录入
            Blueprint::query()->updateOrCreate($attributesB, $valuesB);
            // 蓝图产品修改type表price need
            Type::setPriceNeed($valuesB['product_type_id']);
            // 蓝图材料信息准备
            if (!isset($blueprint['activities']['manufacturing']['materials'])) continue;
            // 蓝图生产材料关系
            foreach ($blueprint['activities']['manufacturing']['materials'] as $materialM) {
                $attributesM = [
                    'blueprint_type_id' => $blueprint['blueprintTypeID'],
                    'material_type_id' => $materialM['typeID'],
                    'material_quantity' => $materialM['quantity'],
                    'activity_type' => BlueprintMaterial::ACTIVITY_TYPE_MANUFACTURING,
                ];
                // 写入蓝图材料关系表
                BlueprintMaterial::query()->create($attributesM);
                // 修改type表price need
                Type::setPriceNeed($materialM['typeID']);
            }
            // 蓝图研发材料关系
            if (empty($blueprint['activities']['invention'])) continue;
            if (empty($blueprint['activities']['invention']['materials'])) continue;
            foreach ($blueprint['activities']['invention']['materials'] as $materialI) {
                $attributesI = [
                    'blueprint_type_id' => $blueprint['blueprintTypeID'],
                    'material_type_id' => $materialI['typeID'],
                    'material_quantity' => $materialI['quantity'],
                    'activity_type' => BlueprintMaterial::ACTIVITY_TYPE_INVENTION,
                ];
                // 写入蓝图材料关系表
                BlueprintMaterial::query()->create($attributesI);
            }
        }
        // 研发图改标记为ForSale
        Blueprint::query()->whereIn('type_id', $inventionTypeIds)
            ->update(['for_sale' => Blueprint::FOR_SALE_YES]);
    }
}


