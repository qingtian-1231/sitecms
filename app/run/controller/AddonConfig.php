<?php
namespace app\run\controller;

use app\common\controller\Run;

class AddonConfig extends Run
{
   
    
    public function lists()
    {
        $this->local['filter'] = [
            'title',
            'vari',
            'value'
        ];
        $this->local['list_fields'] = array(
            //'addon_id',
            'title',
            'value'=>'call.show_value',
            'type' => 0,
            'options' => 0
        );
        $this->addItemAction('set');
        
        if (empty($this->args['name'])) {
            return $this->message('error', '未指定插件', ['back' => false, 'close' => true]);
        }
        
        $this->loadModel('Addon');
        $addon_id = $this->Addon->where('name', '=', trim($this->args['name']))->value('id');
        if (empty($addon_id)) {
            return $this->message('error', '插件不存在，请先安装插件', ['back' => false, 'close' => true]);
        }
        $this->local['where'][] = ['addon_id', '=', $addon_id];
        
        
        $this->local['order'] = array('id' => 'ASC');
        $this->local['item_actions']['modify'] = false;
        $this->local['actions']['create'] = false;
        $this->local['item_actions']['delete'] = false;
        $this->local['item_actions']['detail'] = false;
        $this->local['actions']['batch_delete'] = false;
        
        call_user_func(array('parent', __FUNCTION__));
    }
    
    /**
     *设置
     */
    public function set()
    {
        $id=$this->args['id'];
		if (empty($id)) {
            return $this->_message('error','缺少参数:ID');
		}
        $data   = $this->mdl->find($id);
        if (empty($data)) {
            return $this->_message('error','需要设置的数据不存在');
        }
        $data = $data->toArray();
        
        if ($this->request->isPost()) {
            helper('Form')->data[$this->m]['id'] = $id;
            helper('Form')->data[$this->m]['type'] = $data['type'];
            $result  = $this->mdl->isValidate(false)->modifyData($id, helper('Form')->data[$this->m]);
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
    
    /**
    * 添加
    */
    public function create()
    {   // 设置默认值
        //$this->assignDefault('字段名', '默认值');
        // 字段白名单
        //$this->local['whiteList'] = ['id', 'title', ...允许添加的字段列表];   
        call_user_func(['parent', __FUNCTION__]);
    }
    
    /**
    * 修改
    */
    public function modify()
    {   
        // 字段白名单
        //$this->local['whiteList'] = ['id', 'title', ...允许修改的字段列表];
        call_user_func(['parent', __FUNCTION__]);
    } 
    
    /**
    * 删除
    */
    public function delete()
    {   
        // 设置额外的删除条件
        //$this->local['where'][] = ['is_verify', '=', 0];
        call_user_func(['parent', __FUNCTION__]);
    }  
}
