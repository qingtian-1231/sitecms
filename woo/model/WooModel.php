<?php

namespace woo\model;

use think\Model;
use think\Loader;
use woo\utility\Hash;
use think\Db;

class WooModel extends Model
{
    use \woo\model\traits\WooCURD;
    use \woo\model\traits\WooEvent;
    use \woo\model\traits\WooValidate;
    use \woo\model\traits\WooUpload;
    
    /**
    * 时间戳类型
    */
    protected $autoWriteTimestamp = 'datetime';
    
    /**
    * 创建时间字段
    */
    protected $createTime = false;
    
    /**
    * 修改时间字段
    */
    protected $updateTime = false;
    
    /**
    * 表单
    */
    public $form = [];
    
    /**
    * 表单字段分组
    */
    public $formGroup = [];
    
    /**
    * 表单字段相应
    */
    public $fieldRespond = [];
    
    /**
    * 定义关联属性
    */
    public $assoc = [];
    
    /**
    * 父模型名
    */
    public $parentModel = null;
    
    /**
    * 模型中文名
    */
    public $cname = '';
    
    /**
    * 主要展示字段
    */
    public $display = 'id';    
    
    /**
    * 修改前的原数据
    */
    public $oldData = [];
    
    /**
    * 删除回收
    */
    protected $isDustbin = false;
    
    /**
     * 初始化
    */
    protected function initialize()
    {
        parent::initialize();
        if (!empty($this->assoc)) {
            foreach ($this->assoc as $assocModel => &$assocMsg) {
                if (is_string($assocMsg)) {
                    $assocMsg = ['type' => $assocMsg];
                }                                
                if (!isset($assocMsg['foreign'])) {
                    $assocMsg['foreign'] = $assocModel;
                }
                $assocMsg['localName'] = $this->name;
            }
        }
        
        if (!empty($this->formGroup)) {
            $this->formGroup = ['basic' => '基本选项'] + $this->formGroup;
        }
        
        if (empty($this->cname)) {
            if (!empty($GLOBALS['Model_title'][$this->name])) {
                $this->cname = $GLOBALS['Model_title'][$this->name];
            } else {
                $this->cname = $this->name;
            }
            
        }
        
        if (isset($this->form['created']) && !$this->createTime) {
            $this->createTime = 'created';
        }
        if (isset($this->form['modified']) && !$this->updateTime) {
            $this->updateTime = 'modified';
        }
        if (setting('auto_prepare_options') && !empty($this->form) && empty($this->getData())) {
            foreach ($this->form as $field => &$info) {
                if (empty($info['prepare'])) {
                    continue;
                } 
                if ($info['prepare'] === true) {
                    $info['prepare'] = [
                        'type' => 'select',
                        'property' => 'options' 
                    ];
                } 
                if (is_string($info['prepare'])) {
                    $info['prepare'] = [
                        'type' => $info['prepare'],
                        'property' => 'options' 
                    ];
                } 
                if (empty($info['prepare']['type'])) {
                    $info['prepare']['type'] = 'select';
                } 
                if (empty($info['prepare']['property'])) {
                    $info['prepare']['property'] = 'options';
                } 
                if (!empty($info[$info['prepare']['property']])) {
                    continue;
                }
                if ($info['prepare']['type'] == 'select') {
                    $foreign = isset($info['foreign']) ? $info['foreign'] : $info['prepare']['foreign'];
                    if (!empty($foreign)) {
                        list($foreign_mdl, $displayField) = plugin_split($foreign);
                        if (!empty($this->assoc[$foreign_mdl]['foreign'])) {
                            $foreign_mdl = $this->assoc[$foreign_mdl]['foreign'];
                        }
                    } else {
                        $foreign_mdl = parse_name(substr($field, 0, -3), 1);
                    }
                    $foreign_object = model($foreign_mdl);
                    if (empty($displayField)) {
                        $displayField = $foreign_object->display ? $foreign_object->display : 'title';
                    }
                    $pkField = $foreign_object->getPk();
                    $queryFields = [$pkField, $displayField];
                    $data = $foreign_object
                            ->field($queryFields)
                            ->where(isset($info['prepare']['where']) ? $info['prepare']['where'] : [])
                            ->order(isset($info['prepare']['order']) ? $info['prepare']['order'] : [$pkField => 'DESC'])
                            ->select()
                            ->toArray();
                    $info[$info['prepare']['property']] = Hash::combine($data, '{n}.' . $pkField, '{n}.' . $displayField);
                } elseif ($info['prepare']['type'] == 'dict') {  
                    $dict = isset($info['prepare']['dict']) ? $info['prepare']['dict'] : $this->name . '.' . $field;
                    list($dict_model, $dict_field) = plugin_split($dict);
                    $info[$info['prepare']['property']] = dict($dict_model, $dict_field);
                } elseif ($info['prepare']['type'] == 'cache' && !empty($info['prepare']['key'])) {
                    $info[$info['prepare']['property']] = \Cache::get($info['prepare']['key']);
                }
            }
        }
    }
    
