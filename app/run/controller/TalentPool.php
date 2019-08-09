<?php
namespace app\run\controller;

use app\common\controller\Run;

class TalentPool extends Run
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
            'gender',
            'graduated_school',
            'highest_education',
            'is_finish',
            'created',
        ];

        // 列表字段
        $this->local['list_fields'] = [
            'menu_id',
            'name',
            'gender',
            'birthplace',
            'identification',
            'address',
            'graduated_school',
            'phone',
            'highest_education',
            'interested_position',
            'is_finish',
            'created',
            // 其他列表字段
        ];

        // 添加额外条件
        //$this->local['where'][] = ['字段', '=', '值'];

        call_user_func(['parent', __FUNCTION__]);
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
