<?php

namespace app\common\model;

class SupplierCertification extends App
{
    public $display = 'title';
    public $parentModel = 'Menu';

    public $assoc = array(
        'Menu' => array(
            'type' => 'belongsTo'
        ),
    );

    public function initialize()
    {
        // 字段和表单信息  设置完成以后，在添加页面会自动读取数据并生成对应表单结构
        $this->form = array(
            'id' => array(
                'type' => 'integer',
                'name' => 'ID',
                'elem' => 'hidden',
            ),
            'menu_id' => array(
                'type' => 'integer',
                'name' => '所属栏目',
                'elem' => 'hidden',
                'foreign' => 'Menu.title',
                'list' => 'assoc'
            ),
            'company_name' => array(
                'type' => 'string',
                'name' => '公司名称',
                'elem' => 'text',
                'list' => 'show',
            ),
            'name' => array(
                'type' => 'string',
                'name' => '联系人姓名',
                'elem' => 'text',
                'list' => 'show',
            ),
            'position' => array(
                'type' => 'string',
                'name' => '工作职位',
                'elem' => 'text',
                'list' => 'show',
            ),
            'contact_number' => array(
                'type' => 'string',
                'name' => '联系电话',
                'elem' => 'text',
                'list' => 'show',
            ),
            'company_type' => array(
                'type' => 'string',
                'name' => '公司性质',
                'elem' => 'text',
                'list' => 'show',
            ),
            'question_type' => array(
                'type' => 'text',
                'name' => '公司类型',
                'elem' => 'radio',
                'options' => [
                    '民营' => '民营',
                    '国有或国有控股' => '国有或国有控股',
                    '外资' => '外资',
                    '售后' => '售后',
                ],
            ),
            'question_provide' => array(
                'type' => 'text',
                'name' => '提供哪些产品和服务',
                'elem' => 'textarea',
            ),
            'question_product' => array(
                'type' => 'text',
                'name' => '提供的服务或者产品是否需要强制性检测和认证',
                'elem' => 'textarea',
            ),
            'question_customer' => array(
                'type' => 'text',
                'name' => '曾经为哪些客户或者项目提供过上述产品或服务',
                'elem' => 'textarea',
            ),
            'question_judge' => array(
                'type' => 'text',
                'name' => '是否愿意参加我公司的竞争性谈判',
                'elem' => 'textarea',
            ),
            'question_pay' => array(
                'type' => 'text',
                'name' => '对付款方式是否有特殊要求',
                'elem' => 'textarea',
            ),
            'ip' => array(
                'type' => 'string',
                'name' => '申请者IP',
                'elem' => 'format',
                'list' => 'show',
            ),
            'is_finish' => array(
                'type' => 'boolean',
                'name' => '已处理',
                'elem' => 'checker',
                'list' => 'checker',
            ),
            'created' => array(
                'type' => 'datetime',
                'name' => '添加时间',
                'elem' => 0,
                'list' => 'datetime',
                'elem_group' => 'advanced',
            ),
            'modified' => array(
                'type' => 'datetime',
                'name' => '修改时间',
                'elem' => 0,
                'list' => 'datetime',
                'elem_group' => 'advanced',
            ),
        );

        call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }

    /**
     * 数据验证
     */
    protected $validate = array();
}
