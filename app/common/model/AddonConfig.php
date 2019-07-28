<?php
namespace app\common\model;

class AddonConfig extends App
{
    public $display = 'title';
    public $assoc = array(
        'Addon' => 'belongsTo'
    );

    ##父模型名
    //public $parentModel = 'Addon';

    private $parseJsonTypes = array();

    public function initialize()
    {

        $this->form = array(
            'id' => array(
                'type' => 'integer',
                'name' => 'ID',
                'elem' => 'hidden',
            ),
            'title' => array(
                'type' => 'string',
                'name' => '名称',
                'elem' => 'text.title',
            ),

            'addon_id' => array(
                'type' => 'integer',
                'name' => '插件ID',
                'elem' => 'assoc_select',
                'foreign' => 'Addon.title',
                'list' => 'assoc',
            ),
            'vari' => array(
                'type' => 'string',
                'name' => '引用变量',
                'elem' => 'text',
                'info' => '非程序员请不要随意修改'
            ),
            'value' => array(
                'type' => 'string',
                'name' => '配置值',
                'elem' => 0
            ),
            'type' => array(
                'type' => 'string',
                'name' => '输入类型',
                'elem' => 'select',
                'options' => array('text' => '文本框', 'password' => '密码框', 'textarea' => '文本域', 'file' => '文件', 'checkbox' => '多选框', 'radio' => '单选框', 'color'=> '取色器','select' => '下拉菜单', 'checker' => '是否', 'array' => '数组', 'keyvalue' => '键值对'),
            ),
            'options' => array(
                'type' => 'string',
                'name' => '选项',
                'elem' => 'keyvalue',
                'list' => 'json',
            ),

            'info' => array(
                'type' => 'string',
                'name' => '提示信息',
                'elem' => 'text'
            )

        );


        $this->parseJsonTypes = array('checkbox', 'array', 'keyvalue');
        call_user_func(array('parent', __FUNCTION__));
    }

    public function beforeWriteCall()
    {
        $rslt = call_user_func(array('parent', __FUNCTION__));
        if (isset($this['type']) && isset($this['value'])) {
            if ($this['type'] == 'keyvalue' && is_array($this['value'])) {
                $this['value'] = Hash::combine($this['value'], '{n}.key', '{n}.value');
            }            
            if (in_array($this['type'], $this->parseJsonTypes) && is_array($this['value'])) {
                $this['value'] = json_encode($this['value']);
            }
        }
        return $rslt;
    }

}
