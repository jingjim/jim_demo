<?php

class StoreProvider{
    function __construct()
    {
        var_dump('StoreProvider');
    }
}
class ProductProvider{
    function __construct()
    {
        var_dump('ProductProvider');
    }
}
class Test{
    private $name;
    public $age;
    function __construct()
    {
        $this['dd']=  $this->index();
    }
    function index(){
        return 'index';
    }
}

$test = new Test();
var_dump($test);exit;



        $provider = [
            StoreProvider::class,
            ProductProvider::class,
            Test::class,
            //...其他服务提供者
        ];
        $provider_callback = function ($provider) {
            $obj = new $provider;
//            $this->serviceRegister($obj);
        };
        array_walk($provider, $provider_callback);//注册
