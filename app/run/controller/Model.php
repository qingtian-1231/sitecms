<?php
namespace app\run\controller;

use app\common\controller\Run;
use think\Loader;
use Think\Db;

/**
 * 模型
*/
class Model extends Run
{
    
    protected function initialize()
    {
        call_user_func(array('parent', __FUNCTION__)); 
    }
    
    
    public function lists()
    {
        $this->local['list_fields'] = array(
            'model',
            'cname',
            'is_menu',
            'is_dustbin',
            'is_import',
            'created'
        );
        $this->local['order'] = array('id'=>'ASC') ;
        //$this->local['item_actions']['delete'] = false ;
        $this->local['actions']['create'] = false;
        $this->addItemAction('数据字典', array('Model/datadict',['id'=>'id'], 'parse'=>['id']), '&#xe705;', 'layui-btn-warm');
        $this->addItemAction('modelgolist');
        call_user_func(array('parent', __FUNCTION__));
    }
    
    /**
    * 数据字典
    */
    public function datadict()
    {
        $id = intval($this->args['id']);
        if ($id <= 0) {
            return $this->message('error', '参数：ID获取错误');
        }
        $data = $this->mdl->get($id);
        if (empty($data)) {
            return $this->message('error', 'ID为' . $id . '的模型不存在');
        }
        
        try {
            $tableName = model($data['model'])->getTable();
            $sql = "SHOW COLUMNS FROM {$tableName}";
            $list = db()->query($sql);
        } catch(\Exception $e) {
            return $this->message('error', $e->getMessage());
        }
        
        
        $this->assign->list = $list;
        $model= $this->loadModel($data['model']);
        $form = [];
        if (isset($model->form)) {
            $form = $model->form;
        }
        $this->assign->modelForm = $form;
        
        $this->setTitle('数据字典：' . $model->cname, 'operation');
        
        $this->addAction("添加字段", ['addField', $this->args], 'fa-plus-circle', 'layui-btn layer-ajax-form');
        $this->addAction("返回模型", $this->returnListsUrl(), 'fa-reply', 'layui-btn-normal');
        
        $this->assign->addJs('/files/clipboard/clipboard.min.js');
        $this->assign->addJs('tableResize');
        $this->fetch = true;
    }
    
    /**
    * @name 新增字段
    */
    public function addField()
    {
        if (!config('app_debug')) {
            return $this->message('error', '非调试模式不允许进行该操作');
        }
        if (!config('field_add_del')) {
            return $this->message('error', '当前不允许操作字段');
        }
        $this->setTitle('添加字段', 'operation');
        $this->addAction("返回数据字典", ['Model/datadict', $this->args], 'fa-reply', 'layui-btn-normal');
        $this->assign->warning = '特殊字段/情况，请自行数据库中或专业数据库工具进行操作；交付项目前请务必手动在config/app下的`field_add_del`配置为false避免被随意操作字段';
        
        $id = intval($this->args['id']);
        $modelData = $this->mdl->where('id', '=', $id)->find();
        if (empty($modelData)) {
            return $this->message('error', '模型数据未查询到');
        }
        
        $modelObj = model($modelData['model']);
        $afterList = [];
        foreach ($modelObj->form as $field => $item) {
            $afterList[$field] = $field;
        }
        
        $this->mdl->form = [
            'model' => [
                'name' => '模型名',
                'elem' => 'hidden'
            ],
            'field' => [
                'name' => '字段名',
                'elem' => 'text'
            ],
            'name' => [
                'name' => '中文名称',
                'elem' => 'text'
            ],
            'type' => [
                'name' => '类型',
                'elem' => 'select',
                'options' => [
                    '数字类型' => [
                        'TINYINT' => 'TINYINT',
                        'SMALLINT' => 'SMALLINT',
                        'INT' => 'INT',
                        'BIGINT' => 'BIGINT',
                        'DECIMAL' => 'DECIMAL',
                        'FLOAT' => 'FLOAT',
                        'DOUBLE' => 'DOUBLE'
                    ],
                    '字符串类型' => [
                        'CHAR' => 'CHAR',
                        'VARCHAR' => 'VARCHAR',
                        'TEXT' => 'TEXT',
                        'MEDIUMTEXT' => 'MEDIUMTEXT',
                        'LONGTEXT' => 'LONGTEXT',
                        'BLOB' => 'BLOB',
                        'ENUM' => 'ENUM',
                    ],
                    '日期时间' => [
                        'DATE' => 'DATE',
                        'DATETIME' => 'DATETIME',
                        'TIME' => 'TIME',
                        'TIMESTAMP' => 'TIMESTAMP'
                    ]
                ]
            ],
            'length' => [
                'name' => '长度/值',
                'elem' => 'text',
                'info' => '不需要自己打括号'
            ],
            'default' => [
                'name' => '默认值',
                'elem' => 'text'
            ],
            'is_null' => [
                'name' => 'NULL',
                'elem' => 'checker',
                'info' => '默认都会加上NOT NULL'
            ],
            'is_unsigned' => [
                'name' => 'UNSIGNED',
                'elem' => 'checker',
                'info' => '整数是否无符号'
            ],
            'is_unsigned' => [
                'name' => 'UNSIGNED',
                'elem' => 'checker',
                'info' => '整数是否无符号'
            ],
            'index' => [
                'name' => '索引',
                'elem' => 'select', 
                'options' => [
                    'UNIQUE' => 'UNIQUE',
                    'INDEX' => 'INDEX'
                ]
            ],
            'after' => [
                'name' => '添加于...后',
                'elem' => 'select', 
                'options' => $afterList,
                'info' => '将字段添加到指定字段的后面，不选添加到最后'
            ],
            
        ];
        $this->assignDefault('model', $modelData['model']);
        
        $this->fetch = true;
    }
    
