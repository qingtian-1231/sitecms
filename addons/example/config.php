<?php
return [
    // 插件是否必须登录
    'is_login' => true,
    // 未登陆跳转的地址
    'login_action' => addon_url('test/user/login'),
    'addon_config' => [
        'a' => [
            'title' => '测试1配置',
            'value' => '',
            'type' => 'text',
            'options' => '',
            'info' => '配置提示'
        ],
        'b' => [
            'title' => '测试2配置',
            'value' => 0,
            'type' => 'checker',
            'options' => '',
            'info' => ''
        ],
        'c' => [
            'title' => '测试3配置',
            'value' => '',
            'type' => 'file',
            'options' => '',
            'info' => ''
        ],
        'd' => [
            'title' => '测试4配置',
            'value' => ['k1'],
            'type' => 'checkbox',
            'options' => [
                'k1' => 'v1',
                'k2' => 'v2',
            ],
            'info' => ''
        ]
    ]
];
