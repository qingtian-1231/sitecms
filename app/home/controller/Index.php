<?php
namespace app\home\controller;

use app\common\controller\Home;

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
                break;
            }
        }
        $this->assign->product_service_ids = menu('children', $product_service_id);

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

    /*
    //忘记密码可以访问该方法来获取加密字符串 domain/index/getpwd/pwd/需加密的字符串
    public function getpwd()
    {
        echo helper('Auth')->password($this->args['pwd']);
    }
    */
}
