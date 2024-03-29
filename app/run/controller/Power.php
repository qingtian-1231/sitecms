<?php
namespace app\run\controller;

/**
 * 权限
 * @powerset true
 */
use app\common\controller\Run;

class Power extends Run
{
    //初始化 需要调父级方法
    protected function initialize()
    {
        call_user_func(['parent', __FUNCTION__]);
    }


    public function lists()
    {
        return $this->message('error', '禁止执行该方法');
    }

    /**
    * 授权移除
    */
    public function remove()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式');
        }
        $foreign_id  = intval($this->args['foreign']);
        if ($foreign_id <= 0) {
            return $this->ajax('error', '缺少参数：foreign');
        }
        $power = $this->mdl->where([
            ['type', '=', 'user'],
            ['foreign_id', '=', $foreign_id]
        ])->find();
        if (!empty($power)) {
            $power->delete();
        }
        return $this->ajax('error', '用户授权记录删除成功');
    }

    /**
     * 路由授权
     * @powerset true
     */
    public function content()
    {
        $this->setTitle("用户授权", 'operation');
        $this->loadModel('UserGroup');
        $this->loadModel('User');
        $list = $this->UserGroup->with(['User'])->where('is_admin', '=', 1)->select()->toArray();
        $this->assign->list = $list;

        if ($this->request->isPost()) {
            $error  = [];
            helper('Form')->data[$this->m]['type'] = trim(helper('Form')->data[$this->m]['type']);
            helper('Form')->data[$this->m]['foreign_id'] = intval(helper('Form')->data[$this->m]['foreign_id']);

            if (isset(helper('Form')->data[$this->m]['content'])) {
                $powers = helper('Form')->data[$this->m]['content'];
                $this->assign->powers = $powers;
            } else {
                helper('Form')->data[$this->m]['content'] = $this->assign->powers = [];
            }
            if (!helper('Form')->data[$this->m]['type']) {
                $error[] = '请选择授权类型';
            } else {
                if (!in_array(helper('Form')->data[$this->m]['type'], ['user', 'usergroup'])) {
                    $error[] = '授权对象类型不合法';
                }
            }
            if (helper('Form')->data[$this->m]['foreign_id'] <= 0) {
                $error[] = '请选择授权对象';
            }

            if (empty($error)) {
                $this->assignDefault('user_id', $this->login['id']);
                $data = helper('Form')->data[$this->m];

                //如果只有一个用户，一定要可以要可以设置权限
                if (count($list) == 1 && count($list[0]['User']) == 1 && !in_array('power::content', $data['content'])) {
                    $data['content'][] = 'power::content';
                }

                $exists_id = $this->mdl->where([
                    ['type', '=', $data['type']],
                    ['foreign_id', '=', $data['foreign_id']]
                ])->value('id');
                if ($exists_id) {
                    $this->mdl->isUpdate(true)->save($data, ['id' => $exists_id]);
                } else {
                    $this->mdl->isUpdate(false)->save($data);
                }

                return $this->message('success', '权限授权成功');
            } else {
                $this->assign->error = $error[0];
            }

        } else {
            if (isset($this->args['type'])) {
                helper('Form')->data[$this->m]['type'] = $this->args['type'];
            }

            if (isset($this->args['foreign'])) {
                helper('Form')->data[$this->m]['foreign_id'] = $this->args['foreign'];
            }

            if (isset($this->args['foreign']) && isset($this->args['type'])) {
                if ($this->args['type'] == 'usergroup') {
                    $powers = $this->mdl->where([
                        ['type', '=', 'usergroup'],
                        ['foreign_id', '=', intval($this->args['foreign'])]
                    ])->find();
                    if (!empty($powers)) {
                        $powers = $powers->toArray();
                        $powers['content'] = unserialize(@gzuncompress($powers['content']));
                        $this->assign->powers = $powers['content'];
                    } else {
                        $this->assign->powers = [];
                    }
                }

                if ($this->args['type'] == 'user') {
                    $powers = $this->mdl->where([
                        ['type', '=', 'user'],
                        ['foreign_id', '=', intval($this->args['foreign'])]
                    ])->find();
                    if ($powers) {
                        $powers = $powers->toArray();
                        $powers['content'] = unserialize(@gzuncompress($powers['content']));
                        $this->assign->powers = $powers['content'];
                    } else {
                        $user_group_id = $this->User->where('id', '=', intval($this->args['foreign']))->value('user_group_id');
                        if ($user_group_id) {
                            $powers = $this->mdl->where([
                                ['type', '=', 'usergroup'],
                                ['foreign_id', '=', intval($user_group_id)]
                            ])->find();
                            if ($powers) {
                                $powers = $powers->toArray();
                                $powers['content'] = unserialize(@gzuncompress($powers['content']));
                                $this->assign->powers = $powers['content'];
                            } else {
                                $this->assign->powers = [];
                            }
                        }
                    }
                }

            }
        }
        $this->fetch = true;
    }

    //添加
    public function create()
    {
        $this->message('error', '禁止执行该方法');
    }

    //修改
    public function modify()
    {
        $this->message('error', '禁止执行该方法');
    }

    //删除
    public function delete()
    {
        $this->message('error', '禁止执行该方法');
    }
}
