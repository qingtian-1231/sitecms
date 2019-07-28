<?php
namespace app\manage\controller;

use app\common\controller\Manage;
use woo\helper\Auth;

class User extends Manage
{
    //初始化 需要调父级方法
    public function initialize()
    {
        //定义不登录也允许访问的方法
        helper('Auth')->allow(['login', 'logout', 'register', 'check_username', 'check_email', 'activate']);
        call_user_func(['parent', __FUNCTION__]); 
    }
    
    //用户首页
    public function index()
    {
        $this->redirect('manage/Index/index');
    }
    
    public function updatePassword()
    {
        $this->assign->form_fields = [
            'old_password' => [
                'elem' => 'password',
                'name' => '旧密码'
            ],
            'password' => [
                'elem' => 'password',
                'name' => '新密码'
            ],
            're_password' => [
                'elem' => 'password',
                'name' => '重复密码'
            ]
        ];
        
        if ($this->request->isPost() && helper('Form')->checkToken()) {
            if (!captcha_check(input('post.captcha'))) {
                $this->assign->mdl_error['captcha'] = '验证码错误';
            }
            $data  = string_trim(helper('Form')->data[$this->m]);
            if (empty($data['old_password'])) {
                $this->assign->mdl_error['old_password'] = '请输入旧密码';
            }
            
            if (empty($data['password'])) {
                $this->assign->mdl_error['password'] = '请输入新密码';
            } else {
                if (strlen($data['password']) < 6 || strlen($data['password']) > 16) {
                    $this->assign->mdl_error['password'] = '密码长度应该是6-16位';
                }
            }
            
            if (empty($data['re_password'])) {
                $this->assign->mdl_error['re_password'] = '请输入确认密码';
            }
            
            if ($data['password'] != $data['re_password']) {
                $this->assign->mdl_error['re_password'] = '两次密码输入不一致';
            }
            
            if (empty($this->assign->mdl_error)) {
                if ($data['password'] == $data['old_password']) {
                    $this->assign->mdl_error['password'] = '与旧密码输入一致，不用修改';
                } else {
                    if (Auth::password(helper('Form')->data[$this->m]['old_password']) == $this->login['password']) {
                        
                        $this->mdl->isValidate(false)->save([
                            'password' => helper('Form')->data[$this->m]['password']
                        ], [
                            'id' => $this->login['id'] 
                        ]);
                        helper('Auth')->logout();
                        return $this->message('success', '密码修改成功', ['重新登录' => ['User/login'] , 'back' => false]);                            
                    } else {
                        $this->assign->mdl_error['old_password'] = '旧密码输入错误'; 
                    }
                }
            }
        }
        
        $this->fetch = 'form';
    }
    
    
    
    //登录
    public function login()
    {
        $redirect = session('REFERER') ? session('REFERER') : 'home/Index/index';
        if (strpos($redirect, 'run/') === false) {
            $redirect = 'home/Index/index';
        }
        if (helper('Auth')->user('id')) {
            $this->redirect($redirect);
        }
        if ($this->request->isPost() && helper('Form')->checkToken()) {
            if (captcha_check(input('post.captcha'))) {
                helper('Auth')->login();
                $logined = helper('Auth')->user();
                if ($logined) {
                    if ($logined['status'] == 'verified') {
                        //登录成功
                        \Hook::listen('login', $logined);
                        $this->redirect($redirect);
                    } elseif ($logined['status'] == 'unverified') {
                        $this->assign->error = '用户名未激活';
                        helper('Auth')->logout();
                    } else {
                        $this->assign->error = '用户名已禁用';
                        helper('Auth')->logout();
                    }
                } else {
                    $this->assign->error = '亲！用户名或者密码有误';
                }
            } else {
                $this->assign->error = '亲！验证码错误了哦';
            }
        }
        $this->fetch = true;
    }
    
    //退出
    public function logout()
    {
        if (helper('Auth')->user('id')) {
            helper('Auth')->logout();
        }            
        $this->redirect('User/login');
    }
    
