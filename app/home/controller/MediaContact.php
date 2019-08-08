<?php
namespace app\home\controller;

use app\common\controller\Home;

class MediaContact extends Home
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
            helper('Form')->data[$this->m]['question_who'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['question_who']));
            helper('Form')->data[$this->m]['question_what'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['question_what']));
            helper('Form')->data[$this->m]['question_when'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['question_when']));
            helper('Form')->data[$this->m]['name'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['name']));
            helper('Form')->data[$this->m]['media_name'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['media_name']));
            helper('Form')->data[$this->m]['position'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['position']));
            helper('Form')->data[$this->m]['contact_number'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['contact_number']));
            helper('Form')->data[$this->m]['menu_id'] = intval($this->args['menu_id']);

            $rslt  = $this->mdl->isUpdate(false)->save(helper('Form')->data[$this->m]);
            if ($rslt) {
                return $this->message('success','恭喜你！申请公益合作成功！');
            } else {
                $this->assign->error = $this->mdl->getError();
            }
        }

        call_user_func(array('parent', __FUNCTION__));
    }
}
