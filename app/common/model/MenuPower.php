<?php
namespace app\common\model;

class MenuPower extends App
{
    /**
    * 关联模型
    */
    public $assoc = array (
    );
    
    public function initialize()
    {   
        // 字段和表单信息  设置完成以后，在添加页面会自动读取数据并生成对应表单结构
        $this->form = array (
        'id' =>
          array (
            'type' => 'integer',
            'name' => 'ID',
            'elem' => 'hidden',
          ),
            'type'=>array(
                'type'=>'string',
                'name'=>'权限类型',
                'elem'=>0,
            ),
            'foreign_id'=>array(
                'type'=>'integer',
                'name'=>'关联ID',
                'elem'=>0,
            ),

            'content'=>array(
                'type'=>'blob.array',
                'name'=>'授权规则',
                'elem'=>0,
            ),
  'created' => 
  array (
    'type' => 'datetime',
    'name' => '添加时间',
    'elem' => 0,
    'list' => 'datetime',
  ),
  'modified' => 
  array (
    'type' => 'datetime',
    'name' => '修改时间',
    'elem' => 0,
    'list' => 'datetime',
  ),
);
        
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
    protected $validate = array (
);
}
