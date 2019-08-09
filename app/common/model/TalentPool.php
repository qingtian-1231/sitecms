<?php

namespace app\common\model;

class TalentPool extends App
{
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
                'elem' => 'nest_select.Menu',
                'foreign' => 'Menu.title',
                'list' => 'assoc',
            ],
            'name' => [
                'type' => 'string',
                'name' => '联系人姓名',
                'elem' => 'text',
                'list' => 'show',
            ],
            'gender' => [
                'type' => 'string',
                'name' => '性别',
                'elem' => 'text',
                'list' => 'show',
            ],
            'birthplace' => [
                'type' => 'string',
                'name' => '籍贯',
                'elem' => 'text',
                'list' => 'show',
            ],
            'identification' => [
                'type' => 'string',
                'name' => '身份证号码',
                'elem' => 'text',
                'list' => 'show',
            ],
            'address' => [
                'type' => 'string',
                'name' => '通讯地址',
                'elem' => 'text',
                'list' => 'show',
            ],
            'graduated_school' => [
                'type' => 'string',
                'name' => '毕业院校',
                'elem' => 'text',
                'list' => 'show',
            ],
            'phone' => [
                'type' => 'string',
                'name' => '个人联系电话',
                'elem' => 'text',
                'list' => 'show',
            ],
            'highest_education' => [
                'type' => 'string',
                'name' => '最高学历',
                'elem' => 'text',
                'list' => 'show',
            ],
            'experience' => [
                'type' => 'text',
                'name' => '工作经历',
                'elem' => 'textarea',
            ],
            'education' => [
                'type' => 'text',
                'name' => '教育背景',
                'elem' => 'textarea',
            ],
            'interested_position' => [
                'type' => 'string',
                'name' => '感兴趣职位',
                'elem' => 'text',
                'list' => 'show',
            ],
            'question_what' => [
                'type' => 'text',
                'name' => '您对公司有什么特殊要求？比如薪资待遇不低于多少？对工作地点有什么特殊需求？以及其他？是否介意出差？',
                'elem' => 'textarea',
            ],
            'is_finish' => [
                'type' => 'boolean',
                'name' => '已处理',
                'elem' => 'checker',
                'list' => 'checker',
            ],
            'created' => [
                'type' => 'datetime',
                'name' => '添加时间',
                'elem' => 0,
                'list' => 'datetime',
            ],
            'modified' => [
                'type' => 'datetime',
                'name' => '修改时间',
                'elem' => 0,
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
    protected $validate = [
        'menu_id' =>
            [
                0 =>
                    [
                        'rule' =>
                            [
                                0 => 'egt',
                                1 => 1,
                            ],
                        'message' => '请选择父级导航',
                    ],
                1 =>
                    [
                        'rule' =>
                            [
                                0 => 'call',
                                1 => 'checkTypeOfMenu',
                            ],
                    ],
            ],
    ];
}
