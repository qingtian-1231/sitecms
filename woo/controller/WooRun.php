<?php

namespace woo\controller;

use woo\utility\Hash;
use woo\utility\TpText;

class WooRun extends WooApp
{
    /**
     * 图标
     */
    protected $iconMap = array(
        'create' => 'fa-plus-circle',
        'sort' => 'fa-sort',
        'detail' => 'fa-eye',
        'parent' => 'fa-reply',
        'export' => 'fa-download',
        'batch_delete' => 'fa-trash',
        'batch_verify' => 'fa-heart',
        'batch_disabled' => 'fa-heartbeat'
    );

    /**
     * 不锁屏的方法
     */
    protected $notLockScreenAction = [];
    /**
    * 当前控制器是否进行栏目权限判断
     */
    protected $isCheckMenuPower = false;

    /**
    * 当前用户的栏目权限数据 只有当前控制器需要进行栏目权限判断的时候 才会有数据 否则空数组
     */
    protected $menuPower = [];

    /**
     * 初始化
     * @powerset false
     */
    protected function initialize()
    {
        $this->addTitle('后台管理系统');
        $this->assign->dev = read_file_cache('Developer');

        parent::initialize();

        $is_power = helper('Auth')->checkPower();
        if (!$is_power) {
            $this->redirect('nopower');
            exit;
        }
        $this->isCheckMenuPower = helper('Auth')->isCheckMenuPower($this->m);
        if ($this->isCheckMenuPower) {
            $this->menuPower = helper('Auth')->getMenuPower();
        }
        $isLock = isset($_COOKIE[config('session.prefix') . '_is_lock_screen']) ? true : false;
        if ($isLock && !in_array(strtolower($this->params['action']), ['login', 'logout', 'ajax_login', 'relievescreen']) && ($this->params['controller'] != 'Index' && $this->params['action'] != 'index') && !in_array($this->params['action'], $this->notLockScreenAction)) {
            $this->redirect('run/Index/index');
        }
    }

    /**
     * 首页 默认跳转到lists
     * @powerset false
     */
    public function index()
    {
        $this->redirect('lists');
    }

    /**
     * 新增
     * @powerset true
     */
    public function create()
    {
        if ($this->isCheckMenuPower) {
            if (!empty($this->args['parent_id']) && !in_array($this->args['parent_id'], (array)$this->menuPower['action']['create'])) {
                return $this->message('error', '对不起，您没有权限添加该栏目的数据');
            }
        }

        // 通过URL设置默认值
        foreach ($this->args as $field => $value) {
            if ($field == 'parent_id') {
                if (isset($this->mdl->parentModel)) {
                    if (isset($this->mdl->form[$this->local['parent_conj']])) {
                        $this->assignDefault($this->local['parent_conj'], intval($this->args['parent_id']));
                    }
                }
            } else {
                if (isset($this->mdl->form[$field])) {
                    $this->assignDefault($field, $value);
                }
            }
        }

        // 创建者
        if (isset($this->mdl->form['user_id']) && !isset($this->args['user_id'])) {
            $this->assignDefault('user_id', $this->login['id']);
        }

        // 审核字段默认值
        if ($this->mdl->form['is_verify']) {
            $this->assignDefault('is_verify', !setting('is_verify') || setting('default_verify'));
        }

        if ($this->request->isPost()) {
            $data = helper('Form')->data;
            unset($data[$this->m][$this->mdlPk]);
            if ($this->isCheckMenuPower) {
                if (!empty($data[$this->m]['menu_id']) && !in_array($data[$this->m]['menu_id'], (array)$this->menuPower['action']['create'])) {
                    return $this->message('error', '对不起，您没有权限添加该栏目的数据');
                }
            }

            $result = $this->mdl->createData($data[$this->m], [
                'allowField' => isset($this->local['allowField']) ? $this->local['allowField'] : true
            ]);

            if ($result) {
                $saveData = $this->mdl->getData();
                if (!isset($this->local['success_redirect'])) {
                    return $this->message('success', "{$this->mdl->cname}[ID:{$saveData[$this->mdlPk]}]添加成功", array('返回列表' => array($this->m . '/lists', ['parent_id' => $saveData[$this->local['parent_conj']]]), 'back' => ['create', ['parent_id' => $saveData[$this->local['parent_conj']]]]));
                } else {
                    return $this->message('success', "{$this->mdl->cname}[ID:{$saveData[$this->mdlPk]}]添加成功", $this->local['success_redirect']);
                }
            } else {
                helper('Form')->data[$this->m] = $this->mdl->getData();
                if (empty($this->mdl->getError())) {
                    $this->assign('error', '添加失败【未知原因】');
                }
            }
        } else {
            if (isset($this->args['copy_id'])) {
                $copyId = intval($this->args['copy_id']);
                $copyData = $this->mdl
                    ->where($this->local['where'])
                    ->where($this->mdlPk, '=', $copyId)
                    ->find();
                if ($copyData) {
                    helper('Form')->data[$this->m] = $copyData->toArray();
                }
            }
        }

        $this->assignAssocValue();

        if (!isset($this->local['title'])) {
            $this->local['title'] = "新增{$this->mdl->cname}";
        }
        $this->setTitle($this->local['title'], 'operation');
        $this->addAction("返回列表", $this->returnListsUrl(), 'fa-reply', 'layui-btn-normal');

        $this->fetch = 'form';
    }

