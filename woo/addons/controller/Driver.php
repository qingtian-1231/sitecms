<?php
namespace woo\addons\controller;

use think\Db;

class Driver
{
    public static function check($name)
    {
        if (!$name || !is_dir(ADDONS_PATH . $name)) {
            throw new \Exception('插件' . $name . '不存在');
        }
        $addonClass = get_addon_class($name);
        if (!$addonClass) {
            throw new \Exception("插件主启动程序不存在");
        }
        $addon = new $addonClass();
        if (!$addon->checkInfo()) {
            throw new \Exception("配置文件不完整");
        }
        
        $exists = db('Addon')->where('name', $name)->find();
        if (!empty($exists)) {
            throw new \Exception("该插件已安装");
        }
        return true;
    }
    
    public static function importsql($name)
    {
        $file = ADDONS_PATH . $name . DS . 'install.sql';
        if (is_file($file)) {
            $sqls = get_file_sql($file, config('database.prefix'), config('database.charset'), '__PREFIX__');
            foreach ($sqls as $sql) {
                if (empty($sql)) {
                    continue;
                }
                try {
                    db()->execute($sql);
                } catch (\PDOException $e) {
                }
            }
        }
        return true;
    }
    
    public static function install($name)
    {
        if (empty($name)) {
            throw new \Exception('未知插件名称');
        }
        
        try {
            self::check($name);
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        
        $fromDir = self::getSourceAssetsDir($name);
        $toDir = self::getDestAssetsDir($name);
        if (is_dir($fromDir)) {
            copydirs($fromDir, $toDir);
        }
        
        foreach (self::getCheckDirs() as $k => $dir) {
            if (is_dir(ADDONS_PATH . $name . DS . $dir)) {
                copydirs(ADDONS_PATH . $name . DS . $dir, ROOT_PATH . $dir);
            }
        }
        try {
            $info = get_addon_info($name);
            if (!$info['state']) {
                $info['state'] = 1;
            }
            
            $class = get_addon_class($name);
            
            if (class_exists($class)) {
                $addon = new $class();
                $config = $addon->getConfig();
                $addon->install();
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        
        self::importsql($name);
        
        
        try {
            unset($info['url']);
            $insert_id = db('Addon')->insertGetId($info);
            if (!empty($config['addon_config']) && $insert_id) {
                $addonConfit = [];
                foreach ($config['addon_config'] as $var => $item) {
                    $addonConfit[] = array_merge($item, ['vari' => $var, 'addon_id' => $insert_id]);
                }
                
                model('AddonConfig')->saveAll($addonConfit);
            }
            
            
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        
        return true;
    }
    
    public static function uninstall($name)
    {
        if (!$name || !is_dir(ADDONS_PATH . $name)) {
            throw new \Exception('插件不存在');
        }
        
        $destAssetsDir = self::getDestAssetsDir($name);
        if (is_dir($destAssetsDir)) {
            rmdirs($destAssetsDir);
        }
        
        $list = self::getGlobalFiles($name);
        foreach ($list as $k => $v) {
            @unlink(ROOT_PATH . $v);
        }
        try {
            
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                $addon->uninstall();
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        
        rmdirs(ADDONS_PATH . $name);
        
        try {
            $addon_data = db('Addon')->where('name', '=', $name)->find();
            db('Addon')->where('name', '=', $name)->delete();
            db('AddonConfig')->where('addon_id', '=', $addon_data['id'])->delete();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        
        
        return true;
    }
    
    public static function getGlobalFiles($name, $onlyconflict = false)
    {
        $list = [];
        $addonDir = ADDONS_PATH . $name . DS;
        foreach (self::getCheckDirs() as $k => $dir) {
            $checkDir = ROOT_PATH . DS . $dir . DS;
            if (!is_dir($checkDir)) {
                continue;
            }                
            if (is_dir($addonDir . $dir)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($addonDir . $dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST
                );

                foreach ($files as $fileinfo) {
                    if ($fileinfo->isFile()) {
                        $filePath = $fileinfo->getPathName();
                        $path = str_replace($addonDir, '', $filePath);
                        if ($onlyconflict) {
                            $destPath = ROOT_PATH . $path;
                            if (is_file($destPath)) {
                                if (filesize($filePath) != filesize($destPath) || md5_file($filePath) != md5_file($destPath)) {
                                    $list[] = $path;
                                }
                            }
                        } else {
                            $list[] = $path;
                        }
                    }
                }
            }
        }
        return $list;
    }
    
    protected static function getSourceAssetsDir($name)
    {
        return ADDONS_PATH . $name . DS . 'assets' . DS;
    }
    
    protected static function getDestAssetsDir($name)
    {
        $assetsDir = ROOT_PATH . str_replace("/", DS, "public/addons/{$name}/");
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
        }
        return $assetsDir;
    }
    
    protected static function getCheckDirs()
    {
        return [
            'app',
            'public'
        ];
    }
}
