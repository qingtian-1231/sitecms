<?php
namespace woo\controller;

use think\Loader;
use woo\utility\Hash;
use think\Db;

/**
* 工具
*/
class WooTool extends WooRun
{
    /**
    * 创建模板
    */
    public function addv()
    {
        if (!config('app_debug')) {
            return $this->message('error', '非调试模式不允许进行该操作', ['close' => true, 'back' => false]);
        }
        $this->setTitle("模板创建", 'operation');
        $this->assign->warning = '非程序员请谨慎操作！目前仅支持创建Home模块的模板页面';
        $dir_list  = $GLOBALS['Model_map'];
        unset($dir_list['Exlink']);
        $dir_list['custom'] = '自定义';
        $this->mdl->fieldRespond = array(
            'dir' => array(
                'RespondField' => array('custom'),
                'custom' => array('custom')
            )
        );
        
        $this->mdl->form = array(
            'dir' => array(
                'type' => 'string',
                'name' => '模板目录',
                'elem' => 'select',
                'options' => $dir_list,
            ),
            'custom' => array(
                'type' => 'string',
                'name' => '自定义目录名',
                'elem' => 'text',
                'info' => '请自行控制目录名大小写'
            ),
            'title' => array(
                'type' => 'string',
                'name' => '模板名',
                'elem' => 'text',
                'info' => '无需带后缀'
            ),
            'is_head' => array(
                'type' => 'integer',
                'name' => '重写页头',
                'elem' => 'checker'
            ),
            'is_content' => array(
                'type' => 'integer',
                'name' => '重写主体',
                'elem' => 'checker'
            ),
            'is_foot' => array(
                'type' => 'integer',
                'name' => '重写页尾',
                'elem' => 'checker'
            ),
        );
        if ($this->request->isPost()) {
            $data = helper('Form')->data[$this->m];
            $dirname = trim($data['dir']) != 'custom' ? trim($data['dir']) : trim($data['custom']);
            if (!$dirname) {
                $this->assign->mdl_error['dir'] = '模板目录有误';
            }
            $view_name = trim($data['title']);
            if (!$view_name) {
                $this->assign->mdl_error['title'] = '请填写模板名称';
            }
            
            if (!isset($this->assign->mdl_error)) {
                $dir = APP_PATH . 'home' . DS . 'view' . DS . $dirname . DS;
                if (!file_exists($dir)) {
                    mkdir($dir, 0777);
                }                
                /*if (!is_writeable($dir)) {
                    return $this->message('error', "模板所在{$dir}文件夹没有可写权限");
                }*/
                $path = $dir . $view_name . '.html';
                if (file_exists($path)) {
                    return $this->message('error', "模板home\\view\\{$dirname}\\{$view_name}.html已经存在");
                }
                $head_html = $data['is_head'] ? '{block name="head"}{/block}' : '';
                $cont_html = $data['is_content'] ? '{block name="content"}{/block}' : '{block name="insider"}{/block}';
                $foot_html = $data['is_foot'] ? '{block name="foot"}{/block}' : '';
                
                $addString = <<<HTML
{extends file="../insider_base.html"}

$head_html
$cont_html
$foot_html
HTML;
                file_put_contents($path, $addString);
                return $this->message('success', "模板home\\view\\{$dirname}\\{$view_name}.html创建成功");
            }
        }        
        $this->fetch = 'form';
    }
    
