<?php
namespace app\run\controller;

use app\common\controller\Run;

/**
* @name 图片位置
* @powerset true
*/
class Ad extends Run
{
    protected function initialize(){
        call_user_func(['parent', __FUNCTION__]);
    }

    /**
    * 列表
    * @powerset true
    */
    public function lists()
    {
        $this->local['list_fields'] = [
            'title',
            'image',
            'mobile_image',
            'is_verify',
            'created',
            'list_order'
        ];
        $this->local['sortable'] = true;
        call_user_func(['parent', __FUNCTION__]);
    }

    /**
     * @name 新增
     * @powerset true
     */
    public function create()
    {
        $this->getAdPosition();
        call_user_func(['parent', __FUNCTION__]);
    }

    /**
     * @name 修改
     * @powerset true
     */
    public function modify()
    {
        $ad_position_id = $this->mdl->where('id', '=', intval($this->args['id']))->value('ad_position_id');
        $this->getAdPosition($ad_position_id);
        call_user_func(['parent', __FUNCTION__]);
    }

    protected function getAdPosition($ad_position_id = null)
    {
        if (!$ad_position_id) {
            $ad_position_id = $this->args['parent_id'];
        }
        if (!$ad_position_id) {
            $ad_position_id = $this->args['ad_position_id'];
        }
        if (!$ad_position_id) {
            return $this->message('error', '缺少广告位ID');
        }
        $this->loadModel('AdPosition');
        $pos_data  = $this->AdPosition->get($ad_position_id);

        if ($pos_data['is_text']) {
            $this->mdl->form['content']['elem'] = 'textarea';
        }
        if ($pos_data['is_thumb']) {
            $this->mdl->form['image']['image']['thumb'] = [
                'field' => 'thumb',
                'width' => $pos_data['width'],
                'height' => $pos_data['height'],
                'method' => 3
            ];
            $this->mdl->form['mobile_image']['image']['thumb'] = [
                'field' => 'mobile_thumb',
                'width' => $pos_data['mobile_width'],
                'height' => $pos_data['mobile_height'],
                'method' => 3
            ];
        } else {
            $this->mdl->form['image']['image']['resize'] = [
                'width' => $pos_data['width'],
                'height' => $pos_data['height'],
                'method' => 3
            ];
            $this->mdl->form['mobile_image']['image']['resize'] = [
                'width' => $pos_data['mobile_width'],
                'height' => $pos_data['mobile_height'],
                'method' => 3
            ];
        }
    }
}
