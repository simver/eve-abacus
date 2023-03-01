<?php

namespace App\domain;

use App\Models\Blueprint;
use App\Models\BlueprintMaterial;
use App\Models\ContractItemCache;
use App\Models\ContractItemCacheBak;
use App\Models\Type;
use App\Models\TypePrice;
use Illuminate\Support\Facades\DB;

class ManufacturingList
{
    // 中介费
    public $brokerFee = 0.0298;
    // 销售税
    public $salesTax = 0.08;
    // 物品信息缓存
    public $typesCache = [];

    public function getProfitRank(string $type): array
    {
        if ('market' == $type) return $this->getProfitRankMarket();
        if ('contract' == $type) return $this->getProfitRankContract();
        return [];
    }

    public function getProfitRankMarket(): array
    {
        // 查询市场售卖蓝图
        $blueprints = Blueprint::query()
            ->where('for_sale', Blueprint::FOR_SALE_YES)
            ->get()->toArray();
        $rank = $this->getRank($blueprints);
        array_multisort(array_column($rank, 'profit_for_buyer_sec'), SORT_DESC, SORT_NUMERIC, $rank);
        return $rank;
    }

    public function getProfitRankContract(): array
    {
        // 查询合同蓝图
        $blueprintInContract = DB::table('blueprint as b')
            ->join('contract_item_cache_bak as cb', 'b.type_id', '=', 'cb.type_id')
            ->where('b.for_sale', Blueprint::FOR_SALE_NO)
            ->select(['b.*', 'cb.price as contract_price', 'cb.runs as runs'])->get()->toArray();
        $rank = $this->getRank($blueprintInContract);
        array_multisort(array_column($rank, 'profit_for_buyer_sec'), SORT_DESC, SORT_NUMERIC, $rank);
        return $rank;
    }