    /**
     * 修改
     * @powerset true
     */
    public function modify()
    {
        if (isset($this->local['id'])) {
            $id = intval($this->local['id']);
        } else {
            $id = isset($this->args['id']) ? intval($this->args['id']) : 0;
        }

        if (empty($id)) {
            $this->redirect($this->m . '/create', $this->args);
        }

        if ($this->request->isPost()) {
            $data = helper('Form')->data;

            $result = $this->mdl->modifyData($id, $data[$this->m], [
                'allowField' => isset($this->local['allowField']) ? $this->local['allowField'] : true,
                'where' => $this->local['where']
            ]);

            if ($result) {
                $saveData = $this->mdl->getData();
                if (!isset($this->local['success_redirect'])) {
                    return $this->message('success', "{$this->mdl->cname}[ID:{$id}]编辑成功", array('返回列表' => $this->returnListsUrl($saveData), 'back' => ['modify', ['id' => $id]]));
                } else {
                    return $this->message('success', "{$this->mdl->cname}[ID:{$id}]编辑成功", $this->local['success_redirect']);
                }
            } else {
                helper('Form')->data[$this->m] = array_merge(helper('Form')->data[$this->m], $this->mdl->getData());
                if (empty($this->mdl->getError())) {
                    $this->assign('error', '修改失败【未知原因】');
                }
            }
        } else {
            $oldData = $this->mdl
                ->where($this->local['where'])
                ->where($this->mdlPk, '=', $id)
                ->find();
            if (empty($oldData)) {
                $this->redirect($this->m . '/create');
            }
            if ($this->isCheckMenuPower) {
                if (!empty($oldData['menu_id']) && !in_array($oldData['menu_id'], (array)$this->menuPower['action']['modify'])) {
                    return $this->message('error', '对不起，您没有权限编辑该栏目的数据');
                }
            }

            $oldData = $oldData->toArray();
            $oldFormData = helper('Form')->data;
            if (isset($oldFormData[$this->m])) {
                helper('Form')->data[$this->m] = array_merge($oldData, $oldFormData[$this->m]);
            } else {
                helper('Form')->data[$this->m] = $oldData;
            }
        }

        $this->assignAssocValue();

        if (!isset($this->local['title'])) {
            $this->local['title'] = "修改{$this->mdl->cname}";
        }
        $this->setTitle($this->local['title'], 'operation');
        $this->addAction("返回列表", $this->returnListsUrl($oldData), 'fa-reply', 'layui-btn-normal');

        $this->fetch = 'form';
    }

    /**
     * 列表
     * @powerset true
     */
    public function lists()
    {
        if ($this->request->isPost()) {
            if (isset(helper('Form')->data['Search'])) {
                $search = Hash::flatten(helper('Form')->data['Search']);
                foreach ($search as $field => $value) {
                    if (trim($value) === '') {
                        unset($search[$field]);
                        unset($this->args[$field]);
                    }
                }
                unset($search['controller']);
                unset($search['action']);
                $this->redirect('lists', $search + ['page' => null] +$this->args);
                exit;
            }
        }
        if ($this->isCheckMenuPower) {
            if (!empty($this->args['parent_id']) && !in_array($this->args['parent_id'], (array)$this->menuPower['action']['lists'])) {
                return $this->message('error', '对不起，您没有权限查看该栏目的数据');
            }
            if (empty($this->args['parent_id'])) {
                $this->local['where'][] = ['menu_id', 'IN', $this->menuPower['action']['lists']];
            }
        }
        // 操作
        if (!isset($this->local['actions']['create'])) {
            $this->local['actions']['create'] = ["新增" => [$this->m . '/create', $this->args]];
        }
        if ($this->local['sortable'] && $this->args['parent_id'] && $this->mdl->form['list_order']) {
            $this->local['actions']['sort'] = ["排序" => [$this->m . '/sort', $this->args]];
        }
        if (!isset($this->local['actions']['batch_delete'])) {
            $this->local['actions']['batch_delete'] = ['删除' => 'javascript:void(0);'];
        }
        if (!isset($this->local['actions']['batch_verify']) && isset($this->mdl->form['is_verify'])) {
            $this->local['actions']['batch_verify'] = ["启用" => 'javascript:void(0);'];
        }
        if (!isset($this->local['actions']['batch_disabled']) && isset($this->mdl->form['is_verify'])) {
            $this->local['actions']['batch_disabled'] = ["禁用" => 'javascript:void(0);'];
        }
        if (!empty($this->local['exportable'])) {
            $this->local['actions']['export'] = ["导出" => [$this->m . '/export', $this->args]];
        }

        // 关联图片
        $usePictureModel = is_json_validate(setting('use_picture_model')) ? json_decode(setting('use_picture_model'), true) : [];

        if (in_array($this->m, $usePictureModel)) {
            if (!$this->local['list_fields']['picture_count'] && isset($this->mdl->form['picture_count'])) {
                $this->local['list_fields'] = $this->local['list_fields'] + ['picture_count' => $this->mdl->form['picture_count']];
            }
            $this->addItemAction('添加图片', array($this->m . 'Picture/create', ['parent_id' => $this->mdlPk], 'parse' => ['parent_id']), '&#xe654;', 'layer-ajax-form');
        }

        $addMoreClass = [
            'create' => 'create layui-btn layer-ajax-form ',
            'sort' => 'sort layer-ajax-form layui-btn-warm',
            'view' => 'view layer-ajax-form',
            'batch_delete' => 'batch_delete layui-btn-danger',
            'batch_verify' => 'batch_verify layui-btn-success',
            'batch_disabled' => 'batch_disabled',
            'export' => 'export layui-btn-warm'
        ];

        foreach ($this->local['actions'] as $key => $item) {
            if (is_array($item)) {
                $this->addAction(key($item), current($item), $this->iconMap[$key], isset($addMoreClass[$key]) ? $addMoreClass[$key] : $key);
            } else {
                $this->addAction($item);
            }
        }

        // 列表分析
        $this->parseListParent();
        $this->parseListFields();
        $this->parseListFilters();


        // 列表操作
        if (!isset($this->local['item_actions']['modify'])) {
            $this->assign->item_actions[] = 'modify';
        }
        if (!isset($this->local['item_actions']['detail'])) {
            $this->assign->item_actions[] = 'detail';
        }
        if (isset($this->local['item_actions']['copy'])) {
            $this->assign->item_actions[] = 'copy';
        }
        if (!isset($this->local['item_actions']['delete'])) {
            $this->assign->item_actions[] = 'delete';
        }

        $this->assign->item_actions = Hash::merge(
            isset($this->assign->item_actions) ? (array)$this->assign->item_actions : [],
            isset($this->local['item_actions']) ? (array)$this->local['item_actions'] : []
        );

        // 排序
        if (isset($this->args['sort']) && isset($this->mdl->form[$this->args['sort']])) {
            $direction = isset($this->args['direction']) ? (in_array(strtoupper($this->args['direction']), ['DESC', 'ASC']) ? strtoupper($this->args['direction']) : 'DESC') : 'DESC';
            $this->local['order'][$this->args['sort']] = $direction;
        }

        if (isset($this->mdl->form['list_order']) && !isset($this->local['order']['list_order'])) {
            $this->local['order']['list_order'] = 'DESC';
        }

        if (isset($this->mdl->form[$this->mdlPk]) && !isset($this->local['order'][$this->mdlPk])) {
            $this->local['order'][$this->mdlPk] = 'DESC';
        }

        // 分段
        if (!isset($this->local['limit'])) {
            $this->local['limit'] = intval(setting('admin_list_count'));
        } else {
            $this->local['limit'] = intval($this->local['limit']);
        }
        if ($this->local['limit'] <= 0) {
            $this->local['limit'] = 15;
        }

        // 列表数据
        $requestUrl = $this->request->url();
        $isQuery = true;
        if (setting('is_admin_cache')) {
            $listCache = \Cache::get($requestUrl);
            if ($listCache) {
                $isQuery = false;
                $this->assign->list = $this->list = $listCache['list'];
                $this->assign->page = $this->page = $listCache['page'];
            }
        }
        if ($isQuery) {
            // 原分页查询
            /*
            $result = $this->mdl->getPageList([
                'where' => $this->local['where'],
                'order' => $this->local['order'],
                'with' => $this->local['with'],
                'field' => isset($this->local['field'][$this->m]) ? $this->local['field'][$this->m] : $this->local['field'],
                'limit' => $this->local['limit'],
                'paginate' => []
            ]);
            $this->assign->list = $this->list = $result['list'];
            $this->assign->page = $this->page = $result['page'];
            */
            // 20190111重写分页查询
            $result = $this->mdl->getPageList([
                'where' => $this->local['where'],
                'whereOr' => isset($this->local['whereOr']) ? $this->local['whereOr'] : [],
                'whereColumn' => isset($this->local['whereColumn']) ? $this->local['whereColumn'] : [],
                'whereTime' => isset($this->local['whereTime']) ? $this->local['whereTime'] : [],
                'order' => $this->local['order'],
                'field' => [$this->mdlPk],
                'limit' => $this->local['limit'],
                'paginate' => []
            ]);
            $this->assign->page = $this->page = $result['page'];

            $idList = Hash::combine($result['list'], '{n}.' . $this->mdlPk, '{n}.' . $this->mdlPk);

            if (!empty($idList)) {
                $this->assign->list = $this->list = $this->mdl
                    ->with($this->local['with'])
                    ->where($this->mdlPk, 'IN', $idList)
                    ->order($this->local['order'])
                    ->field(isset($this->local['field'][$this->m]) ? $this->local['field'][$this->m] : $this->local['field'])
                    ->select()
                    ->toArray();
            } else {
                $this->assign->list = $this->list = [];
            }

            if (setting('is_admin_cache')) {
                \Cache::tag($this->m)->set($requestUrl, ['list' => $this->list, 'page' => $this->page], 3600);
            }
        }

        if (!isset($this->local['title'])) {
            $this->local['title'] = "{$this->mdl->cname}列表";
        }
        $this->setTitle($this->local['title'], 'operation');

        if (setting('is_admin_args')) {
            session("{$this->m}_lists_args", [
                'args' => $this->args,
                'time' => time() + 600
            ]);
        }

        $this->assign->addJs([
            'tableResize'
        ]);

        return $this->fetch = 'lists';
    }

