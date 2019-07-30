<?php
namespace app\home\controller;

use app\common\controller\Home;

class Suppliercertification extends Home
{
    /**
    * 初始化
    */
    protected function initialize()
    {
        call_user_func(['parent', __FUNCTION__]);
    }

    public function show()
    {
        if ($this->request->isPost() && helper('Form')->checkToken()) {

            if(captcha_check(input('post.captcha'))){
                helper('Form')->data[$this->m]['truename'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['truename']));
                helper('Form')->data[$this->m]['mobile']   = trim(htmlspecialchars(helper('Form')->data[$this->m]['mobile']));
                helper('Form')->data[$this->m]['content'] = htmlspecialchars(helper('Form')->data[$this->m]['content']);
                helper('Form')->data[$this->m]['title']    = /*helper('Form')->data[$this->m]['title'] ? */trim(htmlspecialchars(helper('Form')->data[$this->m]['title']))/*:menu($this->args['menu_id'],'title')*/;
                helper('Form')->data[$this->m]['user_id']  = helper('Auth')->user('id');
                helper('Form')->data[$this->m]['ip']       = $this->request->ip();
                helper('Form')->data[$this->m]['menu_id']  = intval($this->args['menu_id']);

                $rslt  = $this->mdl->isUpdate(false)->save(helper('Form')->data[$this->m]);
                if ($rslt) {
                    return $this->message('success','恭喜你！留言成功！');
                } else {
                    $this->assign->error = $this->mdl->getError();
                }
            }else{
                $this->assign->error[] = '验证码填写错误';
            }
        }

        call_user_func(array('parent', __FUNCTION__));
    }

    public function view()
    {
        call_user_func(array('parent', __FUNCTION__));
    }
}
