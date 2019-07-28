<?php
namespace woo\addons\controller;

use think\facade\Env;
use think\facade\Request;
use think\facade\Config;
use think\Loader;
use think\Container;
use woo\utility\Hash;
use woo\utility\TpText;
/**
* 插件基类控制器
*/
class Controller extends \woo\controller\WooApp
{
    /**
    * 配置
    */
    public $config = [];
    public $addon_root = '';
    public $addon_absroot = '';

    public function __construct()
    {
        $this->assign = new \woo\lib\Assign();
        $GLOBALS['controller'] = $this;
        parent::__construct();
        $view_path = Env::get('addons_path') . $this->request->param('addon') . DS . 'view' . DS;
        $this->view->config('view_path', $view_path);
    }
    
    protected function initialize()
    {
        // 获取url参数
        $passedArgs = Hash::diff($this->request->param() + $this->request->request(), $this->request->request()) + $this->request->get();
        // IIS编码修正        
        if (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) {
            foreach ($passedArgs as &$args) {
                $args = mb_convert_encoding($args, 'UTF-8', 'GBK');
            }
        }        
        $this->params = [
            'addon' => $this->request->param('addon'),
            'controller' => parse_name($this->request->param('control'), 1),
            'action' => strtolower($this->request->param('action')),
            'param' => $passedArgs
        ];    
        
                    
        unset($this->params['param']['addon']);
        unset($this->params['param']['control']);
        unset($this->params['param']['action']);
        
        $this->args = $passedArgs;
        $this->config = get_addon_config($this->params['addon']);
        $addon = db('Addon')->where('name', '=', $this->params['addon'])->find();
        
        if (empty($addon)) {
            abort('404', '没有安装得插件');
        }
        if ($addon['state'] != 1) {
            abort('404', '已禁用得插件');
        }
        if (isset($this->config['addon_config'])) {
            $addonConfigList = db('AddonConfig')->where('addon_id', '=', $addon['id'])->select();
            if ($addonConfigList) {
                $addonConfigList = Hash::combine($addonConfigList, '{n}.vari', '{n}.value');
                foreach ($addonConfigList as $k => &$v) {
                    if (is_json_validate($v)) {
                        $v = json_decode($v, true);
                    }
                }
                $this->config = array_merge($this->config, $addonConfigList);
            }
        }
        
        $this->m = $this->params['controller'];
        
        if (helper('Auth')->user('id')) {
            if (config('auth.refresh_anytime') === true) {
                helper('Auth')->relogin();
            }
            //helper('Auth')->relogin();//如果需要登录用户数据 时时更新(特别是用户数据有修改想更新就执行这句，重新更新session)
            $this->login = helper('Auth')->user();
            if ($this->login['logined_session_id'] != session_id() && $this->params['action'] != 'logout') {
                helper('Auth')->logout();
            }
        }
        $this->assign->login = $this->login;
        
        if (!$this->config['is_login']) {
            if (in_array($this->params['action'], (array)$this->needLoginAction)) {
                helper('Auth')->deny($this->params['action']);
            } else {
                helper('Auth')->allow($this->params['action']);
            }
        }
        
        // 根目录地址        
        $this->root = $this->assign->root = get_request_root();
        $this->absroot = $this->assign->absroot = get_request_absroot();
        $this->addon_root = $this->assign->addon_root = get_addon_root($this->params['addon']);
        $this->addon_absroot = $this->assign->addon_absroot = get_addon_absroot($this->params['addon']);        

        // 判断当前是否通过移动端访问
        $this->isMobile = $this->assign->isMobile = $this->request->isMobile();

        // 当前服务器时间  
        $this->now = $this->assign->now = time();

        $this->addTitle(setting('site_title'));
        $this->addKeywords(setting('site_keywords'));
		$this->addDescription(setting('site_description'));
        
       // 检查是否可以访问，最后一定要调这个方法        
        if (!helper('Auth')->check()) {
            if (!$this->request->isAjax()) {
                return $this->redirect($this->config['login_action']);
            } else {
                return abort(403, '访问被拒绝'); 
            }
            exit;
        }
    }
    
    /**
    * 实例模型
    * $this->loadModel(模型名);
    * $this->模型名 即该模型对象
    */
    protected function loadModel($model)
    {
        
        $model = parse_name($model, 1);
        if (isset($this->$model) && is_object($this->$model)) {
            return $this->$model;
        }  
        $class = 'addons\\' . $this->params['addon'] . '\\model\\' . $model;              
        if (class_exists($class)) {            
            return $this->$model = $this->assign->mdls[$model] = model($class);
        } else {
            return parent::loadModel($model);
        }
    }
    
    /**
    * 添加插件JS 默认路径在 public/addons/插件名/js
    */
    protected function addAddonJS($file) {
        if (substr($file, 0, 2) == './') {
            return $this->assign->addJs(substr($file, 1));
        } elseif (substr($file, 0, 1) == '/') {
            return $this->assign->addJs('/addons/' . $this->params['addon'] . $file); 
        } elseif (strpos($file, 'http://') === false && strpos($file, 'https://') === false) {
            return $this->assign->addJs('/addons/' . $this->params['addon'] . '/js/' . $file);
        } else {
            return $this->assign->addJs($file); 
        }
    }
    
    /**
    * 添加插件CSS 默认路径在 public/addons/插件名/css
    */
    protected function addAddonCss($file) {
        if (substr($file, 0, 2) == './') {
            return $this->assign->addCss(substr($file, 1));
        } elseif (substr($file, 0, 1) == '/') {
            return $this->assign->addCss('/addons/' . $this->params['addon'] . $file); 
        } elseif (strpos($file, 'http://') === false && strpos($file, 'https://') === false) {
            return $this->assign->addCss('/addons/' . $this->params['addon'] . '/css/' . $file);
        } else {
            return $this->assign->addCss($file); 
        }
    }
}
