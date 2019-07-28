<?php

namespace woo\model\traits;

trait WooValidate
{
    
    protected $validate = [];
    
    protected $validateFormat = [];
    
    protected $validateOptions = [];
    
    private $validateCache = [];
    // 验证对象 
    protected $validateObject = null;
    
    /**
    * 验证前
    */    
    protected function beforeValidate()
    {
        // 文件上传
        $this->processUpload();        
        // 字段数据处理
        $fullData = $this->getData();
        $tableFields = $this->getTableFields();
        $fieldsType = $this->getFieldsType();
                
        foreach ($tableFields as $field) {
            $value = null;
            if (isset($fullData[$field])) {
                $value = $fullData[$field];
            } else {
                // 解决新增时，text|blob类型无法设置默认值后导致： 1364 doesn't have a default value；其他字段自己设置默认值
                if (preg_match('/(text|blob)/is', $fieldsType[$field]) && $this->isUpdate === false) {
                    $this[$field] = '';
                    $value = $this[$field];
                }
            }
            
            if (!($value === null)) {
                if (isset($this->form[$field]['filter'])) {
                    $filter = $this->form[$field]['filter'];
                    if (is_string($filter)) {
                        $function  = trim($filter);
                        if (function_exists($function)) {
                            $this[$field]= $value = $function($value);
                        }
                    } elseif (is_callable($filter)) {
                        // form 字段filter属性支持闭包回调处理你的值 传单2个参数分别是当前字段值和所有data
                        $this[$field] = $value = app('think\\Container')->invokeFunction($filter, [$value, $fullData]);
                    }
                }
                
                if (isset($this->form[$field]['type'])) {
                    $type = strtolower($this->form[$field]['type']);
                    switch ($type) {
                        case 'integer':
                            $this[$field] = intval($value);
                            break;
                        case 'float':
                            $this[$field] = floatval($value);
                            break;
                        case 'time':
                            $this[$field] = strtotime($value);
                            break;
                        case 'array':
                            if (is_array($value)) {
                                $this[$field] = json_encode($value);
                            }
                            break;
                        case 'blob':
                            $this[$field] = gzcompress($value);
                            break;
                        case 'blob.array':
                            $this[$field] = gzcompress(serialize($value));
                            break;
                        case 'string':
                            if (is_string($value)) {
                                $this[$field] = strip_tags($value);
                            }
                            break;
                        case 'text':
                            // 暂不做任何处理
                            break;
                        case 'none':
                            unset($this[$field]);
                            break;
                        default:
                            break;
                    }
                }
                
                if (is_array($this[$field])) {
                    $this[$field] = json_encode($value);
                }
            }
        }
    }
    
    /**
    * 是否自动进行数据验证
    */
    public function isValidate($is = true)
    {
        $this->setValidateOptions([
            'isValidate' => false
        ]);
        return $this;
    }    
    
    /**
    * batch : bool true 是否批量验证
    * only  : bool false 是否自验证data中有的字段，没有的字段不验证
    * rule  : array [] 单独的验证规则 没有自动提取validate属性
    * isValidate : bool true 是否验证进行验证
    */
    public function setValidateOptions(array $options = [])
    {
        $this->validateOptions = $options;
        return $this;
    }
    
    /**
    * 获取验证属性
    */
    public function getValidateOptions()
    {
        return $this->validateOptions + [
            'batch' => true,
            'only' => false,
            'rule' => [],
            'isValidate' => true 
        ];
    }
    
    /**
    * 重置验证属性
    */
    public function resetValidateOptions()
    {
        $this->validateOptions = [];
    }
    
