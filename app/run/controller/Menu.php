<?php

namespace app\run\controller;

use app\common\controller\Run;

class Menu extends Run
{
    protected function initialize()
    {
        call_user_func(['parent', __FUNCTION__]);
    }

    public function create()
    {
        $this->assignDefault('is_nav', 1);
        $this->assignDefault('is_map', 1);
        $this->assignDefault('list_order', 0);
        $this->assignDefault('list_count', 0);
        $this->assignDefault('thumb_width', 0);
        $this->assignDefault('thumb_height', 0);
        if (isset($this->args['parent_id']) && $this->args['parent_id'] > 1) {
            $this->assignDefault('type', menu(intval($this->args['parent_id']), 'type'));
        }
        call_user_func(['parent', __FUNCTION__]);
    }

    public function lists()
    {
        if ($this->args['list'] == 'list') {
            $this->local['list_fields'] = [
                'title',
                'ex_title',
                'parent_id',
                'level',
                'child_count',
                'list_order',
            ];
            $this->local['where'][] = ['id', 'gt', 1];
            $this->local['order'] = ['level' => 'ASC', 'list_order' => 'ASC', 'id' => 'ASC'];
            call_user_func(['parent', __FUNCTION__]);
            $this->addAction("树形列表", ['Menu/lists', ['parent_id' => 1]], 'fa-eye', 'layui-btn-normal');
        } else {
            $this->addAction("新增一级{$this->mdl->cname}", ['Menu/create', ['parent_id' => 1]], 'fa-plus-circle', 'layer-ajax-form layui-btn');
            $this->addAction("一级{$this->mdl->cname}排序", ['Menu/sort', ['parent_id' => 1]], 'fa-sort', 'layer-ajax-form layui-btn-warm');
            $this->addAction("线型列表", ['Menu/lists', ['list' => 'list']], 'fa-eye', 'xxxx');
            $this->addAction("进入内容管理", ['Menu/content'], 'fa-reply', 'layui-btn-normal');
            $this->setTitle("{$this->mdl->cname}结构", 'operation');
            $this->fetch = 'tree';
        }
    }

    public function sort()
    {
        $this->local['order'] = ['list_order' => 'ASC', 'id' => 'ASC'];
        if (empty($this->args)) {
            $this->args['parent_id'] = 1;
        }
        call_user_func(['parent', __FUNCTION__]);
    }

    public function content()
    {
        $this->setTitle("前台内容", 'operation');
        //$this->addAction("进入栏目结构", array('Menu/lists'), 'fa-reply');
        $this->fetch = true;
    }

    /**
     * 广告位生成
     */
    public function createPosition()
    {
        if ($this->request->isAjax()) {
            $menu_id = intval($this->args['id']);
            if ($menu_id < 1) {
                return $this->ajax('error', '缺少参数ID');
            }
            $ad_var = 'menu_' . $menu_id;
            $this->loadModel('AdPosition');

            $position_exists = $this->AdPosition->where('vari', '=', $ad_var)->count();
            if ($position_exists >= 1) {
                return $this->ajax('error', "失败，广告位[变量：{$ad_var}]已经存在");
            }

            $position = $this->AdPosition->where('vari', '=', 'insider_banner')->find();
            if (empty($position)) {
                return $this->ajax('error', "失败，默认广告位[变量：insider_banner]不存在");
            }

            $data['title'] = menu($menu_id, 'title') . '[栏目ID:' . $menu_id . ']Banner广告位';
            $data['ex_title'] = $position['ex_title'];
            $data['vari'] = $ad_var;
            $data['width'] = $position['width'];
            $data['height'] = $position['height'];
            $data['mobile_width'] = $position['mobile_width'];
            $data['mobile_height'] = $position['mobile_height'];
            $data['limit'] = $position['limit'];
            $data['is_thumb'] = $position['is_thumb'];
            $data['is_text'] = $position['is_text'];

            $this->AdPosition->createData($data);
            return $this->ajax('success', "成功，广告位[变量：{$ad_var}]创建成功");
        }
    }
}
