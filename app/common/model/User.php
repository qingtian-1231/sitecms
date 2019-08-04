<?php
namespace app\common\model;

use woo\helper\Auth;

class User extends App
{
    public $display = 'username';
    //public $parentModel = 'UserGroup';

    public $assoc = array(
        'UserGroup' => array(
            'type' => 'belongsTo'
        ),
        'UserGrade' => array(
            'type' => 'belongsTo'
        ),
        'Member' => array(
            'type' => 'hasOne',
            'deleteWith' => true,
        ),
        'Admin' => array(
            'type' => 'hasOne',
            'deleteWith' => true,
        ),
        'UserLogin' => 'hasMany'
    );

    public function initialize()
    {
        $this->form = array(
            'id' => array(
                'type' => 'integer',
                'name' => 'ID',
                'elem' => 'hidden',
            ),
            'username' => array(
                'type' => 'string',
                'name' => '用户名',
                'elem' => 'text'
            ),
            'password' => array(
                'type' => 'string',
                'name' => '密码',
                'elem' => 'password',
                'list' => 'password'
            ),
            'user_group_id' => array(
                'type' => 'integer',
                'name' => '用户组',
                'elem' => 'assoc_select',
                'list' => 'assoc',
                'foreign' => 'UserGroup.title',
                'prepare' => 'select'
            ),
            'user_grade_id' => array(
                'type' => 'integer',
                'name' => '用户等级',
                'elem' => 'format',
                'list' => 'assoc',
                'foreign' => 'UserGrade.title'
            ),
            'status' => array(
                'type' => 'string',
                'name' => '状态',
                'elem' => 'formSelects',
                'select4' => [
                    'type' => 'radio',
                    'skin' => 'default',
                    'height' => '36px'
                ],
                'options' => array(
                    'verified' => '正常',
                    'unverified' => '未激活',
                    'banned' => '已禁用'
                ),
                'list' => 'options',
            ),
            'email' => array(
                'type' => 'string',
                'name' => '认证邮箱',
                'elem' => 'text',
                'list' => 'show'
            ),
            'user_score_sum' => array(
                'type' => 'float',
                'name' => '积分',
                'elem' => 0,
                'list' => 'show'
            ),

            'logined' => array(
                'type' => 'datetime',
                'name' => '最后登录时间',
                'elem' => 0,
                'list' => 'datetime'
            ),
            'logined_ip' => array(
                'type' => 'string',
                'name' => '最后登录IP',
                'elem' => 0
            ),
            'logined_session_id' => array(
                'type' => 'string',
                'name' => '最后登录SESSION_ID',
                'elem' => 0
            ),
            'created' => array(
                'type' => 'datetime',
                'name' => '注册时间',
                'elem' => 0,
                'list' => 'datetime'
            )
        );
        call_user_func(array('parent', __FUNCTION__));
    }

    ##当前类验证规则
    protected $validate = array(
        'username' => array(
            'rule' => array('unique', 'user')
        ),
        'password' => array(
            array(
                'rule' => 'require',
                'on' => 'add'
            ),
            array(
                'rule' => array('length', 5, 16)
            )
        ),

        'email'=>array(
            'allowEmpty' => true,
            'rule' => 'email'

        ),

        'user_group_id' => array(
            'rule' => array('egt', 1),
            'message' => '请选择用户组'
        ),
        'status' => array(
            'rule' => 'require'
        ),

    );

    public function beforeInsertCall()
    {
        $rslt = call_user_func(array('parent', __FUNCTION__));
        if (isset($this['password']) && $this['password'] === '') {
            unset($this['password']);
        }
        if (isset($this['password']) && strlen($this['password']) < 32) {
            $this['password'] = Auth::password($this['password']);
        }
        return $rslt;
    }

    public function beforeUpdateCall()
    {
        $rslt = call_user_func(array('parent', __FUNCTION__));
        if (isset($this['password']) && $this['password'] === '') {
            unset($this['password']);
        }
        if (isset($this['password']) && strlen($this['password']) < 32) {
            $this['password'] = Auth::password($this['password']);
        }
        return $rslt;
    }

    public function afterInsertCall()
    {
        $rslt = call_user_func(array('parent', __FUNCTION__));
        if (!empty($this['user_group_id'])) {
            $user_group = model('UserGroup')->where('id', '=', $this['user_group_id'])->find();
            if (!empty($user_group)) {
                try {
                    $profile = model($user_group['alias']);
                    $data['user_id'] = $this['id'];
                    $profile->data([]);
                    $profile->isValidate(false)->isUpdate(false)->save($data);
                } catch (\Exception $e) {}
            }
        }
// 原代码
//        $data['user_id'] = $this['id'];
//        $data['nickname'] = '';
//        $memberModel = model('Member');
//        $memberModel->data([]);
//        $memberModel->isValidate(false)->isUpdate(false)->save($data);
        return $rslt;
    }

    public function afterUpdateCall()
    {
        $rslt = call_user_func(array('parent', __FUNCTION__));
        $this->checkUserGrade();
        return $rslt;
    }

    protected function checkUserGrade()
    {
        if (!isset($this->oldData['user_score_sum']) || !isset($this['user_score_sum']) || !isset($this['id'])) {
            return;
        }

        // 积分有改变
        if ($this->oldData['user_score_sum'] != $this['user_score_sum']) {
            $gradeModel = model('UserGrade');
            $grade = $gradeModel
                ->where([
                    ['min', '<=', $this['user_score_sum']],
                    ['max', '>', $this['user_score_sum']]
                ])
                ->find();
            if ($grade) {
                $gradeId = $grade['id'];
                $oldGradeId = isset($this['user_grade_id']) ? $this['user_grade_id'] : $this->where('id', '=', $this['id'])->value('user_grade_id');
                if ($oldGradeId != $gradeId) {
                    $this->isValidate(false)->where('id', '=', $this['id'])->setField('user_grade_id', $gradeId);
                    //$this->isValidate(false)->allowField(['id', 'user_grade_id'])->save(['user_grade_id' => $gradeId], ['id' => $this['id']]);
                }
            }
        }
    }
}
