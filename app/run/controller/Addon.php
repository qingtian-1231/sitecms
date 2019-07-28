<?php
namespace app\run\controller;

use app\common\controller\Run;
use woo\addons\controller\Driver;

/**
*@name 插件
*/
class Addon extends Run
{
    
    public function lists()
    {   
        $this->setTitle('我的插件列表', 'operation');
        $list = get_addon_list();
        $this->assign->list = $list;        
        $this->fetch = true;
    }    
    
    /**
    *安装
    */
    public function install()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        $name = trim($this->args['name']);
        
        try {
            Driver::install($name); 
            $this->ajax('success', '插件安装成功！'); 
        } catch (\Exception $e) {
            $this->ajax('error', '插件安装失败：' . $e->getMessage()); 
        }
    }
    
    /**
    *@name 卸载
    */
    public function uninstall()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        $name = trim($this->args['name']);
        
        try {
            Driver::uninstall($name);  
            $this->ajax('success', '插件卸载成功！'); 
        } catch (\Exception $e) {
            $this->ajax('error', '插件卸载失败：' . $e->getMessage()); 
        }
        exit;
    }
    
    public function create(){     
        
        $this->message('error', '该模型不支持该操作');
    }
    
    public function modify(){     
        
        $this->message('error', '该模型不支持该操作');
    }
    
    public function delete(){     
        
        $this->message('error', '该模型不支持该操作');
    }
    
    public function batchDelete(){     
        
        $this->message('error', '该模型不支持该操作');
    }
    
}