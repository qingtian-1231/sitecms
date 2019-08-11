<?php

namespace app\common\model;

class WelfareCooperation extends App
{
    public $display = 'title';
    public $parentModel = 'Menu';
    /**
     * 关联模型
     */
    public $assoc = [
        'Menu' =>
            [
                'type' => 'belongsTo',
            ],
    ];

    public function initialize()
    {
        // 字段和表单信息  设置完成以后，在添加页面会自动读取数据并生成对应表单结构
        $this->form = [
            'id' => [
                'type' => 'integer',
                'name' => 'ID',
                'elem' => 'hidden',
            ],
            'menu_id' => [
                'type' => 'integer',
                'name' => '所属栏目',
                'elem' => 'hidden',
                'foreign' => 'Menu.title',
                'list' => 'assoc',
            ],
            'contact_number' => [
                'type' => 'string',
                'name' => '联系电话',
                'elem' => 'text',
                'list' => 'show',
            ],
            'position' => [
                'type' => 'string',
                'name' => '工作职位',
                'elem' => 'text',
                'list' => 'show',
            ],
            'name' => [
                'type' => 'string',
                'name' => '联系人姓名',
                'elem' => 'text',
                'list' => 'show',
            ],
            'is_finish' =>
                [
                    'type' => 'boolean',
                    'name' => '已处理',
                    'elem' => 'checker',
                    'list' => 'checker',
                    'sortable' => true,
                ],
            'question_demand' => [
                'type' => 'text',
                'name' => '您的具体需求是什么呢？',
                'elem' => 'textarea',
            ],
            'question_reason' => [
                'type' => 'text',
                'name' => '能简单介绍下您申请资助或者希望合作的理由吗？',
                'elem' => 'textarea',
            ],
            'created' =>
                [
                    'type' => 'datetime',
                    'name' => '添加时间',
                    'elem' => 'date',
                    'list' => 'datetime',
                ],
            'modified' =>
                [
                    'type' => 'datetime',
                    'name' => '修改时间',
                    'elem' => 'date',
                    'list' => 'datetime',
                ],
        ];

        call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }

    /**
     * // 表单分组
     * public $formGroup = [
     * 'advanced' => '高级选项'
     * ];
     */

    /**
     * 数据验证
     */
    protected $validate = [];
}
