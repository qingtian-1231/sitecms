<?php

namespace woo\controller;

use woo\utility\Hash;
use woo\utility\TpText;

class WooHome extends WooApp
{
    /**
    * 栏目数据
    */
    protected $menuData = [];


    /**
    * 构造
    */
    public function __construct()
    {

        call_user_func(array('parent', __FUNCTION__));
        if ($this->request->isMobile() && setting('is_use_wap')) {
            $this->view->config('view_path', \Env::get('module_path') . 'wap' . DS);
        }
    }

    /**
    * 初始化
    */
    protected function initialize()
    {
        if (setting('is_force_domain') && setting('pc_domain') && setting('mobile_domain')) {
            $domain = str_ireplace('https://', '', str_ireplace('http://', '', $this->request->domain()));

            if ($this->request->isMobile()) {
                if (stripos(setting('mobile_domain'), $domain) === false) {
                    $this->redirect(setting('mobile_domain'));
                }

            } else {
                if (stripos(setting('pc_domain'), $domain) === false) {
                    $this->redirect(setting('pc_domain'));
                }
            }
        }
        call_user_func(array('parent', __FUNCTION__));
    }

    /**
    * 搜索
    */
    public function search()
    {
        if (empty($this->local['kw_var'])) {
            $this->local['kw_var'] = 'kw';
        }
        if ($this->request->isPost()) {
            $kw = trim(htmlspecialchars($this->request->post($this->local['kw_var'])));
            if (!empty($kw)) {
                $this->redirect(search_link(0, $this->m , $kw, $this->local['kw_var']));
            } else {
                $this->redirect('Index/index');
            }
        }
        $this->local['not_auto_menu_id'] = true;
        $this->show();
        $this->processMenu(reset(array_keys(menu('threaded'))));
    }

    /**
    * 列表
    */
    public function show()
    {
        if (empty($this->local['kw_var'])) {
            $this->local['kw_var'] = 'kw';
        }
        if ($this->request->isPost() && !empty($this->request->post($this->local['kw_var']))) {
            $kw = trim(htmlspecialchars($this->request->post($this->local['kw_var'])));
            if (!empty($kw)) {
                $this->redirect(menu_link($this->args['menu_id'], $kw, $this->local['kw_var']));
            } else {
                $this->redirect(menu_link($this->args['menu_id']));
            }
        }

        // 是否审核
        if (isset($this->mdl->form['is_verify']) && setting('is_verify')) {
            $this->local['where'][] = ['is_verify', '=', 1];
        }

        if (isset($this->mdl->form['menu_id']) && empty($this->local['not_auto_menu_id'])) {
            $this->getShowMenu();
        }
        // 搜索
        if (!empty($this->args[$this->local['kw_var']])) {
            $this->args[$this->local['kw_var']] = trim(htmlspecialchars($this->args[$this->local['kw_var']]));
            $search_field = empty($this->local['search_field']) ?  $this->mdl->display : $this->local['search_field'];
            if (is_array($search_field)) {
                $search_field = implode($search_field, '|');
            }
            $this->local['where'][] = [$search_field, 'LIKE', '%' . $this->args[$this->local['kw_var']] . '%'];
        }

        // 排序
        if (isset($this->mdl->form['list_order']) && empty($this->local['order']['list_order'])) {
            $this->local['order']['list_order'] = 'DESC';
        }
        if (empty($this->local['order'][$this->mdlPk])) {
            $this->local['order'][$this->mdlPk] = 'DESC';
        }

        // 列表数据
        $requestUrl = $this->request->url();
        $isQuery = true;
        if (setting('is_show_cache')) {
            $listCache = \Cache::get($requestUrl);
            if ($listCache) {
                $isQuery = false;
                $this->assign->list = $listCache['list'];
                $this->assign->page = $listCache['page'];
                $this->assign->render = $listCache['render'];
            }
        }
        if ($isQuery) {
            /*
            $result = $this->mdl->getPageList([
                'where' => $this->local['where'],
                'order' => $this->local['order'],
                'with' => isset($this->local['with']) ? $this->local['with'] :[],
                'field' => isset($this->local['field'][$this->m]) ? $this->local['field'][$this->m] : $this->local['field'],
                'limit' => $this->local['limit'],
                'paginate' => []
            ]);
            $this->assign->page = $result['page'];
            $this->assign->render = $result['render'];
            $this->assign->list = $result['list'];
            */

            // 20190111重写分页查询

            $result = $this->mdl->getPageList([
                'where' => $this->local['where'],
                'order' => $this->local['order'],
                'field' => [$this->mdlPk],
                'limit' => $this->local['limit'],
                'paginate' => []
            ]);
            $this->assign->page = $result['page'];
            $this->assign->render = $result['render'];

            $idList = Hash::combine($result['list'], '{n}.' . $this->mdlPk, '{n}.' . $this->mdlPk);

            if (!empty($idList)) {
                $this->assign->list = $this->mdl
                        ->with($this->local['with'])
                        ->where($this->mdlPk, 'IN', $idList)
                        ->order($this->local['order'])
                        ->field(isset($this->local['field'][$this->m]) ? $this->local['field'][$this->m] : $this->local['field'])
                        ->select()
                        ->toArray();
            } else {
                $this->assign->list = [];
            }

            if (setting('is_show_cache')) {
                \Cache::tag($this->m)->set($requestUrl, ['list' => $this->assign->list, 'page' => $this->assign->page, 'render' => $this->assign->render], 3600);
            }
        }

        if (empty($this->fetch)) {
            $this->fetch = 'show';
        }
        app('think\\Route')->setConfig(['default_ajax_return' => 'html']);
        return true;
    }

