<?php

namespace woo\model;

use think\db\Query;
use woo\utility\Hash;

class WooQuery extends Query
{
    
    public function find($data = null)
    {
        $options = $this->getOptions();
        if (empty($options['field'])) {
            $this->field(true);
        }
        /*
        if (!isset($options['alias'])) {
            $this->alias($this->getName());
        }
        */
        return call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }
    
    public function select($data = null)
    {
        $options = $this->getOptions();
        if (empty($options['field'])) {
            $this->field(true);
        }
        /*
        if (!isset($options['alias'])) {
            $this->alias($this->getName());
        }
        */
        return call_user_func_array(['parent', __FUNCTION__], func_get_args());
    }
}
