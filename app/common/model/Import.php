<?php
namespace app\common\model;

use woo\utility\Hash;

class Import extends App
{
    /**
    * 关联模型
    */
   public $assoc = array(
        'User' => 'belongsTo'
    );
    
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
                'name' => '所属用户',
                'foreign' => 'User.username',
                'elem' => 0,
                'list' => 'assoc'
            ),
            'title' => array(
                'type' => 'string',
                'name' => '标题',
                'elem' => 'text.title',
                'list' => 'show',
            ),
            'model' => array(
                'type' => 'string',
                'name' => '模型',
                'elem' => 'select',
                'options' => Hash::combine($GLOBALS['Model'], '{n}[is_import=1].model', '{n}[is_import=1].cname'),
            ),
            'assoc_field' => array(
                'type' => 'array',
                'name' => '关联字段',
                'elem' => 'array',
                'info' => '主要针对beLongtsTo字段，如果没有设置关联字段是原内容直接写入'
            ),
            'file_name' => array(
                'type' => 'string',
                'name' => '原文件名',
                'elem' => 'text',
                'list' => 'show',
            ),
            'is_import' => array(
                'type' => 'boolean',
                'name' => '是否导入',
                'elem' => 'checker',
                'list' => 'checker',
            ),
            'file' => array(
                'type' => 'string',
                'name' => '文件',
                'elem' => 'file',
                'list' => 'file',
                'upload' => array(
                    'nameField' => 'file_name',
                    'sizeField' => 'size',
                    'maxSize' => 512,
                    'validExt' => array('xlsx', 'xls', 'csv')
                ),
                'info' => '如数据太多，建议分多个文件导入'
            ),
            'size' => array(
                'type' => 'integer',
                'name' => '文件大小',
                'elem' => 0,
                'list' => 'filesize',
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
            )
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
        'title' => array(
            'rule' => 'require',
            'message' => '请填写标题'
        )
    ];
}
