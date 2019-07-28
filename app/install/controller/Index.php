<?php
namespace app\install\controller;

use woo\controller\WooApp;
use think\Db;

class Index extends WooApp
{
    protected function initialize()
    {
        if (is_woo_installed()) {
            $this->redirect('home/Index/index');
        }
        parent::initialize();
        
        $this->assign->addJs([
            'jquery-1.11.1.min',
            '/files/layui/layui.js'
        ]);
        
        $this->assign->addCss([
            '/files/layui/css/layui.css',
            'install/global.css',
            'animate.css',
            '/files/awesome-4.7.0/css/font-awesome.min.css'
        ]);
        $this->setTitle('WooCMS安装');
        $this->assign->foot_link = [
            [
                'title' =>'系统官网',
                'link' => 'https://www.eduaskcms.xin'
            ],
            [
                'title' =>'检查更新',
                'link' => 'https://www.eduaskcms.xin'
            ],
            [
                'title' =>'开发手册',
                'link' => 'https://www.kancloud.cn/laowu199/e_dev'
            ],
            [
                'title' =>'ThinkPHP5.1手册',
                'link' => 'https://www.kancloud.cn/manual/thinkphp5_1'
            ],
            [
                'title' =>'Q群：314833523',
                'link' => '//shang.qq.com/wpa/qunwpa?idkey=904af7a5d6b422b0fdc1cd04160b64b117fda091bf830b44d1cbd3b02f079e33'
            ]
        ];
        $this->assign->dev = read_file_cache('Developer');
    }
    
    public function index()
    {
        
        $this->fetch = true;
    }
    
    public function step2()
    {
        $error_count = 0;
        $env['os'][1] = '操作系统';
        $env['os'][2] = '类UNIX';
        $env['os'][3] = true;
        $env['os'][4] = PHP_OS;
        $env['os'][5] = '不限制';
        
        $env['version'][1] = 'PHP版本';
        $env['version'][2] = '>7.0.x';
        $env['version'][3] = true;
        $env['version'][4] = PHP_VERSION;        
        $env['version'][5] = '5.6.x';
        
        $env['session'][1] = 'session';
        $env['session'][2] = '开启';
        $env['session'][5] = '开启';
        
        if (function_exists('session_start')) {
            $env['session'][3] = true;
            $env['session'][4] = '已开启';
        } else {
            $env['session'][3] = false;
            $env['session'][4] = '未开启';
        }
        
        $env['pdo'][1] = 'PDO';
        $env['pdo'][2] = '开启';
        $env['pdo'][5] = '开启';
        $env['pdo']['link'] = 'https://www.baidu.com/s?wd=开启PDO,PDO_MYSQL扩展';
        if (class_exists('pdo')) {
            $env['pdo'][3] = true;
            $env['pdo'][4] = '已开启';
        } else {
            $env['pdo'][3] = false;
            $env['pdo'][4] = '未开启';
            $error_count++;
        }
        
        $env['pdo_mysql'][1] = 'PDO_MySQL';
        $env['pdo_mysql'][2] = '开启';
        $env['pdo_mysql'][5] = '开启';
        $env['pdo_mysql']['link'] = 'https://www.baidu.com/s?wd=开启PDO,PDO_MYSQL扩展';
        if (extension_loaded('pdo_mysql')) {
            $env['pdo_mysql'][3] = true;
            $env['pdo_mysql'][4] = '已开启';
        } else {
            $env['pdo_mysql'][3] = false;
            $env['pdo_mysql'][4] = '未开启';
            $error_count++;
        }
        
        $env['curl'][1] = 'CURL';
        $env['curl'][2] = '开启';
        $env['curl'][5] = '不限制';
        $env['curl']['link'] = 'https://www.baidu.com/s?wd=开启PHP CURL扩展';
        if (extension_loaded('curl')) {
            $env['curl'][3] = true;
            $env['curl'][4] = '已开启';
        } else {
            $env['curl'][3] = false;
            $env['curl'][4] = '未开启';
        }
        
        $env['gd'][1] = 'GD';
        $env['gd'][2] = '开启';
        $env['gd'][5] = '开启';
        $env['gd']['link'] = 'https://www.baidu.com/s?wd=开启PHP GD扩展';
        if (extension_loaded('gd')) {
            $env['gd'][3] = true;
            $env['gd'][4] = '已开启';
        } else {
            $env['gd'][3] = false;
            $env['gd'][4] = '未开启';
            $error_count++;
        }
        
        $env['mbstring'][1] = 'MBstring';
        $env['mbstring'][2] = '开启';
        $env['mbstring'][5] = '开启';
        $env['mbstring']['link'] = 'https://www.baidu.com/s?wd=开启PHP MBstring扩展';
        if (extension_loaded('MBstring')) {
            $env['mbstring'][3] = true;
            $env['mbstring'][4] = '已开启';
        } else {
            $env['mbstring'][3] = false;
            $env['mbstring'][4] = '未开启';
            $error_count++;
        }
        
        
        $env['a']['is_title'] = true;
        $env['a']['title'] = '环境配置检测';
        
        $env['execution'][1] = '最大执行时间';
        $env['execution'][2] = '>=30s';
        $env['execution'][5] = '无限制';
        $env['execution'][3] = true;
        $env['execution'][4] = ini_get('max_execution_time') . 's';
        
        $env['filesize'][1] = '文件上传大小';
        $env['filesize'][2] = '>2M';
        $env['filesize'][5] = '无限制';
        $env['filesize'][3] = true;
        $env['filesize'][4] = ini_get('upload_max_filesize');
        
        $env['rewrite'][1] = 'URL重写';
        $env['rewrite'][2] = '开启';
        $env['rewrite'][5] = '开启';
        $env['rewrite'][3] = false;
        $env['rewrite'][4] = '检测中...';
        $env['rewrite']['id'] = 'rewrite';
        $env['rewrite']['link'] = 'https://www.kancloud.cn/manual/thinkphp5/177576';
        
        $folders    = [
            realpath(ROOT_PATH . 'data') . DS,
            realpath(ROOT_PATH . 'runtime') . DS,
            realpath(WWW_ROOT . 'upload') . DS,
            realpath(WWW_ROOT . 'addons') . DS
        ];
        
        $fresult = [];
        foreach ($folders as $dir) {
            if (is_dir($dir)) {
                if (test_write_dir($dir)) {
                    $fresult[$dir]['w'] = true;
                } else {
                    $fresult[$dir]['w'] = false;
                    $error_count++;
                }
                if (is_readable($dir)) {
                    $fresult[$dir]['r'] = true;
                } else {
                    $fresult[$dir]['r'] = false;
                    $error_count++;
                }
            } else {
                $fresult[$dir]['w'] = false;
                $fresult[$dir]['r'] = false;
                $error_count++;
            }
        }
        
        $this->assign->fresult = $fresult;
        $this->assign->env = $env;
        $this->assign->error_count = $error_count;
        $this->fetch = true;
    }
    
