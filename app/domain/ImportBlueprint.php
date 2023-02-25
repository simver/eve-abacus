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
        Blueprint::query()->update(['for_sale' => Blueprint::FOR_SALE_NO]);
        Type::query()->update(['price_need' => Type::PRICE_NEED_NO]);
        // 读取文件更新
        foreach (yaml_parse_file($filepath) as $blueprint) {
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
            if (empty($marketPricesMap[$blueprint['blueprintTypeID']])) {
                $valuesB['for_sale'] = Blueprint::FOR_SALE_NO;
            } else {
                $valuesB['for_sale'] = Blueprint::FOR_SALE_YES;
                $valuesB['average_price'] = $marketPricesMap[$blueprint['blueprintTypeID']];
            }
            // 蓝图基础信息录入
            Blueprint::query()->updateOrCreate($attributesB, $valuesB);
            // 蓝图产品修改type表price need
            Type::setPriceNeed($valuesB['product_type_id']);
            // 蓝图材料信息准备
            if (!isset($blueprint['activities']['manufacturing']['materials'])) continue;

            // 查询已入库的蓝图材料信息
            $oldMaterials = BlueprintMaterial::query()
                ->where('blueprint_type_id', $blueprint['blueprintTypeID'])
                ->get()->toArray();
            $oldMaterialsMap = array_column($oldMaterials, 'material_quantity', 'material_type_id');
            foreach ($blueprint['activities']['manufacturing']['materials'] as $material) {
                if (!isset($oldMaterialsMap[$material['typeID']]) ||
                    ($material['quantity'] != $oldMaterialsMap[$material['typeID']])
                ) {
                    $attributesBM = [
                        'blueprint_type_id' => $blueprint['blueprintTypeID'],
                        'material_type_id' => $material['typeID'],
                    ];
                    $valuesBM = [
                        'blueprint_type_id' => $blueprint['blueprintTypeID'],
                        'material_type_id' => $material['typeID'],
                        'material_quantity' => $material['quantity'],
                    ];
                    // 写入蓝图材料关系表
                    BlueprintMaterial::query()->updateOrCreate($attributesBM, $valuesBM);
                }
                // TODO 删除不存在的蓝图材料关系
                // 修改type表price need
                Type::setPriceNeed($material['typeID']);
            }
        }
    }
}