    /**
    * 列表地址
    */
    protected function returnListsUrl($data = [], $parent_field = null, $url_merge = []) {

        if (session("?{$this->m}_lists_args")) {
            $store = session("{$this->m}_lists_args");
            if ($store['time'] >= time()) {
                $store = $store['args'];
            } else {
                $store = [];
            }
        } else {
            $store = [];
        }
        $parent_field = empty($parent_field) ? $this->local['parent_conj'] : 'parent_id';

        if (!empty($store) && setting('is_admin_args')) {
            return ["{$this->m}/lists", $store];
        }
        $store = !empty($data) ?  $data : $this->args;

        if (is_array($store)) {
            $parent_id = !empty($store[$parent_field]) ? $store[$parent_field] : null;
        } else {
            $parent_id = !empty($store) ? $store : null;
        }
        if (empty($parent_id) && isset($this->args['parent_id'])) {
            $parent_id = $this->args['parent_id'];
        }
        return ["{$this->m}/lists", array_merge(['parent_id' => $parent_id], $url_merge)];
    }

    /**
     * 导出
     * @powerset true
     */
    public function export()
    {
        if (!isset($this->local['export_type'])) {
            $this->local['export_type'] = 'ajax';//导出方式  支持ajax和xml 建议ajax
        }
        if ($this->isCheckMenuPower) {
            if (!empty($this->args['parent_id']) && !in_array($this->args['parent_id'], (array)$this->menuPower['action']['export'])) {
                return $this->message('error', '对不起，您没有权限导出该栏目的数据');
            }
            if (empty($this->args['parent_id'])) {
                $this->local['where'][] = ['menu_id', 'IN', $this->menuPower['action']['export']];
            }
        }
        $this->local['parent_return_url'] = false;
        $this->parseListParent();
        $this->parseListFields();
        $this->parseListFilters();

        if ($this->request->isPost()) {
            if (isset(helper('Form')->data['Search'])) {
                $search = Hash::flatten(helper('Form')->data['Search']);
                foreach ($search as $field => $value) {
                    if (trim($value) === '') {
                        unset($search[$field]);
                        unset($this->args[$field]);
                    }
                }
                unset($search['controller']);
                unset($search['action']);
                $this->redirect('export', $search + $this->args);
                exit;
            }
            if (isset(helper('Form')->data['Export'])) {
                $export_data = helper('Form')->data['Export'];
                if (empty($export_data['field'])) {
                    return $this->message('error', '未选择需要导出的字段列表');
                }

                $header = [];
                foreach ($export_data['field'] as $field) {
                    if (isset($this->local['list_fields'][$field]['callback'])) {
                        $header[$field]['callback'] = $this->local['list_fields'][$field]['callback'];
                    }
                    if (isset($this->local['list_fields'][$field]['title'])) {
                        $header[$field]['title'] = $this->local['list_fields'][$field]['title'];
                        continue;
                    }
                    $header[$field]['title'] = isset($this->mdl->form[$field]['name']) ? $this->mdl->form[$field]['name'] : $field;
                }

                $this->local['order'][$export_data['sort']] = $export_data['direction'];
                $this->local['limit'] = intval($export_data['limit'] ? $export_data['limit'] : 1000);
                if ($this->local['limit'] > 5000) {
                    $this->local['limit'] = 5000;
                }

                try {
                    $result = $this->mdl->getPageList([
                        'where' => $this->local['where'],
                        'order' => $this->local['order'],
                        'with' => $this->local['with'],
                        'field' => $export_data['field'],
                        'limit' => $this->local['limit'],
                        'paginate' => [
                            'page' => intval($export_data['page'])
                        ]
                    ]);
                } catch (\Exception $e) {
                    return $this->message('error', $e->getMessage());
                }

                if (empty($result['list'])) {
                    return $this->message('error', '当前情况没有发现数据');
                }

                $filename = $this->mdl->cname . '_' . $result['page']['current_page'] . '_' . $this->local['limit'] . '_' . rand();

                $exportType = $this->local['export_type'];
                if ($exportType == 'ajax') {
                    ini_set('memory_limit', '512M');
                    set_time_limit(0);
                    try {
                        foreach ($result['list'] as &$item) {
                            foreach ($item as $field => $value) {
                                if (isset($header[$field]['callback']) && is_callable($header[$field]['callback'])) {
                                    $item[$field] = strval(call_user_func($header[$field]['callback'], $value, $item));
                                }
                            }
                        }
                        $this->ajax('success', '', [
                            'filename' => $filename,
                            'list' => $result['list']
                        ]);
                    } catch (\Exception $e) {
                        return $this->message('error', $e->getMessage());
                    }
                } elseif ($exportType == 'xml') {
                    $export = new \woo\utility\XmlExport();
                    ini_set('memory_limit', '512M');
                    set_time_limit(0);
                    $export->sendXml($filename, $result['list'], $header);
                } elseif ($exportType == 'phpexcel') {
                    return $this->message('error', '当前导出方式未定义');
                } else {
                    return $this->message('error', '当前导出方式未定义');
                }
            }
        }

        $total = $this->mdl->where($this->local['where'])->count();
        $this->assign->total = $total;
        $this->assign->export_type = $this->local['export_type'];

        if (!isset($this->local['title'])) {
            $this->local['title'] = "导出{$this->mdl->cname}";
        }
        $this->setTitle($this->local['title'], 'operation');
        $this->addAction("返回列表", $this->returnListsUrl(), 'fa-reply', 'layui-btn-normal');
        return $this->fetch = 'export';
    }


