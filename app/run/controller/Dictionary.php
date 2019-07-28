<?php
namespace app\run\controller;

use app\common\controller\Run;

class Dictionary extends Run
{
    protected function initialize(){
        call_user_func(array('parent',__FUNCTION__)); 
    }
    
    public function lists(){
        $this->local['list_fields'] = array(
			'title',
			'model',
			'field',			
			'created',
            'dictionary_item_count',
        );
        $this->addItemAction('添加字典项目' , array('DictionaryItem/create', ['parent_id'=>'id'], 'parse'=>['parent_id']) , '&#xe654;', 'layui-btn');
        call_user_func(array('parent', __FUNCTION__));
    }
    
    public function create(){
		if ($this->args['parent_id']) {
			$this->assignDefault('title', menu($this->args['parent_id']));
		}
        if ($this->args['model'] && $this->args['field']) {
            $id  = $this->mdl
                    ->where([
                        ['model', '=', trim($this->args['model'])],
                        ['field', '=', trim($this->args['field'])]
                    ])
                    ->value('id');
            if ($id) {
                $this->redirect('DictionaryItem/create', ['parent_id' => $id]);
            }
        }
        call_user_func(array('parent', __FUNCTION__));
    }   
}
