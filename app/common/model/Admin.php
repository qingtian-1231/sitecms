<?php
namespace app\common\model;

class Admin extends App
{
    /**
    * 关联模型
    */
    public $assoc = array (
  'User' => 
  array (
    'type' => 'belongsTo',
  ),
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
            'user_id' => array(
                'type' => 'integer',
                'name' => '所属用户',
                'foreign' => 'User.username',
                'elem' => 'format',
                'list' => 'assoc'
            ),
            'nickname' => array(
                'type' => 'string',
                'name' => '昵称',
                'elem' => 'text',
                'list' => 'show',
            ),
            'truename' => array(
                'type' => 'string',
                'name' => '真实姓名',
                'elem' => 'text',
                'list' => 'show',
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
