<?php

namespace app\common\model;

use woo\model\WooModel;
use think\Db;

class App extends WooModel
{
    /**
     * 初始化
    */
    protected function initialize()
    {
        // 必须调父级
        parent::initialize();
    }
    
    /**
    * 新增前
    */
    protected function beforeInsertCall()
    {
        // 任何模型的事件方法都比较调父级 以免造成核心代码丢失
        $parentResult = call_user_func(['parent', __FUNCTION__]);        
        // my code        
        return $parentResult;
    }
    
    /**
    * 新增后
    */
    protected function afterInsertCall()
    {
        $parentResult = call_user_func(['parent', __FUNCTION__]);        
        // my code        
        return $parentResult;
    }
    
    /**
    * 更新前
    */
    protected function beforeUpdateCall()
    {
        $parentResult = call_user_func(['parent', __FUNCTION__]);        
        // my code        
        return $parentResult;
    }
    
    /**
    * 更新后
    */
    protected function afterUpdateCall()
    {
        $parentResult = call_user_func(['parent', __FUNCTION__]);        
        // my code        
        return $parentResult;
    }
    
    /**
    * 写入前
    */
    protected function beforeWriteCall()
    {  
       $parentResult = call_user_func(['parent', __FUNCTION__]);        
       // my code        
       return $parentResult;
    }
    
    /**
    * 写入后
    */
    protected function afterWriteCall()
    {  
        $parentResult = call_user_func(['parent', __FUNCTION__]);        
        // my code        
        return $parentResult;
    }
    
    /**
    * 删除前
    */
    protected function beforeDeleteCall()
    {
        $parentResult = call_user_func(['parent', __FUNCTION__]);        
        // my code        
        return $parentResult;
    }
    
    /**
    * 删除后
    */
    protected function afterDeleteCall()
    {
        $parentResult = call_user_func(['parent', __FUNCTION__]);        
        // my code        
        return $parentResult;
    }
}
