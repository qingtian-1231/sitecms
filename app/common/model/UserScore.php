<?php
namespace app\common\model;

class UserScore extends App
{
    public $display = 'title';
    public $parentModel = 'User';
    
    /**
    * 关联模型
    */
    public $assoc = [
        'User' => [
            'type' => 'belongsTo',
            'sumCache' => [
                'sumField' => 'score',
                'cacheField' => 'user_score_sum'
            ]
        ]
    ];
    
    public function initialize()
    {        
        $this->form = [
            'id' => [
            	'type' => 'integer',
            	'name' => 'ID',
            	'elem' => 'hidden',
            ],
            'user_id' => [
                'type' => 'integer',
                'name' => '所属用户',
                'foreign' => 'User.username',
                'elem' => 'assoc_select',
                'list' => 'assoc'
            ],
            'score' => [
                'type' => 'float',
                'name' => '积分',
                'elem' => 'text',
                'list' => 'show',
                'list_count' => 'count'
            ],
            'title' => [
                'type' => 'string',
                'name' => '说明',
                'elem' => 'text',
                'list' => 'show',
            ],
            'remark' => [
                'type' => 'string',
                'name' => '备注',
                'elem' => 'textarea',
                'list' => 'show',
            ],
            'created' => [
                'type' => 'datetime',
                'name' => '添加时间',
                'elem' => 0,
                'list' => 'datetime',
                'elem_group' => 'advanced',
            ],
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
    protected $validate = [
        
        'user_id' => [
            'rule' => ['egt', 1]
        ]
    ];
}
