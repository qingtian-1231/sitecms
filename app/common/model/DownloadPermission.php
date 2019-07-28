<?php
namespace app\common\model;

class DownloadPermission extends App
{
    /**
    * 关联模型
    */
    public $assoc = array (
  'User' => 
  array (
    'type' => 'belongsTo',
  ),
  'Download' => 'belongsTo'
);
    public $parentModel = 'Download';
    
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
  'user_id' => 
  array (
    'type' => 'integer',
    'name' => '所属用户',
    'foreign' => 'User.username',
    'elem' => 'assoc_select',
    'list' => 'assoc',
  ),
  'download_id' => 
  array (
    'type' => 'integer',
    'name' => '所属下载',
    'foreign' => 'Download.title',
    'elem' => 'assoc_select',
    'list' => 'assoc',
    'assoc_options' => [
        'where' => [
            ['download_permission', '=', 'specify']
        ]
    ]
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
