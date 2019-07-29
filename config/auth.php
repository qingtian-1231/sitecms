<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // 只能在系统按照前修改加盐值，安装之后修改会导致已有用户密码无法识别
    'salt' => '123456',
    // 默认登录session名，如果不分session全部设置为一样， 可以开发过程中修改
    'default_name' => 'default',
    // home模块登录session名
    'home_name' => 'default',
    // manage模块登录session名
    'manage_name' => 'default',
    // run模板登录session名
    'run_name' => 'default',
    // addon插件登录session名
    'addon_name' => 'default',
    // 如果开发过程中有自己创建的模块可以按：  模块名_name  来区别开当前模型的session命，默认使用default_name
    // .....


    // 系统默认情况下，每个URL请求的地址都必须登录才允许方法
    // 如果是自己创建的模块，需要达到整个模块都可以不登录也能访问的效果，在下面配置中加入你的模块名
    // 具体规则可以查看开发手册Auth
    'allow_module' => ['home', 'install'],
    // 如果要实现当前账号在其他地方登录以后，本机账号自动退出功能 时时刷新需要开启
    'refresh_anytime' => false,


    /*==================权限相关========================*/
    //是否开启后台权限验证，如果false；即使分别了权限，系统也不会进行权限判断
    'power_check' => true,
    //CMS栏目相关的操作是否再进一步根据栏目授权进行权限判断  如果关闭将无视栏目授权设置
    'menu_power_check' => false,
    // 是否开启权限节点重置功能（相当于完全重置，而下面的控制器权限节点只会重置指定控制器的权限节点）
    'power_reset' => true,
    //是否开启控制器权限节点重置功能
    'power_controller_reset' => true,
    //不加入权限控制的方法（即不生成节点），格式： '控制器' => ['方法名','方法名']，相当于@powerset false；所以这是另一种不加入权限的方案
    'power_action_pass' => [
        //'Ad' => ['aa', 'lists'],//Ad控制器的aa、lists方法不加入权限
        //'Article' => ['ajaxSwitch'],//Article控制器的create方法不加入权限
        //'Product' => false// 如果false 表示整个控制器都不加入权限控制

        // 下面是系统中控制器暂时没有使用到的功能 所有屏蔽权限节点生成
        'Addon' => ['create', 'modify', 'detail', 'sort', 'export', 'delete', 'batchDelete', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'AddonConfig' => ['create', 'modify', 'detail', 'sort', 'export', 'delete', 'batchDelete', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'AdminMenu' => ['batchDelete', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'Database' => ['create', 'modify', 'detail', 'sort', 'export', 'batchDelete', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'Dustbin' => ['create', 'modify', 'sort', 'export', 'batchDelete', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch' ],
        'Email' => ['sort', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch' ],
        'Import' => ['sort', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'Index' => false,
        'Log' => ['sort', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch' ],
        'ManageMenu' => ['batchDelete', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'Member' => ['create', 'detail', 'sort', 'export', 'delete', 'batchDelete', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'Menu' => ['batchVerify', 'batchDisabled', 'content'],
        'Picture' => false,
        'Power' => ['lists', 'create', 'modify', 'detail', 'sort', 'export', 'delete', 'batchDelete', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'MenuPower' => ['lists', 'create', 'modify', 'detail', 'sort', 'export', 'delete', 'batchDelete', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'PowerTree' => ['detail','batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'QueryData' => false,
        'Setting' => ['batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'SettingGroup' => ['batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'Tool' => ['lists', 'create', 'modify', 'detail', 'sort', 'export', 'delete', 'batchDelete', 'batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch', 'getSiteSize', 'getLog', 'removeLog', 'removeTemp', 'clearCache', 'switchTrace', 'lockScreen', 'relieveScreen', 'setSkin', 'getAwesome', 'resetStaticCache'],
        'Upload' => false,
        'User' => ['batchVerify', 'batchDisabled', 'login', 'ajax_login', 'logout'],
        'UserGroup' => ['batchVerify', 'batchDisabled', 'ajaxSetField'],
        'UserGrade' => ['batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'UserScore' => ['batchVerify', 'batchDisabled', 'ajaxSetField', 'ajaxSwitch'],
        'UserLogin' => false,
    ],
    //权限映射的另一种方法，相当于@poweras   这里设置的映射优先于@poweras 设置的映射
    'power_action_as' => [
        /*
        'Ad' => [
            'aa' => 'lists',// 相当于aa方法不单独设置权限，最终权限和lists一致，所以lists有权限操作aa就可以操作；lists没有权限aa就没有权限操作
            'ajaxSetField' => 'lists'
        ],
        */
        /*
        '控制器' => [
            '映射方法' => '映射到的方法名',
            '映射方法' => '映射到的方法名'
        ]*/
    ],
    'power_special_list' => [
        [
            'controller' => 'All',
            'action' => 'all',
            'title' => '超级权限'
        ]
    ]
];
