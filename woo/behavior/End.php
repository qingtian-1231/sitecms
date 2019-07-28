<?php
namespace woo\behavior;

class End {
    
    public function appEnd($params)
    {
        $request = request();
        if ($request->isGet() && !config('app_debug') && setting('is_static_cache') && in_array($request->module(), config('allow_module_static'))) {
            $isWrite = false;
            $fileName = md5($request->url()) . '.html';
            if (setting('is_use_wap') && $request->isMobile()) {
                $fileName = 'mobile_' . $fileName;
            }
            if (file_exists(\Env::get('runtime_path') . 'static' . DS . $fileName)) {
                $html = file_get_contents(\Env::get('runtime_path') . 'static' . DS . $fileName);
                preg_match('/<!--<<<URL:(.*?)\|\|EXPIRE:(.*?)>>>-->/', $html, $cache);
                if ($cache[2] < time()) {
                    $isWrite = true;
                }
            } else {
                $isWrite = true;
            } 
                      
            if ($isWrite) {
                $content = $params->getData();
                if (strpos($content, '</head>') !== false) {
                    if (!file_exists(\Env::get('runtime_path') . 'static' . DS)) {
                        @mkdir(\Env::get('runtime_path') . 'static' . DS, 0777, true);
                    }
                    $cache = '<!--<<<URL:' . $request->url() . '||EXPIRE:' . (time() + intval(setting('static_cache_expire'))) . '>>>-->';
                    @file_put_contents(\Env::get('runtime_path') . 'static' . DS . $fileName, $cache . $content); 
                }
            }
        }
    }  
}