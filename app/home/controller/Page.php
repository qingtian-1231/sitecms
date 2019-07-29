<?php
namespace app\home\controller;

use app\common\controller\Home;

class Page extends Home
{
    public function initialize()
    {
        $this->fetch = true;
        call_user_func(array('parent', __FUNCTION__));
    }

    public function show()
    {
        $menu_id = intval($this->args['menu_id']);
        if (empty($menu_id)) {
            return $this->notFound();
        }
        $this->local['where'][] =   ['menu_id', '=', $menu_id];
        $this->local['order'] = ['id' => 'ASC'];
        $this->local['limit'] =  1;

        call_user_func(array('parent', __FUNCTION__));
        if (empty($this->assign->list)) {
             return $this->notFound();
        }
        $alias = trim(menu($menu_id, 'alias'));
        if (empty($alias)) {
            $this->redirect($this->redirect($this->m.'/view', ['id' => $this->assign->list[0]['id']]));
        } else {
            $this->redirect($this->redirect($this->absroot . $alias.'/' . $this->assign->list[0]['id'] . '.' . config('url_html_suffix')));
        }
    }

    public function view(){
        // ck编辑器内容需要做分页  通过$this->local['page_field'] = '字段';来指定分页字段
        $this->local['page_field'] = 'content';
        call_user_func(array('parent', __FUNCTION__));
        if ($this->assign->meta['title'][0] == $this->assign->meta['title'][1]) {
            unset($this->assign->meta['title'][0]);
        }
    }
}