    /**
     * 详情
     * @powerset true
     */
    public function detail()
    {
        if (isset($this->local['id'])) {
            $id = intval($this->local['id']);
        } else {
            $id = isset($this->args['id']) ? intval($this->args['id']) : 0;
        }

        $this->local['with'] = [];
        $detail_with = [];
        if (empty($this->mdl->assoc)) {
            $this->mdl->assoc = [];
        }

        // 详情关联展示模型 使用 $this->local['detail_with'] 传递
        if (!empty($this->local['detail_with'])) {
            $detail_with = Hash::normalize($this->local['detail_with']);
            foreach ($detail_with as $model => $info) {
                if (array_key_exists($model, $this->mdl->assoc)) {
                    $this->local['with'][$model] = $info;
                }
            }
        }

        foreach ($this->mdl->assoc as $model => $info) {
            if ($info['type'] == 'belongsTo' && !array_key_exists($model, $this->local['with'])) {
                $this->local['with'][$model] = [];
            }
        }

        $data = $this->mdl
            ->with($this->local['with'])
            ->where($this->mdlPk, '=', $id)
            ->where($this->local['where'])
            ->find();
        if (empty($data)) {
            return $this->message('error', "ID：{$id}数据不存在");
        }
        if ($this->isCheckMenuPower) {
            if (!empty($data['menu_id']) && !in_array($data['menu_id'], (array)$this->menuPower['action']['detail'])) {
                return $this->message('error', '对不起，您没有权限查看该栏目的数据');
            }
        }

        $data = $data->toArray();
        $this->assign->data = $data;
        $this->assign->detail_with = $detail_with;


        if (!isset($this->local['title'])) {
            $this->local['title'] = "{$this->mdl->cname}ID：" . $data[$this->mdlPk];
        }
        $this->setTitle($this->local['title'], 'operation');
        //$this->addAction("修改{$this->mdl->cname}", array($this->m . '/modify', ['id' => $data[$this->mdlPk]]), 'fa-edit', 'layui-btn');
        $this->addAction("返回列表", $this->returnListsUrl($data), 'fa-reply', 'layui-btn-normal');
        return $this->fetch = 'detail';
    }

    /**
     * 排序
     * @powerset true
     */
    public function sort()
    {
        if (!isset($this->mdl->form['list_order'])) {
            return $this->message('error', '模型【' . $this->m . '】form属性中不含有list_order字段');
        }
        if (!isset($this->local['order']['list_order'])) {
            $this->local['order']['list_order'] = 'DESC';
        }
        if (!isset($this->local['order'][$this->mdlPk])) {
            $this->local['order'][$this->mdlPk] = 'DESC';
        }

        if ($this->isCheckMenuPower) {
            if (!empty($this->args['parent_id']) && !in_array($this->args['parent_id'], (array)$this->menuPower['action']['sort'])) {
                return $this->message('error', '对不起，您没有权限排序该栏目的数据');
            }
            if (empty($this->args['parent_id'])) {
                $this->local['where'][] = ['menu_id', 'IN', $this->menuPower['action']['sort']];
            }
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            $order_list = explode(',', $data['data']['order']);
            if (strtoupper($this->local['order']['list_order']) != 'ASC') {
                $order_list = array_reverse($order_list);
            }
            foreach ($order_list as $order => $id) {
                $save = [
                    $this->mdlPk => $id,
                    'list_order' => $order
                ];
                $this->mdl->isValidate(false)->modifyData($id, $save);
            }
            if (!isset($this->local['success_redirect'])) {
                return $this->message('success', "{$this->mdl->cname}排序操作成功", ['返回列表' => $this->returnListsUrl()]);
            } else {
                return $this->message('success', "{$this->mdl->cname}排序操作成功", $this->local['success_redirect']);
            }
        } else {
            $this->local['parent_return_url'] = false;
            $this->parseListParent();

            if (!isset($this->local['with'])) {
                $this->local['with'] = [];
            }

            $list = $this->mdl
                ->with($this->mdl->parseWith($this->local['with']))
                ->where($this->local['where'])
                ->order($this->local['order'])
                ->field($this->local['field'])
                ->select()
                ->toArray();
            if (empty($list)) {
                return $this->message('error', '当前排序数据不存在');
            }

            $this->assign->addJs('/files/jquery-ui-1.12.1/jquery-ui.min.js');
            $this->assign->list = $list;
            if (!isset($this->local['title'])) {
                $this->local['title'] = "{$this->mdl->cname}排序";
            }
            $this->setTitle($this->local['title'], 'operation');
            $this->addAction("返回列表", $this->returnListsUrl(), 'fa-reply', 'layui-btn-normal');
            return $this->fetch = 'sort';
        }
    }

