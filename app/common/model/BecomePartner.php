<?php

namespace app\common\model;

class BecomePartner extends App
{
    /**
     * 关联模型
     */
    public $assoc = array(
        'Menu' =>
            array(
                'type' => 'belongsTo',
            ),
    );

    public function initialize()
    {
        // 字段和表单信息  设置完成以后，在添加页面会自动读取数据并生成对应表单结构
        $this->form = array(
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
            'business_area' => [
                'type' => 'string',
                'name' => '公司性质',
                'elem' => 'radio',
                'options' => [
                    '民营' => '民营',
                    '国有或国有控股' => '国有或国有控股',
                    '外资' => '外资',
                    '售后' => '售后',
                ],
                'list' => 'show',
            ],
            'question_competition' => [
                'type' => 'text',
                'name' => '您可以简单分析下这个区域或者行业的竞争格局吗？主要竞争对手是哪些？',
                'elem' => 'textarea',
            ],
            'question_advantage' => [
                'type' => 'text',
                'name' => '您认为进入这个区域或者行业的突破口在哪儿？您的优势在哪儿？希望九谷配合您做哪些工作？',
                'elem' => 'textarea',
            ],
            'is_finish' => [
                'type' => 'boolean',
                'name' => '已处理',
                'elem' => 'checker',
                'list' => 'checker',
                'sortable' => true,
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
        );

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
    protected $validate = array(
        'menu_id' =>
            array(
                0 =>
                    array(
                        'rule' =>
                            array(
                                0 => 'egt',
                                1 => 1,
                            ),
                        'message' => '请选择父级导航',
                    ),
                1 =>
                    array(
                        'rule' =>
                            array(
                                0 => 'call',
                                1 => 'checkTypeOfMenu',
                            ),
                    ),
            ),
    );
}
