<?php
namespace app\run\controller;

use app\common\controller\Run;

class UserGroup extends Run
{
    protected function initialize(){
        
        call_user_func(array('parent', __FUNCTION__)); 
    }

    public function create()
    {
        $this->assignDefault('alias','Member');
        return parent::create();
    }

    public function lists()
    {
        $this->local['list_fields'] = array(
            'title',
            'alias',
            'is_admin'
        );
        //$this->addItemAction('查看用户', array('User/lists', ['parent_id'=>'id'], 'parse'=>['parent_id']), '&#xe60a;');
        call_user_func(array('parent', __FUNCTION__));
    }  
}
