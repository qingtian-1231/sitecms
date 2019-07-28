<?php
namespace app\run\controller;

use app\common\controller\Run;

class DictionaryItem extends Run
{
    protected function initialize(){
        
        call_user_func(array('parent', __FUNCTION__)); 
    }
    
    public function lists(){
        $this->local['list_fields'] = array(
			'dictionary_id',
			'key',
			'value',			
			'created',
        );
        
        call_user_func(array('parent', __FUNCTION__));
    } 
    
    
    public function create(){
        call_user_func(array('parent', __FUNCTION__));
    }   
}
