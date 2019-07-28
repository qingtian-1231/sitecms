<?php
namespace woo\helper;

use PHPMailer\PHPMailer\Exception;
use woo\utility\DocParser;
use think\Db;

class Auth
{
    //  用户模型名称比如User
    public $userModel = 'User';
    private $userModelObject = NULL;

    //  登录地址
    public $loginAction = NULL;

    //  登出以后跳转地址
    public $logoutRedirect = '';

    //  登录以后跳转地址    
    public $loginRedirect = '';

    //  登录帐号字段
    public $userField = 'username';

    //  登录密码字段
    public $pwdField = 'password';

    //  关联查询模型
    public $contain = ['UserGroup'];

    private $allowMethod = [];

    //  登录用户session名
    private $authKey;
    private $authDefaultKey;
    
    private $request = [];
    
    private $reflection = null;

    protected $isCheckMenuPower = [];

    public function __construct()
    {
        $addon = strtolower(request()->param('addon'));
        $controller = parse_name(request()->param('control'));
        $action = strtolower(request()->param('action'));
        if (!empty($addon) && !empty($controller) && !empty($action)) {
            $this->request['addon'] = $addon;
            $this->request['controller'] = $controller;
            $this->request['action'] = $action;
            $this->request['is_addon'] = true;
            $this->request['module'] = 'addon';
        } else {
            $this->request['module'] = request()->module();
            $this->request['controller'] = request()->controller();
            $this->request['action'] = request()->action();
            $this->request['is_addon'] = false;
        }
        
        $this->authDefaultKey = config('auth.default_name');
        $module = $this->request['module'];
        if (config('auth.' . $module . '_name')) {
            $this->authKey = config('auth.' . $module . '_name');
        } else {
            $this->authKey = config('auth.default_name');
        }        
        $this->authDefaultKey = md5($this->authDefaultKey ? $this->authDefaultKey : 'default');
        $this->authKey = md5($this->authKey ? $this->authKey : $this->authDefaultKey);
    }

    //  Auth获取用户模型对象
    protected function getUserObj()
    {
        if ($this->userModelObject) {
            return;
        }
        if (!$this->userModel) {
            exit('请设置Auth::userModel');
        }
        $this->userModelObject = model($this->userModel);
    }

    //  Auth登录
    public function login($data = array())
    {
        //if(session('?'.$this->authKey))  redirect($this->loginRedirect);        
        $this->getUserObj();
        if (empty($data)) {
            $data = input('post.');
        }
        if (isset($data['data'])) {
            $data = $data['data'];
        }
        $data = isset($data[$this->userModel]) ? $data[$this->userModel] : $data;
        if (!is_array($data)) {
            return false;
        }
        if (!trim($data[$this->userField]) || !trim($data[$this->pwdField])) {
            return false;
        }

        $username = addslashes(trim($data[$this->userField]));
        $password = self::password(trim($data[$this->pwdField]));
        $login = $this->userModelObject->with($this->contain)->where([[$this->userField, '=', $username], [$this->pwdField, '=', $password]])->find();

        if (!$login) {
            return false;
        }
        $login = $login->toArray();

        if (!empty($login['UserGroup']['alias'])) {
            try {
                $profile = model($login['UserGroup']['alias']);
                $profile_data = $profile->where('user_id', '=', $login['id'])->find();
                if (!empty($profile_data)) {
                    $login[$login['UserGroup']['alias']] = $profile_data->toArray();
                } else {
                    $login[$login['UserGroup']['alias']] = [];
                }
            } catch (\Exception $e) {}
        }

        if (isset($login['logined_session_id'])) {
            $login['logined_session_id'] = session_id();
        }
        if (isset($login['logined_ip'])) {
            $login['logined_ip'] = request()->ip();
        }
        if (isset($login['logined'])) {
            $login['logined'] = date('Y-m-d H:i:s');
        }
        
        session($this->authKey, $login);
        if (!session('?' . $this->authDefaultKey)) {
            session($this->authDefaultKey, $login);
        }        
        session('REFERER', null);
        return true;
    }

