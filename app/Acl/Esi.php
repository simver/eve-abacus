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
}
