<?php
namespace app\run\controller;

use app\common\controller\Run;

class Admin extends Run
{
    /**
    * 初始化 
    */
    protected function initialize()
    {        
        call_user_func(['parent', __FUNCTION__]); 
    }
    
    /**
    * 列表 
    */
    public function lists()
    {
        $this->redirect('User/lists');

    }
    
    /**
    * 添加
    */
    public function create(){
        $this->message('error', '该模型不支持添加');
    }
    
    /**
    * 修改
    */
    public function modify()
    {   
        // 字段白名单
        //$this->local['whiteList'] = ['id', 'title', ...允许修改的字段列表];
        call_user_func(['parent', __FUNCTION__]);
    } 
    
    /**
    * 删除
    */
    public function delete()
    {   
        // 设置额外的删除条件
        //$this->local['where'][] = ['is_verify', '=', 0];
        call_user_func(['parent', __FUNCTION__]);
    }  
}
