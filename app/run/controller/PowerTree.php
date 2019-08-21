<?php
namespace app\run\controller;

use app\common\controller\Run;
use woo\utility\DocParser;
use think\Db;

class PowerTree extends Run
{

    protected function initialize()
    {
        call_user_func(['parent', __FUNCTION__]);
    }


    public function lists()
    {
        $this->addAction("新增一级{$this->mdl->cname}", array('PowerTree/create', ['parent_id' => 1]), 'fa-plus-circle', 'layer-ajax-form layui-btn');
        $this->addAction("一级{$this->mdl->cname}排序", array('PowerTree/sort', ['parent_id' => 1]), 'fa-sort', 'layer-ajax-form layui-btn-warm');
        if (config('auth.power_reset') || config('auth.power_controller_reset')) {
            $this->addAction("节点智能生成", array('PowerTree/start'), 'fa-bolt', 'layui-btn-danger');
        }
        $this->setTitle("{$this->mdl->cname}结构", 'operation');
        $this->fetch = 'tree';
    }


    protected function getFileList($path)
    {
        if (is_dir($path) && is_readable($path)) {
            $dirResourse = opendir($path);
            $fileList = [];
            while (($tar = readdir($dirResourse)) !== false) {
                if ($tar == '.' || $tar == '..') {
                    continue;
                }
                if (stripos($tar, '.php') !== false) {
                    $fileList['controller'][] = substr($tar, 0, -4);
                } else {
                    $fileList['addons'][] = $tar;
                }
            }
            closedir($dirResourse);
            return $fileList;
        } else {
            return [];
        }
    }