    /**
    * 详情
    */
    public function view()
    {
        if (empty($this->local['id'])) {
            $this->local['id'] = $this->args['id'];
        }

        $this->local['id'] = intval($this->args['id']);
        if ($this->local['id'] <= 0) {
            return $this->notFound();
        }

        // 是否审核
        if (isset($this->mdl->form['is_verify']) && setting('is_verify')) {
            $this->local['where'][] = ['is_verify', '=', 1];
        }

        // 如果自定义了查询字段 必须有menu_id字段
        if (!empty($this->local['field']) && isset($this->mdl->form['menu_id'])) {
            if (in_array('menu_id', $this->local['field'])) {
                $this->local['field'][] = 'menu_id';
            }
        }

        // 详情数据查询
        $data = $this->mdl
            ->with(isset($this->local['with']) ? $this->local['with'] : [])
            ->field($this->local['field'])
            ->where($this->local['where'])
            ->where($this->mdlPk, '=', $this->local['id'])
            ->find();

        if (empty($data)) {
            return $this->notFound();
        }

        $data = $data->toArray();

        if (isset($data['menu_id'])) {
            $this->local['where'][] = ['menu_id', '=', $data['menu_id']];
        }

        $list_order = 0;
        if (isset($data['list_order'])) {
            $list_order = $data['list_order'];
        }
        // 下一篇
        $next = $this->mdl
            //->with(isset($this->local['with']) ? $this->local['with'] : [])
            //->field($this->local['field'])
            ->where($this->local['where'])
            ->where($this->mdlPk, '<>', $this->local['id'])
            ->where('list_order', '<=', $list_order)
            ->order(['list_order' => 'DESC', $this->mdlPk => 'DESC'])
            ->find();
        // 上一篇
        $prev = $this->mdl
            //->with(isset($this->local['with']) ? $this->local['with'] : [])
            //->field($this->local['field'])
            ->where($this->local['where'])
            ->where($this->mdlPk, '<>', $this->local['id'])
            ->where('list_order', '>=', $list_order)
            ->order(['list_order' => 'ASC', $this->mdlPk => 'ASC'])
            ->find();

        // 编辑器内容分页
        if (!empty($this->local['page_field'])) {
            $page_field = trim($this->local['page_field']);
            if (isset($data[$page_field])) {
                $page_count = get_page_count($data[$page_field]);
                if ($page_count > 1) {
                    $current_page = min(max(1, intval(!empty($this->args['page']) ? $this->args['page'] : 1)), $page_count);
                    $paginator = new \think\paginator\driver\Bootstrap(null, 1, $current_page, $page_count, false, ['path' => $this->request->baseUrl()]);
                    $this->assign->render = $paginator->render();
                    $this->assign->page = $paginator->toArray();
                    $data['content'] = extract_page($data['content'], $current_page - 1);
                }
            }
        }

        $this->assign->data = $data;
        $this->assign->prev = $prev ? $prev->toArray() : [];
        $this->assign->next = $next ? $next->toArray() : [];

        if (isset($this->mdl->form['visit_count'])) {
            $this->mdl->where($this->mdlPk, '=', $this->local['id'])->setInc('visit_count');
        }

        if ($data['menu_id']) {
            $this->processMenu($data['menu_id']);
        }

        if ($data['title']) {
            $this->addTitle($data['title']);
        }
        if ($data['keywords']) {
            $this->setKeywords($data['keywords']);
        }
        if ($data['description']) {
            $this->setDescription($data['description']);
        }

        if (empty($this->fetch)) {
            $this->fetch = 'view';
        }

        return true;
    }

