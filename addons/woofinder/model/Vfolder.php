<?php
namespace addons\woofinder\model;

use woo\model\WooModel;
use woo\utility\Hash;

class Vfolder extends WooModel
{
    ##创建日期字段
    protected $createTime = 'created';
    ##最后修改日期字段
    protected $updateTime = 'modified';
    
    public $cache = null;
    
    public function getCache()
    {
        $listStore = [];
        $list = $this->order(['id' => 'ASC'])->select()->toArray();
        $first = reset($list);
        $cache['threaded'][$first['id']] = $this->threaded($first['id'], $list);
        $cache['list'] = Hash::combine($list, '{n}.id','{n}');
        
        return $cache;
    }
    
    public function getFollowIds($parent_id) {
        $list = $this->field(['id'])->where(['parent_id' => $parent_id])->select()->toArray();

        $returnRet = [$parent_id];
        if (!empty($list)) {
            $list = array_keys(Hash::combine($list, '{n}.id'));
            $returnRet = array_merge($returnRet, $list) ;
            foreach ($list as $child) {
                $returnRet = array_merge($returnRet, $this->getFollowIds($child));
            }
        }
        return array_unique($returnRet);
    }
}
