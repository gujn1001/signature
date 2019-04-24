<?php

namespace MiMiao\Signature;

class Signature
{

    private $app;

    // 是否小写
    private $isLower;

    // 参数白名单
    private $whiteList;

    // 额外key
    private $extraKey;

    public function __construct($app)
    {
        $this->app = $app;
        $this->setupConfig();
    }

    private function setupConfig()
    {
        $this->whiteList = $this->app['config']['signature.whiteList'];
        $this->extraKey = $this->app['config']['signature.extraKey'];
        $this->isLower = $this->app['config']['signature.isLower'];
    }

    private function incrExtraKey(array $args)
    {
        $args['extraKey'] = $this->extraKey;
        return $args;
    }

    public function entry(array $args)
    {
        $filterArr = $this->filter($args);
        $sortArr = $this->argsSort($filterArr);
        $sign = md5($this->convertToString($sortArr));
        return $this->isLower ? $sign : strtoupper($sign);
    }

    public function filter(array $args)
    {
        return array_filter($args, function ($value, $key){
            return !in_array($key, $this->whiteList) && ($value != '');
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function argsSort(array $args)
    {
        ksort($args);
        reset($args);
        return $args;
    }

    public function convertToString(array $args)
    {
        $finalArr = $this->incrExtraKey($args);
        $str = http_build_query($finalArr);

        if(get_magic_quotes_gpc()){
            $str = stripslashes($str);
        }

        return $str;
    }

    public function verify(array $args, string $sign)
    {
        return $this->entry($args) == $sign;
    }
}