    public function step3()
    {
        $this->fetch = true;
    }
    
    public function step4()
    {
        session(null);
        if ($this->request->isPost()) {
            $input = input('post.');
            $dbConfig             = [];
            $dbConfig['type']     = 'mysql';
            $dbConfig['hostname'] = trim($input['dbhost']);            
            $dbConfig['username'] = trim($input['dbusername']);
            $dbConfig['password'] = trim($input['dbpassword']);
            $dbConfig['hostport'] = trim($input['dbport']);
            $dbConfig['charset']  = trim($input['dbcharset'] ? $input['dbcharset'] : 'utf8');
            
            $adminUser = [];
            
            $adminUser['username'] = trim($input['username']);
            $adminUser['password'] = trim($input['password']);
            $adminUser['email'] = trim($input['email']);
            
            if (empty($adminUser['username'])) {
                return $this->message('error', '请填写管理员用户名');
            }
            if (strlen($adminUser['password']) < 5 || strlen($adminUser['password']) >16) {
                return $this->message('error', '管理员密码只能5-16位');
            }
            if ($adminUser['password'] != $input['repassword']) {
                return $this->message('error', '两次密码输入不一致');
            }
            if (!filter_var($adminUser['email'], FILTER_VALIDATE_EMAIL)) {
                return $this->message('error', '管理员邮箱格式错误');
            }           
            
            $db     = Db::connect($dbConfig);
            $dbName = trim($input['dbname']);
            try {
                $sql    = "CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET " . $dbConfig['charset'];
                if (!$db->execute($sql)) {
                    return $this->message('error', $sql . '执行失败');
                }
            } catch (\Exception $e) {
                return $this->message('error', $e->getMessage());
            }
            
            
            $dbConfig['database'] = $dbName;
            $dbConfig['prefix']   = trim($input['dbprefix']);
            
            session('install.db_config', $dbConfig);
            $databaseFile = ROOT_PATH . 'data' . DS . 'database.sql';
            if (!file_exists($databaseFile)) {
                return $this->message('error', "数据库文件{$databaseFile}不存在");
            }
            
            $sql = get_file_sql($databaseFile, $dbConfig['prefix'], $dbConfig['charset']);
            session('install.sql', $sql);
            $this->assign('sql_count', count($sql));
            session('install.error', 0);
            session('install.admin_user', $adminUser);
            $this->fetch = true;
        } else {
            exit;
        }
    }
    
