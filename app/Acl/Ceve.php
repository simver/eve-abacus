<?php
/**
 * Ceve.php of eve_abacus.
 * Date: 2023-02-24
 */

namespace App\Acl;

use App\Util\Curl;

class Ceve
{
    const URL   = "https://www.ceve-market.org/tqapi/";
    const JITA  = 30000142;
    const FORGE = 10000002;

    /**
     * 描述: 查询指定物品的价格资料.
     * 调用方式: HTTP GET (RESTful)
     * 返回格式: XML JSON AJAX(需申请)
     * REST URL 格式:
     * https://www.ceve-market.org/api/market/region/{星域ID}/system/{星系ID}/type/{物品ID}.{格式}
     * 资源说明:
     * 资源       描述                          必要
     * 物品ID    指定的物品ID, 如 三钛合金 为 34    是
     * 星域ID    查询该指定星域                   否
     * 星系ID    查询该指定星系                   否
     * 返回格式    必须为 xml 或 json (全部小写)    是
     * 返回值意义请直接阅读示例接口返回的数据
     * 调用示例:
     * // 伏尔戈-吉他：小鹰级价格
     * https://www.ceve-market.org/tqapi/market/region/10000002/system/30000142/type/603.json
     *
     * @see https://www.ceve-market.org/api/
     * @response
     * {
     * "all": {
     * "max": 250000000,
     * "min": 17840,
     * "volume": 2388
     * },
     * "buy": {
     * "max": 141000,
     * "min": 17840,
     * "volume": 1802
     * },
     * "sell": {
     * "max": 250000000,
     * "min": 201000,
     * "volume": 586
     * }
     * }
     * @param int $typeId 物品typeId
     * @param int $regionId 星域ID，如：10000002（伏尔戈）
     * @param int $systemId 星系ID，如：30000142（吉他）
     * @return void
     */
    public static function getPriceByTypeId(int $typeId, int $regionId, int $systemId): array
    {
        $url = self::URL . "market/region/" . $regionId . "/system/" . $systemId . "/type/" . $typeId . ".json";

        return Curl::httpGetRequest($url);
    }

    public static function getJitaPrice(int $typeId): array
    {
        $location = [
            'regionId' => self::FORGE,
            'systemId' => self::JITA,
        ];
        $prices = self::getPriceByTypeId($typeId, self::FORGE, self::JITA);
        return array_merge($location, $prices);
    }
}