    /**
    * @poweras addField
    */
    public function ajaxAddField()
    {
        if (!config('app_debug')) {
            return $this->message('error', '非调试模式不允许进行该操作');
        }
        if (!config('field_add_del')) {
            return $this->message('error', '当前不允许操作字段');
        }
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        #pr(helper('Form')->data[$this->m]);
        $data = string_trim(helper('Form')->data[$this->m]);
        if (empty($data['field'])) {
            return $this->message('error', '请填写字段名'); 
        }
        if (empty($data['name'])) {
            return $this->message('error', '请填写可读名称'); 
        }
        if (empty($data['model'])) {
            return $this->message('error', '模型名称不存在'); 
        }
        if (empty($data['type'])) {
            return $this->message('error', '请选择字段类型'); 
        }
        $modelObj = $this->loadModel($data['model']);
        $default = $data['default'];
        $type = strtoupper($data['type']);
        $length = $data['length'];
        
        if ($default === '') {
            if (in_array($type, ['TINYINT', 'SMALLINT', 'INT', 'BIGINT', 'DECIMAL', 'FLOAT', 'DOUBLE'])) {
                $default = 0;
            } elseif (in_array($type, ['CHAR', 'VARCHAR'])) {
                 $default = '';
            } elseif (in_array($type, ['DATE'])) {
                 $default = '1970-01-01';
            } elseif (in_array($type, ['DATETIME'])) {
                 $default = '1970-01-01 00:00:00';
            } elseif (in_array($type, ['TIME'])) {
                 $default = '00:00:00';
            } else {
                unset($default);
            }
        }
        
        if (in_array($type, ['TEXT', 'MEDIUMTEXT', 'LONGTEXT', 'BLOB'])) {
             unset($default);
        }        
        if (in_array($type, ['CHAR', 'VARCHAR']) && empty($length)) {
             $length = '64';
        }
        if (in_array($type, ['DECIMAL', 'FLOAT', 'DOUBLE']) && empty($length)) {
             $length = '8,2';
        }
        $unsigned = intval($data['is_unsigned']);
        if (!in_array($type, ['TINYINT', 'SMALLINT', 'INT', 'BIGINT'])) {
            $unsigned = 0;
        }        
        
        $sql = sprintf("ALTER TABLE `%s` ADD `%s` %s%s %s %s %s COMMENT '%s' %s %s", 
                $modelObj->getTable(),
                $data['field'],
                $type,
                !empty($length) ? "( {$length} )": '',
                $unsigned ? 'UNSIGNED' : '',
                intval($data['is_null']) == 0 ? 'NOT NULL' : 'NULL',
                isset($default) ? "DEFAULT '{$default}'" : '',
                $data['name'],
                $data['after'] ? "AFTER `{$data['after']}`" : '',
                $data['index'] ? ",ADD {$data['index']} ( `{$data['field']}` )" : ''                
            );
        
        try {
            Db()->execute($sql);
        } catch (\Exception $e) {
            return $this->message('error', $e->getMessage());
        }        
        
        
        return $this->message('success', '字段添加成功，你需要自行在模型文件的form属性<a style="color:green;" href="https://www.kancloud.cn/laowu199/e_dev/408386" target="_blank">设置表单信息</a>，这样后台操作中即可自动生成');        
    }
    
    /**
    * @name 删除字段
    */
    public function delfield()
    {
        if (!config('app_debug')) {
            return $this->message('error', '非调试模式不允许进行该操作');
        }
        if (!config('field_add_del')) {
            return $this->message('error', '当前不允许操作字段');
        }
        $id = intval($this->args['id']);
        if ($id <= 0) {
            return $this->message('error', '参数：ID获取错误');
        }
        $data = $this->mdl->get($id);
        if (empty($data)) {
            return $this->message('error', 'ID为' . $id . '的模型不存在');
        }
        $modelObj = $this->loadModel($data['model']);
        $field = trim($this->args['field']);
        $sql = "ALTER TABLE `" . $modelObj->getTable() . "` DROP `{$field}`";
        try {
            Db()->execute($sql);
        } catch (\Exception $e) {
            return $this->message('error', $e->getMessage());
        }  
        
        return $this->message('success', '字段删除成功');
    }
    
    /**
    * @powerset false
    */
    public function create()
    {     
        return $this->redirect('run/tool/addM');
    }
    
    
    public function modify()
    {
        if(helper('Form')->data[$this->m]['model']) {
            helper('Form')->data[$this->m]['model'] = Loader::parseName(trim(helper('Form')->data[$this->m]['model']),1);
        }
        call_user_func(array('parent', __FUNCTION__));
    } 
}