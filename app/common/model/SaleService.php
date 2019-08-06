<?php
namespace app\common\model;

class SaleService extends App
{
    /**
    * 关联模型
    */
    public $assoc = array (
  'Menu' => 
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
  'menu_id' => 
  array (
    'type' => 'integer',
    'name' => '所属栏目',
    'elem' => 'nest_select.Menu',
    'foreign' => 'Menu.title',
    'list' => 'assoc',
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
  'menu_id' => 
  array (
    0 => 
    array (
      'rule' => 
      array (
        0 => 'egt',
        1 => 1,
      ),
      'message' => '请选择父级导航',
    ),
    1 => 
    array (
      'rule' => 
      array (
        0 => 'call',
        1 => 'checkTypeOfMenu',
      ),
    ),
  ),
);
}