    /**
    * init 事件调用 事件到对应方法中去写  这里保持不动即可
    */
    public static function  init()
    {
        self::event('before_insert', function($object) {
            return $object->beforeInsertCall();
        });
        
        self::event('after_insert', function($object) {
            return $object->afterInsertCall();
        });
        
        self::event('before_update', function($object) {
            return $object->beforeUpdateCall();
        });
        
        self::event('after_update', function($object) {
            return $object->afterUpdateCall();
        });
        
        self::event('before_write', function($object) {
            return $object->beforeWriteCall();
        });
        
        self::event('after_write', function($object) {
            return $object->afterWriteCall();
        });
        
        self::event('before_delete', function($object) {
            return $object->beforeDeleteCall();
        });
        
        self::event('after_delete', function($object) {
            return $object->afterDeleteCall();
        });
        
        self::event('before_restore', function($object) {
            return $object->beforeRestoreCall();
        });
        
        self::event('after_restore', function($object) {
            return $object->afterRestoreCall();
        });
    }
    
    /**
    * 关联分析 将你的关联数组交给它分析
    */
    public function parseWith($with)
    {
        
        if (empty($with)) {
            return [];
        }
        
        if (is_string($with)) {
            $with = explode(',', $with);
        }
        
        $with = Hash::normalize($with);
        foreach ($with as $key => &$relation) {
            if (!is_array($relation)) {
                if (empty($relation)) {
                    $relation = [];
                } else {
                    continue;
                }
            }            
            $assocMsg = $this->assoc[$key];
            $relation = function($query) use($relation, $assocMsg) {
                foreach ($relation as $key1 => $value1) {
                    if (is_numeric($key1)) {
                        $relation['with'][] = $value1;
                    }
                }

                // 关联条件
                if (isset($assocMsg['where'])) {
                    $query->where($assocMsg['where']);
                }                
                if (isset($relation['where'])) {
                    $query->where($relation['where']);
                }
                // 关联字段
                $field = [];
                if (isset($assocMsg['field'])) {
                    $field = $assocMsg['field'];
                }
                if (isset($relation['field'])) {
                    $field = $relation['field'];
                }
                if (!empty($field)) {
                    // hasOne 、hasMany 类型中字段列表必须有外键字段 否则报错                    
                    if (in_array($assocMsg['type'], ['hasOne', 'hasMany'])) {
                        $foreignKey = isset($assocMsg['foreignKey']) ? $assocMsg['foreignKey'] : parse_name($assocMsg['localName']) . '_id';
                        if (!in_array($foreignKey, $field)) {
                            $field[] = $foreignKey;
                        }
                    }
                    if (in_array($assocMsg['type'], ['belongsTo'])) {
                        $localKey = isset($assocMsg['localKey']) ? $assocMsg['localKey'] : 'id';
                        if (!in_array($localKey, $field)) {
                            $field[] = $localKey;
                        }
                    }
                    $query->withField($field);
                    if (in_array($assocMsg['type'], ['hasMany'])) {
                        $query->field($field);
                    }
                }             
                
                //排序
                $order = [];
                if (isset($assocMsg['order'])) {
                    $order = $assocMsg['order'];
                }
                if (isset($relation['order'])) {
                    $order = $relation['order'];
                }
                if (!empty($order)) {
                    $query->order($order);
                }
                
                if (isset($relation['limit'])) {
                    $query->limit($relation['limit']);
                } else {
                    if (isset($assocMsg['limit'])) {
                        $query->limit($assocMsg['limit']);
                    }
                }
                // 嵌套关联不支持更多属性， 比如where、order等 ， 但支持按tp原本的所有语法 比如闭包
                if (isset($relation['with'])) {
                    $query->with($relation['with']);
                }
            };
        }
        return $with;
    }
    
    /**
    * 关联预载入  将连贯操作第一个写with，不然就将你的with数组先给parseWith分析的结果交给with
    */
    public function with($with)
    {
        return parent::with($this->parseWith($with));
    }
    