    /**
    * 创建模型
    */
    public function addm()
    {
        if (!config('app_debug')) {
            return $this->message('error', '非调试模式不允许进行该操作', ['close' => true, 'back' => false]);
        }
        if (!config('field_add_del')) {
            return $this->message('error', '当前不允许操作字段', ['close' => true, 'back' => false]);
        }
        $this->setTitle("模型生成", 'operation');
        $this->assign->warning = '非程序员请谨慎操作！';
        $this->mdl->form = array(
            'model' => array(
                'type' => 'string',
                'name' => '模型文件名',
                'elem' => 'text',
            ),
            'cname' => array(
                'type' => 'string',
                'name' => '模型名称',
                'elem' => 'text',
                'list' => 'show',
                'info' => '比如：栏目'
            ),
            'is_menu' => array(
                'type' => 'boolean',
                'name' => '是否栏目',
                'elem' => 'checker',
                'list' => 'checker',
            ),
            'is_dustbin' => array(
                'type' => 'boolean',
                'name' => '删除回收',
                'elem' => 'checker',
                'list' => 'checker',
            ),
            'is_import' => array(
                'type' => 'boolean',
                'name' => '是否允许导入',
                'elem' => 'checker',
                'list' => 'checker',
            ),
            'add_fields' => array(
                'type' => 'string',
                'name' => '基础字段',
                'elem' => 'formSelects.checkbox',
                'options' => [
                    'title' => '标题(title)',                    
                    'user_id' => '用户(user_id)：自动关联User',
                    'menu_id' => '前台栏目(menu_id):自动关联Menu',
                    'date' => '日期(date)',
                    'is_verify' => '审核(is_verify)',
                    'image' => '图片(image)',
                    'file' => '文件(file)',
                    'list_order' => '排序(list_order)',
                    'content' => '内容(content)',
                    'created' => '创建日期(created)',
                    'modified' => '修改日期(modified)',
                    
                ],
                'info' => '如果有选取字段才会自动建表，默认会自动加ID主键字段，这里暂时只能选取基础字段，更多字段在“模型字典”中自行添加'
            ),
        );
        if ($this->request->isPost()) {
            $data = helper('Form')->data[$this->m];
            $model = parse_name(trim($data['model']), 1);
            $fields = !empty($data['add_fields']) ? $data['add_fields'] : [];
            if (is_string($fields)) {
                $fields = explode(',', $fields);
            }
            if (!$model) {
                $this->assign->mdl_error['model'] = '请填写模型名称';
            }
            if (!isset($this->assign->mdl_error)) {
                $dir = APP_PATH . 'common' . DS . 'model' . DS;
                /*if (!is_writeable($dir)) {
                    return $this->message('error', "模型common/model文件夹没有可写权限");
                }*/
                $path = $dir . $model . '.php';
                if (file_exists($path)) {
                    return $this->message('error', "模型common\\model\\{$model}已经存在");
                }                
                
                $assoc = [];
                if (in_array('user_id', $fields)) {
                    $assoc['User'] = [
                        'type' => 'belongsTo'
                    ];
                }
                if (in_array('menu_id', $fields)) {
                    $assoc['Menu'] = [
                        'type' => 'belongsTo'
                    ];
                    $data['is_menu'] = 1;
                }
                $assoc = var_export($assoc, true);
                
                $formData = [
                    'id' => [
                    	'type' => 'integer',
                    	'name' => 'ID',
                    	'elem' => 'hidden',
                    ],
                ];
                $validate = [];
                if (count($fields) >= 1) {
                    $tableField =  [];
                    $tableField[] = "`id` int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'ID'";
                    
                    if (in_array('title', $fields)) {
                        $formData['title'] = [
                            'type' => 'string',
                            'name' => '标题',
                            'elem' => 'text',
                            'list' => 'show'
                        ];
                        $tableField[] = "`title` varchar(128) NOT NULL DEFAULT '' COMMENT '标题'";
                        $validate['title'] = [
                            'rule' => 'require',
                            'message' => '请填写标题'
                        ]; 
                    }
                    if (in_array('user_id', $fields)) {
                        $formData['user_id'] = [
                            'type' => 'integer',
                            'name' => '所属用户',
                            'foreign' => 'User.username',
                            'elem' => 0,
                            'list' => 'assoc'
                        ];
                        $tableField[] = "`user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户'";
                    }
                    if (in_array('menu_id', $fields)) {
                        $formData['menu_id'] = [
                            'type' => 'integer',
                            'name' => '所属栏目',
                            'elem' => 'nest_select.Menu',
                            'foreign' => 'Menu.title',
                            'list' => 'assoc'
                        ];
                        $tableField[] = "`menu_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属栏目'";
                        $validate['menu_id'] = [
                            [
                                'rule' => array('egt', 1),
                                'message' => '请选择父级导航'
                            ],            
                            [
                                'rule' => array('call', 'checkTypeOfMenu')
                            ]
                        ]; 
                    }
                    if (in_array('date', $fields)) {
                        $formData['date'] = [
                            'type' => 'date',
                            'name' => '发布日期',
                            'elem' => 'date',
                            'list' => 'date',               
                            'options' => [
                                'type' => 'date', 
                            ]
                        ];
                        $tableField[] = "`date` date NOT NULL DEFAULT '1970-01-01' COMMENT '发布日期'";
                    }
                    if (in_array('is_verify', $fields)) {
                        $formData['is_verify'] = [
                            'type' => 'boolean',
                            'name' => '是否审核',
                            'elem' => 'checker',
                            'list' => 'checker',
                            'sortable' => true
                        ];
                        $tableField[] = "`is_verify` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否审核'";
                    }
                    if (in_array('image', $fields)) {
                        $formData['image'] = [
                            'type' => 'string',
                            'name' => '封面图片',
                            'elem' => 'image.upload',
                            'list' => 'image',
                            'image' => array(
                                'thumb' => array(
                                    'field' => 'thumb'
                                ),
                            ),
                            'upload' => array(
                                'maxSize' => 1024,
                                'validExt' => array('jpg', 'png', 'gif')
                            )
                        ];
                        $formData['thumb'] = [
                            'type' => 'string',
                            'name' => '缩略图',
                            'elem' => 0,
                            'list' => 0,
                            'detail' => 0
                        ]; 
                        $tableField[] = "`image` varchar(128) NOT NULL DEFAULT '' COMMENT '封面图片'";
                        $tableField[] = "`thumb` varchar(128) NOT NULL DEFAULT '' COMMENT '缩略图'";
                    }
                    if (in_array('file', $fields)) {
                        $formData['file'] = [
                            'type' => 'string',
                            'name' => '文件',
                            'elem' => 'file',
                            'list' => 'file',
                            'upload' => array(                                
                                'maxSize' => 2048
                            )
                        ];
                        $tableField[] = "`file` varchar(128) NOT NULL DEFAULT '' COMMENT '文件'";
                    }
                    if (in_array('list_order', $fields)) {
                        $formData['list_order'] = [
                            'type' => 'integer',
                            'name' => '排序权重',
                            'elem' => 'number',
                            'list' => 'edit.text'
                        ];
                        $tableField[] = "`list_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序权重'";
                    }
                    if (in_array('content', $fields)) {
                        $formData['content'] = [
                            'type' => 'text',
                            'name' => '内容',
                            'elem' => 'editor',
                            'length' => 80,
                            'list' => 0
                        ];
                        $tableField[] = "`content` mediumtext NOT NULL COMMENT '内容'";
                    }
                    if (in_array('created', $fields)) {
                        $formData['created'] = [
                            'type' => 'datetime',
                            'name' => '添加时间',
                            'elem' => 0,
                            'list' => 'datetime'
                        ];
                        $tableField[] = "`created` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '创建时间'";
                    }
                    if (in_array('modified', $fields)) {
                        $formData['modified'] = [
                            'type' => 'datetime',
                            'name' => '修改时间',
                            'elem' => 0,
                            'list' => 'datetime'
                        ];
                        $tableField[] = "`modified` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '最后修改时间'";
                    }
                    
                    if (in_array('user_id', $fields)) {
                        $tableField[] = "KEY `user_id` (`user_id`)";
                    }
                    if (in_array('menu_id', $fields)) {
                        $tableField[] = "KEY `menu_id` (`menu_id`)";
                    }
                    if (in_array('is_verify', $fields)) {
                        $tableField[] = "KEY `is_verify` (`is_verify`)";
                    }
                    if (in_array('list_order', $fields)) {
                        $tableField[] = "KEY `list_order` (`list_order`)";
                    }                    
                }
                $formData = var_export($formData, true);
                $validate = var_export($validate, true);
                
                $addString = <<<MODEL
<?php
namespace app\common\model;

class $model extends App
{
    /**
    * 关联模型
    */
    public \$assoc = $assoc;
    
    public function initialize()
    {   
        // 字段和表单信息  设置完成以后，在添加页面会自动读取数据并生成对应表单结构
        \$this->form = $formData;
        
        call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }
    
    /**
    // 表单分组
    public \$formGroup = [
        'advanced' => '高级选项'
    ];
    */
    
    /**
    * 数据验证 
    */
    protected \$validate = $validate;
}

MODEL;


                file_put_contents($path, $addString);
                
                if (!empty($tableField)) {
                    $sql = sprintf("CREATE TABLE IF NOT EXISTS `%s%s`(%s)DEFAULT CHARSET=%s COMMENT='%s'", config('database.prefix'), parse_name($model, 0), implode(',', $tableField), config('database.charset'), $data['cname']);
                    Db()->execute($sql);
                }
                
                $this->loadModel('Model');
                $this->Model->save([
                    'model' => $model,
                    'cname' => $data['cname'],
                    'is_menu' => (int)$data['is_menu'],
                    'is_power' => (int)$data['is_power'],
                    'is_import' => (int)$data['is_import']
                ]);
                return $this->message('success', '操作已成功');
            }
        }
        $this->fetch = 'form';
    }
    
