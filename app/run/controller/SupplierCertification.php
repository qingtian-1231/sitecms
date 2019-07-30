<?php
namespace app\run\controller;

use app\common\controller\Run;

class SupplierCertification extends Run
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
        // 搜索字段
        /*        
        $this->local['filter'] = [
            'title'
        ];
        */
        
        // 列表字段
        $this->local['list_fields'] = [
            'title'
            // 其他列表字段
        ];
        
        // 添加额外条件
        //$this->local['where'][] = ['字段', '=', '值'];        
        
        call_user_func(['parent', __FUNCTION__]);
    }
    
    /**
    * 添加
    */
    public function create()
    {   // 设置默认值
        //$this->assignDefault('字段名', '默认值');
        // 字段白名单
        //$this->local['whiteList'] = ['id', 'title', ...允许添加的字段列表];   
        call_user_func(['parent', __FUNCTION__]);
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