    public function install()
    {
        $dbConfig = session('install.db_config');
        $sql      = session('install.sql');

        if (empty($dbConfig) || empty($sql)) {
            $this->result([], -1, "非法安装");
        }
        $index = isset($this->args['index']) ? intval($this->args['index']) : 0;
        $db = Db::connect($dbConfig);
        if ($index >= count($sql)) {
            $installError = session('install.error');
            $this->result(['error' => $installError], 2, "安装完成！");
        }        
        $sqlExec = $sql[$index] . ';';
        
        $result = execute_sql($db, $sqlExec);        
        if (!empty($result['error'])) {
            $installError = session('install.error');
            $installError = empty($installError) ? 0 : $installError;
            session('install.error', $installError + 1);
            $this->error($result['message'], '', [
                'sql'       => $sqlExec,
                'exception' => $result['exception']
            ]);
        } else {
            $this->success($result['message'], '', [
                'sql' => $sqlExec
            ]);
        }
    }
    
    public function setDbConfig()
    {
        $dbConfig = session('install.db_config');        
        try {
            $confDir = ROOT_PATH . 'data' . DS . 'config' . DS;
            if (!file_exists($confDir)) {
                mkdir($confDir, 0777, true);
            }
            file_put_contents($confDir . 'database.php', "<?php\nreturn " . var_export($dbConfig, true) . "\n?>");
            
        } catch (\Exception $e) {    
            $this->error("数据配置文件写入失败！");    
        }
        $this->success("数据配置文件写入成功！");
    } 
    
    public function setAdminUser()
    {
        $data = session('install.admin_user');
        if (empty($data)) {
            $this->error("非法安装！");
        }
        
        //缓存生成
        model('Menu')->writeToFile();
        model('Model')->writeToFile();
        model('ManageMenu')->writeToFile();
        model('AdminMenu')->writeToFile();
        model('setting')->writeToFile();
        model('Dictionary')->resetFileCache();
        
        $data['user_group_id'] = 1;
        $data['status'] = 'verified';
        $result = false;
        try {
            $userModel = model('User');
            $result = $userModel->isUpdate(false)->save($data);            
        } catch (\Exception $e) {    
            $this->error("后台管理员创建失败！", '', [
                'error' => $e->getMessage()
            ]);    
        }
        if ($result) {
            session("install.finish", true);
            $this->success("后台管理员创建完成！");
        } else {
            $this->error("后台管理员创建失败！" , '', $userModel->getError());
        }
    }
    
    // 手动安装
    public function installBySelf()
    {
         //缓存生成
        model('Menu')->writeToFile();
        model('Model')->writeToFile();
        model('ManageMenu')->writeToFile();
        model('AdminMenu')->writeToFile();
        model('setting')->writeToFile();
        model('Dictionary')->resetFileCache();
        
        $data['user_group_id'] = 1;
        $data['status'] = 'verified';
        $data['username'] = 'adming';
        $data['password'] = '123456';
        
        $userModel = model('User');
        $result = $userModel->isUpdate(false)->save($data); 
        
        if ($result) {
            @touch(ROOT_PATH . 'data/install.lock');
            return $this->message('系统手动安装成功');
        } else {
            return $this->message('系统手动安装失败');
        }
        
    }
    
    public function step5()
    {
        if (session("install.finish")) {
            @touch(ROOT_PATH . 'data/install.lock');
            session("install.finish", null);
            $this->fetch = true;            
        } else {
            return $this->message('error', "非法安装！");
        }
    }
    
    
    public function testDbPwd()
    {
        if ($this->request->isGet()) {
            $dbConfig         = $this->args;
            $dbConfig['type'] = 'mysql';
            try {
                Db::connect($dbConfig)->query("SELECT VERSION();");
                
            } catch (\Exception $e) {
                $this->error('数据库账号或密码不正确！');
            }
            $this->success('验证成功！');
        }
    }
    
    public function testRewrite()
    {
        $this->ajax('success', '支持');
    }
}
