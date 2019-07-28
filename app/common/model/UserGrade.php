<?php
namespace app\common\model;

class UserGrade extends App
{
    /**
    * 关联模型
    */
    public $assoc = [];
    
    public function initialize()
    {        
        $this->form = [
            'id' => [
            	'type' => 'integer',
            	'name' => 'ID',
            	'elem' => 'hidden',
            ],
            'title' => [
                'type' => 'string',
                'name' => '等级名称',
                'elem' => 'text',
                'list' => 'show',
            ],
            'min' => [
                'type' => 'float',
                'name' => '积分最小值',
                'elem' => 'text',
                'list' => 'show',
            ],
            'max' => [
                'type' => 'float',
                'name' => '积分最大值',
                'elem' => 'text',
                'list' => 'show',
                'info' => '不含'
            ],
            'image' => array(
                'type' => 'string',
                'name' => '等级图标',
                'elem' => 'image.upload',
                'list' => 'image',
                'upload' => array(
                    'maxSize' => 512,
                    'validExt' => array('jpg', 'png', 'gif')
                )
            ),
            //其他字段
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
    protected $validate = [];
}