    /**
    * 自动验证数据
    */
    public function validateCheck($data = [], $scene = '')
    {
        $options = $this->getValidateOptions();        
              
        $this->beforeValidate();
        if ($options['isValidate'] === false) {
            return true;
        }
        if (isset($options['scene'])) {
            $scene = trim($options['scene']);
        }
        if (empty($scene)) {
            $scene = 'full';
        }
         
        if (!is_object($this->validateObject)) {
            $fields = [];
            foreach ($this->getTableFields() as $field) {
                if (isset($this->form[$field]['name'])) {
                    $fields[$field] = $this->form[$field]['name'];
                } else {
                    $fields[$field] = $field;                             
                }
            }
            $this->validateObject = new \think\Validate([], [], $fields);
        }
        
        if (empty($options['rule'])) {
            if (empty($this->validateFormat)) {
                $this->validateCache = [];
                $this->validateFormat($this->validate);
                $this->validateFormat = $this->validateCache;
            }
            $validate = $this->validateFormat;
        } else {
            $this->validateCache = [];
            $this->validateFormat($options['rule']);
            $validate = $this->validateCache;
        }
        
        $message = $validate['message'];
        /*
        if (!isset($validate['rule'])) {
            return empty($this->error) ? true : false;
        }
        
        if (!array_key_exists($scene, $validate['rule'])) {
            $scene = 'full';
        }
        
        if (!isset($validate['rule'][$scene])) {
           return empty($this->error) ? true : false;
        }
        */
        if (!isset($validate['rule']) || !isset($validate['rule'][$scene])) {
            return empty($this->error) ? true : false;
        }
        
        
        $validate = $validate['rule'][$scene];        
        
        $data = empty($data) ? $this->getData() : $data;
        
        if ($options['only']) {
            $rule = [];
            foreach ($validate as $field => $each) {
                if (array_key_exists($field, $data)) {
                    $rule[$field] = $each;
                }
            }
        } else {
            $rule  = $validate;
        }
        
        if (empty($rule)) {
            return empty($this->error) ? true : false;
        }
        
        //$this->validateObject->rule($rule);
        $this->validateObject->message($message);
        
        if (!$this->validateObject->batch(!!$options['batch'])->check($data, $rule)) {
            $this->forceError($this->validateObject->getError());
        }
        
        return empty($this->error) ? true : false;
    }
    
    /**
    * 将验证规则处理成TP识别格式
    */    
    protected function validateFormat(array $validate) 
    {
        if (empty($validate)) {
            return [];
        }
        
        foreach ($validate as $field => $info) {
            if (isset($info['rule'])) {
                if (is_array($info['rule'])) {
                    $ruleArray = $info['rule'];
                    $rule = trim($ruleArray[0]);
                    if ($rule === 'call') {
                        $rule = trim($ruleArray[1]);
                        unset($ruleArray[0]);
                        unset($ruleArray[1]);
                        $value = implode(',', $ruleArray);
                        $this->validateObject->extend($rule, [$this, $rule]);
                    } else {
                        unset($ruleArray[0]);
                        $value = implode(',', $ruleArray);
                    }
                    
                    $this->validateCache['rule']['full'][$field][$rule] = $value;                    
                    if (isset($info['on'])) {
                        $info['on'] = strtolower($info['on']);
                        $this->validateCache['rule'][$info['on']][$field][$rule] = $value;
                    } else {
                        $this->validateCache['rule']['add'][$field][$rule] = $value;
                        $this->validateCache['rule']['edit'][$field][$rule] = $value;
                    }
                } else {
                    $rule = trim(strval($info['rule']));
                    if (isset($this->validateCache['rule']['full'][$field])) {
                        if (in_array($rule, $this->validateCache['rule']['full'][$field])) {
                            continue;
                        }
                    }
                    
                    $this->validateCache['rule']['full'][$field][] = $rule;                    
                    if (isset($info['on'])) {
                        $info['on'] = strtolower($info['on']);
                        $this->validateCache['rule'][$info['on']][$field][] = $rule;
                    } else {
                        $this->validateCache['rule']['add'][$field][] = $rule;
                        $this->validateCache['rule']['edit'][$field][] = $rule;
                    }
                }
                
                if (isset($info['message'])) {
                    $this->validateCache['message']["{$field}.{$rule}"] = $info['message'];
                }
                
                if (!isset($info['allowEmpty']) && !in_array('require', (array)$this->validateCache['rule']['full'][$field])) {
                    $this->validateCache['rule']['full'][$field][] = 'require';
                    $this->validateCache['message']["{$field}.require"] = '该字段不能为空';
                    if (isset($info['on'])) {
                        $info['on'] = strtolower($info['on']);
                        $this->validateCache['rule'][$info['on']][$field][] = 'require';
                    } else {
                        $this->validateCache['rule']['add'][$field][] = 'require';
                        $this->validateCache['rule']['edit'][$field][] = 'require';
                    }
                }
                
            } else {
                if (is_array($info)) {
                    foreach ($info as $deepInfo) {
                        $this->validateFormat([$field => $deepInfo]);
                    }
                }
            }
        }
    }
}
