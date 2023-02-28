<?php


namespace App\Console\Commands;

use App\domain\Contract;
use App\domain\GetPrice;
use App\domain\ImportBlueprint;
use App\domain\ImportType;
use App\domain\ManufacturingList;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class Start extends Command
{
    /**
     * @var string 脚本名称
     */
    protected $signature = 'start';
    /**
     * @var string 脚本描述
     */
    protected $description = '开始';

    /**
     * 脚本主流程
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $choices = [[1, '导入 TypeId 数据'], [2, '导入蓝图数据'], [3, '更新价格数据'], [4, '计算生产盈利排行'],
            [5, '更新合同数据'],
            [0, '退出']];
        $this->table(['选择', '功能'], $choices);
        while ($choice = $this->choice("请选择", array_column($choices, 0))) {
            switch ($choice) {
                case 1:
                    $this->importType();
                    echo "已导入 TypeId 数据." . PHP_EOL;
                    break;
                case 2:
                    $this->importBlueprint();
                    echo "已导入蓝图数据." . PHP_EOL;
                    break;
                case 3:
                    $this->getTypePrice();
                    echo "已更新价格数据." . PHP_EOL;
                    break;
                case 4:
                    $this->manufacturingList();
                    break;
                case 5:
                    $this->updateContracts();
                    break;
                default:
                    break;
            }
            $this->table(['选择', '功能'], $choices);
        }
    }

    public function importBlueprint()
    {
        ImportBlueprint::import('/Users/simver/Downloads/sde/fsd/blueprints.yaml');
    }

    public function importType()
    {
        ImportType::import('/Users/simver/Downloads/sde/fsd/typeIDs.yaml');
    }

    public function getTypePrice()
    {
        GetPrice::getPriceNeed();
    }

    public function manufacturingList()
    {
        $new = new ManufacturingList();
        $manufacturingList = $new->getProfitRank();
//        $manufacturingList = array_slice($manufacturingList, 0, 100);
        $headers = ['blueprint_type_id' => "蓝图ID", 'manufacturing_time' => '产时', 'product_type_id' => '产品ID', 'blueprint_name_zh' => '蓝图', 'product_name_zh' => '产品', 'product_buy_max' => '买价', 'materials_cost' => '成本', 'profit_for_buyer' => '利润', 'profit_for_buyer_sec' => '秒利润', 'RIO' => 'RIO', 'PPD' => 'PPD'];
        $manufacturingList = array_map(function ($item) use ($headers) {
            return Arr::only($item, array_keys($headers));
        }, $manufacturingList);
//        array_multisort(array_column($manufacturingList, 'PPD'), SORT_DESC, SORT_NUMERIC, $manufacturingList);
        $this->table(array_values($headers), $manufacturingList);
    }

    public function updateContracts() {
        Contract::updateContracts();
    }
}
