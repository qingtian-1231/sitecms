<?php
namespace app\common\model;

use woo\utility\Hash;

class AdminMenu extends App
{
    public $assoc = [];
    public $display = 'title';
    public $cache = [];

    public $parentModel = 'parent';

    public function initialize()
    {
        $this->form = [
            'id' => [
                'type' => 'integer',
                'name' => 'ID',
                'elem' => 'hidden'
            ],
            'parent_id' => array(
                'type' => 'integer',
                'name' => '父级栏目',
                'elem' => 'nest_select.AdminMenu'
            ),
            'power_tree_id' => array(
                'type' => 'integer',
                'name' => '关联权限',
                'elem' => 0,//'nest_select.PowerTree',
                'foreign' => 'PowerTree.title',
                'info' => '选择关联权限后，只有拥有该操作权限才会显示'
            ),
            'title' => array(
                'type' => 'string',
                'name' => '栏目标题',
                'elem' => 'text'
            ),
            'url' => array(
                'type' => 'string',
                'name' => '栏目URL',
                'elem' => 'text'
            ),
            'icon' => array(
                'type' => 'string',
                'name' => '栏目图标',
                'elem' => 'icon'
            ),
            'is_nav' => array(
                'type' => 'integer',
                'name' => '导航显示',
                'elem' => 'checker'
            ),
            'is_debug' => array(
                'type' => 'integer',
                'name' => '调试隐藏',
                'elem' => 'checker',
                'info' => '当关闭调试以后，将不会显示'
            ),
            'list_order' => array(
                'type' => 'integer',
                'name' => '排序权重',
                'elem' => 'number',
                'list' => 'edit'
            ),
            //其他字段
        ];
        call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }
       
    protected $validate = [
        'title' => array(
            'rule' => 'require',
            'message' => '请填写栏目标题'
        ),
        'parent_id' => array(
            array(
                'rule' => array('egt', 1),
                'message' => '请选择父级导航'
            ),
            array(
                'rule' => array('call', 'checkParent')
            )
        ),
        'power_tree_id' => array(
            'allowEmpty' =>true,
            //'rule' => array('call', 'checkPower')
        )
    ];
    
    public function checkParent($value, $rule, $data)
    {
        if ($value == $data['id'] || in_array($value, (array)adminmenu('children', $data['id']))) {
            return '不能选择本栏目以及其子栏目做为父级';
        }
        return true;
    }
    
    public function checkPower($value, $rule, $data) 
    {
        if ($value) {
            $action = powertree($value, 'action');
            if (empty($action)) {
                return '权限节点有误或没有选择到最底层权限';
            }
        }
        return true;
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
            $this->where('id', 'IN', adminmenu('children', $this['id']))->delete();##删除掉所有子级
        }        
        $this->writeToFile();
        return $parent_rslt;
    }
    
    public function writeToFile()
    {
        $list = $this
            ->order(['list_order' => 'ASC', 'id' => 'ASC'])
            ->select()
            ->toArray();
        $first = $this
            ->order(['id' => 'ASC'])
            ->find();
        $list = $full = Hash::combine($list, '{n}.id' , '{n}');
        $this->cache['threaded'][$first['id']] = $this->threaded($first['id'], $list);
        $this->getChildren($this->cache['threaded'], 'children');
        $list = $this
            ->where('is_nav', '=', 1)
            ->order(['list_order' => 'ASC', 'id' => 'ASC'])
            ->select()
            ->toArray();
        $this->cache['nav'] = $this->threaded($first['id'], $list);
        $this->getChildren($this->cache['nav'], 'nav_children');
        $this->cache['list'] = $full;
        write_file_cache('AdminMenu', $this->cache);        
        return true;
    }
}
