<?php
namespace woo\behavior;

class Init {
    
    public function run()
    { 
        $request = request();
        if ($request->isGet() && !config('app_debug') && setting('is_static_cache')) {
            $fileName = md5($request->url()) . '.html';
            if (setting('is_use_wap') && $request->isMobile()) {
                $fileName = 'mobile_' . $fileName;
            }
            if (file_exists(\Env::get('runtime_path') . 'static' . DS . $fileName)) {                
                $html = file_get_contents(\Env::get('runtime_path') . 'static' . DS . $fileName);
                preg_match('/<!--<<<URL:(.*?)\|\|EXPIRE:(.*?)>>>-->/', $html, $cache);                
                if ($cache[2] > time()) {
                    $html = preg_replace('/<!--<<<URL:(.*?)\|\|EXPIRE:(.*?)>>>-->/', '', $html);
                    response($html)->send();
                    exit;
                } else {
                    @unlink(\Env::get('runtime_path') . 'static' . DS . $fileName);
                }
            }
        }
    }  
}
