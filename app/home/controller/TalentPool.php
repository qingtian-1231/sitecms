<?php
namespace app\home\controller;

use app\common\controller\Home;

class TalentPool extends Home
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
            if (is_array(helper('Form')->data[$this->m]['education'])) {
                $education = serialize(helper('Form')->data[$this->m]['education']);
            }
            if (is_array(helper('Form')->data[$this->m]['experience'])) {
                $experience = serialize(helper('Form')->data[$this->m]['experience']);
            }
            helper('Form')->data[$this->m]['experience'] = $experience;
            helper('Form')->data[$this->m]['education'] = $education;
            helper('Form')->data[$this->m]['name'] = htmlspecialchars(helper('Form')->data[$this->m]['name']);
            helper('Form')->data[$this->m]['gender'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['gender']));
            helper('Form')->data[$this->m]['birthplace'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['birthplace']));
            helper('Form')->data[$this->m]['identification'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['identification']));
            helper('Form')->data[$this->m]['address'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['address']));
            helper('Form')->data[$this->m]['graduated_school'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['graduated_school']));
            helper('Form')->data[$this->m]['phone'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['phone']));
            helper('Form')->data[$this->m]['highest_education'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['highest_education']));
            helper('Form')->data[$this->m]['interested_position'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['interested_position']));
            helper('Form')->data[$this->m]['question_what'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['question_what']));
            helper('Form')->data[$this->m]['menu_id'] = intval($this->args['menu_id']);

            $rslt  = $this->mdl->isUpdate(false)->save(helper('Form')->data[$this->m]);
            if ($rslt) {
                return $this->message('success','恭喜你！申请人才储备成功！');
            } else {
                $this->assign->error = $this->mdl->getError();
            }
        }

        call_user_func(array('parent', __FUNCTION__));
    }
}
