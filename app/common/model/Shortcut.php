<?php
namespace app\common\model;

class Shortcut extends App
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
            'title' => array(
                'type' => 'string',
                'name' => '标题',
                'elem' => 'text',
                'list' => 'show'
            ),
            'icon' => array(
                'type' => 'string',
                'name' => '图标',
                'elem' => 'icon'
            ),
            'type' => array(
                'type' => 'string',
                'name' => '处理类型',
                'elem' => 'select',
                'options' => [
                    'func' => 'JS函数',
                    'url' => 'A链接跳转'
                ]
            ),
            'func' => array(
                'type' => 'string',
                'name' => 'JS函数名',
                'elem' => 'text',
                'list' => 'show',
                'info' => '代码自行在Index/index定义'
            ),
            'url' => array(
                'type' => 'string',
                'name' => '链接地址',
                'elem' => 'text',
                'list' => 'show'
            ),
            'target' => array(
                'type' => 'string',
                'name' => '打开方式',
                'elem' => 'select',
                'options' => [
                    'tab' => '新Tab打开',
                    'blank' => '新窗口打开'
                ]
            ),
            
            'is_verify' => array(
                'type' => 'boolean',
                'name' => '是否启用',
                'elem' => 'checker',
                'list' => 'checker'
            ),
            'list_order' => array(
                'type' => 'integer',
                'name' => '排序权重',
                'elem' => 'number',
                'list' => 'edit'
            ),
            //其他字段
        ];
        call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }
    
    public $fieldRespond = [
        'type' => [
            'RespondField' => ['url', 'func', 'target'],
            'url' => ['url', 'target'],
            'func' => ['func', 'url']
        ]
    ];
    
    public function afterWriteCall()
    {
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));
        \Cache::rm('admin_shortcut_list');    
        return $parent_rslt;
    }
    
    public function afterDeleteCall()
    {
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));
        \Cache::rm('admin_shortcut_list');    
        return $parent_rslt;
    }
    
    
    /**
    // 表单分组
    public $formGroup = [
        'advanced' => '高级选项'
    ];
    */
    
    /**
    * 数据验证 
    */
    protected $validate = [
        'title' => array(
            'rule' => 'require',
            'message' => '请填写标题'
        ),
        'type' => array(
            'rule' => 'require',
            'message' => '请选择处理方式'
        ),
        'icon' => array(
            'rule' => 'require',
            'message' => '请选择图标'
        )
    ];
}
