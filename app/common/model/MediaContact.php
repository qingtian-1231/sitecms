<?php

namespace app\common\model;

class MediaContact extends App
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
            'id' =>
                [
                    'type' => 'integer',
                    'name' => 'ID',
                    'elem' => 'hidden',
                ],
            'menu_id' =>
                [
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
            'name' => [
                'type' => 'string',
                'name' => '联系人姓名',
                'elem' => 'text',
                'list' => 'show',
            ],
            'contact_number' => [
                'type' => 'string',
                'name' => '联系电话',
                'elem' => 'text',
                'list' => 'show',
            ],
            'position' => [
                'type' => 'string',
                'name' => '公司职位/头衔',
                'elem' => 'text',
                'list' => 'show',
            ],
            'media_name' => [
                'type' => 'string',
                'name' => '媒体名称',
                'elem' => 'text',
                'list' => 'show',
            ],
            'question_who' => [
                'type' => 'text',
                'name' => '您希望了解哪方面的资讯？或者您希望采访谁？',
                'elem' => 'textarea',
            ],
            'question_what' => [
                'type' => 'text',
                'name' => '您方便提供采访提纲吗？',
                'elem' => 'textarea',
            ],
            'question_when' => [
                'type' => 'text',
                'name' => '您希望我们什么时间向您反馈？',
                'elem' => 'textarea',
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
