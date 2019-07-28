<?php
namespace app\run\controller;

use app\common\controller\Run;

class Setting extends Run
{
    protected function initialize()
    { 
        call_user_func(array('parent',__FUNCTION__));
    }
    
    public function lists()
    {
        $this->local['filter'] = [
            'title',
            'vari',
            'value'
        ];
        $this->local['list_fields'] = array(
            'setting_group_id',
            'title',
            'value'=>'call.show_value',
            'type',
            'vari',
            'options'=>0
        );
        $this->addItemAction('set');
        $this->local['order'] = array('id' => 'ASC');
        if (!config('app_debug')) {
            $this->local['item_actions']['modify'] = false;
            $this->local['item_actions']['delete'] = false;
            $this->local['actions']['batch_delete'] = false;
        }
        
        call_user_func(array('parent', __FUNCTION__));
    }
    
    /**
    * 设置
    */
    public function set()
    {
        $id=$this->args['id'];
		if (empty($id)) {
            return $this->message('error','缺少参数:ID');
		}
        $data   = $this->mdl->find($id);
        if (empty($data)) {
            return $this->message('error','需要设置的数据不存在');
        }
        $data = $data->toArray();
        
        if ($this->request->isPost()) {
            helper('Form')->data[$this->m]['id'] = $id;
            helper('Form')->data[$this->m]['type'] = $data['type'];
            $result  = $this->mdl->isValidate(false)->modifyData($id, helper('Form')->data[$this->m]);
            $this->mdl->writeToFile() ;
            return $this->fetch = 'set_success';            
        } else {
            helper('Form')->data[$this->m]   =   $data ;
            $this->assign->data = helper('Form')->data;
        }
        $this->assign->addJs('artTemplate.js');
		$this->assign->addJs('json2.js');
        $this->assign->addCss('/files/colorpicker/css/colorpicker.css');
        $this->assign->addJs('/files/colorpicker/js/colorpicker.js');        
        return $this->fetch = true;
    }
}
