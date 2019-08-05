<?php
namespace app\home\controller;

use app\common\controller\Home;

class SupplierCertification extends Home
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
            helper('Form')->data[$this->m]['question_provide'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['question_provide']));
            helper('Form')->data[$this->m]['question_type'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['question_type']));
            helper('Form')->data[$this->m]['question_product'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['question_product']));
            helper('Form')->data[$this->m]['question_customer'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['question_customer']));
            helper('Form')->data[$this->m]['question_judge'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['question_judge']));
            helper('Form')->data[$this->m]['question_pay'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['question_pay']));
            helper('Form')->data[$this->m]['company_name'] = htmlspecialchars(helper('Form')->data[$this->m]['company_name']);
            helper('Form')->data[$this->m]['name'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['name']));
            helper('Form')->data[$this->m]['position'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['position']));
            helper('Form')->data[$this->m]['contact_number'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['contact_number']));
            helper('Form')->data[$this->m]['company_type'] = trim(htmlspecialchars(helper('Form')->data[$this->m]['company_type']));
            helper('Form')->data[$this->m]['ip'] = $this->request->ip();
            helper('Form')->data[$this->m]['menu_id'] = intval($this->args['menu_id']);

            $rslt  = $this->mdl->isUpdate(false)->save(helper('Form')->data[$this->m]);
            if ($rslt) {
                return $this->message('success','恭喜你！申请合格供应商认证成功！');
            } else {
                $this->assign->error = $this->mdl->getError();
            }
        }

        call_user_func(array('parent', __FUNCTION__));
    }

    public function view()
    {
        call_user_func(array('parent', __FUNCTION__));
    }
}
