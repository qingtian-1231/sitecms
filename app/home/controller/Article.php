<?php
namespace app\home\controller;

use app\common\controller\Home;
use woo\utility\Hash;

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
        if ($this->isMobile && $this->assign->menu_data['list_style'] === 'show_case_jiugu_list1') {
            foreach ($this->assign->list as $key =>$item) {
                if (mb_strlen($item['title']) > 15) {
                    $this->assign->list[$key]['title'] = mb_substr($item['title'], 0, 15) . '...';
                }
            }
        }
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

    public function ajax_select_work() {
        if ($this->request->isAjax()) {
            $data = null;
            $artical = $this->loadModel('Article');
            $params = $this->request->param();

            if (!empty($params['title'])) {
                $where = [
                    ['is_verify', '=', 1],
                    ['menu_id', '=', (int) $params['menu_id']],
                    ['title', 'LIKE', '%' . $params['title'] . '%']
                ];
                $order = [
                    'list_order' => 'DESC',
                    'id' => 'DESC',
                ];
                $result = $artical->getPageList([
                    'where' => $where,
                    'order' => $order,
                    'field' => ['id'],
                    'limit' => 5,
                    'paginate' => []
                ]);
                $idList = Hash::combine($result['list'], '{n}.id', '{n}.id');

                $data = $artical
                    ->where('id', 'IN', $idList)
                    ->order($order)
                    ->field([])
                    ->select()
                    ->toArray();

                $output = '';

                foreach ($data as $item) {
                    $output .= '<li>
        <div class="layui-card">
            <div class="layui-card-header">' . $item['title'] . '</div>
            <div class="layui-card-body">
                ' . $item['content'] . '
                <div class="row-button">
                    <a class="layui-btn layui-btn-radius" href="' . menu_link(37) . '" type="button">马上申请</a>
                </div>
            </div>
        </div>
    </li>';
                }

                return $this->ajax('success','查询到数据', $output);
            }
            else {
                return $this->ajax('error', '你搜索时没有输入字段', $data);
            }
        }
    }
}
