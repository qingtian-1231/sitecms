<?php
return [
    // 插件是否必须登录
    'is_login' => true,
    'login_action' => url('run/user/login'),
    'addon_config' => [
        'delete_file_together' => [
            'title' => '是否同步删除文件',
            'value' => 1,
            'type' => 'checker',
            'options' => '',
            'info' => '删除文件的记录的同时是否一起删除具体文件'
        ]
    ]
];