    /**
    * 创建控制器
    */
    public function addc()
    {
        if (!config('app_debug')) {
            return $this->message('error', '非调试模式不允许进行该操作', ['close' => true, 'back' => false]);
        }
        $this->setTitle("控制器生成", 'operation');
        $this->assign->warning = '非程序员请谨慎操作！';
        $this->mdl->form = array(
            'controller' => array(
                'type' => 'string',
                'name' => '控制器名称',
                'elem' => 'text',
            ),
            'module' => array(
                'type' => 'string',
                'name' => '生成模块',
                'elem' => 'checkbox',
                'options' => ['run' => '后台', 'home' => '前台', 'manage' => '用户']
            ),
        );

        if ($this->request->isPost()) {
            $data = helper('Form')->data[$this->m];
            $controller = parse_name(trim($data['controller']), 1);
            $modules = $data['module'];
            if (!$controller) {
                $this->assign->mdl_error['controller'] = '请填写控制器的名称';
            }
            if (empty($modules)) {
                $this->assign->mdl_error['module'] = '请选择控制器生成模块';
            }
            
            
            if (!isset($this->assign->mdl_error)) {
                foreach ($modules as $module) {
                    $dir = APP_PATH . $module . DS . 'controller' . DS;
                    /*
                    if (!is_writeable($dir)) {
                        return $this->message('error', "模块{$module}/controller文件夹没有可写权限");
                    }*/
                    $path = $dir . $controller . '.php';
                    if (file_exists($path)) {
                        return $this->message('error', "控制器{$module}\\controller\\{$controller}已经存在");
                    }

                    $extends = ucfirst($module);
                    if ($module == 'run') {
                        $addString = <<<CONTROLLER
<?php
namespace app\\$module\controller;

use app\common\controller\Run;

class $controller extends $extends
{
    /**
    * 初始化 
    */
    protected function initialize()
    {        
        call_user_func(['parent', __FUNCTION__]); 
    }
    
    /**
    * 列表 
    */
    public function lists()
    {
        // 搜索字段
        /*        
        \$this->local['filter'] = [
            'title'
        ];
        */
        
        // 列表字段
        \$this->local['list_fields'] = [
            'title'
            // 其他列表字段
        ];
        
        // 添加额外条件
        //\$this->local['where'][] = ['字段', '=', '值'];        
        
        call_user_func(['parent', __FUNCTION__]);
    }
    
    /**
    * 添加
    */
    public function create()
    {   // 设置默认值
        //\$this->assignDefault('字段名', '默认值');
        // 字段白名单
        //\$this->local['whiteList'] = ['id', 'title', ...允许添加的字段列表];   
        call_user_func(['parent', __FUNCTION__]);
    }
    
    /**
    * 修改
    */
    public function modify()
    {   
        // 字段白名单
        //\$this->local['whiteList'] = ['id', 'title', ...允许修改的字段列表];
        call_user_func(['parent', __FUNCTION__]);
    } 
    
    /**
    * 删除
    */
    public function delete()
    {   
        // 设置额外的删除条件
        //\$this->local['where'][] = ['is_verify', '=', 0];
        call_user_func(['parent', __FUNCTION__]);
    }  
}

CONTROLLER;
                    } elseif ($module == 'home') {
                        $addString = <<<CONTROLLER
<?php
namespace app\\$module\controller;

use app\common\controller\Home;

class $controller extends $extends
{
    /**
    * 初始化
    */
    protected function initialize()
    {        
        call_user_func(['parent', __FUNCTION__]); 
    }
}

CONTROLLER;
                    } elseif ($module == 'manage') {
                        
                        $addString = <<<CONTROLLER
<?php
namespace app\\$module\controller;

use app\common\controller\Manage;

class $controller extends $extends
{
    /**
    * 初始化
    */
    protected function initialize()
    {        
        call_user_func(['parent', __FUNCTION__]); 
    }
}

CONTROLLER;
                    }
                    file_put_contents($path, $addString);
                }
                return $this->message('success', '操作已成功');
            }
            if ($data['module']) {
                helper('Form')->data[$this->m]['module'] = json_encode($data['module']);
            }
        }
        $this->fetch = 'form';
        
    }
    
