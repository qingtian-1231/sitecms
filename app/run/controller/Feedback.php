<?php
namespace app\run\controller;

use app\common\controller\Run;

class Feedback extends Run
{
    protected function initialize()
    {
        
        call_user_func(array('parent',__FUNCTION__)); 
    }
    
    public function lists()
    {
        $this->local['filter'] = [
            'title',
            'truename',
            'mobile'
        ];
        $this->local['list_fields'] = array(
            'title',
            'user_id',
            'truename',
            'mobile',
            'reply_user_id',
            'menu_id',
            'created',            
            'is_verify',
            'is_finish',
        );
        //$this->local['actions']['create'] = false ;
        $this->local['order'] = ['is_finish' => 'ASC', 'list_order' => 'DESC', 'id' => 'DESC'];
        call_user_func(array('parent', __FUNCTION__));
    } 
    /*
    public function create(){
        return $this->message('error','该模块不允许添加');	
    } */  
    
    public function modify(){
        if(helper('Form')->data[$this->m]['reply_content'] && !helper('Form')->data[$this->m]['reply_user_id']) {
            helper('Form')->data[$this->m]['reply_user_id'] = helper('Auth')->user('id');
        }           
        $this->mdl->form['reply_user_id']['elem'] = 'format';
        $this->mdl->form['user_id']['elem'] = 'format';
        call_user_func(array('parent', __FUNCTION__));
        
    }
    
    
         
}