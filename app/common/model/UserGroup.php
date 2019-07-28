<?php
namespace app\common\model;

class UserGroup extends App
{
    public $display = 'title';
    public $assoc = array(
        'User' => array(
            'type' => 'hasMany'
        )
    );

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
                'name' => '组名',
                'elem' => 'text',
            ),
            'alias' => array(
                'type' => 'string',
                'name' => '信息模型名',
                'elem' => 'text',
                'filter' => function($value, $data) {
                    return parse_name(trim($value), 1);
                },
                'info' => '该组下用户的信息模型名'
            ),
            'is_admin' => array(
                'type' => 'integer',
                'name' => '可登陆后台',
                'elem' => 'checker',
                'list' => 'checker.show',
            ),
        );

        call_user_func(array('parent', __FUNCTION__));
    }

    protected $validate = array(
        'title' => array(
            'rule' => 'require',
            'message' => '请填写组名'
        ),
        'alias' => [
            [
                'rule' => 'require',
                'message' => '请填写信息模型名'
            ],
            [
                'rule' => array('call', 'checkAlias')
            ]
        ]
    );

    public function checkAlias($value)
    {
        try {
            $model = model($value);
        } catch (\Exception $e) {
            return '模型不存在';
        }
        if (isset($model->form['user_id'])) {
            return true;
        }
        return $value . '模型文件form中必须存在user_id字段';
    }
}