<?php
namespace app\run\controller;

use app\common\controller\Run;

class SupplierCertification extends Run
{
    /**
    * 初始化
    */
    protected function initialize()
    {
        call_user_func(['parent', __FUNCTION__]);
    }

    /**
    * 列表
    */
    public function lists()
    {
        $this->local['filter'] = [
            'name',
            'company_name',
            'contact_number',
            'company_type'
        ];
        $this->local['list_fields'] = [
            'menu_id',
            'name',
            'company_name',
            'position',
            'contact_number',
            'company_type',
            'is_finish',
            'created',
            'modified',
        ];
        //$this->local['actions']['create'] = false ;
        $this->local['exportable'] = true;
        $this->local['order'] = ['is_finish' => 'ASC', 'created' => 'DESC', 'id' => 'DESC'];

        call_user_func(['parent', __FUNCTION__]);
    }

    public function export()
    {
        $this->local['filter'] = [
            'name',
            'created',
            'is_finish'

        ];
        $this->local['list_fields'] = [
            /*
            'date' => [
                'callback' => function($val) {
                    return strtotime($val);
                }
            ]*/
        ];
        call_user_func(array('parent', __FUNCTION__));
    }

    /**
    * 添加
    */
    public function create()
    {
        return $this->message('error','该表单不允许后台添加');
    }

    /**
    * 修改
    */
    public function modify()
    {
        return $this->message('error','该表单不允许后台修改');
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