    /**
     * 节点缓存
     * @powerset false
     */
    public function writeCache()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '请求方式错误');
        }
        ini_set('memory_limit', '512M');
        set_time_limit(60);
        $this->mdl->writeToFile();
        return $this->ajax('success', '节点数据缓存生成成功');

    }

    /**
     * 特殊权限节点
     * @poweras start
     */
    public function elsePower()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '请求方式错误');
        }

        try {
            Db::name($this->m)->delete(true);
            $rootId = Db::name($this->m)->insertGetId(['id' => 1, 'parent_id' => 0, 'title' =>'根节点', 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);
        } catch (\Exception $e) {
            return $this->ajax('error', $e->getMessage(), ['break' => true]);
        }

        $msg = '特殊权限节点生成成功';
        $power_special_list = config('auth.power_special_list');
        if (!empty($power_special_list)) {
            $elseId = Db::name($this->m)->insertGetId(['id' => null, 'controller' => '', 'action' =>'', 'together' => '', 'parent_id' => 1, 'title' =>'特殊权限', 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);
            $dataList = [];
            foreach ($power_special_list as $item) {
                $dataList[] = [
                    'id' => null,
                    'parent_id' => $elseId,
                    'title' => $item['title'],
                    'addon' => '',
                    'controller' => $item['controller'],
                    'action' => $item['action'],
                    'together' => parse_name(trim($item['controller'])) . '/' . strtolower(trim($item['action'])),
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s')
                ];
            }
            Db::name($this->m)->insertAll($dataList);

            $this->loadModel('Power');
            if (!$this->Power->count()) {
                $this->Power->save([
                    'id' => null,
                    'type' => 'user',
                    'foreign_id' => helper('Auth')->user('id'),
                    'user_id' => helper('Auth')->user('id'),
                    'content' => ['all/all']
                ]);
                $msg .= '，当前账号已经拥有超级权限（需要调整请到权限授权中自行修改）';
            }
        }
        return $this->ajax('success', $msg);
    }

    /**
     * 控制器权限节点重置
     * @poweras start
     */
    public function ajaxConReset()
    {
        /*
        if (!$this->request->isAjax()) {
            return $this->message('error', '请求方式错误');
        }
        */
        if (!config('auth.power_reset')) {
            return $this->ajax('error', '配置文件中设定不允许重置权限节点', ['break' => true]);
        }
        $controller = trim($this->args['c']);
        list($addon, $controller) = explode('/', $controller);
        if (empty($controller)) {
            $controller = $addon;
            $addon = '';
        }

        $addon = strtolower($addon);
        $controller = parse_name($controller, true);
        if (empty($addon)) {
            $controllerFull = 'app\\run\\controller\\' . $controller;
        } else {
            $controllerFull = 'app\\run\\controller\\' . $addon . '\\' . $controller;
        }
        if (!class_exists($controllerFull)) {
            return $this->ajax('error', '控制器【' . $controllerFull . '】不存在', ['break' => true]);
        }
        $passMethod = ['initialize', '__debugInfo', 'registerMiddleware', 'finish', '__construct'];

        $mainMethod = [
            'create' => '新增',
            'modify' => '修改',
            'lists' => '列表',
            'detail' => '详情',
            'sort' => '排序',
            'export' => '导出',
            'delete' => '删除',
            'batchDelete' => '批量删除',
            'batchVerify' => '批量审核',
            'batchDisabled' => '批量禁用',
            'ajaxSetField' => '列表修改',
            'ajaxSwitch' => '列表开关'
        ];
        $power_action_pass = config('auth.power_action_pass');
        $power_action_as =   config('auth.power_action_as');
        $reflection = new \ReflectionClass($controllerFull);
        $classDoc = (new DocParser())->parse($reflection->getDocComment());

        $exists = Db::name($this->m)
            ->where([
                ['controller', '=', $controller],
                ['addon', '=', $addon]
            ])
            ->order(['id' => 'ASC'])
            ->find();

        // 当前控制器不需要加入权限验证
        if (isset($classDoc['powerset']) && stripos($classDoc['powerset'], 'false') !== false) {
            if ($exists) {
                $this->mdl->deleteData($exists['id']);
            }
            return $this->ajax('success', '控制器【' . $controller . '】不加入权限，已经清空', ['finish' => true]);
        }
        if (isset($power_action_pass[($addon ? $addon . '/' : '') . $controller]) && $power_action_pass[($addon ? $addon . '/' : '') . $controller] === false) {
            if ($exists) {
                $this->mdl->deleteData($exists['id']);
            }
            return $this->ajax('success', '控制器【' . $controller . '】不加入权限，已经清空', ['finish' => true]);
        }

        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methodsAssoc = [];
        foreach ($methods as $item) {
            $name = $item->getName();
            if (in_array($name, $passMethod)) {
                continue;
            }
            $methodsAssoc[$name] = $item;
        }

        $methodsData = [];
        if (empty($exists)) {

            $parentId = Db::name($this->m)->insertGetId([
                'id' => null,
                'parent_id' => 1,
                'title' => trim($classDoc['name']) ? trim($classDoc['name']) : (isset($GLOBALS['Model_title'][$controller]) ? $GLOBALS['Model_title'][$controller] : $controller),
                'addon' => $addon,
                'controller' => $controller,
                'action' => '',
                'together' => '',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ]);
        } else {
            $parentId = $exists['id'];
            Db::name($this->m)->where('parent_id', '=', $parentId)->delete();
        }

        foreach ($mainMethod as $key => $title) {
            if (empty($methodsAssoc[$key])) {
                continue;
            }
            $doc = (new DocParser())->parse($methodsAssoc[$key]->getDocComment());
            if (isset($doc['powerset']) && stripos($doc['powerset'], 'false') !== false) {
                continue;
            }
            if (isset($power_action_pass[($addon ? $addon . '/' : '') . $controller]) && in_array($key, (array)$power_action_pass[($addon ? $addon . '/' : '') . $controller])) {
                continue;
            }
            if (isset($doc['poweras'])) {
                continue;
            }
            if (isset($power_action_as[($addon ? $addon . '/' : '') . $controller]) && array_key_exists($key, (array)$power_action_as[($addon ? $addon . '/' : '') . $controller])) {
                continue;
            }

            $methodsData[$key] = [
                'id' => null,
                'parent_id' => $parentId,
                'title' => empty(trim($doc['name'])) ? $title : trim($doc['name']),
                'addon' => $addon,
                'controller' => $controller,
                'action' => $key,
                'together' => strtolower($addon ? $addon . '/' : '') . parse_name(trim($controller)) . '/' . strtolower(trim($key)),
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
        }
        foreach ($methodsAssoc as $key => $item) {
            if (isset($methodsData[$key])) {
                continue;
            }
            $doc = (new DocParser())->parse($methodsAssoc[$key]->getDocComment());
            if (isset($doc['powerset']) && stripos($doc['powerset'], 'false') !== false) {
                continue;
            }
            if (isset($power_action_pass[($addon ? $addon . '/' : '') . $controller]) && in_array($key, (array)$power_action_pass[($addon ? $addon . '/' : '') . $controller])) {
                continue;
            }
            if (isset($doc['poweras'])) {
                continue;
            }
            if (isset($power_action_as[($addon ? $addon . '/' : '') . $controller]) && array_key_exists($key, (array)$power_action_as[($addon ? $addon . '/' : '') . $controller])) {
                continue;
            }

            $methodsData[$key] = [
                'id' => null,
                'parent_id' => $parentId,
                'title' => empty(trim($doc['name'])) ? $key : trim($doc['name']),
                'addon' => $addon,
                'controller' => $controller,
                'action' => $key,
                'together' => strtolower($addon ? $addon . '/' : '') . parse_name(trim($controller)) . '/' . strtolower(trim($key)),
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
        }
        if ($methodsData) {
            Db::name($this->m)->insertAll($methodsData);
        }

        return $this->ajax('success', '控制器【' . $controller . '】节点重置成功');
    }

    /**
     * 权限节点重置
     * @poweras start
     */
    public function ajaxReset()
    {

        if (!$this->request->isAjax()) {
            return $this->message('error', '请求方式错误');
        }

        if (!config('auth.power_reset')) {
            return $this->ajax('error', '配置文件中设定不允许重置权限节点', ['break' => true]);
        }

        $controllers = session('controller_list');
        if (empty($controllers)) {
            return $this->ajax('error', '控制器列表数据不存在', ['break' => true]);
        }
        $index = intval(trim($this->args['index']));
        // 表示每次生成几个控制器的节点，可以减少生成的时候ajax的请求次数
        $step = 1;
        $min = ($index - 1) * $step;
        $max = $min + ($step - 1);
        $finish = false;


        //$rootData = Db::name($this->m)->order(['id' => 'ASC'])->find();
        //$rootId = $rootData['id'];
        $rootId = 1;

        $passMethod = ['initialize', '__debugInfo', 'registerMiddleware', 'finish', '__construct'];

        $mainMethod = [
            'create' => '新增',
            'modify' => '修改',
            'lists' => '列表',
            'detail' => '详情',
            'sort' => '排序',
            'export' => '导出',
            'delete' => '删除',
            'batchDelete' => '批量删除',
            'batchVerify' => '批量审核',
            'batchDisabled' => '批量禁用',
            'ajaxSetField' => '列表修改',
            'ajaxSwitch' => '列表开关'
        ];
        $power_action_pass = config('auth.power_action_pass');
        $power_action_as =   config('auth.power_action_as');


        $install = [];
        for ($i = $min; $i <= $max; $i++) {
            if (empty($controllers[$i])) {
                $finish = true;
                break;
            }
            list($addon, $controller) = explode('/', $controllers[$i]);
            if (empty($controller)) {
                $controller = $addon;
                $addon = '';
            }
            if (empty($addon)) {
                $controllerFull = 'app\\run\\controller\\' . $controller;
            } else {
                $controllerFull = 'app\\run\\controller\\' . $addon . '\\' . $controller;
            }
            try {
                $install[] = strtolower($addon ? $addon . '/' : '') . $controller;
                $reflection = new \ReflectionClass($controllerFull);
                $classDoc = (new DocParser())->parse($reflection->getDocComment());
                // 当前控制器不需要加入权限验证
                if (isset($classDoc['powerset']) && stripos($classDoc['powerset'], 'false') !== false) {
                    continue;
                }
                if (isset($power_action_pass[($addon ? $addon . '/' : '') . $controller]) && $power_action_pass[($addon ? $addon . '/' : '') . $controller] === false) {
                    continue;
                }

                $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                $methodsAssoc = [];
                foreach ($methods as $item) {
                    $name = $item->getName();
                    if (in_array($name, $passMethod)) {
                        continue;
                    }
                    $methodsAssoc[$name] = $item;
                }

                $methodsData = [];


                $parentId = Db::name($this->m)->insertGetId([
                    'id' => null,
                    'parent_id' => $rootId,
                    'title' => trim($classDoc['name']) ? trim($classDoc['name']) : (isset($GLOBALS['Model_title'][$controller]) ? $GLOBALS['Model_title'][$controller] : $controller),
                    'addon' => $addon,
                    'controller' => $controller,
                    'action' => '',
                    'together' => '',
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s')
                ]);

                foreach ($mainMethod as $key => $title) {
                    if (empty($methodsAssoc[$key])) {
                        continue;
                    }
                    $doc = (new DocParser())->parse($methodsAssoc[$key]->getDocComment());
                    if (isset($doc['powerset']) && stripos($doc['powerset'], 'false') !== false) {
                        continue;
                    }
                    if (isset($power_action_pass[($addon ? $addon . '/' : '') . $controller]) && in_array($key, (array)$power_action_pass[($addon ? $addon . '/' : '') . $controller])) {
                        continue;
                    }
                    if (isset($doc['poweras'])) {
                        continue;
                    }
                    if (isset($power_action_as[($addon ? $addon . '/' : '') . $controller]) && array_key_exists($key, (array)$power_action_as[($addon ? $addon . '/' : '') . $controller])) {
                        continue;
                    }

                    $methodsData[$key] = [
                        'id' => null,
                        'parent_id' => $parentId,
                        'title' => empty(trim($doc['name'])) ? $title : trim($doc['name']),
                        'addon' => $addon,
                        'controller' => $controller,
                        'action' => $key,
                        'together' => strtolower($addon ? $addon . '/' : '') . parse_name(trim($controller)) . '/' . strtolower(trim($key)),
                        'created' => date('Y-m-d H:i:s'),
                        'modified' => date('Y-m-d H:i:s')
                    ];
                }
                foreach ($methodsAssoc as $key => $item) {
                    if (isset($methodsData[$key])) {
                        continue;
                    }
                    $doc = (new DocParser())->parse($methodsAssoc[$key]->getDocComment());
                    if (isset($doc['powerset']) && stripos($doc['powerset'], 'false') !== false) {
                        continue;
                    }
                    if (isset($power_action_pass[($addon ? $addon . '/' : '') . $controller]) && in_array($key, (array)$power_action_pass[($addon ? $addon . '/' : '') . $controller])) {
                        continue;
                    }
                    if (isset($doc['poweras'])) {
                        continue;
                    }
                    if (isset($power_action_as[($addon ? $addon . '/' : '') . $controller]) && array_key_exists($key, (array)$power_action_as[($addon ? $addon . '/' : '') . $controller])) {
                        continue;
                    }

                    $methodsData[$key] = [
                        'id' => null,
                        'parent_id' => $parentId,
                        'title' => empty(trim($doc['name'])) ? $key : trim($doc['name']),
                        'addon' => $addon,
                        'controller' => $controller,
                        'action' => $key,
                        'together' => strtolower($addon ? $addon . '/' : '') . parse_name(trim($controller)) . '/' . strtolower(trim($key)),
                        'created' => date('Y-m-d H:i:s'),
                        'modified' => date('Y-m-d H:i:s')
                    ];

                }
                if ($methodsData) {
                    Db::name($this->m)->insertAll($methodsData);
                }

            } catch (\Exception $e) {
                return $this->ajax('error', $e->getMessage(), ['break' => true]);
            }
        }

        $finish = $max+1 >= count($controllers) ? true : false;
        return $this->ajax('success', '控制器【' . implode('、', $install) . '】权限节点已经安装完成', ['finish' => $finish]);
    }

    /**
    * 节点生成
    */
    public function start(){

        $level1List = $this->getFileList(APP_PATH . 'run' . DS . 'controller');
        if (empty($level1List)) {
            return $this->message('error', APP_PATH . 'run' . DS . 'controller' . '没有读的权限，控制器列表获取失败');
        }

        $controllers = $level1List['controller'];
        if (!empty($level1List['addons'])) {
            foreach ($level1List['addons'] as $addon) {
                $addonList = $this->getFileList(APP_PATH . 'run' . DS . 'controller' . DS . $addon);
                if (!empty($addonList['controller'])) {
                    foreach ($addonList['controller'] as $con) {
                       $controllers[] = $addon .'/' . $con;
                    }
                }
            }
        }

        session('controller_list', $controllers);


        $this->setTitle("权限节点智能生成", 'operation');
        $this->addAction("返回列表", array($this->m . '/lists'), 'fa-reply', 'layui-btn-normal');
        $this->fetch = 'start';

        /*
        if (!$this->request->isAjax()) {
            return $this->message('error', '请求方式错误');
        }

        if ($this->mdl->count()) {
            return $this->ajax('error', '节点已存在，不能再执行初始化');
        }

        $map = [
            'lists' => '查看列表',
            'create' => '新增',
            'modify' => '更新',
            'delete' => '删除',
            'sort' => '排序',
            'export' => '导出',
            'detail' => '详情',
            'batch_delete' => '批量删除',
            'ajax_switch' => '列表开关',
            'ajax_set_field' => '列表值设置'
        ];

        $map_else = [
            'Dustbin' => [
                'recover' => '还原数据'
            ],
            'Menu' => [
                'create_position' => '栏目广告位'
            ],
            'Model' => [
                'datadict' => '数据字典'
            ],
            'Power' => [
                'content' => '用户授权',
                'remove' => '删除授权',
                'lists' => false,
                'create' => false,
                'modify' => false,
                'delete' => false,
                'sort' => false,
                'export' => false,
                'detail' => false,
                'batch_delete' => false,
                'ajax_switch' => false,
                'ajax_set_field' => false
            ],
            'Database' => [
                'lists' => '数据表列表',
                'filelist' => '备份文件列表',
                'backup' => '备份',
                'optimize' => '优化',
                'repair' => '修复',
                'delete' => '删除备份文件',
                'download' => '下载备份文件',
                'create' => false,
                'modify' => false,
                'sort' => false,
                'export' => false,
                'detail' => false,
                'batch_delete' => false,
                'ajax_switch' => false,
                'ajax_set_field' => false
            ],
            'Setting' => [
                'set' => '设置值'
            ],
            'Import' => [
                'show' => '导入预览',
                'import' => '数据导入'
            ],
            'Member' => [
                'create' => false
            ]
        ];
        ##其他权限
        $else = [
            [
                'title' => '模型生成',
                'controller' => 'Tool',
                'action' => 'addm'
            ],
            [
                'title' => '模板创建',
                'controller' => 'Tool',
                'action' => 'addv'
            ],
            [
                'title' => '控制器生成',
                'controller' => 'Tool',
                'action' => 'addc'
            ],
            [
                'title' => '超级权限',
                'controller' => 'All',
                'action' => 'all'
            ],
        ];

        $db = db($this->m);
        $rootId = $db->insertGetId(['id' => 1, 'parent_id' => 0, 'title' =>'根节点', 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);

        unset($GLOBALS['Model_title']['Exlink']); ##外链没有操作
        unset($GLOBALS['Model_title']['Picture']); ##不同的图片类型单独设置
        $GLOBALS['Model_title']['Database'] = '数据管理';

        foreach ($GLOBALS['Model_title'] as $model => $model_name) {
            $data = [
                'id' => null,
                'parent_id' => $rootId,
                'title' => $model_name,
                'controller' => '',
                'action' => '',
                'together' => '',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
            $parentId = $db->insertGetId($data);

            $children = $map;
            if (isset($map_else[$model])) {
                $children = array_merge($map, $map_else[$model]);
            }

            foreach ($children as $action => $action_name) {
                if ($action_name !== false) {
                    $data = [
                        'id' => null,
                        'parent_id' => $parentId,
                        'title' => $action_name,
                        'controller' => $model,
                        'action' => $action,
                        'together' => strtolower(trim($model)) . '::' . strtolower(trim($action)),
                        'created' => date('Y-m-d H:i:s'),
                        'modified' => date('Y-m-d H:i:s')
                    ];
                    $db->insertGetId($data);
                }
            }
        }

        $elseId = $db->insertGetId(['id' => null, 'controller' => '', 'action' =>'', 'together' => '', 'parent_id' => $rootId, 'title' =>'其他权限', 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')]);
        foreach ($else as $each) {
            $data = [
                'id' => null,
                'parent_id' => $elseId,
                'title' => $each['title'],
                'controller' => $each['controller'],
                'action' => $each['action'],
                'together' => strtolower(trim($each['controller'])) . '::' . strtolower(trim($each['action'])),
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ];
            $db->insertGetId($data);
        }
        $this->mdl->writeToFile();

        $this->loadModel('Dictionary');
        if (!$this->Dictionary->where([['model', '=', $this->m], ['field', '=', 'action']])->count()) {
            $this->Dictionary->save(['title' => '权限节点.方法名', 'model' => $this->m, 'field' => 'action', 'dictionary_item_count' => count($map)]);
            $dictId = $this->Dictionary->id;
            foreach ($map as $action => $action_name) {
                db('DictionaryItem')->insert([
                    'id' => null,
                    'dictionary_id' => $dictId,
                    'value' => $action,
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s')
                ]);
            }
            $this->Dictionary->writeToFile($dictId);
        }
        $this->loadModel('Power');
        $this->Power->save([
            'id' => null,
            'type' => 'user',
            'foreign_id' => helper('Auth')->user('id'),
            'user_id' => helper('Auth')->user('id'),
            'content' => ['all::all']
        ]);

        return $this->ajax('success', '处理完成(当前用户自动拥有超级权限，请自行修改)');
        */
    }

    public function sort()
    {
        $this->local['order'] = array('list_order' => 'ASC', 'id' => 'ASC');
        if (empty($this->args)) {
            $this->args['parent_id'] = 1;
        }
        call_user_func(array('parent', __FUNCTION__));
    }

    //添加
    public function create()
    {
        if ($this->args['parent_id']) {
            $controller  = powertree(intval($this->args['parent_id']), 'controller');
            if ($controller) {
                $this->assignDefault('controller', $controller);
            }
        }
        call_user_func(['parent', __FUNCTION__]);
    }

    //修改
    public function modify()
    {
        call_user_func(['parent', __FUNCTION__]);
    }

    //删除
    public function delete()
    {
        call_user_func(['parent', __FUNCTION__]);
    }
}
