<?php
namespace app\run\controller;

use app\common\controller\Run;

class Download extends Run
{
    protected function initialize(){
        
        call_user_func(array('parent',__FUNCTION__)); 
    }
    
    public function lists(){
         
        $this->local['filter'] = [
            'title',
            'menu_id' => [
                'elem' => 'options',
                'assoc_options' => [
                    'where' => [
                        ['type', '=', $this->m]
                    ],
                    'order' => ['id' => 'ASC']
                ]
            ],
            'file_name'
        ];
        $this->local['list_fields'] = array(
            'title',
            'file_name',
            'size',
            'menu_id',
            'download_permission',
            //'user_id',
            'created',
            'is_verify',
            'is_index'
        );
        $this->addItemAction('download_permission');
        call_user_func(array('parent',__FUNCTION__));
    }
    
    public function create(){
        $this->assignDefault('date', date('Y-m-d'));
        $this->assignDefault('list_order', 0);
        $this->assignDefault('download_permission', 'free');
        $this->assignDefault('score', 0);
        $this->mdl->form['file_name']['elem'] = 0 ;
        return call_user_func(array('parent',__FUNCTION__));
    }   
}
