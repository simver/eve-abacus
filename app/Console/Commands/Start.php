<?php


namespace App\Console\Commands;

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
    public function handle(): bool
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');


//        Ceve::getPriceByTypeId(603, 10000002, 30000142);

//        $this->importType();
//        $this->importBlueprint();
//        $this->getTypePrice();
        $this->manufacturingList();


        return true;
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
        $manufacturingList = array_slice($manufacturingList, 0, 48);
        $manufacturingList = array_map(function($item) {
            return Arr::only($item, ['blueprint_type_id', 'manufacturing_time', 'product_type_id', 'blueprint_name_zh', 'product_name_zh', 'product_buy_max', 'materials_cost', 'profit_for_buyer', 'profit_for_buyer_sec', 'RIO', 'PPD']);
        }, $manufacturingList);
        $this->table(array_keys(current($manufacturingList)), $manufacturingList);
    }
}
