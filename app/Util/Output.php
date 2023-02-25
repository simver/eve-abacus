<?php
/**
 * Output.php of eve_abacus.
 * Date: 2023-02-24
 */

namespace App\Util;

class Output
{
    public static function error(string $msg): void
    {
        echo date('md H:i:s') . '[Error]' . $msg . PHP_EOL;
    }

    public static function info(string $msg): void
    {
        echo date('md H:i:s') . '[Info]' . $msg . PHP_EOL;
    }
}