    /**
    * 插件开发
    */
    public function addaddon()
    {
        if (!config('app_debug')) {
            return $this->message('error', '非调试模式不允许进行该操作', ['close' => true, 'back' => false]);
        }
        $this->setTitle("控制器生成", 'operation');
        $this->assign->warning = '快速帮你生成插件开发结构，非程序员请谨慎操作！';
        
        $this->mdl->form = array(
            'addon' => array(
                'type' => 'string',
                'name' => '插件目录',
                'elem' => 'text',
                'info' => '小写英文目录命名'
            ),
            'title' => array(
                'type' => 'string',
                'name' => '插件名称',
                'elem' => 'text'
            ),
            'intro' => array(
                'type' => 'string',
                'name' => '插件描述',
                'elem' => 'text'
            ),
            'author' => array(
                'type' => 'string',
                'name' => '插件作者',
                'elem' => 'text'
            ),
            'is_admin' => array(
                'type' => 'string',
                'name' => '是否有后台',
                'elem' => 'checker'
            ),
        );
        
        if ($this->request->isPost()) {
            
            $data = helper('Form')->data[$this->m];
            $addon = trim($data['addon']);
            $title = trim($data['title']);
            $intro = trim($data['intro']);
            $author = trim($data['author']);
            $is_admin = intval(trim($data['is_admin']));
            if (!$addon) {
                $this->assign->mdl_error['addon'] = '请填写插件目录';
            }
            if (!$title) {
                $this->assign->mdl_error['title'] = '请填写插件名称';
            }
            if (!$author) {
                $this->assign->mdl_error['author'] = '请填写插件作者';
            }
            $class = parse_name($addon, true);
            $addonPath = ADDONS_PATH . $addon . DS;
            
            if (!is_dir($addonPath)) {
                
                mkdir($addonPath, 0755, true);
                mkdir($addonPath . 'assets', 0755, true);
                mkdir($addonPath . 'controller', 0755, true);
                mkdir($addonPath . 'model', 0755, true);
                mkdir($addonPath . 'view', 0755, true);
                mkdir($addonPath . 'view' . DS . 'index', 0755, true);
                !is_dir(WWW_ROOT . 'addons' . DS . $addon) && mkdir(WWW_ROOT . 'addons' . DS . $addon, 0755, true);
                !is_dir(WWW_ROOT . 'addons' . DS . $addon . DS . 'js') && mkdir(WWW_ROOT . 'addons' . DS . $addon . DS . 'js', 0755, true);
                !is_dir(WWW_ROOT . 'addons' . DS . $addon . DS . 'css') && mkdir(WWW_ROOT . 'addons' . DS . $addon . DS . 'css', 0755, true);
                !is_dir(WWW_ROOT . 'addons' . DS . $addon . DS . 'images') && mkdir(WWW_ROOT . 'addons' . DS . $addon . DS . 'images', 0755, true);
                !is_dir(WWW_ROOT . 'addons' . DS . $addon . DS . 'files') && mkdir(WWW_ROOT . 'addons' . DS . $addon . DS . 'files', 0755, true);
                $configstr = <<<ADDON
<?php
return [
    // 插件是否必须登录
    'is_login' => true,
    'login_action' => url('run/user/login'),
    'addon_config' => [
        
    ]
];

ADDON;
              
               file_put_contents($addonPath . 'config.php', $configstr); 
               $inistr = <<<ADDON
name = $addon
title = $title
intro = $intro
author = $author
version = 1.0.0
state = 1
ADDON;
                file_put_contents($addonPath . 'info.ini', $inistr);
                $filestr = <<<ADDON
<?php
namespace addons\\$addon;

use woo\addons\Addons;

class $class extends Addons
{
    /**
    * 插件安装方法
    * @return bool
    */
    public function install()
    {
        return true;
    }
    
    /**
    * 插件卸载方法
    * @return bool
    */
    public function uninstall()
    {
        return true;
    }
}
ADDON;
                file_put_contents($addonPath . $class . '.php', $filestr);   
                $filestr = <<<ADDON
<?php
namespace addons\\$addon\controller;

use woo\addons\controller\Controller;

class Index extends Controller
{
    protected function initialize()
    {
        call_user_func(['parent', __FUNCTION__]);
    }
    
    public function index()
    {
        return \$this->fetch = true;
    }
}
ADDON;
                file_put_contents($addonPath . 'controller' . DS . 'Index.php', $filestr);                
                $filestr = <<<ADDON
<!DOCTYPE html>
<html lang="zh-cn" >
<head>{\$form=helper('Form')}{\$html=helper('Html')}
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" name="viewport"/>
<meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible"/>
<meta name="renderer" content="webkit"/>
<meta name="HandheldFriendly" content="true"/>
<meta name="format-detection" content="telephone=no, email=no" />
<meta name="Keywords" content="{if \$meta.keywords}{implode(',',\$meta.keywords)}{/if}" />
<meta name="Description" content="{if \$meta.description}{implode(',',\$meta.description)}{/if}" />
{if \$is_favicon}<link rel="shortcut icon" href="{\$root}favicon.ico" />{/if}
<title>{implode(' - ', \$meta.title|default:[])}</title>
{\$html->css(\$css,true)}
<script type="text/javascript">var wwwroot='{\$root}';var absroot='{\$absroot}';</script>
{\$html->script(\$js, true)}
</head>
{block name=function}{/block}
<body>
{block name=global}{/block}
{if isset(\$deferJs)}
{\$html->script(\$deferJs, true)}
{/if}
{block name=script}{/block}
</body>
</html>
ADDON;
                file_put_contents($addonPath . 'view' . DS . 'global.html', $filestr);                
                $filestr = <<<ADDON
{extends file="../global.html"}
{block name="global"}
<div>首页</div>
{/block}
ADDON;
                file_put_contents($addonPath . 'view' . DS . 'index' . DS . 'index.html', $filestr);                
                if ($is_admin) {
                    mkdir($addonPath . 'app', 0755, true);
                    mkdir($addonPath . 'app' . DS . 'run' . DS . 'controller', 0755, true);
                    mkdir($addonPath . 'app' . DS . 'common' . DS . 'model', 0755, true);
                    !is_dir(APP_PATH . DS . 'run' . DS . 'controller' . DS . $addon) && mkdir(APP_PATH . DS . 'run' . DS . 'controller' . DS . $addon, 0755, true);
                }
                return $this->message('success', '操作已成功');
            } else {
                $this->assign->mdl_error['addon'] = '当前插件已存在';
            }
        }
        $this->fetch = 'form';
    }
    
