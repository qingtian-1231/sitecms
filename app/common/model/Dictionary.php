<?php
namespace app\common\model;

class Dictionary extends App
{
    public $display = 'title';
    public $assoc = array(
        'DictionaryItem' => array(
            'type' => 'hasMany'
        )
    );

    public function initialize()
    {
        $this->form = array(
            'id' => array(
                'type' => 'integer',
                'name' => 'ID',
                'elem' => 'hidden',
            ),
            'title' => array(
                'type' => 'string',
                'name' => '名称',
                'elem' => 'text',
            ),
            'model' => array(
                'type' => 'string',
                'name' => '模型',
                'elem' => 'select',
                'options' => $GLOBALS['Model_title'],
            ),
            'field' => array(
                'type' => 'string',
                'name' => '字段',
                'elem' => 'text',
            ),
            'dictionary_item_count' => array(
                'type' => 'integer',
                'name' => '字典项计数',
                'elem' => 0,
                'list' => 'counter',
                'counter' => 'DictionaryItem',
            ),
            'created' => array(
                'type' => 'datetime',
                'name' => '添加时间',
                'elem' => 0,
                'list' => 'datetime'
            )
        );

        call_user_func_array(array('parent', __FUNCTION__), func_get_args());
    }

    protected $validate = array(
        'title' => array(
            'rule' => 'require',
            'message' => '请设置名称'
        ),
        'model' => array(
            'rule' => 'require',
            'message' => '请选择模型'
        ),
        'field' => array(
            'rule' => 'require',
            'message' => '请选择字段名'
        )
    );

    public function afterInsertCall()
    {
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));
        $oldDict = dict();
        $oldDict[$this['model']][$this['field']] = [];
        write_file_cache($this->name, $oldDict);
        return $parent_rslt;
    }

    public function afterUpdateCall()
    {
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));
        if ($this->oldData) {
            if ($this->oldData['model'] != $this['model'] || $this->oldData['field'] != $this['field']) {
                $oldDict = dict();
                unset($oldDict[$this->oldData['model']][$this->oldData['field']]);
                write_file_cache($this->name, $oldDict);
                $this->writeToFile($this['id']);
            }
        }
        return $parent_rslt;
    }
    
    public function resetFileCache()
    {
        $list  = $this->select()->toArray();
        if (!empty($list)) {
            foreach ($list as $item) {
                $this->writeToFile($item['id']);
            }
        }
    }
    
    public function writeToFile($id)
    {
        $dictData = $this
            ->with([
                'DictionaryItem' => [
                    'order' => ['list_order' => 'DESC', 'id' => 'DESC']
                ]
            ])
            ->where('id', '=', $id)
            ->find();
                    
        if (!empty($dictData)) {
            $dictData = $dictData->toArray();
            $dict = [];
            if (!empty($dictData['DictionaryItem'])) {
                foreach ($dictData['DictionaryItem'] as $item) {
                    if ($item['key'] !== '' && $item['key'] !== null) {
                        $dict[trim($item['key'])] = $item['value'];
                    } else {
                        $dict[$item['id']] = $item['value'];
                    }
                }
            }
            $oldDict = dict();
            $oldDict[$dictData['model']][$dictData['field']] = $dict;
            write_file_cache($this->name, $oldDict);
            unset($GLOBALS[$this->name]);
        }
    }
}