    /**
    * 判断栏目选择是否正确
    */
    public function checkTypeOfMenu($value, $rule, $data)
    {
        $menuType = menu($value, 'type');
        if (empty($menuType)) {
            $menuType = model('Menu')->where('id', $value)->column('type');
            if ($menuType) {
                $menuType = $menuType[0];
            }
        }
        if ($menuType != $this->name) {
            return '你选择的栏目【所属类型】并非' . $this->cname;
        }
        return true;
    }
    
    /**
    * 返回无限级家族列
    */
    public function getFamily($field, $id){
        $pk = $this->getPk();
        if ($this->form[$field]['foreign']) {
            list($foreign_model, $foreign_field) = plugin_split(trim($this->form[$field]['foreign'])); 
            $foreign_model = model($foreign_model); 
        } else {
            $foreign_model = $this;
            if ($this->display) {
                $foreign_field = $this->display;
            } else {
                $foreign_field = $this->getPk();
            }
        }
        if ($foreign_model->form['family']) {
            $family = $foreign_model->where($pk, '=', $id)->value('family');
            $family = explode(',', $family);
            array_pop($family);
            array_shift($family);
            return $family;
        } else {
            $parents = $foreign_model->getParentIds($id, 0);
            if (!empty($parents)) {
                $parents = array_reverse($parents);
                array_shift($parents);
            }
            $family = array_merge($parents, [$id]);
            return $family;
        }
    }
    
    /**
    * 返回无限极中某id的上级
    */
    public function getParentIds($child_id, $deep = 1, $now_deep = 1) {
        $list = $this->field(['parent_id'])->where($this->getPk(), '=', $child_id)->select()->toArray();
        $returnRet = [];
        if (!empty($list)) {
            $list = array_keys(Hash::combine($list, '{n}.parent_id'));
            $returnRet = array_merge($returnRet, $list);
            if ($deep === 0 || $now_deep < $deep) {
                foreach ($list as $parent) {
                    if ($parent) {
                        $returnRet = array_merge($returnRet, $this->getParentIds($parent, $deep, $now_deep++));
                    }
                }
            }
        }
        return array_unique($returnRet);
    }
    
    /**
    * 返回无限极中某id的下级id
    */
    public function getChildrenIds($parent_id, $deep = 1, $now_deep = 1) {
        $pk  = $this->getPk();
        $list = $this->field([$pk])->where('parent_id', '=', $parent_id)->select()->toArray(); 
        
        $returnRet = [];
        if (!empty($list)) {
            $list = array_keys(Hash::combine($list, '{n}.' . $pk));
            $returnRet = array_merge($returnRet, $list) ;
            if ($deep === 0 || $now_deep < $deep) {
                foreach ($list as $child) {
                    $returnRet = array_merge($returnRet, $this->getChildrenIds($child, $deep, $now_deep++));
                }
            }
        }
        return array_unique($returnRet);
    }
    
    protected function getChildren($threaded, $key = 'children')
    {
        foreach ((array)$threaded as $parent_id => $childen_threaded) {
            $this->cache[$key][$parent_id] = array_keys((array)$childen_threaded);
            if ($childen_threaded) {
                $this->getChildren($childen_threaded, $key);
            }
        }
    }

    /**
    *  自行准备数据$listData，实现无限极数据处理，提高数据库效率
    */
    protected function threaded($parent_id = 1, $listData = [])
    {
        $pk = $this->getPk();
        if (empty($listData)) {
            return false;
        }
        $treeObj = Hash::combine($listData, '{n}[parent_id=' . $parent_id . '].' . $pk, '{n}[parent_id=' . $parent_id . ']');
        $treeList = [];
        if ($treeObj) {
            foreach ($treeObj as $item) {
                $treeList[$item[$pk]] = $this->threaded($item[$pk], $listData);
            }
        }
        return $treeList;
    }
    
    /**
    * 多级联动数据处理方法
    * @param $objects  一般为你select查询以后的结果，里面包含了所有联动数据对象，  数据里面必须包含id、parent_id、展示等字段（如果数据量大建议，查询的时候只查询该3个字段）
    * @param $name   给你的联动取一个名字，定义form字段属性时会使用到 ，用来标识你的联动数据，所以不要和其他联动数据重复
    * @param $field  展示字段 ，默认为title
    */
    protected function multiSelect($objects, $name = null, $field = 'title') 
    {
        $pk = $this->getPk();
        if (empty($objects) || !is_array($objects)) {
            return false;
        }
        if ($name === null) {
            $name = $this->name;
        }
        $name = 'multiSelect' . parse_name($name, 1);
        if (is_object($objects)) {
            $objects = $this->toArray();
        }
        $objects = Hash::combine($objects, '{n}.' . $pk, '{n}');
        
        $cache['data']['top_id'] = min(array_keys($objects));
        $cache['data']['count'] = count($objects);
        $cache['data']['field'] = $field;
        $threaded[$cache['data']['top_id']] = $this->threaded($cache['data']['top_id'], $objects);
        $this->getChildren($threaded, 'options');
        $cache['options'] = $this->cache['options'];
        unset($this->cache['options']);
        $cache['list'] = $objects;
        write_file_cache($name, $cache);
        return true;
    }
    
