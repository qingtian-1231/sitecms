<?php
namespace app\common\model;

class Addon extends App
{
    /**
    * 关联模型
    */
    public $assoc = [];
    
    public function initialize()
    {        
        $this->form = [
            'id' => [
            	'type' => 'integer',
            	'name' => 'ID',
            	'elem' => 'hidden',
            ],
            'name' => [
            	'type' => 'string',
            	'name' => '插件名',
            	'elem' => 'format',
            ],
            'title' => [
            	'type' => 'string',
            	'name' => '插件名称',
            	'elem' => 'format',
            ],
            'intro' => [
            	'type' => 'string',
            	'name' => '插件描述',
            	'elem' => 'format',
            ],
            'author' => [
            	'type' => 'string',
            	'name' => '插件作者',
            	'elem' => 'format',
            ],
            'version' => [
            	'type' => 'string',
            	'name' => '插件版本',
            	'elem' => 'format',
            ],
            'state' => [
            	'type' => 'string',
            	'name' => '插件状态',
            	'elem' => 'format',
            ],
            //其他字段
        ];
        call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }
    
}