    /**
    * 插件控制器
    */
    public function addac()
    {
        if (!config('app_debug')) {
            return $this->message('error', '非调试模式不允许进行该操作', ['close' => true, 'back' => false]);
        }
        $this->setTitle("生成插件控制器", 'operation');
        $this->assign->warning = '非程序员请谨慎操作！';
        
        
        $addonList = Db::name('Addon')->select();
        $addonList = Hash::combine($addonList, '{n}.name', '{n}.name');
        
        $this->mdl->form = array(
            'addon' => array(
                'type' => 'string',
                'name' => '插件名',
                'elem' => 'select',
                'options' => $addonList
            ),
            'controller' => array(
                'type' => 'string',
                'name' => '控制器名称',
                'elem' => 'text',
            ),
            'module' => array(
                'type' => 'string',
                'name' => '生成地点',
                'elem' => 'checkbox',
                'options' => ['run' => '后台', 'home' => '前台']
            ),
        );
        
        if ($this->request->isPost()) {
            $data = helper('Form')->data[$this->m];
            
            $controller = parse_name(trim($data['controller']), 1);
            $addon = strtolower(trim($data['addon']));
            $module = $data['module'];
            if (!$controller) {
                $this->assign->mdl_error['controller'] = '请填写控制器的名称';
            }
            if (empty($addon)) {
                $this->assign->mdl_error['addon'] = '请选择插件';
            }
            if (empty($module)) {
                $this->assign->mdl_error['module'] = '请选择添加地点';
            }
            
            if (empty($this->assign->mdl_error)) {
                $msg = '';
                if (in_array('run', $module)) {
                    !is_dir(APP_PATH . DS . 'run' . DS . 'controller' . DS . $addon) && mkdir(APP_PATH . DS . 'run' . DS . 'controller' . DS . $addon, 0755, true);
                    $namespace = 'app\\run\\controller\\' . $addon;
                    if (!class_exists($namespace . '\\' . $controller)) {
                        $addString = <<<CONTROLLER
<?php
namespace $namespace;

use app\common\controller\Run;

class $controller extends Run
{
    /**
    * 初始化 
    */
    protected function initialize()
    {        
        call_user_func(['parent', __FUNCTION__]); 
    }
    
    /**
    * 列表 
    */
    public function lists()
    {
        // 搜索字段
        /*        
        \$this->local['filter'] = [
            'title'
        ];
        */
        
        // 列表字段
        \$this->local['list_fields'] = [
            'title'
            // 其他列表字段
        ];
        
        // 添加额外条件
        //\$this->local['where'][] = ['字段', '=', '值'];        
        
        call_user_func(['parent', __FUNCTION__]);
    }
    
    /**
    * 添加
    */
    public function create()
    {   // 设置默认值
        //\$this->assignDefault('字段名', '默认值');
        // 字段白名单
        //\$this->local['whiteList'] = ['id', 'title', ...允许添加的字段列表];   
        call_user_func(['parent', __FUNCTION__]);
    }
    
    /**
    * 修改
    */
    public function modify()
    {   
        // 字段白名单
        //\$this->local['whiteList'] = ['id', 'title', ...允许修改的字段列表];
        call_user_func(['parent', __FUNCTION__]);
    } 
    
    /**
    * 删除
    */
    public function delete()
    {   
        // 设置额外的删除条件
        //\$this->local['where'][] = ['is_verify', '=', 0];
        call_user_func(['parent', __FUNCTION__]);
    }  
}
CONTROLLER;
                        file_put_contents(APP_PATH . DS . 'run' . DS . 'controller' . DS . $addon . DS . $controller . '.php', $addString);
                        $msg .= '，' . $controller . '后台生成成功';
                    } else {
                        $msg .= '，' . $controller . '后台已存在';
                    }
                }
                
                if (in_array('home', $module)) {
                    $addonPath = ADDONS_PATH . $addon . DS;
                    if (is_dir($addonPath . 'controller')) {
                        $namespace = 'addons\\' . $addon . '\\controller';
                        if (!class_exists($namespace . '\\' . $controller)) {
                            $addString = <<<CONTROLLER
<?php
namespace $namespace;

use woo\addons\controller\Controller;

class $controller extends Controller
{
    protected function initialize()
    {
        call_user_func(['parent', __FUNCTION__]);
    }
    
    public function index()
    {
        return \$this->fetch = true;
    }
}
CONTROLLER;
                            file_put_contents($addonPath . 'controller' . DS . $controller . '.php', $addString);
                            $msg .= '，' . $controller . '前台生成成功';
                        } else {
                            $msg .= '，' . $controller . '前台已存在';
                        }
                        
                        
                    } else {
                        $msg .= '，' . '插件' . $addon . '前台控制器目录不存在';
                    }
                }
                return $this->message('success', '操作完成' .$msg);
            }
            
            if ($data['module']) {
                helper('Form')->data[$this->m]['module'] = json_encode($data['module']);
            }
        }
        
        $this->fetch = 'form';
    }
    
