<?php
namespace app\run\controller;

use app\common\controller\Run;

class MediaContact extends Run
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
        // 搜索字段
        $this->local['filter'] = [
            'name',
            'media_name',
            'contact_number',
            'position',
            'is_finish',
            'created',
        ];

        // 列表字段
        $this->local['list_fields'] = [
            'menu_id',
            'name',
            'media_name',
            'contact_number',
            'position',
            'is_finish',
            'created',
            'modified',
        ];
        $this->local['exportable'] = true;
        // 添加额外条件
        //$this->local['where'][] = ['字段', '=', '值'];

        call_user_func(['parent', __FUNCTION__]);
    }

    public function export()
    {
        $this->local['filter'] = [
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
