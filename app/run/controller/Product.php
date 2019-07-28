<?php
namespace app\run\controller;

use app\common\controller\Run;

class Product extends Run
{
    protected function initialize(){
        
        call_user_func(array('parent',__FUNCTION__)); 
    }
    
    public function lists(){
         
        $this->local['filter'] = [
            'id',
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
            'date'
        ];
        
        $this->local['list_fields'] = array(
            'title',
            //'ex_title',
            'menu_id',
            'image',
            'date',
            //'user_id',
            //'created',
            'is_verify',
            'is_index'
        );
        //$this->local['item_actions']['copy'] = true ;
        call_user_func(array('parent', __FUNCTION__));
    } 
    
    
    public function create(){
        $this->assignDefault('date',date('Y-m-d'));
        $this->assignDefault('list_order',0);
        $this->assignDefault('price',0);
        $this->assignDefault('original_price',0);
        call_user_func(array('parent', __FUNCTION__));
    }   
    
    public function modify()
    {
        $this->mdl->form['created']['elem'] = 'format';
        $this->mdl->form['modified']['elem'] = 'format';
        call_user_func(array('parent', __FUNCTION__));
    } 
}
