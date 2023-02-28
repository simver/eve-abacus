<?php
/**
 * Esi.php of eve_abacus.
 * Date: 2023-02-25
 */

namespace App\Acl;

use App\Util\Curl;

class Esi
{
    const URL = "https://esi.evetech.net/latest/";

    public static function marketPrices()
    {
        $url = self::URL . 'markets/prices/?datasource=tranquility';
        return Curl::httpGetRequest($url);
    }

    // 获取区域合同
    public static function getContracts(int $regionId): \Generator
    {
        $page = 1;
        while (true) {
            $url = self::URL . "contracts/public/{$regionId}/?datasource=tranquility&page={$page}";
            if (empty($result = Curl::httpGetRequest($url)))
                if (empty($result = Curl::httpGetRequest($url))) break;
            echo ">P{$page}";
            $page++;
            yield $result;
        }
    }

    // 获取合同内物品
    public static function getContractItems(int $contractId)
    {
        $url = self::URL . "contracts/public/items/{$contractId}/?datasource=tranquility&page=1";
        $result = Curl::httpGetRequest($url);
        if (empty($result)) echo PHP_EOL . "[Error] Items is null. URL:" . $url . PHP_EOL;
        // retry
        return Curl::httpGetRequest($url);
    }
}
