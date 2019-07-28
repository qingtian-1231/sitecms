<?php

namespace app\run\controller;

use app\common\controller\Run;

/**
 * @name 文章
 * @powerset true
 */
class Article extends Run
{
    protected function initialize(){
        
        call_user_func(array('parent', __FUNCTION__));
    }

    /**
     * @name 列表
     * @powerset true
     */
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
            'date',
            'is_verify'
        ];
        $this->local['list_fields'] = array(
            'title',
            'menu_id',
            'user_id',
            'image',
            'date',
            'created',
            'is_verify',
            'is_index',
            'list_order'
        ); 
        $this->local['exportable'] = true;
        // 测试高级查询
//        $this->local['whereOr'] = [
//            [
//                ['title', 'like', 'thinkphp%'],
//                ['content', 'like', '%thinkphp'],
//            ],
//            [
//                ['title', 'like', 'kancloud%'],
//                ['content', 'like', '%kancloud'],
//            ]
//        ];
//        $this->local['whereTime'] = ['created', 'today'];
        call_user_func(array('parent', __FUNCTION__));
    }
        
    public function export()
    {
        $this->local['filter'] = [
            'title',
            'menu_id',
            'date',
            'is_verify'
            
        ];   
        $this->local['list_fields'] = [
            /*
            'date' => [
                'callback' => function($val) {
                    return strtotime($val);
                }
            ]*/
        ];          
        call_user_func(array('parent', __FUNCTION__));
    }
    
    
    public function detail()
    {
        //$this->local['detail_with'] = ['Menu' => ['field' => ['id', 'title', 'ex_title']], 'User1'];
        call_user_func(array('parent', __FUNCTION__));
    }
    
    public function create()
    {
        $this->assignDefault('date', date('Y-m-d'));
        $this->assignDefault('list_order', 0);
        call_user_func(array('parent', __FUNCTION__));
    }
    
    public function delete()
    {
        call_user_func(array('parent', __FUNCTION__));
    }
    
    public function modify()
    {
        $this->mdl->form['created']['elem'] = 'format';
        $this->mdl->form['modified']['elem'] = 'format';
        call_user_func(array('parent', __FUNCTION__));
    }
}
