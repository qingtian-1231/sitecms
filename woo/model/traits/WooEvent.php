<?php

namespace woo\model\traits;

use woo\utility\Hash;

trait WooEvent
{
    /**
    * 新增前
    */
    protected function beforeInsertCall()
    {
        $scene = 'add';
        $this->isUpdate = false;
        return $this->validateCheck([], $scene);
    }
    
    /**
    * 新增后
    */
    protected function afterInsertCall()
    {
        $this->sumCache();
    }
    
    /**
    * 更新前
    */
    protected function beforeUpdateCall()
    { 
        $pk = $this->getPk();
        $this->isUpdate = true;
        $scene = 'edit';
        if (false === $this->validateCheck([], $scene)) {
            return false;
        }        
        $this->oldData =  [];
        if (isset($this[$pk])) {
            $old_data = $this->where($pk, '=', $this[$pk])->find();
        } else {
            $where = $this->getWhere();
            $old_data = $this->where($this->getWhere())->find();
        }
        if (!empty($old_data)) {
            if (!isset($this[$pk])) {
                $this[$pk] = $old_data[$pk];
            }
            $this->oldData = $old_data->toArray();
        }
        return true;
    }
    
    /**
    * 更新后
    */
    protected function afterUpdateCall()
    {  
    }
    
    /**
    * 写入前
    */
    protected function beforeWriteCall()
    {
        if ($this->createTime && isset($this[$this->createTime])) {
            unset($this[$this->createTime]);
        }
        if ($this->updateTime && isset($this[$this->updateTime])) {
            unset($this[$this->updateTime]);
        }
        /*
        if ($this->isUpdate) {
            $pk = $this->getPk();
            $scene = 'edit';
            $this->oldData =  [];            
            $scene = 'edit';
            if (isset($this[$pk])) {
                $old_data = $this->where($pk, '=', $this[$pk])->find();
            } else {
                $old_data = $this->where($this->getWhere())->find();
            }
            if (!empty($old_data)) {
                $this->oldData = $old_data->toArray();
            }
        } else {
            $scene = 'add';
        }

        return $this->validateCheck([], $scene);
        */
    }
    
    /**
    * 写入后
    */
    protected function afterWriteCall()
    {  
        // 重置验证属性
        $this->resetValidateOptions();
        $this->counterCache();
        \Cache::clear($this->name);
    }
    
    /**
    * 删除前
    */
    protected function beforeDeleteCall()
    {   
    }
    
    /**
    * 删除后
    */
    protected function afterDeleteCall()
    {
        // 重置验证属性
        $this->resetValidateOptions();
        $this->counterCache();
        $this->deleteWith();
        $is_dustbin = Hash::combine($GLOBALS['Model'], '{n}[model=' . $this->name . '].model', '{n}[model=' . $this->name . '].is_dustbin');
        settype($is_dustbin, 'array');
        if (($is_dustbin[$this->name] || $this->isDustbin === true) && $this->name != 'Dustbin') {
            $this->addDustbin();
        }
        \Cache::clear($this->name);
    }
    
    /**
    * 恢复前
    */
    protected function beforeRestoreCall()
    {        
    }
    
    /**
    * 恢复后
    */
    protected function afterRestoreCall()
    {        
    }
    
    /**
    * 删除以后入回收站
    */
    protected function addDustbin()
    {
        if ($this->getData()) { 
            $pk = $this->getPk();
            $data['model'] = $this->name;
            $data['model_id'] = $this[$pk];
            $data['title'] = $this[$this->display];
            $data['data'] = gzcompress(serialize($this->getData()));
            $data['status'] = 0;
            $dustbinModel = model('Dustbin');
            $dustbinModel->data($data);
            $dustbinModel->isUpdate(false)->save();
        }
    }
    
