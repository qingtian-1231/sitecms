<?php

namespace app\http;

class Callback
{
    protected $ts;//  控制器对象
    protected $m;//  当前模块
    protected $c;//  当前控制名
    protected $a;//  当前方法名
    protected $ca; //  控制器::方法名
    public $appBeforeAction = NULL;
    public $appAfterAction = NULL;

    public function __construct($controller)
    {
        $this->ts = $controller;
        $this->m = $this->ts->params['module'];
        $this->c = $this->ts->params['controller'];
        $this->a = $this->ts->params['action'];
        $this->ca = $this->c . '::' . $this->a;

        $appBeforeAction = 'appBefore' .parse_name($this->m, 1);
        $appAfterAction = 'appAfter' . parse_name($this->m, 1);
        if (is_callable([$this, $appBeforeAction])) {
            $this->appBeforeAction = $appBeforeAction;
        }
        if (is_callable([$this, $appAfterAction])) {
            $this->appAfterAction = $appAfterAction;
        }
    }
    //  每次访问URL方法之前（不管任何模块）都会执行
    //  在这里面可以写入自己的通用逻辑代码，而又不需要动核心App控制器代码
    //  这些方法里面的代码写法和控制器里面一样，只是用$this->ts 表示 控制器的$this
    public function appBeforeEach()
    {

    }

    //  每次访问URL方法后、渲染页面之前（不管任何模块，这个时候所有需要assign到页面的数据都已经准备好）都会执行
    public function appAfterEach()
    {

    }

    //  每次访问URL方法之前（仅限Home模块方法）都会执行
    public function appBeforeHome()
    {
        //  前台每个页面都需要家长的css 和js
        $this->ts->assign->addJs([
            'jquery-1.11.1.min',
            '/files/layui/layui.js',
            'common',
            'custom',
        ]);

        $this->ts->assign->addCss([
            '/files/layui/css/layui.css',
            'global.css',
            'animate.css',
            '/files/awesome-4.7.0/css/font-awesome.min.css'
        ]);

        $this->ts->assign->is_response = true;
        $this->ts->assign->is_favicon = true;
        $this->ts->assign->top_id = 0;

        /**
         * //  手动查询栏目数据模板
        $this->ts->getMenuData(栏目ID(integer), 查询条数(integer), [
            'family' => true,//  true 是否查询下级栏目；false 只查询当前栏目
            'type' => 'select',//  select find
            'with' => ['Menu'],//  关联模型
            'where' => [],//  条件
            'field' => [],//  字段
            'order' => []//  排序
        ]);
        //  查询以后，在这里可以使用 $this->ts->assign->query_data[栏目ID] 获取到数据
        //  查询以后，在试图中可以使用$query_data[栏目ID]获取到数据
        */

        //  可以类似于这样 单独指定不同控制器不同方法执行的代码
        switch ($this->ca) {
            case 'Article::show':
                //  文字列表页
                break;
            case 'Product::show':
                //  产品列表页
                break;
            case 'Product::view':
                //  产品详情页
                break;
        }

        if ($this->ca == 'Index::index') {
            //  首页页面
            $this->ts->assign->is_index = true;
            $this->ts->assign->addCss(['index.css']);
            $this->ts->assign->top_id = 0;

        } else {
            //  内页页面（非首页）
            $this->ts->assign->is_index = false;
            $this->ts->assign->addCss(['insider.css', 'change.css']);
        }
    }

