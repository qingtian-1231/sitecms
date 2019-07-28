<?php
namespace app\run\controller;

use app\common\controller\Run;

class SettingGroup extends Run
{
    protected function initialize()
    {
        call_user_func(array('parent', __FUNCTION__));
    }
    
    
    public function lists()
    {
        $this->local['list_fields'] = array(
            'id',
            'title'
        );
        $this->local['order'] = ['id' => 'ASC'];
        $this->addItemAction('查看设置', array('Setting/lists', ['parent_id'=>'id'], 'parse'=>['parent_id']), '&#xe60a;', 'layui-btn');
        call_user_func(array('parent', __FUNCTION__));
    }
}
