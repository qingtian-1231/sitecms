<?php
namespace app\run\controller;

use app\common\controller\Run;

class Email extends Run
{
    
    protected function initialize()
    {        
        call_user_func(['parent', __FUNCTION__]); 
    }
    
    
    public function lists()
    {
        $this->local['filter'] = [
            'vari',
            'title',
            'email_title'
        ];
        
        $this->local['list_fields'] = [
            'vari',
            'title',
            'email_title',
            'created'
        ];
        call_user_func(['parent', __FUNCTION__]);
    }
    
    //添加
    public function create()
    {        
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