    //  每次访问URL方法后、渲染页面之前（仅限Home模块方法，这个时候所有需要assign到页面的数据都已经准备好）都会执行
    public function appAfterHome()
    {
        if ($this->ca == 'Index::index') {
            //  首页广告位
            $this->ts->getAdData('index_banner', 0);
            $this->ts->assign->ad_var = 'index_banner';

        } else {
            //  内页广告位
            if (setting('is_menu_position')) {
                //  如果你希望每个栏目的广告都不一样，可以使用或参考下面代码（要求：不同栏目建一个广告位，广告为变量是：menu_栏目id）
                $path  = array_reverse((array)$this->ts->assign->path) ;
                $insiderAd = array();
                $this->ts->assign->ad_var = 'insider_banner';
                foreach ($path as $child_id) {
                    $this->ts->getAdData('menu_' . $child_id,0);
                    if ($this->ts->assign->ad['menu_' . $child_id]['Ad']) {
                        $this->ts->assign->ad_var = 'menu_' . $child_id;
                        break ;
                    }
                }
                if (empty($this->ts->assign->ad[$c->assign->ad_var]['Ad'])) {
                    $this->ts->getAdData('insider_banner', 0);
                }
            } else {
                $this->ts->getAdData('insider_banner', 0);
                $this->ts->assign->ad_var = 'insider_banner';
            }
        }

        $menu_data = $this->ts->assign->menu_data;
        if (!empty($menu_data['parent_id']) && ($menu_data['parent_id'] !== 1 && $menu_data['parent_id'])) {
            $family = explode(',', $menu_data['family']);
            $menu_parent_id = $family[2];
            $second_menu_parent_id = isset($family[3]) ? $family[3] : 0;
            $parent_data = menu($menu_parent_id);
            $second_parent_data = menu($second_menu_parent_id);

            $ad = &$this->ts->assign->ad;

            if (!empty($parent_data['image'])) {
                $ad['insider_banner']['Ad'][0]['image'] = $parent_data['image'];
                if ($this->ts->isMobile && setting('is_use_wap') && !empty($parent_data['mobile_image'])) {
                    $ad['insider_banner']['Ad'][0]['image'] = $parent_data['mobile_image'];
                }
            }

            if ($second_parent_data && !empty($second_parent_data['image'])) {
                $ad['insider_banner']['Ad'][0]['image'] = $second_parent_data['image'];
                if ($this->ts->isMobile && setting('is_use_wap') && !empty($second_parent_data['mobile_image'])) {
                    $ad['insider_banner']['Ad'][0]['image'] = $second_parent_data['mobile_image'];
                }
            }
            $ad['insider_banner']['Ad'][0] = array_shift($ad['insider_banner']['Ad']);
        }

        /**
         * 获得固定的名称为联系我们的栏目下所有菜单id.
         *
         */
        $about_us_id = '1';
        $all_menu_list = menu('list');
        foreach ($all_menu_list as $menu_item) {
            if ($menu_item['title'] === '联系我们') {
                $about_us_id = $menu_item['id'];
                break;
            }
        }

        $about_us_children_menus = menu('children', $about_us_id);
        if (!$this->ts->isMobile) {
            foreach ($about_us_children_menus as $key => $children_menu_id) {
                $menu_item = menu($children_menu_id);
                if (isset($menu_item['is_map']) && !$menu_item['is_map']) {
                    unset($about_us_children_menus[$key]);
                }
            }
        }
        $this->ts->assign->all_about_us_ids = [
            'about_us_id' => $about_us_id,
            'about_us_children_id' => $about_us_children_menus,
        ];

        //手机端首页内容
        if ($this->ts->isMobile && setting('is_use_wap')) {
            $index_menus = [];
            foreach ($all_menu_list as $menu_item) {
                if ($menu_item['title'] === '解决方案') {
                    $index_menus[] = $this->processMobileContentForIndex($menu_item);
                }
                if ($menu_item['title'] === '工作机会') {
                    $index_menus[] = $this->processMobileContentForIndex($menu_item);
                }
                if ($menu_item['title'] === '关于我们') {
                    $index_menus[] = $this->processMobileContentForIndex($menu_item);
                }
            }
            $this->ts->assign->mobile_index_menus = $index_menus;
        }

        // 重写获取side_menu的方法，在每一个页面都要获得这个页面所属和包含的所有菜单结构
        if (property_exists($this->ts->assign, 'side_menu')) {
            $all_menu_children = menu('children');
            $family = explode(',', $this->ts->assign->menu_data['family']);
            $top_menu = $family[2];
            $menus = (array) $all_menu_children[$top_menu];

            //从侧边二级菜单中删除地图导航关闭的菜单
            if (!$this->ts->isMobile) {
                foreach ($menus as $key => $menu_id) {
                    $menu_item = menu($menu_id);
                    if (isset($menu_item['is_map']) && !$menu_item['is_map']) {
                        unset($menus[$key]);
                    }
                }
            }
            foreach ($menus as $menu_key => $second_menu_id) {
                if (isset($all_menu_children[$second_menu_id]) &&
                    !empty($all_menu_children[$second_menu_id])
                ) {
                    $menus[$menu_key] = [
                        'parent_menu' => $second_menu_id,
                        'second_menu' => $all_menu_children[$second_menu_id],
                    ];
                }
            }
            $this->ts->assign->side_menu['top_menu'] = $top_menu;
            $this->ts->assign->side_menu['menus'] = $menus;
        }

        //  可以类似于这样 单独指定不同控制器不同方法执行的代码
        switch ($this->ca) {
          case 'Article::search':
            $this->ts->assign->menu_data['title'] = '搜索结果列表';
            break;

          case 'Page::view':
              $menu_data = $this->ts->assign->menu_data;
              $page_type = $this->ts->assign->mdl;
              if ($menu_data['title'] === '首页地图导航' && $page_type === 'Page') {
                  $this->ts->assign->full_image_page = true;
              }
            break;

        }
    }

