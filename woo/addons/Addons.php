<?php

namespace woo\addons;

use think\facade\Config;

abstract class Addons
{
   
    protected $view = null;
    protected $error;
    public $info = [];
    public $addons_path = '';
    public $config_file = '';

    public function __construct()
    {
        // 获取当前插件目录
        $this->addons_path = \think\facade\Env::get('addons_path') . $this->getName() . DIRECTORY_SEPARATOR;
        // 读取当前插件配置信息
        if (is_file($this->addons_path . 'config.php')) {
            $this->config_file = $this->addons_path . 'config.php';
        }

        $config = ['view_path' => $this->addons_path];
        $this->request = \think\Container::get('request');
        $this->app     = \think\Container::get('app');
        // 初始化视图模型
        $this->view    = \think\Container::get('view')->init(
            array_merge($this->app['config']->get('template.'), $config)
        );

        // 控制器初始化
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    /**
     * 获取插件信息
     * @return array
     */
    final public function getInfo($name = '')
    {
        if (empty($name)) {
            $name = $this->getName();
        }
        $info_file = $this->addons_path . 'info.ini';
        if (is_file($info_file)) {
            $info = Config::parse($info_file, '', "addon-info-{$name}");
            $info['url'] = addon_url($name);
        }
        return $info;
    }

    /**
     * 获取插件的配置数组
     * @param string $name 可选模块名
     * @return array|mixed|null
     */
    final public function getConfig($name = '')
    {
        static $_config = array();
        if (empty($name)) {
            $name = $this->getName();
        }
        if (isset($_config[$name])) {
            return $_config[$name];
        }
        
        $config = include $this->config_file;
        
        /*
        $map['name'] = $name;
        $map['status'] = 1;
        $config = [];
        if (is_file($this->config_file)) {
            $temp_arr = include $this->config_file;
            foreach ($temp_arr as $key => $value) {
                if ($value['type'] == 'group') {
                    foreach ($value['options'] as $gkey => $gvalue) {
                        foreach ($gvalue['options'] as $ikey => $ivalue) {
                            $config[$ikey] = $ivalue['value'];
                        }
                    }
                } else {
                    $config[$key] = $temp_arr[$key]['value'];
                }
            }
            unset($temp_arr);
        }
        $_config[$name] = $config;
        */
        return $config;
    }

    /**
     * 获取当前模块名
     * @return string
     */
    final public function getName()
    {
        $data = explode('\\', get_class($this));
        return strtolower(array_pop($data));
    }

    /**
     * 检查配置信息是否完整
     * @return bool
     */
    final public function checkInfo()
    {
        $info = $this->getInfo();
        $info_check_keys = ['name', 'title', 'intro', 'author', 'version', 'state'];
        foreach ($info_check_keys as $value) {
            if (!array_key_exists($value, $info)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $template 模板文件名或者内容
     * @param array $vars 模板输出变量
     * @param array $replace 替换内容
     * @param array $config 模板参数
     * @return mixed
     * @throws \Exception
     */
    public function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        if (!is_file($template)) {
            $template = '/' . $template;
        }
       
        echo $this->view->fetch($template, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array $vars 模板输出变量
     * @param array $replace 替换内容
     * @param array $config 模板参数
     * @return mixed
     */
    public function display($content, $vars = [], $replace = [], $config = [])
    {
        echo $this->view->display($content, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 内容
     * @param array $vars 模板输出变量
     * @return mixed
     */
    public function show($content, $vars = [])
    {
        echo $this->view->fetch($content, $vars, [], [], true);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    public function assign($name, $value = '')
    {
        $this->view->assign($name, $value);
    }

    /**
     * 获取当前错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    //必须实现安装
    abstract public function install();

    //必须卸载插件方法
    abstract public function uninstall();
}
