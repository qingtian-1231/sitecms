<?php
namespace app\run\controller;

use app\common\controller\Run;

class Page extends Run
{
    protected function initialize(){
        
        call_user_func(array('parent', __FUNCTION__)); 
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
            ]
        ];
        $this->local['list_fields'] = array(
            'title',
            'image',
            'menu_id',
            'is_verify'
        );
        call_user_func(array('parent', __FUNCTION__));
    } 
    
    
    public function create(){
        $this->assignDefault('list_order', 0);
        if($this->args['parent_id']) {
            $this->assignDefault('title', menu(intval($this->args['parent_id']), 'title'));
        }
        call_user_func(array('parent', __FUNCTION__));
    } 
    
    public function modify()
    {
        $this->mdl->form['created']['elem'] = 'format';
        $this->mdl->form['modified']['elem'] = 'format';
        call_user_func(array('parent', __FUNCTION__));
    }    
}
