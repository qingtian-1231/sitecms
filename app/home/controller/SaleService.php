<?php
namespace app\home\controller;

use app\common\controller\Home;

class SaleService extends Home
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
            helper('Form')->data[$this->m]['feedback'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['feedback']));
            helper('Form')->data[$this->m]['name'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['name']));
            helper('Form')->data[$this->m]['contact_number'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['contact_number']));
            helper('Form')->data[$this->m]['menu_id'] = intval($this->args['menu_id']);

            $rslt  = $this->mdl->isUpdate(false)->save(helper('Form')->data[$this->m]);
            if ($rslt) {
                return $this->message('success','恭喜你！申请售后服务成功！');
            } else {
                $this->assign->error = $this->mdl->getError();
            }
        }

        call_user_func(array('parent', __FUNCTION__));
    }
}
