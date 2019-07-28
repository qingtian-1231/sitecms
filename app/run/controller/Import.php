<?php
namespace app\run\controller;

use app\common\controller\Run;

class Import extends Run
{
    /**
    * 初始化 需要调父级方法
    */
    protected function initialize()
    {        
        call_user_func(['parent', __FUNCTION__]); 
    }
    
    /**
    * 列表 
    */
    public function lists()
    {
        // 搜索字段
        /*        
        $this->local['filter'] = [
            'title'
        ];
        */
        
        // 列表字段
        $this->local['list_fields'] = [
            'title',
            'file',
            'is_import',
            'file_name',
            'size',
            'created'
            // 其他列表字段
        ];
        
        // 添加额外条件
        //$this->local['where'][] = ['字段', '=', '值'];        
        $this->addItemAction('导入预览', array('Import/show', ['id'=>'id'], 'parse'=>['id']), '&#xe60a;');
       
        call_user_func(['parent', __FUNCTION__]);
    }
    
    /**
    * 导入预览
    */
    public function show() 
    {
        $id = intval($this->args['id']);
        if ($id <= 0) {
            return $this->message('error', '缺少参数【ID】');
        }
        
        $import = $this->mdl->get($id);
        if (empty($import)) {
            return $this->message('error', '数据不存在');
        }
        $import = $import->toArray();
        $import['model'] = trim($import['model']);
        if (empty($import['model'])) {
            return $this->message('error', '数据未指定“模型”，无法预览');
        }
        if (empty($import['file'])) {
            return $this->message('error', '数据未上传文件，无法预览');
        }
        $data = excel_reader($import['file'], true);
        if ($data === false) {
            return $this->message('error', $GLOBALS['excel_read_error']);
        }        
        if (empty($data['list'])) {
            return $this->message('error', '没有需要导入的列表数据');
        }
        if (!empty($import['assoc_field'])) {
            $import['assoc_field'] = json_decode($import['assoc_field']);
        }
        $this->assign->import = $import;
        
        session('import', $import);
        $this->assign->data = $data;
        
        
        $this->setTitle('导入预览', 'operation');
        $this->addAction('返回列表', ['Import/lists'], 'fa-reply');
        $this->addAction('开始导入', 'javascript:void(0);', 'fa-upload do-import');
        $this->fetch = true;
    }
    
    /**
    * 执行导入
    */
    public function import() {
        if (!$this->request->isAjax()) {
            return $this->message('error', '不是一个正确的请求方式'); 
        }
        $data = input('post.');
        $import = session('import');
        if (empty($import)) {
            return $this->ajax('error', '预览数据已经丢失，请返回列表后重新进入预览页面');
        }
        if ($data['id'] != $import['id']) {
            return $this->ajax('error', '数据发生冲突，请一个文件导入完成以后再换另外一个文件导入');
        }
        if (empty($data['data'])) {
            return $this->ajax('error', '没有获取到导入的数据');
        }
        
        $model = $this->loadModel($import['model']);
        
        if (!($model instanceof \think\Model)) {
            return $this->ajax('error', "模型{$import['model']}不存在");
        }
        $pk = $model->getPk();
        // 关联识别
        $cache = [];
        if (!empty($import['assoc_field'])) {
            foreach ($data['data'] as &$item) {
                foreach ($import['assoc_field'] as $assoc_field) {
                    if (isset($model->form[$assoc_field]['foreign']) && isset($item[$assoc_field])) {
                        if (isset($cache[$assoc_field][$item[$assoc_field]])) {
                            $item[$assoc_field] = $cache[$assoc_field][$item[$assoc_field]];
                            continue;
                        }
                        list($foreign_mdl, $foreign_field) = plugin_split($model->form[$assoc_field]['foreign']);
                        $foreign = $this->loadModel($foreign_mdl);
                        $foreign_id = $foreign->where($foreign_field, '=', $item[$assoc_field])->value($foreign->getPk());
                        if ($foreign_id) {
                            $cache[$assoc_field][$item[$assoc_field]] = $foreign_id;
                            $item[$assoc_field] = $foreign_id;
                        } else {
                            $cache[$assoc_field][$item[$assoc_field]] = $item[$assoc_field];
                        }
                    }
                }
            }
        }
        $importCount = 100;
        try {
            if ($data['last']) {
                $this->mdl->where('id', '=', $data['id'])->setField('is_import', 1);
            }
            
            $rslt = $model->saveAll($data['data']);
            $count = count($data['data']);
            $start = $data['index'] * $importCount + 1;
            $end = $start + $count - 1;
            $html = "本次导入[{$start}-{$end}]，共{$count}条数据；";
            $success_count = 0;
            $error_count = 0;
            $error = [];
            foreach ($rslt as $key => $value) {
                if (!empty($value[$pk])) {
                    $success_count++;
                } else {
                    $error_count++;
                    $error[$start + $key] = $value->getError();
                }
            }
            $html .= "成功插入{$success_count}条，失败{$error_count}条";
            if (!$error_count) {
                return $this->ajax('success', $html);
            } else {
                return $this->ajax('success', $html, $error);
            }
            
        } catch(\Exception $e) {
            return $this->ajax('error', $e->getMessage());
        }
        
    }
    
    /**
    * 添加
    */
    public function create()
    {   // 设置默认值
        //$this->assignDefault('字段名', '默认值');
        // 字段白名单
        //$this->local['whiteList'] = ['id', 'title', ...允许添加的字段列表];   
        $this->mdl->form['file_name']['elem'] = 0 ;
        call_user_func(['parent', __FUNCTION__]);
    }
    
    /**
    * 修改
    */
    public function modify()
    {   
        // 字段白名单
        //$this->local['whiteList'] = ['id', 'title', ...允许修改的字段列表];
        call_user_func(['parent', __FUNCTION__]);
    } 
    
    /**
    * 删除
    */
    public function delete()
    {   
        // 设置额外的删除条件
        //$this->local['where'][] = ['is_verify', '=', 0];
        call_user_func(['parent', __FUNCTION__]);
    }  
}