    public function getSiteSize()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        set_time_limit(0);
        echo $this->ajax('success',return_size(get_dir_size(dirname(APP_PATH))));
        exit ;
    }
    
    /**
    * 日志下载
    */
    public function getLog()
    {        
        include  \Env::get('root_path') . 'include' . DS . 'Pclzip' . DS . 'pclzip.lib.php';
        if (!file_exists(WWW_ROOT . 'tempfile')) {
            mkdir(WWW_ROOT . 'tempfile');
        }        
        $filepath = WWW_ROOT . 'tempfile' . DS . 'log_' . date('Ymd') . '.zip';
        $logszip = new \PclZip($filepath);
        $zipList = $logszip->create(\Env::get('runtime_path') . 'log' . DS, PCLZIP_OPT_REMOVE_ALL_PATH);  
        if ($zipList == 0) {
            $this->message('error', '日志文件压缩失败:' . $logszip->errorInfo(true));
        }
        ob_end_clean();
        header("Content-Type: application/force-download;"); 
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($filepath));
        header("Content-Disposition: attachment; filename=" . 'log_' . date('Ymd') . '.zip'); 
        header("Expires: 0");
        header("Cache-control: private");
        header("Pragma: no-cache"); 
        readfile($filepath);         
        exit ;
    }
    
    function removeLog()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        remove_dir(\Env::get('runtime_path') . 'log' . DS);
        return $this->ajax('success', '日志清除成功！');
    }
    
    function removeTemp()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        remove_dir(\Env::get('runtime_path') . 'temp' . DS);
        remove_dir(WWW_ROOT . 'tempfile');
        return $this->ajax('success', '临时文件清除成功！');
    }
    
    public function clearCache()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        \Cache::clear(); 
        remove_dir(\Env::get('runtime_path') . 'static' . DS);
        return $this->ajax('success', '缓存清除成功！');
    }
    
    public function switchTrace()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        $is_trace = intval(!config('app_trace'));
        $this->loadModel('Setting');
        $this->Setting->where(['vari' => 'is_trace'])->update(['value' => $is_trace]);
        $this->Setting->writeToFile();
        if($is_trace)
            return $this->ajax('success', 'Trace已启用');
        else
            return $this->ajax('success', 'Trace已关闭');
    }
    
    public function lockScreen()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        cookie(['prefix' => 'think_', 'expire' => 3600]);
        cookie('is_lock_screen', 1, 86400);
        return $this->ajax('success', '锁屏成功');
    }
    
    public function relieveScreen()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        
        $data = $this->request->post();
        if (trim($data['pwd'])) {
            $lock_pwd = helper('Auth')->password($data['pwd']);
            $this->loadModel('User');
            $check_pwd = $this->User->where(['id' => $this->login['id']])->value('password');
            if ($lock_pwd == $check_pwd) {
                cookie(['prefix' => 'think_', 'expire' => 3600]);
                cookie('is_lock_screen', null);
                return $this->ajax('success', '解屏成功');
            } else {
                return $this->ajax('error', '密码输入不一致');
            }
        } else {
            return $this->ajax('error', '请输入密码');
        }
    }
    
    public function setSkin()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        $data = $this->request->post();
        $skin = trim($data['skin']);
        cookie(['prefix' => 'think_', 'expire' => 3600]);
        cookie('skin_name', $skin, 2592000);
        return $this->ajax('success', '皮肤切换成功');
    }
    
    public function getAwesome()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        $return = \Cache::get('awesome');
        if (empty($return)) {
            $url  = "http://code.zoomla.cn/boot/font.html"; 
            $content = curl_send(['url' => $url]);
            preg_match_all('/<i\s+class="fa\s+([^"]+)"\s+aria-hidden="true">/is', $content['content'], $icons);
            $return = $icons[1] ? $icons[1] : [];
            \Cache::set('awesome', $return, 2592000);
        }
        return $this->ajax('success', '', $return);
    }
   
    public function  resetStaticCache()
    {
        try {
            model('Menu')->writeToFile();
            model('Model')->writeToFile();
            model('ManageMenu')->writeToFile();
            model('AdminMenu')->writeToFile();
            model('setting')->writeToFile();
            model('Dictionary')->resetFileCache();
            return $this->ajax('success', '静态缓存生成成功（尽量不要删除\data\cache目录）');
        } catch(\Exception $e) {
            model('AdminMenu')->writeToFile();
            return $this->ajax('error', '缓存量过大，生成出错');
        }
    }
}
  