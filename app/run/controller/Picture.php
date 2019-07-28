<?php
namespace app\run\controller;

use app\common\controller\Run;

class Picture extends Run
{
    protected function initialize(){
        call_user_func(array('parent',__FUNCTION__)); 
    }
    
    public function lists(){
        $this->local['list_fields'] = array(
            'title',
            'image',
            'user_id',
            'foreign_id',
            'created',
            'is_verify',
        );
        $this->local['where'][] = ['module', '=', $this->m];
        call_user_func(array('parent',__FUNCTION__));
    }
    
    public function create(){
        $this->assignDefault('list_order', 0);
        $this->assignDefault('module', $this->m);
        call_user_func(array('parent', __FUNCTION__));
    }    
}
