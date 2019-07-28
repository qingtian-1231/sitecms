<?php
namespace app\common\model;

class Log extends App
{
    /**
    * 关联模型
    */
    public $display = 'url';
    public $assoc = [
        'User' => array(
            'type' => 'belongsTo'
        ),
    ];
    
    public function initialize()
    {        
        $this->form = [
            'id' => [
            	'type' => 'integer',
            	'name' => 'ID',
            	'elem' => 'hidden',
            ],
            'user_id' => array(
                'type' => 'integer',
                'name' => '操作用户',
                'foreign' => 'User.username',
                'elem' => 'format',
                'list' => 'assoc'
            ),
            'url' => array(
                'type' => 'string',
                'name' => '模/控/方',
                'elem' => 'format',
                'list' => 'show'
            ),
            'full_url' => array(
                'type' => 'string',
                'name' => '完整URL',
                'elem' => 'format',
                'list' => 'show'
            ),
            'ip' => array(
                'type' => 'string',
                'name' => 'IP',
                'elem' => 'format',
                'list' => 'show'
            ),
            'user_agent' => array(
                'type' => 'string',
                'name' => '用户代理',
                'elem' => 'format',
                'list' => 'show'
            ),
            'method' => array(
                'type' => 'string',
                'name' => '请求',
                'elem' => 'format',
                'list' => 'show'
            ),
            'last_sql' => array(
                'type' => 'string',
                'name' => '最后SQL',
                'elem' => 'format',
                'list' => 'show',
                'detail' => 'code.SQL'
            ),
             'created' => array(
                'type' => 'datetime',
                'name' => '添加时间',
                'elem' => 'format',
                'list' => 'datetime'
            ),
            
            //其他字段
        ];
        call_user_func_array(['parent', __FUNCTION__], func_get_args());
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
    protected $validate = [];
}