    /**
    * 关联统计累加
    */
    protected function sumCache() {
        if (empty($this->assoc)) {
            return;
        }
        foreach ($this->assoc as $assocModel => $assocInfo) {
            if ($assocInfo['type'] != 'belongsTo' || !isset($assocInfo['sumCache']['sumField'])) {
                continue;
            }
            $sumField  = $assocInfo['sumCache']['sumField'];
            $foreignKey = isset($assocInfo['foreignKey']) ? $assocInfo['foreignKey'] : parse_name($assocModel) . '_id';
            if (empty($this[$sumField]) || empty($this[$foreignKey])) {
                continue;
            }            
            $cacheField = !empty($assocInfo['sumCache']['cacheField']) ? $assocInfo['sumCache']['cacheField'] : parse_name($this->name) . '_sum'; 
            
            $assocModelObj = model($assocModel)->get($this[$foreignKey]);
            if (!empty($assocModelObj)) {
                $assocModelObj
                    ->isValidate(false)
                    ->setInc($cacheField, $this[$sumField]);
            }
            
            /*
            $assocModelData = $assocModelObj->get($this[$foreignKey]);
            if (!empty($assocModelData)) {
                $assocModelObj
                    ->isValidate(false)
                    ->modifyData($this[$foreignKey], [$cacheField => $assocModelData[$cacheField] + $this[$sumField]]);
                    
            }*/
        }
    }
    
    
    /**
    * 关联统计计数
    */
    protected function counterCache()
    {
        if (!empty($this->assoc)) {  
            foreach ($this->assoc as $assocModel => $assocInfo) {
                if ($assocInfo['type'] != 'belongsTo') {
                    continue;
                }
                if (!isset($assocInfo['counterCache'])) {
                    $assocInfo['counterCache'] = false;
                }
                if (!$assocInfo['counterCache']) {
                    continue;
                }
                $counter_foreign = isset($assocInfo['foreignKey']) ? $assocInfo['foreignKey'] : parse_name($assocModel) . '_id';
                if (!isset($this[$counter_foreign])) {
                    continue;
                }
                $counter_field = is_string($assocInfo['counterCache']) ? $assocInfo['counterCache'] : parse_name($this->name) . '_count';
                $assocModel = isset($assocInfo['foreign']) ? $assocInfo['foreign'] : $assocModel;
                $assocModelObj = model($assocModel);
                $assocPk = $assocModelObj->getPk();
                
                if (!empty($this->oldData)) {
                    if ($this->oldData[$counter_foreign] == $this[$counter_foreign]) {
                        continue;
                    }
                    $oldCount = $this
                        ->where($counter_foreign, '=', $this->oldData[$counter_foreign])
                        ->where(isset($assocInfo['countWhere']) ? $assocInfo['countWhere'] : [])
                        ->count();
                    
                    
                    $assocModelObj
                        ->isValidate(false)
                        ->where($assocPk, '=', $this->oldData[$counter_foreign])
                        ->update([$counter_field => $oldCount]);
                }
                
                $count = $this
                    ->where($counter_foreign, '=', $this[$counter_foreign])
                    ->where(isset($assocInfo['countWhere']) ? $assocInfo['countWhere'] : [])
                    ->count();
                $assocModelObj
                    ->isValidate(false)
                    ->where($assocPk, '=', $this[$counter_foreign])
                    ->update([$counter_field => $count]);
            }
        }
    }
    
    /**
    * 关联删除
    */
    protected function deleteWith()
    {
        $pk  = $this->getPk();
        if ($this->assoc && isset($this[$pk])) {
            foreach ($this->assoc as $assocModel => $assocInfo) {
                if (!in_array($assocInfo['type'], ['hasOne', 'hasMany']) || !isset($assocInfo['deleteWith'])) {
                    continue;
                }
                if ($assocInfo['deleteWith'] === true) {
                    $foreignKey = isset($assocInfo['foreignKey']) ? $assocInfo['foreignKey'] : parse_name($this->name) . '_id';
                    $assocModel = isset($assocInfo['foreign']) ? $assocInfo['foreign'] : $assocModel;
                    model($assocModel)->where($foreignKey, '=', $this[$pk])->delete();
                }
            }
        }
    }
}