    /**
     * 删除
     * @powerset true
     */
    public function delete()
    {
        if (!isset($this->args['id'])) {
            return $this->message('error', "缺少【id】参数");
        }
        $id = intval($this->args['id']);
        if ($this->isCheckMenuPower) {
            $this->local['where'][] = ['menu_id', 'IN', $this->menuPower['action']['delete']];
        }
        $result = $this->mdl->deleteData($id, $this->local['where']);
        if ($result['delete_count'] > 0) {
            if (!isset($this->local['success_redirect'])) {
                return $this->message('success', "{$this->mdl->cname}[ID:{$id}]删除成功", array('返回列表' => $this->returnListsUrl($result['delete_list'][0])));
            } else {
                return $this->message('success', "{$this->mdl->cname}[ID:{$id}]删除成功", $this->local['success_redirect']);
            }
        } else {
            if (!isset($this->local['error_redirect'])) {
                return $this->message('error', "{$this->mdl->cname}[ID:{$id}]删除失败", array('返回列表' => $this->returnListsUrl()));
            } else {
                return $this->message('error', "{$this->mdl->cname}[ID:{$id}]删除失败", $this->local['error_redirect']);
            }
        }
    }

    /**
     * 批量删除
     * @powerset true
     * @poweras delete
     */
    public function batchDelete()
    {
        if (!$this->request->isPost()) {
            return $this->message('error', '不是一个正确的请求方式');
        }
        if ($this->isCheckMenuPower) {
            $this->local['where'][] = ['menu_id', 'IN', $this->menuPower['action']['delete']];
        }
        $selected_data = $this->request->post();
        $selected_ids = $selected_data['selected_id'];
        $result = $this->mdl->deleteData($selected_ids, $this->local['where']);

        if ($result['delete_count'] > 0) {
            if ($result['delete_count'] >= $result['select_count']) {
                return $this->message('success', "批量删除{$this->mdl->cname}成功，{$result['delete_count']}条删除成功");
            } else {
                return $this->message('success', "批量删除{$this->mdl->cname}成功，{$result['delete_count']}条删除成功，" . ($result['select_count'] - $result['delete_count']) . "条删除失败");
            }
        } else {
            return $this->message('error', "{$this->mdl->cname}[ID:" . implode(',', $selected_ids) . "]批量删除失败");
        }
    }

    /**
     * 批量审核
     * @powerset true
     */
    public function batchVerify()
    {
        if (!$this->request->isPost()) {
            return $this->message('error', '不是一个正确的请求方式');
        }
        $selected_data = $this->request->post();
        $selected_ids = $selected_data['selected_id'];
        if ($this->isCheckMenuPower) {
            $this->local['where'][] = ['menu_id', 'IN', $this->menuPower['action']['batchVerify']];
        }
        if (!isset($this->mdl->form['is_verify'])) {
            return $this->message('error', '当前模型不存在is_verify字段');
        }
        $result = $this->batchModifyField('is_verify', 1, $selected_ids);
        if ($result == count($selected_ids)) {
            return $this->message('success', '成功启用' . $result . '条记录');
        } else {
            return $this->message($result != 0 ? 'success' : 'error', '成功启用' . $result . '条记录，' . (count($selected_ids) - $result) . '条记录启用失败');
        }
    }

    /**
     * 批量禁用
     * @powerset true
     * @poweras batchVerify
     */
    public function batchDisabled()
    {
        if (!$this->request->isPost()) {
            return $this->message('error', '不是一个正确的请求方式');
        }
        $selected_data = $this->request->post();
        $selected_ids = $selected_data['selected_id'];
        if ($this->isCheckMenuPower) {
            $this->local['where'][] = ['menu_id', 'IN', $this->menuPower['action']['batchVerify']];
        }
        if (!isset($this->mdl->form['is_verify'])) {
            return $this->message('error', '当前模型不存在is_verify字段');
        }
        $result = $this->batchModifyField('is_verify', 0, $selected_ids);
        if ($result == count($selected_ids)) {
            return $this->message('success', '成功禁用' . $result . '条记录');
        } else {
            return $this->message($result != 0 ? 'success' : 'error', '成功禁用' . $result . '条记录，' . (count($selected_ids) - $result) . '条记录禁用失败');
        }
    }

    /**
     * 批量修改某个字段的值
     * @powerset true
     * @poweras batchDisabled
     */
    public function batchModifyField($field, $value, $idList = [])
    {
        if (empty($idList)) {
            return 0;
        }
        $success = 0;
        foreach ($idList as $id) {
            if ($this->mdl->isValidate(false)->modifyData($id, [$field => $value], ['where' => $this->local['where']])) {
                $success++;
            }
        }
        return $success;
    }

