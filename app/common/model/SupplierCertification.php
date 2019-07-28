<?php
namespace app\common\model;

class SupplierCertification extends App
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