    public function getRank(array $blueprints): array
    {
        $rank = [];
        $salesOrderDiscount = 1 - ($this->salesTax + $this->brokerFee);
        $salesImmediateDiscount = 1 - $this->salesTax;
        foreach ($blueprints as $blueprint) {
            if (is_object($blueprint)) $blueprint = get_object_vars($blueprint);
            $rankItem = [];
            $rankItem['blueprint_type_id'] = $blueprint['type_id'];
            $rankItem['manufacturing_time'] = $blueprint['manufacturing_time'];
            $rankItem['product_type_id'] = $blueprint['product_type_id'];
            $rankItem['product_quantity'] = $blueprint['product_quantity'];
            if (!empty($blueprint['runs'])) {
                $rankItem['product_quantity'] = $blueprint['runs'] * $blueprint['product_quantity'];
            }
            // 查询蓝图信息
            $blueprintsType = Type::query()
                ->where('type_id', $blueprint['type_id'])
                ->first();
            $rankItem['blueprint_name_zh'] = $blueprintsType->name_zh;
            $rankItem['blueprint_name_en'] = $blueprintsType->name_en;
            // 查询蓝图产品信息
            $productType = Type::query()
                ->where('type_id', $blueprint['product_type_id'])
                ->where('published', 1)
                ->first();
            if (is_null($productType)) continue;
            $rankItem['product_name_zh'] = $productType->name_zh;
            $rankItem['product_name_en'] = $productType->name_en;
            // 查询蓝图产品价格
            $productPrice = TypePrice::query()
                ->where('type_id', $blueprint['product_type_id'])
                ->first();
            if (is_null($productPrice)) continue;
            if ($productPrice->sell_min < 100) continue;
            if ($productPrice->buy_max <= 0) continue;
            $rankItem['product_buy_max'] = $productPrice->buy_max;
            $rankItem['product_buy_quantity'] = $productPrice->buy_quantity;
            $rankItem['product_sell_min'] = $productPrice->sell_min;
            $rankItem['product_sell_quantity'] = $productPrice->sell_quantity;
            // 产品价值（按售价）
            $rankItem['product_sell_total'] = bcmul($productPrice->sell_min, $blueprint['product_quantity'], 2);
            $rankItem['product_sell_total_tax'] = bcmul($rankItem['product_sell_total'], $salesOrderDiscount, 2);
            // 产品价值（按收价）
            $rankItem['product_buy_total'] = bcmul($productPrice->buy_max, $blueprint['product_quantity'], 2);
            $rankItem['product_buy_total_tax'] = bcmul($rankItem['product_buy_total'], $salesImmediateDiscount, 2);
            // 查询蓝图材料
            $blueprintMaterials = BlueprintMaterial::query()
                ->where('blueprint_type_id', $blueprint['type_id'])
                ->get()->toArray();
            if (empty($blueprintMaterials)) continue;
            foreach ($blueprintMaterials as $blueprintMaterial) {
                $rankMaterial = [];
                $rankMaterial['material_type_id'] = $blueprintMaterial['material_type_id'];
                $rankMaterial['material_quantity'] = $blueprintMaterial['material_quantity'];
                if (empty($this->typesCache[$blueprintMaterial['material_type_id']])) {
                    // 查询材料信息
                    $materialType = Type::query()
                        ->where('type_id', $blueprintMaterial['material_type_id'])
                        ->first();
                    // 未查询到蓝图材料信息，跳过2层，跳过这个蓝图
                    if (is_null($materialType)) continue 2;
                    $rankMaterial['material_name_zh'] = $materialType['name_zh'];
                    $rankMaterial['material_name_en'] = $materialType['name_en'];
                    $rankMaterial['material_volume'] = $materialType['volume'];
                    // 查询材料价格
                    $materialPrice = TypePrice::query()
                        ->where('type_id', $blueprintMaterial['material_type_id'])
                        ->first();
                    // 未查询到蓝图材料价格信息，跳过2层，跳过这个蓝图
                    if (is_null($materialPrice)) continue 2;
                    if ($materialPrice['sell_min'] <= 0) {
                        echo $rankMaterial['material_name_zh'] . " sell price is 0" . PHP_EOL;
                        continue 2;
                    }
                    $cache = [
                        'type_id' => $blueprintMaterial['material_type_id'],
                        'name_zh' => $materialType['name_zh'],
                        'name_en' => $materialType['name_en'],
                        'volume' => $materialType['volume'],
                        'buy_max' => $materialPrice['buy_max'],
                        'buy_quantity' => $materialPrice['buy_quantity'],
                        'sell_min' => $materialPrice['sell_min'],
                        'sell_quantity' => $materialPrice['sell_quantity'],
                    ];
                    // 加入缓存
                    $this->typesCache[$blueprintMaterial['material_type_id']] = $cache;
                } else {
                    $cache = $this->typesCache[$blueprintMaterial['material_type_id']];
                }
                $rankMaterial['material_buy_max'] = $cache['buy_max'];
                $rankMaterial['material_buy_quantity'] = $cache['buy_quantity'];
                $rankMaterial['material_sell_min'] = $cache['sell_min'];
                $rankMaterial['material_sell_quantity'] = $cache['sell_quantity'];
                // 计算此材料出售估价总和
                $rankMaterial['material_cost'] = bcmul($blueprintMaterial['material_quantity'], $cache['sell_min'], 2);
                $rankItem['materials'][] = $rankMaterial;
            }
            if (empty($rankItem['materials'])) continue;
            // 计算材料成本总和
            $rankItem['materials_cost'] = array_sum(array_column($rankItem['materials'], 'material_cost'));
            // copy蓝图添加图的成本
            if (!empty($blueprint['contract_price'])) {
                $rankItem['materials_cost'] += $blueprint['contract_price'];
            }
            // HACK 考虑蓝图效率
//            $rankItem['materials_cost'] = $rankItem['materials_cost'] * 0.9;
//            $rankItem['manufacturing_time'] = $rankItem['manufacturing_time'] * 0.8;

            // HACK 过滤生产成本
//            if ($rankItem['materials_cost'] > 1000000) continue;
            // 计算生产利润
            $rankItem['profit_to_sell'] = bcsub($rankItem['product_sell_total_tax'], $rankItem['materials_cost'], 2);
            $rankItem['profit_for_buyer'] = bcsub($rankItem['product_buy_total_tax'], $rankItem['materials_cost'], 2);
            if ($rankItem['profit_for_buyer'] <= 0) {
                continue;
            }
            // 计算时效利润
            $rankItem['profit_for_buyer_sec'] = bcdiv($rankItem['profit_for_buyer'], $rankItem['manufacturing_time'], 2);
            $rankItem['profit_to_sell_sec'] = bcdiv($rankItem['profit_to_sell'], $rankItem['manufacturing_time'], 2);
            // 产出-投入比率 RIO（output-input ratio）
            $rankItem['RIO'] = bcdiv($rankItem['product_buy_total_tax'], $rankItem['materials_cost'], 2);
            // 日产量
            $dailyOutput = floor(bcdiv(86400, $rankItem['manufacturing_time'], 10));
            // 每日收益
            $rankItem['PPD'] = bcmul($rankItem['profit_for_buyer'], $dailyOutput, 2);

            $rank[] = $rankItem;
        }
        return $rank;
    }
}
