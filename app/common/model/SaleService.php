<?php

namespace app\common\model;

class SaleService extends App
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
            'contact_number' => [
                'type' => 'string',
                'name' => '联系电话',
                'elem' => 'text',
                'list' => 'show',
            ],
            'feedback' => [
                'type' => 'text',
                'name' => '回馈与咨询',
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