    //  每次访问URL方法之前（仅限Run模块方法）都会执行
    public function appBeforeRun()
    {
        $this->ts->assign->addJs([
            'jquery-3.2.1.min.js',
            '/files/layui/layui.js',
            'admin/global.js'
        ]);

        $this->ts->assign->addCss([
            '/files/layui/css/layui.css',
            'admin/global.css',
            'admin/animate.css',
            '/files/awesome-4.7.0/css/font-awesome.min.css'
        ]);

        $this->ts->assign->is_response = true;
        $this->ts->assign->is_favicon = true;

    }

    //  每次访问URL方法后、渲染页面之前（仅限Run模块方法，这个时候所有需要assign到页面的数据都已经准备好）都会执行
    public function appAfterRun()
    {

        switch ($this->ca) {
            case 'TalentPool::detail':
                $experienceOutput = $this->processSerializeFields($this->ts->assign->data['experience'], 'experience');
                $educationOutput = $this->processSerializeFields($this->ts->assign->data['education'], 'education');
                $this->ts->assign->data['experience'] = $experienceOutput;
                $this->ts->assign->data['education'] = $educationOutput;
                //  人才储备详情页
                break;
            case 'TalentPool::modify':
                //  人才储备编辑页
                break;
        }
    }

    //  每次访问URL方法之前（仅限Run模块方法）都会执行
    public function appBeforeManage()
    {
        $this->ts->assign->addJs([
            'jquery-1.11.1.min',
            '/files/layui/layui.js',
            'common'
        ]);


        $this->ts->assign->addCss([
            '/files/layui/css/layui.css',
            'global.css',
            'manage/manage.css',
            'insider.css',
            'change.css',
            'animate.css',
            '/files/awesome-4.7.0/css/font-awesome.min.css'
        ]);
    }

    //  每次访问URL方法后、渲染页面之前（仅限Run模块方法，这个时候所有需要assign到页面的数据都已经准备好）都会执行
    public function appAfterManage()
    {

    }

    protected function processSerializeFields($field, $fieldName)
    {
        $output = '';
        $fieldArray = unserialize($field);
        $output = '<table class="layui-table">';
        $output .= '<thread>';
        $output .= '<tr>';
        $output .= '<th>开始时间</th>';
        $output .= '<th>结束时间</th>';
        if ($fieldName === 'experience') {
            $output .= '<th>雇用单位</th>';
            $output .= '<th>岗位</th>';
        }
        elseif ($fieldName === 'education') {
            $output .= '<th>学校</th>';
            $output .= '<th>专业</th>';
        }

        $output .= '</tr>';
        $output .= '</thread>';
        $output .= '<tbody>';
        foreach ($fieldArray as $item) {
            $output .= '<tr>';
            foreach ($item as $value) {
                $output .= '<td>' . $value . '</td>';
            }
            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';

        return $output;
    }

    protected function processMobileContentForIndex($menu_item) {
        $all_menu_children = menu('children');
        $index_menu = NULL;
        if (isset($all_menu_children[$menu_item['id']]) &&
            !empty($all_menu_children[$menu_item['id']])) {
            $index_menu = [
                'parent_menu' => $menu_item['id'],
                'second_menu' => [],
            ];
            foreach ($all_menu_children[$menu_item['id']] as $third_menu_key => $third_menu_id) {
                if (isset($all_menu_children[$third_menu_id]) &&
                !empty($all_menu_children[$third_menu_id])) {
                    $index_menu['second_menu'][] = [
                        'third_parent_menu' => $third_menu_id,
                        'third_menu' => $all_menu_children[$third_menu_id],
                    ];
                } else {
                    $index_menu = [
                        'parent_menu' => $menu_item['id'],
                        'second_menu' => $all_menu_children[$menu_item['id']],
                    ];
                }
            }
        }
        else {
            $index_menu = $menu_item['id'];
        }

        return $index_menu;
    }
    //  以后扩展模块，每个模块都支持添加这样的2个方法用来表示 执行URL指定方法前和后的 动作
}
