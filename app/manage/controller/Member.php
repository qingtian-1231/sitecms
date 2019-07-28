<?php
namespace app\manage\controller;

use app\common\controller\Manage;

class Member extends Manage
{
    //初始化 需要调父级方法
    public function initialize()
    {        
        call_user_func(['parent', __FUNCTION__]); 
    }
    
    public function info()
    {
        if ($this->login['UserGroup']['alias'] != 'Member') {
            //如果当前用户所在组的 用户信息模型非Member 将不能在此处修改信息，可以使用下面的跳转 直接跳转到对应模型 的信息修改页 需要自行开发
            //$this->redirect($this->login['UserGroup']['alias'] . '/info');
            return $this->message('error', '亲！您所在用户组的会员信息不在此次修改哦');
        }

        ##表单字段必须通过form_fields 属性assign出去
        ##具体定义的方式请参考 开发手册 > 模型 > 表单定义
        ##系统form_fields 仅仅是定义表单规则，然后帮你生成html表单，很多和功能相关的属性在这里定义是无效（比如图片上传中的upload和image属性，
        ##系统仍然会获取模型中的属性进行判断，如果确实要在方法中定义可以： 模型对象->form[字段] = ... 进行重新赋值）。最终的逻辑业务还是需要你自己写。
        ##如果表单太特殊，自动生成实现不了，就自己写页面
        $this->assign->form_fields = [
            'nickname' => array(
                'name' => '昵称',
                'elem' => 'text'
            ),
            'truename' => array(
                'name' => '真实姓名',
                'elem' => 'text'
            ),
            'headimg' => array(
                'name' => '头像',
                'elem' => 'image.upload'
            ),
            'sex' => array(
                'name' => '性别',
                'elem' => 'radio',
                'options' => ['m' => '男', 'f' => '女', 'x' => '保密']
            ),
            'mobile' => array(
                'name' => '手机号码',
                'elem' => 'text'
            ),
            'region' => array(
                'name' => '所在地区',
                'elem' => 'multi_select.ajax',
                'multi_field' => [
                    'province' => '省',
                    'city' => '市',
                    'area' => '区',
                ],
                'multi_options' => [
                    'order' => ['list_order' => 'DESC','id' => 'ASC'],
                    'where' => []
                ],
                'foreign' => 'Region.title'
            ),
            'address' => array(
                'name' => '联系地址',
                'elem' => 'text'
            )
        ];
        //如果完全和模型中form一致，可以直接赋值
        //$this->assign->form_fields = $this->mdl->form;
        
        if ($this->request->isPost() && helper('Form')->checkToken()) {
            if (captcha_check(input('post.captcha'))) {
                ##执行更新  用户中心为了安全建议多使用allowField 和 模型filter属性 
                $result = $this->mdl->allowField(array_merge(array_keys($this->assign->form_fields) ,array_keys($this->assign->form_fields['region']['multi_field'])))
                            ->force()
                            ->save(helper('Form')->data[$this->m], ['id' => $this->login['Member']['id']]);
                $mdl_error = $this->mdl->getError();
                if ($result || empty($mdl_error)) {
                    helper('Auth')->relogin();
                    return $this->message('success', '用户信息修改成功！');
                } else {
                    $this->assign->mdl_error = $mdl_error;
                }
            } else {
                $this->assign->mdl_error['captcha'] = '验证码错误';
            }
        } else {
            ##对表单默认值进行赋值
            helper('Form')->data[$this->m] = $this->login['Member'];
        }
        
        $this->assign->addJs('admin/global.js');
        ##需要使用html编辑器 elem => editor 必须引入下面2个 
        //$this->assign->addJs('/editor-4.9.2/ckeditor.js');
        //$this->assign->addJs('/editor-4.9.2/adapters/jquery.js');  
        ##需要使用取色器 elem => color 必须引入下面2个    
        //$this->assign->addCss('/files/colorpicker/css/colorpicker.css');
        //$this->assign->addJs('/files/colorpicker/js/colorpicker.js');
        ##需要使用数组、键值对编辑 elem => array 或 keyvalue 必须引入下面2个
        //$this->assign->addJs('artTemplate.js');
        //$this->assign->addJs('json2.js');  
        ##需要自动生成表单，页面必须是form或者继承form页面    
        $this->fetch = 'form';
    }
}
