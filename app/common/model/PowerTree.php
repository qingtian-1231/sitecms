<?php
namespace app\common\model;

use woo\utility\Hash;
use think\facade\Cache;

class PowerTree extends App
{
    //关联模型
    public $assoc = [];
    public $display = 'title';
    public $cache = [];

    ##父模型名
    public $parentModel = 'parent';
    
    public function initialize()
    {        
        $this->form = [
            'id' => [
            	'type' => 'integer',
            	'name' => 'ID',
            	'elem' => 'hidden',
            ],
            'parent_id' => array(
                'type' => 'integer',
                'name' => '父级栏目',
                'elem' => 'nest_select.PowerTree'
            ),
            'title' => array(
                'type' => 'string',
                'name' => '节点名称',
                'elem' => 'text',
                'list' => 'show'
            ),
            'addon' => array(
                'type' => 'string',
                'name' => '插件目录',
                'elem' => 'text'
            ),
            'controller' => array(
                'type' => 'string',
                'name' => '控制器名',
                'elem' => 'text',
                //'options' => $GLOBALS['Model_title'],
            ),
            'action' => array(
                'type' => 'string',
                'name' => '方法名',
                'elem' => 'text'
            ),
            'together' => array(
                'type' => 'string',
                'name' => '访问规则',
                'elem' => 'format'
            ),
            'created' => array(
                'type' => 'datetime',
                'name' => '添加时间',
                'elem' => 0,
                'list' => 'datetime'
            ),
            'modified' => array(
                'type' => 'datetime',
                'name' => '修改时间',
                'elem' => 0,
                'list' => 'datetime'
            ),
            'list_order' => array(
                'type' => 'integer',
                'name' => '排序权重',
                'elem' => 'number',
                'list' => 'edit'
            ),
        ];
        call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }
       
    
    //数据验证    
    protected $validate = [
        'title' => array(
            'rule' => 'require',
            'message' => '请填写节点名称'
        ),
        'together' => array(
            'rule' => array('unique', 'power_tree'),
            'message' => '当前访问规则已存在'
        ),
        'parent_id' => array(
            /*array(
                'rule' => array('egt', 1),
                'message' => '请选择父级导航'
            ),*/
            array(
                'rule' => array('call', 'checkParent'),
                'on' => 'edit'
            )
        )
    ];
    
    public function checkParent($value, $rule, $data)
    {
        if ($value == $data['id'] || in_array($value, (array)powertree('children', $data['id']))) {
            return '不能选择本节点以及其子节点做为父级';
        }
        return true;
    }
    
    public function beforeWriteCall()
    {
        if (isset($this['controller']) && isset($this['action']) && isset($this['addon'])) {
            $this['addon'] = trim($this['addon']);
            $this['controller'] = trim($this['controller']);
            $this['action'] = trim($this['action']);            
            $this['together'] = strtolower($this['addon'] ? $this['addon'] . '/' : '') . parse_name($this['controller']) . '/' . strtolower($this['action']);
        }         
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));               
        return $parent_rslt;
    }
    
    public function afterWriteCall()
    {
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));
        $this->writeToFile();
        return $parent_rslt;
    }
    
    public function afterDeleteCall()
    {
        $parent_rslt = call_user_func(array('parent', __FUNCTION__));
        if (isset($this['id'])) {                   
            $this->where('id', 'IN', powertree('children', $this['id']))->delete();##删除掉所有子级
        }        
        $this->writeToFile();
        return $parent_rslt;
    }
    
    public function writeToFile()
    {
        Cache::rm('static_powertree_list');
        $list = $this
            ->field(['id', 'parent_id', 'title', 'addon', 'controller', 'action', 'together'])
            ->order(['list_order' => 'ASC', 'id' => 'ASC'])
            ->select()
            ->toArray();
        $list = $full = Hash::combine($list, '{n}.id' , '{n}');
        $first = $this
            ->order(['id' => 'ASC'])
            ->find();
        
        $this->cache['threaded'][$first['id']] = $this->threaded($first['id'], $list);
        $this->getChildren($this->cache['threaded'], 'children');

        $this->cache['list'] = $list;

        write_file_cache('PowerTree', $this->cache);
        return true;
    }
}
