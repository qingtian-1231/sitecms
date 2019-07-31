<?php

namespace app\run\controller;

use app\common\controller\Run;
use think\Db;

class Index extends Run
{
    public function index()
    {
        if (!adminmenu('nav')) {
            //$this->loadModel('AdminMenu');
            //$this->AdminMenu->writeToFile();
        }

        $this->assign->addCss('admin/index.css');
        $this->assign->addJs('admin/echarts.common.min');


        //统计图形数据准备
        // 官网 ：http://echarts.baidu.com
        // 具体配置： http://echarts.baidu.com/option.html  这里都按PHP数组配置 页面中会自动转换为JSON

        //一、 真实实现7天会员注册量统计
        $user_charts = \Cache::get('admin_user_charts');
        if (empty($user_charts)) {
            $now = time();
            for ($i = 6; $i >= 0; $i--) {
                $data[] = date('m-d', $now - $i * 86400);
                $value[] = Db::name('User')->where('created', 'LIKE', '%' . date('Y-m-d', $now - $i * 86400). '%')->count();
            }
            $user_charts = [
                'title' => [
                    'text' => '最近一周新增的用户量',
                    'left' => 'center',
                    'top' => '10px',
                    'textStyle' => [
                        'fontWeight' => 'normal',
                        'color' => '#333',
                        'fontSize' => '18px'
                    ]
                ],
                'grid' => [
                    'top' => '50px',
                    'bottom' => '40px',
                    'left' => '8%',
                    'right' => '8%',

                ],
                'tooltip' => [
                    'trigger' => 'item',
                    'formatter' => '{b}:{c}'
                ],
                'xAxis' => [
                    'type' => 'category',
                    'data' => $data,
                    'splitLine' => [
                        'show' => true,
                        'lineStyle' => [
                            'color' => '#e6e6e6'
                        ]
                    ],
                    'axisTick' => [
                        'show' => false
                    ],
                    'axisLine' => [
                        'lineStyle' => [
                            'color' => '#009688',
                            'width' => 2,
                            'opacity' => 0.8
                        ]
                    ],
                    'axisLabel' => [
                        'color' => '#333'
                    ]
                ],
                'yAxis' => [
                    'type' => 'value',
                    'axisTick' => [
                        'show' => false
                    ],
                    'splitLine' => [
                        'show' => true,
                        'lineStyle' => [
                            'color' => '#e6e6e6'
                        ]
                    ],
                    'axisLine' => [
                        'lineStyle' => [
                            'color' => '#009688',
                            'width' => 2,
                            'opacity' => 0.8
                        ]
                    ],
                    'axisLabel' => [
                        'color' => '#000'
                    ]
                ],
                'series' => [
                    [
                        'data' => $value,
                        'type' => 'line',
                        'smooth' => true,
                        'itemStyle' => [
                            'color' => '#009688'
                        ]
                    ]
                ]
            ];
            \Cache::set('admin_user_charts', $user_charts, 86400);
        }

        //二、虚拟统计图
        $demo1_charts = \Cache::get('admin_demo1_charts');
        if (empty($demo1_charts)) {
            $demo1_charts = [
                'title' => [
                    'text' => '内容统计图',
                    'left' => 'center',
                    'top' => '10px',
                    'textStyle' => [
                        'fontWeight' => 'normal',
                        'color' => '#333',
                        'fontSize' => '18px'
                    ]
                ],
                'grid' => [
                    'top' => '50px',
                    'bottom' => '40px',
                    'left' => '8%',
                    'right' => '8%',

                ],
                'tooltip' => [
                    'trigger' => 'item',
                    'formatter' => '{b}:{c}'
                ],
                'xAxis' => [
                    'type' => 'category',
                    'data' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    'splitLine' => [
                        'show' => true,
                        'lineStyle' => [
                            'color' => '#e6e6e6'
                        ]
                    ],
                    'axisTick' => [
                        'show' => false
                    ],
                    'axisLine' => [
                        'lineStyle' => [
                            'color' => '#009688',
                            'width' => 2,
                            'opacity' => 0.8
                        ]
                    ],
                    'axisLabel' => [
                        'color' => '#000'
                    ]
                ],
                'yAxis' => [
                    'type' => 'value',
                    'axisTick' => [
                        'show' => false
                    ],
                    'splitLine' => [
                        'show' => true,
                        'lineStyle' => [
                            'color' => '#e6e6e6'
                        ]
                    ],
                    'axisLine' => [
                        'lineStyle' => [
                            'color' => '#009688',
                            'width' => 2,
                            'opacity' => 0.8
                        ]
                    ],
                    'axisLabel' => [
                        'color' => '#000'
                    ]
                ],
                'series' => [
                    [
                        'data' => [120, 200, 150, 80, 70, 110, 130],
                        'type' => 'bar',
                        'smooth' => true,
                        'itemStyle' => [
                            'color' => '#009688'
                        ]
                    ]
                ]
            ];
            \Cache::set('admin_demo1_charts', $demo1_charts, 86400);
        }

        // 三、虚拟统计图
        $demo2_charts = \Cache::get('admin_demo2_charts');
        if (empty($demo2_charts)) {
            $demo2_charts = [
                'title' => [
                    'text' => '虚拟统计图二',
                    'left' => 'center',
                    'top' => '10px',
                    'textStyle' => [
                        'fontWeight' => 'normal',
                        'color' => '#333',
                        'fontSize' => '18px'
                    ]
                ],
                'grid' => [
                    'top' => '50px',
                    'bottom' => '40px',
                    'left' => '8%',
                    'right' => '8%',

                ],
                'tooltip' => [
                    'trigger' => 'item',
                    'formatter' => '{a}<br/>{b}:{c}({d}%)'
                ],
                'legend'=> [
                    'icon' => '',
                    'orient' => 'vertical',
                    'left' => '6%',
                    'top' => '50px',
                    'data' => [
                        [
                            'name' => '直接访问',
                            'icon' => 'circle'
                        ],
                        [
                            'name' => '邮件营销',
                            'icon' => 'circle'
                        ],
                        [
                            'name' => '联盟广告',
                            'icon' => 'circle'
                        ],
                        [
                            'name' => '视频广告',
                            'icon' => 'circle'
                        ],
                        [
                            'name' => '搜索引擎',
                            'icon' => 'circle'
                        ]
                    ]
                ],
                'series' => [
                    [
                        'name' => '访问来源',
                        'radius' => '55%',
                        'data' => [
                            ['value' => 335, 'name' => '直接访问', 'itemStyle' => ['color' => '#41cac0']],
                            ['value' => 310, 'name' => '邮件营销', 'itemStyle' => ['color' => '#78CD51']],
                            ['value' => 310, 'name' => '联盟广告', 'itemStyle' => ['color' => '#58c9f3']],
                            ['value' => 310, 'name' => '视频广告', 'itemStyle' => ['color' => '#ff6c60']],
                            ['value' => 310, 'name' => '搜索引擎', 'itemStyle' => ['color' => '#f1c500']]
                        ],
                        'type' => 'pie',
                        'itemStyle' => [
                            'emphasis' => [
                                 'shadowBlur' => 10,
                                 'shadowOffsetX' => 0,
                                 'shadowColor' => 'rgba(0, 0, 0, 0.5)'
                            ]
                        ]
                    ]
                ]
            ];
            \Cache::set('admin_demo2_charts', $demo2_charts, 86400);
        }

        //$charts 必须以索引数组方式 装你准备好的各项统计配置数组  你装几个就自动显示几个
        $charts[] = $user_charts;
        $charts[] = $demo1_charts;// 不显示虚拟数据自行注释
        $charts[] = $demo2_charts;// 不显示虚拟数据自行注释
        $this->assign->charts = $charts;

        //快捷方式数据
        $shortcut_list = \Cache::get('admin_shortcut_list');
        if (empty($shortcut_list)) {
            $this->loadModel('Shortcut');
            $shortcut_list  = $this->Shortcut
                                ->where([
                                    ['is_verify', '=', 1]
                                ])
                                ->order(['list_order' => 'DESC', 'id' => 'ASC'])
                                ->select()
                                ->toArray();

            \Cache::set('admin_shortcut_list', $shortcut_list, 86400);
        }
        $this->assign->shortcut_list = $shortcut_list;


        //环境信息获取
        $this->assign->dev['php_version'] = PHP_VERSION;
        if (@ini_get('file_uploads')) {
            $this->assign->dev['upload_max_filesize'] = ini_get('upload_max_filesize');
        } else {
            $this->assign->dev['upload_max_filesize'] = '禁止上传';
        }
        $this->assign->dev['php_os'] = PHP_OS;
        $softArr = explode('/',$_SERVER["SERVER_SOFTWARE"]) ;
        $this->assign->dev['server_software'] = array_shift($softArr);
        $this->assign->dev['server_name'] = gethostbyname($_SERVER['SERVER_NAME']);
        $rslt = Db::name('')->query('SELECT VERSION() AS `version`');
        $this->assign->dev['mysql_version'] = $rslt[0]['version'];
        if (extension_loaded('curl')) {
            $this->assign->dev['curl_extension'] = 'YES';
        } else {
            $this->assign->dev['curl_extension'] = 'NO';
        }

        if (extension_loaded('MBstring')) {
            $this->assign->dev['mbstring_extension'] = 'YES';
        } else {
            $this->assign->dev['mbstring_extension'] = 'NO';
        }

        if (extension_loaded('pdo')) {
            $this->assign->dev['pdo_extension'] = 'YES';
        } else {
            $this->assign->dev['pdo_extension'] = 'NO';
        }

        $this->assign->dev['max_execution_time'] = ini_get('max_execution_time') . 'S';

        //数据统计
        $count['article'] = Db::name('Article')->count();
        $count['product'] = Db::name('Product')->count();
        $count['user'] = Db::name('User')->count();
        $count['album'] = Db::name('Album')->count();
        $count['supplier_certification'] = Db::name('SupplierCertification')->where(['is_finish' => 0])->count();
        $count['feedback'] = Db::name('Feedback')->where(['is_finish' => 0])->count();
        $this->assign->count = $count ;

        cookie(['prefix' => 'think_', 'expire' => 3600]);
        $this->assign->is_lock_screen = cookie('?is_lock_screen') ? true : false;
        $this->assign->default_skin = cookie('?skin_name') ? cookie('skin_name') : '';


        // 左侧栏目权限使用
        $powers = helper('Auth')->getPowerList();
        $GLOBALS['static_power_list'] = $powers;
        if (in_array('all/all', (array)$powers)) {
            $this->assign->is_super_power = true;
        } else {
            $this->assign->is_super_power = false;
        }

        $this->assign->powers = array_keys((array)$powers);



        // 后台主题
        $theme = [
            [
                'name' => 'black',
                'title' => '黑色主题',
                'header_bg' => '#ffffff',
                'sider_bg' => '#20222A',
                'logo_bg' => 'transparent'
            ],
            [
                'name' => 'coffee',
                'title' => '啡色主题',
                'header_bg' => '#ffffff',
                'sider_bg' => '#2E241B',
                'logo_bg' => 'transparent'
            ],
            [
                'name' => 'purple-red',
                'title' => '紫红主题',
                'header_bg' => '#ffffff',
                'sider_bg' => '#50314F',
                'logo_bg' => 'transparent'
            ],
            [
                'name' => 'ocean',
                'title' => '海洋主题',
                'header_bg' => '#ffffff',
                'sider_bg' => '#344058',
                'logo_bg' => '#1E9FFF'
            ],
            [
                'name' => 'green',
                'title' => '绿色主题',
                'header_bg' => '#ffffff',
                'sider_bg' => '#3A3D49',
                'logo_bg' => '#2F9688'
            ],
            [
                'name' => 'yellow',
                'title' => '黄色主题',
                'header_bg' => '#ffffff',
                'sider_bg' => '#20222A',
                'logo_bg' => '#F78400'
            ],
            [
                'name' => 'red-yellow-header',
                'title' => '红黄全头',
                'header_bg' => '#F78400',
                'sider_bg' => '#28333E',
                'logo_bg' => '#AA3130'
            ],
            [
                'name' => 'ocean-header',
                'title' => '海洋全头',
                'header_bg' => '#1E9FFF',
                'sider_bg' => '#344058',
                'logo_bg' => '#0085E8'
            ],
            [
                'name' => 'classic-black-header',
                'title' => '深黑全头',
                'header_bg' => '#393D49',
                'sider_bg' => '#20222A',
                'logo_bg' => '#20222A'
            ],
            [
                'name' => 'purple-red-header',
                'title' => '紫红全头',
                'header_bg' => '#50314F',
                'sider_bg' => '#50314F',
                'logo_bg' => '#50314F'
            ],
            [
                'name' => 'fashion-red-header',
                'title' => '红色全头',
                'header_bg' => '#AA3130',
                'sider_bg' => '#28333E',
                'logo_bg' => '#28333E'
            ],
            [
                'name' => 'default',
                'title' => '默认主题',
                'header_bg' => '#009688',
                'sider_bg' => '#28333E',
                'logo_bg' => '#009688'
            ]
        ];
        $this->assign->theme = $theme;


        $this->fetch = 'index';
    }


}
