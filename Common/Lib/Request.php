<?php
namespace Common\Lib;

class Request extends \PhalApi\Request {

    public function getService() {

       
        if($_SERVER['SCRIPT_NAME'])
        {
            $pathArr = explode('/', $_SERVER['SCRIPT_NAME']);//    /user/index.php
            $AppName = ucwords($pathArr[1]);//user
        }
  
        // 优先返回自定义格式的接口服务名称
        $service = $this->get('service');
        if (!empty($service)) {
            #拆分后转化大小写
            $arr = explode('.', $service);
            foreach($arr as $key=>$val)
            {
                $arr[$key] = ucwords($val);
            }
            $service = implode('/',$arr);
            $namespace = count(explode('/', $service)) == 2 ? "$AppName." : '';
            //var_dump($namespace . str_replace('/', '.', $service));exit;
            return $namespace . str_replace('/', '.', $service);
        }

        return parent::getService();
    }
}