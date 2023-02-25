<?php

namespace App\Util;

class Curl
{

    public static function httpGetRequest(string $url)
    {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 是否开启SSL证书检查
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        $output = curl_exec($curl);     //返回api的json对象
        if ($output === false) Output::error('httpGetRequest error. url: ' . $url);
        //关闭URL请求
        curl_close($curl);
        return \json_decode($output, true);    //返回json对象
    }
}
