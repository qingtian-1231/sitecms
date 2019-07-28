<?php
namespace app\common\model;

use woo\utility\Hash;

class Model extends App
{

    public function initialize()
    {
        $this->form = [
            'id' => [
                'type' => 'integer',
                'name' => 'ID',
                'elem' => 'hidden',
            ],
            'model' => array(
                'type' => 'string',
                'name' => '模型类名',
                'elem' => 'text',
                'list' => 'show',
                'info' => '比如：Menu'
            ),
            'cname' => array(
                'type' => 'string',
                'name' => '模型名称',
                'elem' => 'text',
                'list' => 'show',
                'info' => '比如：栏目'
            ),
            'is_menu' => array(
                'type' => 'boolean',
                'name' => '是否栏目',
                'elem' => 'checker',
                'list' => 'checker',
            ),
            'is_dustbin' => array(
                'type' => 'boolean',
                'name' => '删除回收',
                'elem' => 'checker',
                'list' => 'checker',
            ),
            'is_power' => array(
                'type' => 'boolean',
                'name' => '权限',
                'elem' => 0,//'checker',
                'list' => 0,//'checker',
            ),
            'is_import' => array(
                'type' => 'boolean',
                'name' => '是否允许导入',
                'elem' => 'checker',
                'list' => 'checker',
            ),
            'created' => array(
                'type' => 'datetime',
                'name' => '添加时间',
                'elem' => 0,
                'list' => 'datetime'
            ),
            'modified' => array(
                'type' => 'datetime',
                'name' => '修改时间',
                'elem' => 0,
                'list' => 'datetime'
            ),
        ];
        call_user_func_array(array('parent', __FUNCTION__), func_get_args());
    }


    //数据验证    
    protected $validate = [
        'model' => array(
            'rule' => array('unique', 'model'),
            'message' => '该模型已存在'
        ),
        'cname' => array(
            'rule' => 'require',
            'message' => '请填写模型名称'
        ),
    ];

    public function afterWriteCall()
    {
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));
        $this->writeToFile();
        return $parent_rslt;
    }

    public function afterDeleteCall()
    {
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));
        $this->writeToFile();
        return $parent_rslt;
    }

    public function writeToFile()
    {
        $list = $this
                ->order(['id' => 'ASC'])
                ->field(['id', 'model', 'cname', 'is_menu', 'is_power', 'is_dustbin', 'is_import'])
                ->select()
                ->toArray();
        
        $cache = Hash::combine($list, '{n}.id', '{n}');
        write_file_cache('Model', $cache);
        return true;
    }
}
