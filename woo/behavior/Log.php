<?php
namespace woo\behavior;

class Log
{
    
    public function appLogWrite($params)
    {
        $old_data = isset($params[0]) ? (array)$params[0] : [];
        $new_data = isset($params[1]) ? (array)$params[1] : [];
        $diff_data = array_diff($new_data, $old_data);
        
        db('Log')->insert([
            'user_id' => $GLOBALS['controller']->Auth->user('id'),
            'module' => $GLOBALS['controller']->params['module'],
            'controller' => $GLOBALS['controller']->params['controller'],
            'action' => $GLOBALS['controller']->params['action'],
            'param' => json_encode($GLOBALS['controller']->params['param']),
            'new_data' => json_encode($new_data),
            'old_data' => json_encode($old_data),
            'diff_data' => json_encode($diff_data),
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s')
        ]);
    }  
}
