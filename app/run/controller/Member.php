<?php
namespace app\run\controller;

use app\common\controller\Run;

class Member extends Run
{
    //初始化 需要调父级方法
    protected function initialize(){        
        call_user_func(['parent', __FUNCTION__]); 
    }
    
    //列表 
    public function lists(){
        $this->redirect('User/lists');
        $this->local['list_fields'] = [
            'user_id',
            'nickname',
            'truename',
            'headimg',
            'sex',
            'mobile',
            'address'
        ];
        $this->local['actions']['create'] = false ;
        call_user_func(['parent', __FUNCTION__]);
    }
    
    //添加
    public function create(){
        $this->message('error', '该模型不支持添加');
    }
    
    //修改
    public function modify(){    
        
        if(!$this->args['id'] && $this->args['parent_id']){
            $member  = $this->mdl->getByUser($this->args['parent_id']);
            if(empty($member)) {
                $this->redirect('User/create');
            }
            $this->local['id']  = $member['id'];
        }
        call_user_func(['parent', __FUNCTION__]);
    } 
    
    //删除
    public function delete(){        
        call_user_func(['parent', __FUNCTION__]);
    }  
}