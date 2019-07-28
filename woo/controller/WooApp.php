<?php

namespace woo\controller;

use think\Controller;
use woo\utility\Hash;
use woo\utility\TpText;

/**
 * 后台控制器基类
 */
class WooApp extends Controller
{
    const VERSION = '1.3.3';
    /**
    * Assign赋值对象
    */
    public $assign = null;

    /**
    * 必须登录以后才可以访问的访问
    */
    protected $needLoginAction = [];

    /**
    * 不允许访问的方法列表
    */
    protected $notAccessAction = null;

    /**
    * 方法间调用数据传递
    */
    protected $local = [
        'where' => [],
        'order' => [],
        'field' => []
        // ...
    ];

    /**
    * Callback 对象
    */
    protected $callback;

    /**
    * 当前请求控制器、模块、方法、参数等
    */
    public $params = null;

    /**
    * 当前请求参数
    */
    public $args = null;

    /**
    * 当前模型对象
    */
    public $mdl = null;

    /**
    * 当前模型对象主键
    */
    protected $mdlPk = 'id';

    /**
    * 助手对象容器
    */
    public $helper = [];

    /**
    * 当前控制器名
    */
    protected $m = null;

    /**
    * 登录用户信息
    */
    protected $login = [];

    /**
    * 当前相对 和 绝对 根地址 方便页面中路径
    */
    public $root = null;
    public $absroot = null;

    /**
    * 是否是从移动端访问
    */
    public $isMobile = false;

    /**
    * 当前服务器时间
    */
    public $now = 0;

    /**
    * 视图模板
    */
    public $fetch = null;

    /**
    * 构造
    * @powerset false
    */
    public function __construct()
    {
        if (!is_woo_installed() && request()->module() != 'install') {
            $this->redirect(get_request_absroot() . '?s=install');
            exit;
        }
        $GLOBALS['controller'] = $this;
        // 默认只加载Auth助手
        helper('Auth');
        $this->assign = new \woo\lib\Assign();
        parent::__construct();
    }

    /**
    * 初始化
    * @powerset false
    */
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

        $controller_part = explode('.', $this->request->controller());
        if (empty($controller_part[1])) {
            $controller_part[1] = $controller_part[0];
            $controller_part[0] = '';
        }
        $controller = parse_name(array_pop($controller_part), 1);

        $this->params = [
            'module' => $this->request->module(),
            'controller' => $controller,
            'addon_name' => parse_name(trim($controller_part[0])),
            'action' => $this->request->action(),
            'param' => $passedArgs
        ];

        $this->args = $this->params['param'];

        $this->callback = new \app\http\Callback($this);
        if (is_callable([$this->callback, 'appBeforeEach'])) {
            call_user_func([$this->callback, 'appBeforeEach']);
        }

        if ($this->callback->appBeforeAction) {
            call_user_func([$this->callback, $this->callback->appBeforeAction]);
        }

        $this->m = $this->params['controller'];
        // 当前模型
        $this->mdl = $this->loadModel(($this->params['addon_name'] ? $this->params['addon_name'] . '/' : '') . $this->m);

        if ($this->mdl instanceof \think\Model) {
            $this->mdlPk = $this->mdl->getPk();
        }
        $this->assign->mdl = $this->m;
        $this->assign->mdl_name = isset($this->mdl->cname) ? $this->mdl->cname : $this->m;

        if (isset($this->mdl->parentModel)) {
            if ($this->mdl->parentModel == 'parent')
                $this->local['parent_conj'] = 'parent_id';
            else
                $this->local['parent_conj'] = isset($this->mdl->assoc[$this->mdl->parentModel]['foreignKey']) ? $this->mdl->assoc[$this->mdl->parentModel]['foreignKey'] : parse_name($this->mdl->parentModel) . '_id';
            $this->assign->parent_field = $this->local['parent_conj'];
        }

        // Auth配置
        helper('Auth')->loginAction = 'User/login';
        helper('Auth')->logoutRedirect = 'User/logout';
        helper('Auth')->loginRedirect = 'Index/Index';

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

        // 用户登录以后 如果所在用户组不允许登录后台 将自动退出
        if (strtolower($this->params['module']) === 'run') {
            if (!empty($this->login)) {
                if (!$this->login['UserGroup']['is_admin'] && $this->params['action'] != 'logout') {
                    $this->redirect('User/logout');
                }
            }
        }

        $allow_module = config('auth.allow_module');
        // 整个模块不登录也可以访问  在配置文件auth.allow_module设置
        if (in_array($this->params['module'], $allow_module, true)) {
            if (in_array($this->params['action'], (array)$this->needLoginAction)) {
                helper('Auth')->deny($this->params['action']);
            } else {
                helper('Auth')->allow($this->params['action']);
            }
        }