    /**
    * 获取当前栏目
    */
    protected function getShowMenu()
    {
        if (empty($this->local['menu_id'])) {
            $this->local['menu_id'] = intval($this->args['menu_id']);
        }

        if ($this->local['menu_id'] <= 0) {
            return $this->notFound();
        }

        $menu_data = menu($this->local['menu_id']);
        if (empty($menu_data)) {
            return $this->notFound();
        }

        if ($this->m != $menu_data['type']) {
            return $this->message('error', '当前访问栏目类型不匹配', ['返回首页' => url('Index/index')]);
        }

        if ($menu_data['child_count'] > 0 && !where_exists('menu_id', $this->local['where'])) {
            $this->local['where'][] = ['menu_id', 'IN', get_closest_menus($this->local['menu_id'])];
        } else {
            if (!where_exists('menu_id', $this->local['where'])) {
                $this->local['where'][] = ['menu_id', '=', $this->local['menu_id']];
            }
        }

        if (!isset($this->local['menu_options'])) {
            $this->local['menu_options'] = [];
        }
        $this->processMenu($this->local['menu_id'], (array)$this->local['menu_options'] + ['limit' => true]);
        return true;
    }

    /**
    * 栏目处理
    */
    protected function processMenu($menu_id, $options = [])
    {
        $menu_data = $this->menuData = menu($menu_id);
        if (empty($menu_data)) {
            return $this->notFound();
        }

        $this->local['options'] = $options;
        $this->local['options']['no_redirect'] = isset($this->local['options']['no_redirect']) ? $this->local['options']['no_redirect'] : false;

        if (!$this->local['options']['no_redirect'] && $this->menuData['child_count'] && $this->menuData['is_redirect']) {
            $children_menus = menu('children', $this->menuData['id']);
            $first_child_id = reset($children_menus);
            if ($first_child_id) {
                /*
                if (menu($first_child_id, 'type') != 'Exlink') {
                    $alias = trim(menu($first_child_id, 'alias'));
                    if (!$alias) {
                        $this->redirect(menu($first_child_id, 'type') . '/show', ['menu_id' => $first_child_id]);
                    } else {
                        $this->redirect($this->absroot . $alias . '.' . config('url_html_suffix'));
                    }
                } else {
                    $this->redirect(furl(menu($first_child_id, 'ex_link')));
                }*/
                $this->redirect(menu_link($first_child_id));
            }
        }

        $this->assign->menu_data = $menu_data;
        $this->addTitle($menu_data['title']);

        $top_id = $menu_id;
        $path = [$menu_id];

        $limit = 0;
        while (true) {
            $parent_id = menu($top_id, 'parent_id');
            if (!$limit) {
                $limit = menu($top_id, 'list_count');
            }
            if ($parent_id == 1 || !$parent_id) {
                break;
            }
            if (menu($parent_id, 'is_nav')) {
                array_unshift($path, $parent_id);
            }
            $top_id = $parent_id;
        }

        $this->assign->top_id = $top_id;
        $this->assign->path = $path;
        $local_menu_children = menu('nav_children');

        // side_menu
        if ($menu_data['child_count'] > 0) {
            $this->assign->side_menu['top_menu'] = $menu_data['id'];
            $this->assign->side_menu['menus'] = (array)$local_menu_children[$menu_data['id']];
            if (empty($this->assign->side_menu['menus']) && $menu_data['id'] == reset(array_keys(menu('threaded')))) {
                $this->assign->side_menu['menus'] = array_keys(menu('nav'));
            }
        } else {
            $this->assign->side_menu['top_menu'] = $menu_data['parent_id'];
            $this->assign->side_menu['menus'] = [];

            if ($menu_data['parent_id'] == 1) {
                $source_ids = array_keys(menu('nav'));
            } else {
                $source_ids = $local_menu_children[$menu_data['parent_id']];
            }
            $this->assign->side_menu['menus'] = (array)$source_ids;
        }

        if ($menu_data['template']) {
            $tpls = json_decode(str_replace("'", '"', $menu_data['template']), true);

            if (!empty($tpls[strtolower($this->params['action'])])) {
                $this->fetch = (strpos($tpls[strtolower($this->params['action'])], '/') !== false) ? $tpls[strtolower($this->params['action'])] : parse_name($this->params['controller']) . '/' . $tpls[strtolower($this->params['action'])];
            }
        }
        if ($menu_data['keywords']) {
            $this->assign->meta['keywords'] = array($menu_data['keywords']);
        }
        if ($menu_data['description']) {
            $this->assign->meta['description'] = array($menu_data['description']);
        }
        if (isset($options['limit']) && !isset($this->local['limit'])) {
            $this->local['limit'] = $limit;
        }
        if (empty($this->local['limit'])) {
            $this->local['limit'] = intval(setting('list_count'));
        }
    }

    /**
    * 控制器中获取栏目数据
    */
    public function getMenuData($menu_id, $limit = 4, $options = [])
    {
        $this->assign->query_data[$menu_id] = get_menu_data($menu_id, $limit, $options);
        return true;
    }

    /**
    * 控制器中获取广告数据
    */
    public function getAdData($vari, $limit = 0, $options = [])
    {
        if (empty($this->assign->ad)) {
            $this->assign->ad = get_ad_data($vari, $limit, $options);
        } else {
            $this->assign->ad = get_ad_data($vari, $limit, $options) + (array)$this->assign->ad;
        }
        return true;
    }
}