    //  Auth登录后更新用户信息并重写session
    public function relogin()
    {
        if (!session('?' . $this->authKey)) {
            return false;
        }
        
        $this->getUserObj();
        $login = $this->userModelObject->with($this->contain)->where('id', $this->user('id'))->find();
        if (!$login) {
            $this->logout();
            return false;
        }
        $login = $login->toArray();
        if (!empty($login['UserGroup']['alias'])) {
            try {
                $profile = model($login['UserGroup']['alias']);
                $profile_data = $profile->where('user_id', '=', $login['id'])->find();
                if (!empty($profile_data)) {
                    $login[$login['UserGroup']['alias']] = $profile_data->toArray();
                } else {
                    $login[$login['UserGroup']['alias']] = [];
                }
            } catch (\Exception $e) {}
        }

        //unset($login[$this->pwdField]);
        session($this->authKey, $login);
        $default = session($this->authDefaultKey);
        if ($default['id'] == $login['id']) {
            session($this->authDefaultKey, $login);
        }
        return true;
    }

    //  Auth登出
    public function logout()
    {
        $login = session($this->authKey);
        $default = session($this->authDefaultKey);
        if ($login['id'] == $default['id']) {
            session($this->authDefaultKey, NULL);
        }
        session($this->authKey, NULL);
    }

    //  Auth获取用户信息
    public function user($key = NULL)
    {
        if (!session('?' . $this->authKey)) {
            return false;
        }
        $login = session($this->authKey);
        if (!$key) {
            return $login;
        }
        if (in_array($key, array_keys($login))) {
            return $login[$key];
        }
        return $login;
    }

    //  Auth可访问方法
    public function allow($method = NULL)
    {
        $args = func_get_args();
        if (empty($args)) {
            $this->allowMethod = get_class_methods($GLOBALS['controller']);
            return;
        }
        if (!is_array($method)) {
            $method = array($method);
        }
        $this->allowMethod += $method;
    }

    //  Auth不可访问方法
    public function deny($method = NULL)
    {
        $args = func_get_args();
        if (empty($args)) {
            $this->allowMethod = array();
            return;
        }
        if (!is_array($method)) {
            $method = array($method);
        }
        $this->allowMethod = array_diff($this->allowMethod, $method);
    }

    //  检查是否登录
    public function check()
    {
        if (!session('?' . $this->authKey) && !in_array($this->request['action'], $this->allowMethod)) {
            session('REFERER', request()->url(true));
            return false;
        }
        return true;
    }

    public function isCheckMenuPower($modelName)
    {
        if (isset($this->isCheckMenuPower[$modelName])) {
            return $this->isCheckMenuPower[$modelName];
        }
        if (!config('auth.power_check') || !config('auth.menu_power_check')) {
            $this->isCheckMenuPower[$modelName] = false;
            return $this->isCheckMenuPower[$modelName];
        }
        try {
            $model_object = model($modelName);
            if (!isset($model_object->form['menu_id'])) {
                $this->isCheckMenuPower[$modelName] = false;
                return $this->isCheckMenuPower[$modelName];
            }
        } catch (\Exception $e) {
            $this->isCheckMenuPower[$modelName] = false;
            return $this->isCheckMenuPower[$modelName];
        }
        $count = \Cache::get("menu_power_count");
        if (!is_numeric($count)) {
            $count = Db::name('MenuPower')->count();
            \Cache::tag('MenuPower')->set("menu_power_count", $count, 3600);
        }
        $this->isCheckMenuPower[$modelName] = $count > 0 ? true : false;
        return $this->isCheckMenuPower[$modelName];
    }

    public function checkMenuPower($menu_id, $action)
    {
        $menu_id = intval($menu_id);
        $action = trim($action);
        $powers = $this->getMenuPower();
        if (empty($powers)) {
            return false;
        }
        return in_array($action, (array)$powers['menu'][$menu_id]);
    }

    public function getMenuPower()
    {
        $user_id  = $this->user('id');
        $user_group_id = $this->user('user_group_id');

        $powers = \Cache::get("menu_power_type_user_id_{$user_id}");
        if (empty($powers)) {
            $powers = \Cache::get("menu_power_type_usergroup_id_{$user_group_id}");
        }
        if (empty($powers)) {
            $powers = Db::name('MenuPower')->field(['id', 'content'])->where([['foreign_id', '=', intval($user_id)], ['type', '=', 'user']])->find();
            if (!empty($powers)) {
                $powers = unserialize(@gzuncompress($powers['content']));
                \Cache::tag('MenuPower')->set("menu_power_type_user_id_{$user_id}", $powers, 3600);
            }
        }
        if (!empty($powers)) {
            return $powers;
        }
        $powers = \Cache::get("menu_power_type_usergroup_id_{$user_group_id}");
        if (empty($powers)) {
            $powers = Db::name('MenuPower')->field(['id', 'content'])->where([['foreign_id', '=', intval($user_group_id)], ['type', '=', 'usergroup']])->find();
            if (!empty($powers)) {
                $powers = unserialize(@gzuncompress($powers['content']));
                \Cache::tag('MenuPower')->set("menu_power_type_usergroup_id_{$user_group_id}", $powers, 3600);
            } else {
                $powers = [];
            }
        }
        return $powers;
    }

