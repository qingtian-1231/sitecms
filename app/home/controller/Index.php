<?php
namespace app\home\controller;

use app\common\controller\Home;
use woo\utility\Hash;

class Index extends Home
{
    public function initialize()
    {
        call_user_func(array('parent', __FUNCTION__));
    }
    //首页
    public function index()
    {
        $product_service_id = '1';
        $all_menu_list = menu('list');
        foreach ($all_menu_list as $menu_item) {
            if ($menu_item['title'] === '产品与服务') {
                $product_service_id = $menu_item['id'];
                continue;
            }

            if ($menu_item['title'] === '首页地图导航') {
                $index_map_id = $menu_item['id'];
                continue;
            }

            if ($menu_item['title'] === '经典案例') {
                $typical_case_id = $menu_item['id'];
            }

            if ($menu_item['title'] === '公司文化') {
                $company_calture_id = $menu_item['id'];
            }
        }

        $company_calture_page = $this->getPage($company_calture_id);
        $typical_case = $this->getArticle($typical_case_id, 1);
        $this->assign->mobile_typical_case = array_shift($typical_case);
        $this->assign->index_map_id = $index_map_id;
        $this->assign->product_service_info = menu($product_service_id);
        $this->assign->company_calture_page = $company_calture_page['content'];
        $this->assign->product_service_ids = menu('children', $product_service_id);


        $indexNews = [];
        $menu_ids = menu('children', 8);
        $indexNews['ids'] = $menu_ids;
        foreach ($menu_ids as $menu_id) {
            $indexNews['articals'][] = $this->getArticle($menu_id, 4);
        }

        $this->assign->indexNews = $indexNews;
        $this->fetch = true;
    }

    public function qrcode()
    {
        echo make_qrcode('https://www.eduaskcms.xin', false, '' , 'H', 10, 2, true, 100);
    }

    //引导页，需要在app/route中配置
    public function guide()
    {
        $this->fetch = true;
    }

    //网站地图
    public function sitemap()
    {
        $this->fetch = true;
    }

    protected function getArticle($menu_id, $limit) {
      $artical = $this->loadModel('Article');
      $where = [
        ['is_verify', '=', 1],
        ['menu_id', '=', $menu_id]
      ];
      $order = [
        'list_order' => 'DESC',
        'id' => 'DESC',
      ];
      $result = $artical->getPageList([
        'where' => $where,
        'order' => $order,
        'field' => ['id'],
        'limit' => $limit,
        'paginate' => []
      ]);
      $idList = Hash::combine($result['list'], '{n}.id', '{n}.id');

      $list = $artical
        ->where('id', 'IN', $idList)
        ->order($order)
        ->field([])
        ->select()
        ->toArray();

      return $list;
    }

    protected function getPage($menu_id) {
        $page = $this->loadModel('Page');
        $where = [
            ['is_verify', '=', 1],
            ['menu_id', '=', $menu_id]
        ];
        $order = [
            'list_order' => 'DESC',
            'id' => 'DESC',
        ];
        $result = $page
            ->where($where)
            ->order($order)
            ->field([])
            ->find()
            ->toArray();

        return $result;
    }
    /*
    //忘记密码可以访问该方法来获取加密字符串 domain/index/getpwd/pwd/需加密的字符串
    public function getpwd()
    {
        echo helper('Auth')->password($this->args['pwd']);
    }
    */
}