    /**
     * 列表修改
     * @powerset true
     */
    public function ajaxSetField()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式');
        }
        if ($this->isCheckMenuPower) {
            $this->local['where'][] = ['menu_id', 'IN', $this->menuPower['action']['ajaxSetField']];
        }
        $id = intval($this->args['id']);
        $old_data = $this->mdl->where($this->mdlPk, '=', $id)->where($this->local['where'])->find();
        if (empty($old_data)) {
            return $this->ajax('error', '数据【ID:' . $id . '】不存在或不允许设置');
        }
        $field = strval($this->args['field']);
        if (empty($field)) {
            return $this->ajax('error', '需要设置的字段不存在');
        }
        if (!isset($this->mdl->form[$field])) {
            return $this->ajax('error', "模型{$this->m}中不包括{$field}字段");
        }
        $value = trim(urldecode($this->args['value']));
        $data = [$field => $value];
        $result = $this->mdl->setValidateOptions([
            'only' => true
        ])->modifyData($id, $data, [
            'allowField' => [$this->mdlPk, $field]
        ]);
        if ($result) {
            return $this->ajax('success', $this->mdl->form[$field]['name'] . '设置成功');
        } else {
            return $this->ajax('error', reset($this->mdl->getError()));
        }
    }

    /**
     * 列表开关
     * @powerset true
     * @poweras ajaxSetField
     */
    public function ajaxSwitch()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式');
        }
        $id = intval($this->args['id']);
        if ($this->isCheckMenuPower) {
            $this->local['where'][] = ['menu_id', 'IN', $this->menuPower['action']['ajaxSetField']];
        }
        $old_data = $this->mdl->where($this->mdlPk, '=', $id)->where($this->local['where'])->find();
        if (empty($old_data)) {
            return $this->ajax('error', '数据【ID:' . $id . '】不存在或不允许设置');
        }
        $field = strval($this->args['field']);
        if (empty($field)) {
            return $this->ajax('error', '需要设置的字段不存在');
        }
        if (!isset($this->mdl->form[$field])) {
            return $this->ajax('error', "模型{$this->m}中不包括{$field}字段");
        }
        $value = intval($this->args['value']);
        $data = [$field => !$value];
        $result = $this->mdl->isValidate(false)->modifyData($id, $data, [
            'allowField' => [$this->mdlPk, $field]
        ]);
        if ($result) {
            return $this->ajax('success', $this->mdl->form[$field]['name'] . '设置成功', [
                'result' => !$value,
                'url' => url('ajaxSwitch', ['id' => $id, 'field' => $field, 'value' => !$value])
            ]);
        } else {
            return $this->ajax('error', $this->mdl->form[$field]['name'] . '设置失败');
        }
    }

    /**
     * 分析列表父类数据
     */
    protected function parseListParent()
    {
        $parent_info = [];
        if (isset($this->mdl->parentModel)) {
            if ($this->mdl->parentModel != 'parent') {
                $parent_mdl = $this->loadModel($this->mdl->parentModel);
            } else {
                $parent_mdl = $this->mdl;
            }
            if (isset($this->args['parent_id'])) {
                $parent_id = intval($this->args['parent_id']);
                $parent_data = $parent_mdl->where($this->mdlPk, '=', $parent_id)->find();
                if ($parent_data) {
                    $parent_info = [
                        'mdl' => $this->mdl->parentModel,
                        $this->mdlPk => $parent_id,
                        'cname' => $parent_mdl->cname,
                        'title' => $parent_data[$parent_mdl->display ? $parent_mdl->display : 'title']
                    ];
                }
                if (!where_exists($this->local['parent_conj'], $this->local['where'])) {
                    $this->local['where'][] = [$this->local['parent_conj'], '=', $parent_id];
                }

                $this->local['filter'] = Hash::normalize((array)$this->local['filter']);
                if (key_exists($this->local['parent_conj'], $this->local['filter']) && !isset($this->local['filter'][$this->local['parent_conj']]['hide'])) {
                    $this->local['filter'][$this->local['parent_conj']]['hide'] = true;
                }

                $this->local['list_fields'] = Hash::normalize((array)$this->local['list_fields']);

                if (key_exists($this->local['parent_conj'], $this->local['list_fields']) && !isset($this->local['list_fields'][$this->local['parent_conj']]['list'])) {

                    $this->local['list_fields'][$this->local['parent_conj']]['list'] = false;
                }
            }

            if (isset($this->mdl->parentModel) && $this->mdl->parentModel != 'parent' && $this->local['parent_return_url'] !== false) {
                if (!$this->local['parent_return_url']) {
                    if ($this->mdl->parentModel == 'Menu') {
                        $this->local['parent_return_url'] = array($this->mdl->parentModel . '/content');
                    } else {
                        $parent_parent_id = null;
                        if (isset($parent_mdl->parentModel)) {
                            $parent_parent_foreign_key = isset($parent_mdl->assoc[$parent_mdl->parentModel]['foreignKey']) ? $parent_mdl->assoc[$parent_mdl->parentModel]['foreignKey'] : parse_name($parent_mdl->parentModel) . '_id';
                            $parent_parent_id = $parent_data[$parent_parent_foreign_key] ? $parent_data[$parent_parent_foreign_key] : null;
                        }
                        if ($parent_parent_id) {
                            $this->local['parent_return_url'] = array($this->mdl->parentModel . '/lists', ['parent_id' => $parent_parent_id]);
                        } else {
                            $this->local['parent_return_url'] = array($this->mdl->parentModel . '/lists');
                        }
                    }
                }
                $this->addAction("父级", $this->local['parent_return_url'], $this->iconMap['parent'], 'layui-btn-normal');
            }
        }
        $this->assign->parent_info = $parent_info;
    }

    /**
     * 分析列表搜索
     */
    protected function parseListFilters()
    {
        if (!isset($this->local['filter'])) {
            $this->local['filter'] = [];
        }

        if (!is_array($this->local['filter'])) {
            $this->local['filter'] = [];
        }
        $fields = Hash::normalize($this->local['filter']);
        foreach ($fields as $field => &$info) {
            if (!isset($info['name'])) {
                if (array_key_exists($field, (array)$this->mdl->form)) {
                    $info['name'] = $this->mdl->form[$field]['name'];
                } else {
                    $info['name'] = $field;
                }
            }
            if (!isset($info['elem'])) {
                if ($this->mdl->form[$field]['elem'] === 'date') {
                    $info['elem'] = 'date';
                    if (!isset($info['options'])) {
                        $info['options'] = $this->mdl->form[$field]['options'];
                    }
                    if ($info['options'] == 'datetime' && !isset($info['type'])) {
                        $info['type'] = 'datetime';
                    }
                } elseif ($this->mdl->form[$field]['elem'] === 'checker') {
                    $info['elem'] = 'options';
                    $info['options'][0] = '未' . $this->mdl->form[$field]['name'];
                    $info['options'][1] = '已' . $this->mdl->form[$field]['name'];
                } elseif (isset($info['options']) && is_array($info['options'])) {
                    $info['elem'] = 'options';
                } elseif (isset($this->mdl->form[$field]['options'])) {
                    $info['elem'] = 'options';
                    $info['options'] = $this->mdl->form[$field]['options'];
                } elseif (in_array($this->mdl->form[$field]['type'], ['integer', 'float'])) {
                    $info['elem'] = 'number';
                } else {
                    $info['elem'] = 'text';
                }
            }
            if (!isset($info['type'])) {
                if (isset($this->mdl->form[$field]['type'])) {
                    $info['type'] = $this->mdl->form[$field]['type'];
                } else {
                    $info['type'] = 'string';
                }
                if ($info['elem'] == 'datetime' || $info['options']['type'] == 'datetime') {
                    $info['type'] = 'datetime';
                }
                if ($info['elem'] == 'date_range' || $info['elem'] == 'number_range') {
                    $info['type'] = 'range';
                }
            }

            if ($info['elem'] == 'options' && !isset($info['options'])) {
                if (isset($this->mdl->form[$field]['foreign'])) {
                    list($foreign_mdl, $foreign_field) = plugin_split($this->mdl->form[$field]['foreign']);
                    if ($this->mdl->assoc[$foreign_mdl]['type'] == 'belongsTo') {
                        $this->loadModel($foreign_mdl);
                        $pk = $this->$foreign_mdl->getPk();
                        $assoc_options = [];
                        if (isset($info['assoc_options'])) {
                            $assoc_options = $info['assoc_options'];
                        } else {
                            $assoc_options = $this->mdl->form[$field]['assoc_options'];
                        }

                        $mdl_list = $this->$foreign_mdl
                            ->field([$pk, $foreign_field])
                            ->where(isset($assoc_options['where']) ? $assoc_options['where'] : [])
                            ->limit(isset($assoc_options['max']) ? $assoc_options['max'] : 50)
                            ->order(isset($assoc_options['order']) ? $assoc_options['order'] : [$pk => 'DESC'])
                            ->select()
                            ->toArray();
                        $info['options'] = Hash::combine($mdl_list, '{n}.' . $pk, '{n}.' . $foreign_field);
                    }
                }
            }

            $info['value'] = Hash::get((array)$this->args, $field);
            if (!isset($info['hide'])) {
                $info['hide'] = false;
            }

            if (isset($this->mdl->form[$field]['foreign'])) {
                $info['foreign'] = $this->mdl->form[$field]['foreign'];
            }

            if (!isset($this->args[$field])) {
                continue;
            }
            $val = trim($this->args[$field]);

            if (where_exists($field, $this->local['where'])) {
                $where_result = true;
            }

            if (isset($info['where']) && is_callable($info['where']) && !isset($where_result)) {
                $where_result = call_user_func_array($info['where'], [$val, $field, &$info]);
                if (!empty($where_result)) {
                    $this->local['where'][] = (array)$where_result;
                }
                $where_result = true;
            }

            if ($info['elem'] == 'number') {
                $sign = trim($this->args[$field . '_sign']);
                $sign_map = ['eq' => '=', 'gt' => '>', 'egt' => '>=', 'lt' => '<', 'elt' => '<=', 'neq' => '<>'];
                if (array_key_exists($sign, $sign_map)) {
                    $this->addFilter($field . '_sign', $sign);
                    $this->local['where'][] = [$field, $sign_map[$sign], floatval($val)];
                    $where_result = true;
                }
            }

            if (isset($info['foreign']) && !$where_result) {
                if (!empty($info['options'])) {
                    if (in_array($val, array_keys($info['options']))) {
                        $this->local['where'][] = [$field, '=', $val];
                        $where_result = true;
                    }

                }
                if (!$where_result) {
                    list($foreign_mdl, $foreign_field) = plugin_split($info['foreign']);
                    if ($this->mdl->assoc[$foreign_mdl]['type'] == 'belongsTo') {
                        if (isset($this->mdl->assoc[$foreign_mdl]['foreign'])) {
                            $foreign_mdl = $this->mdl->assoc[$foreign_mdl]['foreign'];
                        }
                        $this->loadModel($foreign_mdl);
                        $pk = $this->$foreign_mdl->getPk();
                        $foreign_ids = $this->$foreign_mdl->where($foreign_field, 'LIKE', '%' . $val . '%')->column($pk);
                        if (empty($foreign_ids)) {
                            $foreign_ids = [0];
                        }
                        if (count($foreign_ids) == 1) {
                            $this->local['where'][] = [$field, '=', $foreign_ids[0]];
                        } else {
                            $this->local['where'][] = [$field, 'IN', $foreign_ids];
                        }
                        $where_result = true;
                    }
                }
            }

            ##只有是当前模型字段，或者自定义了where回调才处理条件，其他自定义字段需要自己处理条件
            if (array_key_exists($field, (array)$this->mdl->form) && !isset($where_result)) {
                if (in_array($info['type'], ['text', 'string'])) {
                    $this->local['where'][] = [$field, 'LIKE', '%' . urldecode($val) . '%'];
                } elseif ($info['type'] == 'datetime') {
                    $this->local['where'][] = [$field, 'LIKE', urldecode($val) . '%'];
                } elseif ($info['type'] == 'range') {
                    $rangeVal = explode('~', urldecode($val));
                    if (trim($rangeVal[0]) !== '') {
                        $this->local['where'][] = [$field, '>=', trim($rangeVal[0])];
                    }
                    if (trim($rangeVal[1]) !== '') {
                        $this->local['where'][] = [$field, '<=', trim($rangeVal[1])];
                    }
                } else {
                    $this->local['where'][] = [$field, '=', urldecode($val)];
                }
            }
            /*
            if($info['options']){
				if (array_key_exists($val, $info['options'])) {
					$local_val=$info['options'][$val];
				} else {
					continue;
				}
			}*/
            if (!isset($local_val)) $local_val = $val;
            $this->addFilter($field, $local_val);
            unset($local_val);
            unset($where_result);
        }
        $this->assign->list_search_fields = $fields;
        return true;
    }

    /**
     * 分析列表字段
     */
    protected function parseListFields()
    {
        if (!isset($this->local['list_fields'])) {
            return $this->message('error', '该模块未设置列表字段');
        }

        if ($this->mdl && isset($this->mdl->form[$this->mdlPk])) {
            array_unshift($this->local['list_fields'], $this->mdlPk);
        }

        $this->local['list_fields'] = Hash::normalize($this->local['list_fields']);
        $this->local['field'] = [];


        $tableFields = $this->mdl->getTableFields();
        foreach ($this->local['list_fields'] as $col => &$info) {

            list($local_mdl, $field) = plugin_split($col);
            if (isset($info) && !$info) {
                $this->local['field'][] = "$field";
                continue;
            }

            if (isset($this->mdl->form[$col]['list']) && !isset($info['list'])) {
                $info['list'] = $this->mdl->form[$col]['list'];
            }

            $is_foreign_filed = false;
            if (!$local_mdl || $local_mdl == $this->m) {
                if (isset($this->mdl->form[$field]['foreign'])) {
                    list($foreign_mdl, $foreign_field) = plugin_split($this->mdl->form[$field]['foreign']);
                    if (!isset($info['list'])) {
                        $info['list'] = 'assoc';
                    }
                    $info['foreign']['model'] = $foreign_mdl;
                    $info['foreign']['field'] = $foreign_field;
                }
            } else {
                list($foreign_mdl, $foreign_field) = plugin_split($col);
                if ($foreign_mdl && $foreign_field) {
                    if (isset($this->mdl->assoc[$foreign_mdl])) {
                        $assocInfo = $this->mdl->assoc[$foreign_mdl];
                        if (in_array($assocInfo['type'], ['belongsTo', 'hasOne'])) {
                            if (!isset($info['list'])) {
                                $info['list'] = 'assoc';
                            }
                            $info['foreign']['model'] = trim($foreign_mdl);
                            $info['foreign']['field'] = trim($foreign_field);
                            if ($assocInfo['type'] == 'belongsTo') {
                                $field = isset($assocInfo['foreignKey']) ? $assocInfo['foreignKey'] : parse_name($info['foreign']['model']) . '_id';
                            }
                            $is_foreign_filed = true;
                        }
                    }
                }
            }

            if (is_string($info)) {
                $info = ['list' => $info];
            }

            if ($info['list'] != 'assoc') {
                if (in_array($field, $tableFields)) {
                    $this->local['field'][] = "$field";
                }
                settype($info, 'array');
                if (isset($this->mdl->form[$field]) && !is_array($this->mdl->form[$field]) && $info['type'] != 'none') {
                    return $this->message('error', "未能在{$this->m}的{$field}字段上得到array");
                }
                $info += (array)$this->mdl->form[$field];
                if (isset($this->mdl->form[$field]['image']['thumb'])) {
                    $thumb_field = $this->mdl->form[$field]['image']['thumb']['field'] ? $this->mdl->form[$field]['image']['thumb']['field'] : 'thumb';
                    $this->local['field'][] = "$thumb_field";
                }
            } else {
                if (isset($info['foreign']['model']) && isset($info['foreign']['field'])) {

                    $this->local['with'][$foreign_mdl]['field'][] = $foreign_field;
                    if (!$is_foreign_filed && isset($this->mdl->form[$field])) {
                        $info += $this->mdl->form[$field];
                    }
                    settype($info['foreign'], 'array');
                    $this->loadModel($foreign_mdl);
                    $info['foreign'] += (array)$this->$foreign_mdl->form[$foreign_field];
                }
                if ($field && in_array($field, $tableFields)) {
                    $this->local['field'][] = "$field";
                }
            }

            if (!isset($info['list'])) {
                if (isset($info['options'])) {
                    $info['list'] = 'options';
                } else {
                    $info['list'] = 'show';
                }
            }
            $info['field'] = $field;

            $sortable_types = array('integer', 'float', 'date', 'datetime');
            if (!isset($info['sortable']) && (/*in_array($info['foreign']['type'], $sortable_types, true) ||*/
                in_array($info['type'], $sortable_types, true))
            ) {
                $info['sortable'] = true;
            }
        }
        $this->assign->list_fields = array_diff_key((array)$this->local['list_fields'], array_flip((array)$this->local['list_ignore_fields']));

        return true;
    }

    /**
     * 关联表单值获取
     */
    protected function assignAssocValue()
    {
        if (empty(helper('Form')->data[$this->m])) {
            return false;
        }
        foreach (helper('Form')->data[$this->m] as $field => $value) {
            if (!in_array($this->mdl->form[$field]['elem'], ['assoc_select', 'format']) || empty($this->mdl->form[$field]['elem']) || empty($value)) {
                continue;
            }
            if (!isset($this->mdl->form[$field]['foreign'])) {
                continue;
            }

            list($assoc_model, $assoc_field) = plugin_split($this->mdl->form[$field]['foreign']);
            if (!empty($assoc_model)) {
                $assoc_model = isset($this->mdl->assoc[$assoc_model]['foreign']) ? $this->mdl->assoc[$assoc_model]['foreign'] : $assoc_model;
                $this->loadModel($assoc_model);
                $pk = $this->$assoc_model->getPk();
                $this->assign->assoc_value[$field] = $this->$assoc_model->where($pk, '=', $value)->value($assoc_field);
            }
        }
    }

    /**
     * 表单设置默认值
     */
    protected function assignDefault($col, $val, $mdl = null, $overWriteEmpty = false, $index = null)
    {
        if (empty($mdl)) $mdl = $this->m;

        if ($index !== null) {
            if (!isset(helper('Form')->data[$mdl][$index][$col]) || ($overWriteEmpty && empty(helper('Form')->data[$mdl][$index][$col]))) {
                $this->assign->default_data[$mdl][$index][$col] = helper('Form')->data[$mdl][$index][$col] = $val;
            }
        } else {
            if (!isset(helper('Form')->data[$mdl][$col]) || ($overWriteEmpty && empty(helper('Form')->data[$mdl][$col]))) {
                $this->assign->default_data[$mdl][$col] = helper('Form')->data[$mdl][$col] = $val;
            }
        }
    }

    /**
     * 表单设置默认值
     */
    protected function assignValue($col, $val, $mdl = null, $index = null)
    {
        if (empty($mdl)) {
            $mdl = $this->m;
        }

        if ($index !== null) {
            $this->assign->default_data[$mdl][$index][$col] = helper('Form')->data[$mdl][$index][$col] = $val;
        } else {
            $this->assign->default_data[$mdl][$col] = helper('Form')->data[$mdl][$col] = $val;
        }
    }

    /**
     * 搜索
     */
    protected function addFilter($col, $value)
    {
        if (is_array($value)) {
            $value = implode(' - ', Hash::flatten($value));
        }
        $this->assign->filter[$col] = $value;
    }

    /**
     * 顶部操作
     */
    protected function addAction($title, $url = null, $icon = null, $class = null)
    {
        $this->assign->actions = isset($this->assign->actions) ? $this->assign->actions : [];
        if (!isset($url)) {
            array_push($this->assign->actions, $title);
        } else {
            array_push($this->assign->actions, compact('title', 'url', 'icon', 'class'));
        }
    }

    /**
     * 列表项目操作
     */
    protected function addItemAction($title, $url = null, $icon = null, $class = null)
    {
        $this->assign->item_actions = isset($this->assign->item_actions) ? $this->assign->item_actions : [];
        if (!isset($url)) {
            $this->assign->item_actions[] = $title;
        } else {
            $this->assign->item_actions[] = compact('title', 'url', 'icon', 'class');
        }
    }

    /**
     * 关联选择查询数据
     * @powerset false
     */
    public function assocSelect()
    {
        return parent::assocSelect();
    }

     /**
     * AJAX关联联动查询
     * @powerset false
     */
    public function ajaxMultiSelect()
    {
        return parent::ajaxMultiSelect();
    }

    /**
     * 没有权限以后 跳转方法
     * @powerset false
     */
    public function nopower($msg = '没有权限的操作')
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', $msg, ['close' => true, 'back' => false]);
        } else {
            return $this->ajax('nopower', $msg);
        }
        exit;
    }

    /**
     * 每次请求最后
     */
    protected function beforeFinish()
    {
        if (setting('is_run_log')) {
            $data['user_id'] = isset($this->login['id']) ? $this->login['id'] : 0;
            $data['url'] = $this->params['module'] . '/' . $this->params['controller'] . '/' . $this->params['action'];
            $data['full_url'] = $this->request->url();
            $data['ip'] = $this->request->ip();
            $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            $data['method'] = $this->request->method();
            $lastSql = $this->mdl->getLastSql();
            $data['last_sql'] = !preg_match('/SELECT/i', $lastSql) ? $lastSql : '';
            $this->loadModel('Log')->createData($data);
        }
    }

}