    /**
    * 添加错误信息
    */
    public function forceError($field, $error = '')
    {
        if (is_string($field)) {
            if (!empty($error)) {
                $this->error[$field] = $error;
            } else {
                $this->error[] = $field;
            }
        } elseif (is_array($field)) {
            $this->error = array_merge($field, is_array($this->error) ? $this->error : []);
        }
        return true;
    }
    
    /**
    * belongsTo关联自动调用
    */
    protected function belongsToCall($assocModel, $assocMsg = [])
    {
        if (!isset($assocMsg['foreignKey'])) {
            $foreignKey = parse_name($assocModel) . '_id';
            if (!isset($this->form[$foreignKey])) {
                $foreignKey = parse_name($assocMsg['foreign']) . '_id';
            }
        } else {
            $foreignKey = $assocMsg['foreignKey'];
        }
        $localKey = isset($assocMsg['localKey']) ? $assocMsg['localKey'] : null;
        return $this->belongsTo($assocMsg['foreign'], $foreignKey, $localKey)->setEagerlyType(1);
    }
    
    /**
    * hasOne关联自动调用
    */
    protected function hasOneCall($assocModel, $assocMsg = [])
    {
        $foreignKey = isset($assocMsg['foreignKey']) ? $assocMsg['foreignKey'] : parse_name($this->name) . '_id';
        $localKey = isset($assocMsg['localKey']) ? $assocMsg['localKey'] : null;
        return $this->hasOne($assocMsg['foreign'], $foreignKey, $localKey)->setEagerlyType(1);
    }
    
    /**
    * hasMany关联自动调用
    */
    protected function hasManyCall($assocModel, $assocMsg = [])
    {
        $foreignKey = isset($assocMsg['foreignKey']) ? $assocMsg['foreignKey'] : parse_name($this->name) . '_id';
        $localKey = isset($assocMsg['localKey']) ? $assocMsg['localKey'] : null;
        return $this->hasMany($assocMsg['foreign'], $foreignKey, $localKey);
    }
    
    /**
    * belongsToMany关联自动调用
    */
    protected function belongsToManyCall($assocModel, $assocMsg = [])
    {
        $foreignKey = isset($assocMsg['foreignKey']) ? $assocMsg['foreignKey'] : parse_name($assocModel) . '_id';
        $localKey = isset($assocMsg['localKey']) ? $assocMsg['localKey'] : null;
        return $this->belongsToMany($assocModel, $assocMsg['table'], $foreignKey, $localKey);
    }
    
    /**
    * hasManyThrough关联自动调用
    */
    protected function hasManyThroughCall($assocModel, $assocMsg = [])
    {
        $foreignKey = isset($assocMsg['foreignKey']) ? $assocMsg['foreignKey'] : parse_name($this->name) . '_id';
        $localKey = isset($assocMsg['localKey']) ? $assocMsg['localKey'] : null;
        $throughKey = isset($assocMsg['throughKey']) ? $assocMsg['throughKey'] : parse_name($assocMsg['through']) . '_id';
        return $this->hasManyThrough($assocModel, $assocMsg['through'], $foreignKey, $throughKey, $localKey);
    }
    
    public function __call($method, $args)
    {
        if (array_key_exists(parse_name($method, 1), (array)$this->assoc)) {
            $method = parse_name($method, 1);
            
            if (!isset($this->assoc[$method]['type'])) {
                exception('未定义关联类型');
            }  
            
            $callMethod = $this->assoc[$method]['type'] . 'Call';
            
            if (method_exists($this, $callMethod)) {
                $assocResult = $this->$callMethod($method, $this->assoc[$method]);
            
                return $assocResult;
            } else {
                exception('关联类型：' . $this->assoc[$method]['type'] . '没有配置对应方法');
            }
        }
        return call_user_func_array([$this->db(), $method], $args);
    }
    
    public function __get($name)
    {
        if (array_key_exists($name, (array)$this->assoc)) {
            $modelRelation = $this->$name();
            $value = $this->getRelationData($modelRelation);
            $this[$name] = $value;
            return $this;
        }
        return $this->getAttr($name);
    }
}
