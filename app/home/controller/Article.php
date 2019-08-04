<?php
namespace app\home\controller;

use app\common\controller\Home;

class Article extends Home
{
    public function initialize()
    {
        call_user_func(array('parent', __FUNCTION__));
    }

    public function show()
    {
        ##如果列表页也需要将每个文章的关联图片查询出来打开注释即可
        /*
        if (in_array($this->m, json_decode(setting('use_picture_model'), true))) {
            $this->local['with']  = [
                'ArticlePicture' => [
                    'where' => [
                        'is_verify' => 1
                    ],
                    'order' => [
                        'list_order' => 'DESC',
                        'id' => 'ASC'
                    ]
                ]
            ];
        }
        */
        call_user_func(array('parent', __FUNCTION__));
    }

    public function view()
    {
        if (in_array($this->m, json_decode(setting('use_picture_model'), true))) {
            $this->local['with']  = [
                'ArticlePicture' => [
                    'where' => [
                        'is_verify' => 1
                    ],
                    'order' => [
                        'list_order' => 'DESC',
                        'id' => 'ASC'
                    ]
                ]
            ];
        }
        // ck编辑器内容需要做分页  通过$this->local['page_field'] = '字段';来指定分页字段
        $this->local['page_field'] = 'content';
        call_user_func(array('parent', __FUNCTION__));

        if ($this->assign->data['video']) {
            $this->assign->addCss('/files/ckin/css/ckin.min.css');
            $this->assign->addJs('/files/ckin/js/ckin.min', true);
        }
    }
}
