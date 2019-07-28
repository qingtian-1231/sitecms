<?php
// +----------------------------------------------------------------------
// | thinkphp5 Addons [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.zzstudio.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Byron Sampson <xiaobo.sun@qq.com>
// +----------------------------------------------------------------------
namespace woo\addons\controller;

use think\facade\Hook;

/**
* 插件执行默认控制器
* Class AddonsController
* @package think\addons
*/
class Route extends \think\Controller
{
    
    public function execute()
    {
        $this->addon = parse_name($this->request->param('addon'));
        $this->controller = parse_name($this->request->param('control'));
        $this->action = strtolower($this->request->param('action'));
        if (!empty($this->addon) && !empty($this->controller) && !empty($this->action)) {
            // 获取类的命名空间
            $class = get_addon_class($this->addon, 'controller', $this->controller);
            if (class_exists($class)) {
                $model = new $class();
                if ($model === false) {
                    abort(500, lang('addon init fail'));
                }
                // 调用操作
                if (!method_exists($model, $this->action)) {
                    abort(500, lang('Controller Class  Method ' . $this->action . ' Not Exists'));
                }
                // 监听addons_init
                Hook::listen('addons_init', $this);
                call_user_func_array([$model, $this->action], [$this->request]);
                return $model->finish();                
            } else {
                abort(500, lang('Controller Class ' . $this->controller . ' Not Exists'));
            }
        }
        abort(500, lang('addon cannot name or action'));
    }
}
