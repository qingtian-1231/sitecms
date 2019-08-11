<?php

namespace app\common\model;

class PotentialCustomers extends App
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
            'is_finish' => [
                'type' => 'boolean',
                'name' => '已处理',
                'elem' => 'checker',
                'list' => 'checker',
                'sortable' => true,
            ],
            'company_name' => [
                'type' => 'string',
                'name' => '公司名称',
                'elem' => 'text',
                'list' => 'show',
            ],
            'name' => [
                'type' => 'string',
                'name' => '联系人姓名',
                'elem' => 'text',
                'list' => 'show',
            ],
            'position' => [
                'type' => 'string',
                'name' => '公司职位/头衔',
                'elem' => 'text',
                'list' => 'show',
            ],
            'contact_number' => [
                'type' => 'string',
                'name' => '联系电话',
                'elem' => 'text',
                'list' => 'show',
            ],
            'question_when' => [
                'type' => 'text',
                'name' => '您希望我们什么时间向您反馈？',
                'elem' => 'textarea',
            ],
            'question_what' => [
                'type' => 'text',
                'name' => '您联系我们是希望我们提供那些服务？合格供应商征集和摸底？前期技术方案征集？前期方案咨询？参与规划设计投标？参与施工或者系统集成投标？参与运维投标？',
                'elem' => 'textarea',
            ],
            'question_how' => [
                'type' => 'text',
                'name' => '您要了解的产品和服务主要用于什么项目？您能介绍下项目的基本情况吗？比如项目用途？主要业态？投资规模？进度安排？目前现状？',
                'elem' => 'textarea',
            ],
            'question_service' => [
                'type' => 'text',
                'name' => '您需要了解我们那些产品和服务？',
                'elem' => 'textarea',
            ],
            'created' => [
                'type' => 'datetime',
                'name' => '添加时间',
                'elem' => 'date',
                'list' => 'datetime',
            ],
            'modified' => [
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