        // 根目录地址
        $this->root = $this->assign->root = get_request_root();
        $this->absroot = $this->assign->absroot = get_request_absroot();

        // 判断当前是否通过移动端访问
        $this->isMobile = $this->assign->isMobile = $this->request->isMobile();

        // 当前服务器时间
        $this->now = $this->assign->now = time();

        $this->addTitle(setting('site_title'));
        $this->addKeywords(setting('site_keywords'));
		$this->addDescription(setting('site_description'));

        // 系统，继承方法较多；某类如果某方法如果不允许访问，可以通过$this->not_access_action指定
        if (is_array($this->notAccessAction) && !empty($this->notAccessAction)) {
            if (in_array($this->params['action'], (array)$this->notAccessAction)) {
                return abort(403, '访问被拒绝');
            }
        }

        // 检查是否可以访问，最后一定要调这个方法
        if (!helper('Auth')->check()) {
            if (!$this->request->isAjax()) {
                return $this->redirect(helper('Auth')->loginAction);
            } else {
                return abort(403, '访问被拒绝');
            }
            exit;
        }
    }

    /**
    * 渲染之前执行方法
    */
    protected function beforeRender()
    {
        $this->assign->mdls[$this->m] = $this->mdl;
        if (isset($this->mdl->assoc)) {
            foreach (array_keys($this->mdl->assoc) as $modelName) {
                $this->assign->mdls[$modelName] = $this->loadModel(!isset($this->mdl->assoc[$modelName]['foreign']) ? $modelName : $this->mdl->assoc[$modelName]['foreign']);
            }
        }


        if (is_callable([$this->callback, 'appAfterEach'])) {
            call_user_func([$this->callback, 'appAfterEach']);
        }
        if ($this->callback->appAfterAction) {
            call_user_func([$this->callback, $this->callback->appAfterAction]);
        }
    }

    /**
    * 实例模型
    * $this->loadModel(模型名);
    * $this->模型名 即该模型对象
    */
    protected function loadModel($model)
    {
        list($dir, $model) = explode('/', $model);
        if (empty($model)) {
            $model = $dir;
            $dir = '';
        }
        $model = parse_name($model, 1);
        $dir = trim(strtolower($dir));
        if (isset($this->$model) && is_object($this->$model)) {
            return $this->$model;
        }
        if (class_exists('app\\' . $this->params['module'] . '\\model\\'  . ($dir ? $dir . '\\' : '') . $model)) {
            return $this->$model = $this->assign->mdls[$model] = app()->model('app\\' . $this->params['module'] . '\\model\\' . ($dir ? $dir . '\\' : '') . $model);
        } elseif (class_exists('app\\common\\model\\' . ($dir ? $dir . '\\' : '') . $model)) {
            return $this->$model = $this->assign->mdls[$model] = app()->model('app\\common\\model\\' . ($dir ? $dir . '\\' : '') . $model);
        } elseif (class_exists('app\\' . $this->params['module'] . '\\model\\'. $model)) {
            return $this->$model = $this->assign->mdls[$model] = app()->model('app\\' . $this->params['module'] . '\\model\\' . $model);
        } elseif (class_exists('app\\common\\model\\' . $model)) {
            return $this->$model = $this->assign->mdls[$model] = app()->model('app\\common\\model\\' . $model);
        } else {
            return $this->$model = $this->assign->mdls[$model] = db($model);
        }
    }

    /**
    * ajax输出方法
    */
    protected function ajax($type, $msg, $data = null)
    {
        echo(json_encode(array(
            'result' => $type,
            'data' => $data,
            'message' => $msg
        )));
        exit;
    }

    /**
    * jsonp输出
    */
    protected function jsonp($type, $msg, $data = null)
    {
        $callback = $this->args['callback'];
        echo(TpText::insert(
			'var callback=window["{callback}"] || parent.window["{callback}"];if(callback)callback({data});',
			array(
				'data'=>json_encode(
					array(
						'result'=>$type,
						'data'=>$data,
						'message'=>$msg
					)
				),
				'callback'=>$callback,
			),
			array(
				'before'=>'{',
				'after'=>'}'
			)
		));
		exit;
    }

    /**
    * 提示信息方法  替代自带success 和 error方法
    */
    protected function message($type, $msg = null, $redirects = null, $auto = 3)
    {
        if ($this->request->isAjax()) {
            return $this->ajax($type, $msg, $redirects);
        }

        if (!$redirects) {
            $redirects = 'back';
        }

        settype($redirects, 'array');
        if (!array_key_exists('back', $redirects)) {
            $redirects['返回上一页'] = session('last_url') ?: 'javascript:window.history.go(-1);';
        }

        foreach ($redirects as $title => &$url) {
            if (is_numeric($title) && $url == 'back') {
                unset($redirects[$title]);
                $redirects['返回上一页'] = session('last_url') ?: 'javascript:window.history.go(-1);';
                continue;
            }

            if (!$url) {
                continue;
            }
            if ($title == 'back') {
                unset($redirects['back']);
                if (is_array($url)) {
                    $url = url($url[0], $url[1] ? $url[1] : null);
                }
                $redirects['返回上一页'] = $url;
                continue;
            }

            if (is_array($url)) {
                $url = url($url[0], $url[1] ? $url[1] : null);
            }
        }


        $this->assign->data = array(
            'type' => $type,
            'redirect' => Hash::filter($redirects),
            'message' => $msg,
            'auto' => $auto,
        );
        if (config('app_debug')) {
            return $this->fetch = '/message';
        }

        session('messageinfo', $this->assign->data);
        return $this->redirect('Index/redirectMessage');
        exit();
    }

    /**
    * @powerset false
    */
    public function redirectMessage()
    {
		$this->setTitle('系统消息', 'operation');
        $this->assign->data = session('messageinfo');
        $this->fetch = '/message';
    }

    /**
    * 关联选择查询数据
    */
    protected function assocSelect()
    {
        $error = null;
        $field = $this->args['assoc'];
        if (empty($field)) {
            $error = '没有找到关联字段';
        }
        $assoc_info = $this->mdl->form[$field];

        if (isset($assoc_info['foreign'])) {
            list($assocModel, $assocField) = plugin_split($assoc_info['foreign']);
        } else {
            $assocModel = parse_name(substr($field, 0, -3), 1);
        }
        if (!isset($this->mdl->assoc[$assocModel])) {
            $error = '模型assoc属性中未找到' . $assocModel .'，模型中：public $assoc = ["' . $assocModel . '" => ...]关联配置';
        }


        if (empty($error)) {
            $assocModel = $this->mdl->assoc[$assocModel]['foreign'];
            $this->local['limit'] = isset($assoc_info['assoc_options']['limit']) ? intval($assoc_info['assoc_options']['limit']) : 10;
            $this->loadModel($assocModel);
            $pk = $this->$assocModel->getPk();
            if (empty($assocField)) {
                $assocField = $this->$assocModel->display;
            }
            $this->local['field'] = [$pk, $assocField];
            $info['field'] = $assocField;
            $info['name'] = $this->$assocModel->form[$assocField]['name'];
            $info['assoc'] = $field;

            if (isset($assoc_info['assoc_options']['where'])) {
                $this->local['where'] = $assoc_info['assoc_options']['where'];
            }
            if (isset($assoc_info['assoc_options']['order'])) {
                $this->local['order'] = $assoc_info['assoc_options']['order'];
            }

            if ($this->args['keyword']) {
                $this->args['keyword'] = trim($this->args['keyword']);
                $info['keyword'] = $this->args['keyword'];
                $this->local['where'][] = [$assocField, 'LIKE', "%{$this->args['keyword']}%"];
            }
            $result = $this->$assocModel->getPageList([
                'order' => isset($this->local['order']) ? $this->local['order'] : [],
                'field' => $this->local['field'],
                'where' => $this->local['where'],
                'limit' => $this->local['limit']
            ]);
            $this->assign->info = $info;
            $this->assign->list = $result['list'];
            $this->assign->page = $result['page'];

        } else {
            $this->assign->error = $error;
        }
        app('think\\Route')->setConfig(['default_ajax_return' => 'html']);
        $this->fetch = '/common/assoc_select';
    }

    /**
    * AJAX关联联动查询
    */
    protected function ajaxMultiSelect() {
        $muiti_field = $this->mdl->form[trim(input('post.field'))];
        if (!$muiti_field) {
            $this->ajax('error', '没有获取到字段');
        }
        list($foreign_model, $foreign_field) = plugin_split($muiti_field['foreign']);
        if (!$foreign_model) {
            $foreign_model = $this->m;
            $foreign_field = $this->mdl->display;
        }


        if (isset($muiti_field['multi_options']['where'])) {
            $this->local['where'] = $muiti_field['multi_options']['where'];
        }
        if (isset($muiti_field['multi_options']['order'])) {
            $this->local['order'] = $muiti_field['multi_options']['order'];
        }

        $this->loadModel($foreign_model);
        if (!isset($this->$foreign_model->form['parent_id'])) {
            $this->ajax('error', $foreign_model . '没有parent_id字段');
        }
        $pk = $this->$foreign_model->getPk();
        $list = $this->$foreign_model->field([$foreign_field, $pk])
            ->order(isset($this->local['order']) ? $this->local['order'] : [$pk => 'DESC'])
            ->where(isset($this->local['where']) ? $this->local['where'] : [])
            ->where('parent_id', '=', intval(input('post.parent_id')))
            ->select();
        $list = $list->toArray();
        $this->ajax('success', '', $list);
    }

    /**
    * 追加网页标题
    */
    protected function addTitle($title)
    {
        settype($this->assign->meta['title'], 'array');
        array_unshift($this->assign->meta['title'], $title);
    }

    /**
    * 追加网页关键词
    */
    protected function addKeywords($title)
    {
        settype($this->assign->meta['keywords'], 'array');
        array_unshift($this->assign->meta['keywords'], $title);
    }

    /**
    * 追加网页网站描述
    */
    protected function addDescription($title)
    {
        settype($this->assign->meta['description'], 'array');
        array_unshift($this->assign->meta['description'], $title);
    }

    /**
    * 追加当前位置路径
    */
    protected function addPath($title, $url = null)
    {
        if ($url) {
            $this->assign->path[$title] = $url;
        } else {
            $this->assign->path[] = $title;
        }
    }

    /**
    * 重置网页标题
    */
    protected function setTitle($title, $type = 'title')
    {
        switch ($type) {
            case 'title':
                $this->assign->meta['title'] = array($title);
                break;
            case 'operation' || $type === true:
                $this->assign->title[$type] = $title;
                $this->addTitle($title);
                break;
            default:
                $this->assign->title[$type] = $title;
                break;
        }
    }

    /**
    * 重置网页关键词
    */
    protected function setKeywords($title)
    {
        $this->assign->meta['keywords'] = array($title);
    }

    /**
    * 重置网页关键词
    */
    protected function setDescription($title)
    {
        $this->assign->meta['description'] = array($title);
    }

    /**
    * 重置网页描述
    * @powerset false
    */
    public function notFound()
    {
        return abort(404, '页面不存在');
    }

    /**
    * 获取系统版本
    * @powerset false
    */
    public function version()
    {
        return static::VERSION;
    }

    /**
    * 重写assign
    */
    protected function assign($name, $value = '')
    {
        $this->assign->$name = $value;
        return true;
    }

    protected function assignVars()
    {
        $this->assign->args = $this->args;
        $this->assign->params = $this->params;
        $this->assign->cthis = $GLOBALS['controller'];

        if (!$this->assign->varsReturned) {
            foreach ($this->assign as $name => $value) {
                parent::assign($name, $value);
            }
            $this->assign->varsReturned = true;
        }
        return true;
    }

    protected function checkTemplate($template = '')
    {

        if ($template && !in_array(substr($template, 0, 1), ['.', '/']) && strpos($template, '/') === false && strpos($template, '@') === false) {
            $modulePath = config('template.view_path') ?: app()->getModulePath() . 'view' . DS;
            if (empty($this->params['addon_name'])) {
                $default = parse_name($this->params['controller']) . '/' . $template ;
            } else {
                $default = $this->params['addon_name'] . '/' . parse_name($this->params['controller']) . '/' . $template;
            }

            if (file_exists($modulePath . $default . '.' . config('template.view_suffix'))) {
                return $default;
            } else {
                if (file_exists($modulePath . $template . '.' . config('template.view_suffix'))) {
                    return '/' . $template;
                } else {
                    if (empty($this->params['controller_path'])) {
                        return parse_name($this->params['controller']) . '/' . $template;
                    } else {
                        return $this->params['addon_name'] . '/' . parse_name($this->params['controller']) . '/' . $template;
                    }
                }
            }
        }
        return $template;
    }

    protected function fetch($template = '', $vars = [], $config = [])
    {

        if ($template) {
            $template = $this->checkTemplate($template);
        }

        $this->beforeRender();
        $this->assignVars();
        return parent::fetch($template, $vars, $config);
    }

    protected function display($content = '', $vars = [], $config = [])
    {
        $this->beforeRender();
        $this->assignVars();
        return parent::display($content, $vars, $config);
    }

    protected function beforeFinish()
    {}

    /**
    *@powerset false
    */
    public function finish()
    {
        $this->beforeFinish();
        ##所有试图页面都尽量使用 $this->fetch = '页面' 来指定
        if ($this->fetch !== null && $this->fetch !== false) {
            if ($this->fetch === true) {
                if (empty($this->params['addon_name'])) {
                    $this->fetch = parse_name($this->params['controller']) . '/' . parse_name($this->params['action']);
                } else {
                    $this->fetch = $this->params['addon_name'] . '/' . parse_name($this->params['controller']) . '/' . parse_name($this->params['action']);
                }
            }
            return $this->fetch($this->fetch);//在这里实现最终模板渲染
        }
    }
}