    //注册
    public function register()
    {
        $this->assign->is_email_register = false;
        if ($this->request->isPost() /*&& helper('Form')->checkToken()*/) {            
            $error = [];            
            if (!captcha_check(input('post.captcha'))) {
                $error['captcha'] = '验证码输入错误';
            }
            
            helper('Form')->data[$this->m]['username'] = trim(helper('Form')->data[$this->m]['username']);
            helper('Form')->data[$this->m]['password'] = trim(helper('Form')->data[$this->m]['password']);
            
            if (!preg_match('/^\w{6,12}$/', helper('Form')->data[$this->m]['username'])) {
                $error['username'] = '用户名应该是6-12位数字、字母、下划线组成';
            } else {
                if ($this->mdl->where(['username' => helper('Form')->data[$this->m]['username']])->count()) {
                    $error['username'] = '帐号为[' . helper('Form')->data[$this->m]['username'] . ']的用户已存在';
                }
            }
            
            if (strlen(helper('Form')->data[$this->m]['password']) < 6 || strlen(helper('Form')->data[$this->m]['password']) > 16) {
                $error['password'] = '密码长度应该是6-16位';
            } else {
                if (helper('Form')->data[$this->m]['password'] != helper('Form')->data[$this->m]['repassword']) {
                    $error['repassword'] = '两次密码输入不一致';
                }
            }
            
            if (setting('is_email_verify')) {
                //注册必须先通过邮箱认证
                if (filter_var(helper('Form')->data[$this->m]['email'], FILTER_VALIDATE_EMAIL)) { //必须填写邮箱，验证邮箱格式
                    if ($this->mdl->where(['email' => helper('Form')->data[$this->m]['email']])->count()) {
                        $error['email'] = '认证邮箱为[' . helper('Form')->data[$this->m]['email'] . ']的用户已存在';
                    }
                } else {
                    $error['email'] = '请填写一个正确的邮箱地址';                    
                }
                helper('Form')->data[$this->m]['status'] = 'unverified';//如果需要邮箱认证，默认状态为未激活
            } else {
                helper('Form')->data[$this->m]['status'] = 'verified';//不需要邮箱认证，注册默认激活
            }
            
            if (empty($error)) {
                $this->loadModel('UserGroup');
                helper('Form')->data[$this->m]['user_group_id'] = 2;// $this->UserGroup->where(['alias' => 'Member'])->value('id'); //默认用户组 这里写死的，也可以用后面的代码动态获取
                
                $this->mdl->data(helper('Form')->data[$this->m]);
                //注册的时候为了安全，需要设置白名单字段；如果开发的时候有更多字段，记住添加其他字段，不然保存不进
                $result = $this->mdl->isUpdate(false)->allowField(['username', 'password', 'email', 'status', 'user_group_id'])->save();
                if ($result) {
                    //注册成功
                    $register = $this->mdl->where(['id' => $this->mdl->id])->find();
                    if (setting('is_email_verify')) {
                        $rslt = send_email('register_verify', $register['email'], [
                            'username' => $register['username'],
                            'link' => $this->absroot . 'manage/user/activate?id=' . $register['id'] . '&code=' . get_verify_code($register['email'], 5, 0, true)//验证码不过期，使用md5加密
                        ]);
                        if ($rslt === true) {
                            $this->assign->is_email_register = true;
                        } else {
                            $register->delete();
                            return $this->message('error', '邮件发送失败：' . $rslt);
                        }
                    } else {                        
                        // $this->redirect('User/login');  
                        //注册以后自动登录                      
                        helper('Auth')->login();
                        $logined = helper('Auth')->user();
                        if ($logined) {
                            return $this->message('success', '恭喜你！用户注册成功', ['去首页' => 'Index/Index', 'back' =>false]);
                        } else {
                            return $this->message('success', '恭喜你！用户注册成功', ['去登录' => 'User/login', 'back' =>false]);
                        }
                    }                    
                } else {
                    $error = $this->mdl->getError();   
                }                 
            }
            $this->assign->error = $error;
        }
        
        $this->fetch = true;
    }
    
    //邮箱激活
    public function activate()
    {
        $id = intval($this->args['id']);
        $code = $this->args['code'];        
        $register = $this->mdl->where(['id' => $id])->find();
        if (empty($register)) {
            return $this->message('error', '帐号不存在', ['去注册' => url('User/register'), 'back' => false]);
        }
        
        if ($register['status'] == 'verified') {
            return $this->message('error', '当前帐号已激活', ['去登录' => url('User/login'), 'back' => false]);
        }
        
        if ($register['status'] != 'unverified') {
            return $this->message('error', '当前帐号禁止为激活', ['去登录' => url('User/login'), 'back' => false]);
        }
        //验证码认证
        if (!check_verify_code($register['email'], $code, true)) {
            return $this->message('error', '帐号激活失败', ['去注册' => url('User/register'), 'back' => false]);
        }
        
        $this->mdl->isValidate(false)->save([
            'status' => 'verified'
        ], ['id' => $id]);
        
        return $this->message('success', '恭喜你，帐号已激活！', ['马上登录' => url('User/login'), 'back' => false]);
    }
    
    
    //验证某个用户名是否存在 ajax get提交一个username参数
    public function checkUsername()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        if (!preg_match('/^\w{6,12}$/', input('get.username'))) {
            return $this->ajax('error', '用户名应该是6-12位数字、字母、下划线组成');
        }        
        if (!$this->mdl->where(['username' => input('get.username')])->count()) {
            return $this->ajax('success', '当前用户名可用');
        } else {
            return $this->ajax('error', '当前用户名已存在');
        }        
    }
    
    //验证某个邮箱是否存在  ajax get提交一个email参数
    public function checkEmail()
    {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        if (!filter_var(helper('Form')->data[$this->m]['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->ajax('error', '请填写一个正确的邮箱地址');
        }
        if (!$this->mdl->where(['email' => input('get.email')])->count()) {
            return $this->ajax('success', '当前邮箱可用');
        } else {
            return $this->ajax('error', '当前邮箱已存在');
        }        
    }

    
    
    
}