    public function getPowerList()
    {
        $user_id  = $this->user('id');
        $user_group_id = $this->user('user_group_id');

        $powers = \Cache::get("power_type_user_id_{$user_id}");
        if (empty($powers)) {
            $powers = \Cache::get("power_type_usergroup_id_{$user_group_id}");
        }
        if (empty($powers)) {
            $powers = Db::name('Power')->field(['id', 'content'])->where([['foreign_id', '=', intval($user_id)], ['type', '=', 'user']])->find();
            if (!empty($powers)) {
                $powers = unserialize(@gzuncompress($powers['content']));
               \Cache::tag('Power')->set("power_type_user_id_{$user_id}", $powers, 3600);
            }
        }
        if (!empty($powers)) {
            return $powers;
        }
        $powers = \Cache::get("power_type_usergroup_id_{$user_group_id}");
        if (empty($powers)) {
            $powers = Db::name('Power')->field(['id', 'content'])->where([['foreign_id', '=', intval($user_group_id)], ['type', '=', 'usergroup']])->find();
            if (!empty($powers)) {
                $powers = unserialize(@gzuncompress($powers['content']));
               \Cache::tag('Power')->set("power_type_usergroup_id_{$user_group_id}", $powers, 3600);
            } else {
                $powers = [];
            }
        }
        return $powers;
    }
    
    public function checkPower()
    {
        if (!config('auth.power_check')) {
            return true;
        }
        if (in_array($this->request['action'], $this->allowMethod)) {
            return true;
        } 
        $together = $this->getTogether();
        if (Db::name('PowerTree')->where('together', '=', $together)->count()) {
            $powers = $this->getPowerList();
            if (in_array('all/all', $powers)) {
                return true;
            }
            return in_array($together, $powers);
        }
        return true;
    }
    
    protected function getTogether()
    {
        
        $addon = $GLOBALS['controller']->params['addon_name'];
        $controller = $GLOBALS['controller']->params['controller'];
        $action = $GLOBALS['controller']->params['action'];        
        $powerAs = $action;
                
        $power_action_as = config('auth.power_action_as');
        $isBreak = false;
        if (!empty($power_action_as[$controller])) {
            foreach ($power_action_as[$controller] as $key => $value) {
                if (strtolower($key) == $action) {
                    $isBreak = true;
                    $powerAs = strtolower($value);
                    break;
                }
            }
        }
        if (!$isBreak) {
            if (empty($addon)) {
                $controllerFull = 'app\\run\\controller\\' . $controller;
            } else {
                $controllerFull = 'app\\run\\controller\\' . $addon . '\\' . $controller;
            }
            $this->reflection = new \ReflectionClass($controllerFull);
            $powerAs = $this->getPowerAs($action);
            if (empty($powerAs)) {
                $getPowerAs = $action;
            }
        }
        return ($addon ? strtolower($addon) . '/' : '') . parse_name($controller) . '/' . strtolower($powerAs);
    }
    
    protected function getPowerAs($action)
    {
        $doc = $this->getActionDoc($action);
        if (empty($doc)) {
            return $action;
        }
        if (!isset($doc['poweras'])) {
            return $action;
        }
        return $this->getPowerAs($doc['poweras']);
    }
    
    
    public function getActionDoc($action = '', $controller = '')
    {
        if ($controller) {
            $reflection = new \ReflectionClass($controller);
            $this->reflection = $reflection;
        } else {
            $reflection = $this->reflection;
        }
        
        if (empty($action)) {
            return [];
        }
        
        $method = '';
        try {
            $method = $reflection->getMethod($action);
        } catch (\Exception $e) {
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $item) {
                if (strtolower($item->getName()) == $action) {
                    $method = $item;
                    break;
                }
            }
        }
        
        if (!is_object($method)) {
            return [];
        }
        return (new DocParser())->parse($method->getDocComment());
    }    

    //  密码加密，返回密码
    public static function password($password)
    {
        return md5(crypt($password, config('auth.salt') . substr($password, 0, 2)));
    }

    public function __destruct()
    {
        if (request()->isGet() && !request()->isAjax()) {
            session('last_url', request()->url(true));
        }
    }
}
