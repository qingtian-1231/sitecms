<?php
namespace woo\controller;

class WooManage extends WooApp
{
    protected $currentMenuId = 0;
    
    public function __construct()
    { 
        call_user_func(array('parent', __FUNCTION__));
        if ($this->request->isMobile() && setting('is_use_wap')) {
            $this->view->config('view_path', \Env::get('module_path') . 'wap' . DS);
        }
    }
    
    protected function initialize()
    {
        call_user_func(array('parent', __FUNCTION__));
    }
    
    protected function processMenu($menu_id, $options = [])
    {        
        $menu_data = managemenu($menu_id);
        if (empty($menu_data)) {
            $this->assign->manage_menu_data = [];
            $this->assign->manage_top_id = 0;
            $this->assign->manage_path = [];
            $this->assign->manage_side_menu = ['top_menu' => 0, 'menus' => []];
            return true;
        }
        $this->assign->manage_menu_data = $menu_data;
        $this->addTitle($menu_data['title']);
        $top_id = $menu_id;
        $path = array($menu_id);

        while (true) {
            $parent_id = managemenu($top_id, 'parent_id');
            if (!$limit) {
                $limit = managemenu($top_id, 'list_count');
            }
            if ($parent_id == 1 || !$parent_id) {
                break;
            }
            if (managemenu($parent_id, 'is_nav')) {
                array_unshift($path, $parent_id);
            }
            $top_id = $parent_id;
        }
        
        $this->assign->manage_top_id = $top_id;
        $this->assign->manage_path = $path;
        $local_menu_children = managemenu('nav_children');
        
        if (count(managemenu('children', $menu_data['id']))) {
            $this->assign->manage_side_menu['top_menu'] = $menu_data['id'];
            $this->assign->manage_side_menu['menus'] = (array)$local_menu_children[$menu_data['id']];
        } else {
            $this->assign->manage_side_menu['top_menu'] = $menu_data['parent_id'];
            $this->assign->manage_side_menu['menus'] = [];
            if ($menu_data['parent_id'] == 1) {
                $source_ids = array_keys(managemenu('nav'));
            } else {
                $source_ids = $local_menu_children[$menu_data['parent_id']];
            }
            $this->assign->manage_side_menu['menus'] = (array)$source_ids;
        }
        
        if ($menu_data['keywords']) {
            $this->assign->meta['keywords'] = array($menu_data['keywords']);
        }
        if ($menu_data['description']) {
            $this->assign->meta['description'] = array($menu_data['description']);
        }
        if (isset($options['limit']) && !$this->local['limit']) {
            $this->local['limit'] = $limit;
        }
        if (!$this->local['limit']) {
            $this->local['limit'] = intval(setting('list_count'));
        }        
        if ($menu_data['template']) {
            $tpls = json_decode(str_replace("'", '"', $menu_data['template']), true);
            if ($tpls[strtolower($this->params['action'])]) {
                $this->fetch = (strpos($tpls[strtolower($this->params['action'])], '/') !== false) ? $tpls[strtolower($this->params['action'])] : parse_name($this->params['controller']) . '/' . $tpls[strtolower($this->params['action'])];
            }
        }
    }
    
    protected function beforeFinish()
    {
        call_user_func(['parent', __FUNCTION__]);
        if ($this->currentMenuId === 0) {
            $together = parse_name(trim($this->params['module'])) . '/' . parse_name(trim($this->params['controller'])) . '/' .parse_name(trim($this->params['action']));
            $query = managemenu('query');
            $query = $query[$together];
            $currentMenuId = 0;                       
            if ($query) {
                $counter = count($query);
                if ($counter === 1) {
                    $currentMenuId = reset(array_keys($query));
                } elseif ($counter > 1) {
                    if ($this->args) {
                        $match_count = [];
						$match_count_max = [];
						$match_count_max_number = 0;
                        $match_length = [];
                        foreach ($query as $k => $v) {
                            $match_count[$k] = array_intersect_assoc($v, $this->args);
                            if (count($match_count[$k]) == $match_count_max_number) {
                            	$match_count_max = array_merge($match_count_max, array($k));
                            } elseif (count($match_count[$k]) > $match_count_max_number) {
                            	$match_count_max = array($k);
                            	$match_count_max_number = count($match_count[$k]) ;
                            }                                
                            $match_length[$k] = count($v);
                        }                        
                        if (count($match_count_max) === 1) {
                            $currentMenuId = reset($match_count_max);
                        } elseif (count($match_count_max) > 1) {
                            $match_length_min = 1000;
							$match_length_min_arr = [];
							foreach($match_count_max as $k=>$v){
								if ($match_length[$v] < $match_length_min) {
									$match_length_min_arr = [$v];
									$match_length_min = $match_length[$v];
								} elseif ($match_length[$v] == $match_length_min) {
									$match_length_min_arr = array_merge($match_length_min_arr, [$v]);
								}
							}
                            if (count($match_length_min_arr) === 1) {
                                $currentMenuId = reset($match_length_min_arr);
                            } else {
                                $currentMenuId = reset($match_length_min_arr);
                            }
                        }
                    } else {
                        $currentMenuId = reset(array_keys($query));
                    }
                }
            }
            $this->currentMenuId = $currentMenuId;
        }
        $this->processMenu($this->currentMenuId, (array)$this->local['menu_options'] + array('limit' => true));
    }
    
    /**
    * 关联选择查询数据
    */
    public function assocSelect()
    {
        return parent::assocSelect();
    }
    
    /**
    * AJAX关联联动查询
    */
    public function ajaxMultiSelect()
    {
        return parent::ajaxMultiSelect();
    }
    
}
