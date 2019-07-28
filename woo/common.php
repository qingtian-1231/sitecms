<?php

use think\Loader;
use think\Db;
use think\facade\Hook;
use think\facade\Config;
use think\facade\Cache;
use woo\utility\Hash;
use woo\utility\TpText;
use think\facade\Url;


define('APP_CACHE_DIR', dirname(APP_PATH) . DS . 'data' . DS . 'cache' . DS);

// 定义自己的一些文件目录
\Env::set([
    'SMARTY_PATH' => dirname(dirname(__FILE__)) . DS . 'include' . DS . 'smarty-3.1.27' . DS . 'libs' . DS,
    'WWW_ROOT' =>  WWW_ROOT,
    'WOO_PATH' => WOO_PATH,
    'ADDONS_PATH' => ADDONS_PATH
]);

// 注册woo命名空间
\think\Loader::addNamespace('woo', WOO_PATH);
include_once WOO_PATH . 'addons' . DS . 'common.php';


function url($url = '', $vars = '', $suffix = true, $domain = false) {
    if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false) {
        return $url;
    }
    $urlSplit = explode('/', $url);
    if (empty($urlSplit[0])) {
        array_shift($urlSplit);
    }

    if (count($urlSplit) <= 2 && $urlSplit[0] != $GLOBALS['controller']->params['module'] && !empty($GLOBALS['controller']->params['addon_name'])) {
        if (count($urlSplit) == 2) {
            array_unshift($urlSplit, $GLOBALS['controller']->params['addon_name']);
            array_unshift($urlSplit, $GLOBALS['controller']->params['module']);
        } elseif (count($urlSplit) == 1) {
            array_unshift($urlSplit, $GLOBALS['controller']->params['controller']);
            array_unshift($urlSplit, $GLOBALS['controller']->params['addon_name']);
            array_unshift($urlSplit, $GLOBALS['controller']->params['module']);
        } else {
            array_unshift($urlSplit, $GLOBALS['controller']->params['action']);
            array_unshift($urlSplit, $GLOBALS['controller']->params['controller']);
            array_unshift($urlSplit, $GLOBALS['controller']->params['addon_name']);
            array_unshift($urlSplit, $GLOBALS['controller']->params['module']);
        }
    }

    if (substr($url, 0, 1) != '/' && count($urlSplit) > 3) {
        $urlSplit[2] = parse_name($urlSplit[2]);
        $url = '/' . implode($urlSplit, '/');
    }

    return Url::build($url, $vars, $suffix, $domain);
}

/**
* 打印、调试数据
*/
if (!function_exists('pr')) {
    function pr($var) {
    	if (config('app_debug')) {
    		$template = PHP_SAPI !== 'cli' ? '<pre>%s</pre>' : "\n%s\n";
    		printf($template, print_r($var, true));
    	}
    }
}

/**
* 获取url绝对跟路径
*/
if (!function_exists('get_request_absroot')) {
    function get_request_absroot() {
        $absroot = request()->root(true);
        $absroot = (substr($absroot, -10) != '/index.php' ? $absroot : substr($absroot, 0, -10));
        $absroot = (substr($absroot, -strlen(config('run_module_index')) - 1) != '/' . config('run_module_index') ? $absroot : substr($absroot, 0, -strlen(config('run_module_index')) - 1));
        return $absroot . '/';
    }
}

/**
* 获取url相对路径
*/
if (!function_exists('get_request_root')) {
    function get_request_root() {
        $root = request()->root();
        $root = (substr($root, -10) != '/index.php' ? $root : substr($root, 0, -10));
        $root = (substr($root, -strlen(config('run_module_index')) - 1) != '/' . config('run_module_index') ? $root : substr($root, 0, -strlen(config('run_module_index')) - 1));
        return $root . '/';
    }
}

/**
* 获取插件url绝对跟路径
*/
if (!function_exists('get_addon_absroot')) {
    function get_addon_absroot($name) {
        return get_request_absroot() . 'addons/' . $name . '/';
    }
}

/**
* 获取插件url相对路径
*/
if (!function_exists('get_addon_root')) {
    function get_addon_root($name) {
        return get_request_root() . 'addons/' . $name . '/';
    }
}

/**
* 加载或获取助手
*/
if (!function_exists('helper')) {
    function helper($helper, array $args = []) {
        $helper = parse_name($helper, 1);
        if (isset($GLOBALS['controller']->helper[$helper])) {
            return $GLOBALS['controller']->helper[$helper];
        } else {
            $class = 'woo\\helper\\' . $helper;
            if (class_exists($class)) {
                $helperObject = new $class;
                if (!empty($args)) {
                    foreach ($args as $key => $value) {
                        $helperObject->$key = $value;
                    }
                }
                return $GLOBALS['controller']->helper[$helper] =  $helperObject;
            } else {
                exception('helper not exists:' . $class);
            }
        }
    }
}

/**
* 判断条件中是否含有某个字段的条件
*/
if (!function_exists('where_exists')) {
    function where_exists($field, $where) {
        $field = strval($field);
        // 只判断数组格式的条件组合
        if (!is_array($where)) {
            return false;
        }
        $flag = false;
        foreach ($where as $key => $condition) {
            if (is_array($condition)) {
                list($m, $f) = plugin_split($condition[0]);
                if ($f === $field) {
                    $flag = true;
                    break;
                }
            } else {
                if (is_numeric($key)) {
                    continue;
                }
                list($m, $f) = plugin_split($key);
                if ($f === $field) {
                    $flag = true;
                    break;
                }
            }
        }
        return $flag;
    }
}

/**
* 写入数组文件缓存
*/
if (!function_exists('write_file_cache')) {
    function write_file_cache($name, $value)
    {
    	$exists = file_exists(APP_CACHE_DIR . $name . '.php');
    	file_put_contents(APP_CACHE_DIR . $name . '.php', "<?php\nreturn " . var_export($value, true) . "\n?>");
    	if (!$exists) {
    	   @chmod(APP_CACHE_DIR . $name . '.php',fileperms(APP_CACHE_DIR)&0xffff);
    	}
    }
}

/**
* 读取write_file_cache 创建的缓存文件
*/
if (!function_exists('read_file_cache')) {
    function read_file_cache($name)
    {
        if (is_file(APP_CACHE_DIR . $name . '.php')) {
            return @include(APP_CACHE_DIR . $name.'.php');
        } else {
            return [];
        }
    }
}

/**
* 以.分割字符串
*/
if (!function_exists('plugin_split')) {
    function plugin_split($name, $dotAppend = false, $plugin = null) {
    	if (strpos($name, '.') !== false) {
    		$parts = explode('.', $name, 2);
    		if ($dotAppend) {
    			$parts[0] .= '.';
    		}
    		return $parts;
    	}
    	return array($plugin, $name);
    }
}

if (!function_exists('string_insert')) {
    function string_insert($string, $value, $replace_bracket = true) {
    	$ret = TpText::insert($string, Hash::flatten((array)$value), array('before'=>'{', 'after'=>'}'));

    	if ($replace_bracket) {
    	   $ret = preg_replace('/\{[^\}]+\.[^\}]+\}/s', '', $ret);
    	}
    	return $ret;
    }
}

if (!function_exists('string_trim')) {
    function string_trim($string) {
        if (is_string($string)) {
            return trim($string);
        } elseif (is_array($string)) {
            foreach ($string as &$str) {
                $str = trim($str);
            }
            return $string;
        } else {
            return trim(strval($string));
        }
    }
}

if (!function_exists('ucfirst_deep')) {
    function ucfirst_deep($string) {
        if(!is_string($string)) return $string ;
        $strArr =  explode('_',$string);
        foreach($strArr as $str){
            $retStr .= ucfirst(strtolower($str)) ;
        }
        return $retStr ;
    }
}

if (!function_exists('furl')) {
    function furl($url) {
        $url = trim($url);
        if (strpos($url, 'http://') === false && strpos($url, 'https://') === false) {
            if ($url{0} == '/') $url = substr($url, 1);
            $url = $GLOBALS['controller']->absroot . $url;
        }
        return $url;
    }
}

/**
* 另外一种生成URL的方式
*/
if (!function_exists('U')) {
    function U($request=array(), $query = array(), $abs = false) {
        if (func_num_args() == 2 && is_bool(func_get_arg(1))) {
            $query=array() ;
            $abs = func_get_arg(1);
        }

        // 当前模块
        $module  = isset($request['module']) ? strtolower($request['module']) : $GLOBALS['controller']->params['module'];
        if (!in_array($module, config('allow_module_list'))) {
            $module = config('default_module');
        }
        if ($module == config('default_module')) {
            $module = null;
        }
        unset($request['module']);

        // 当前控制器
        $controller  = isset($request['controller']) ? ucfirst($request['controller']) : $GLOBALS['controller']->params['controller'];
        unset($request['controller']);

        // 当前方法
        $action = isset($request['action']) ? strtolower($request['action']) : $GLOBALS['controller']->params['action'];
        unset($request['action']);

        // mian
        $main = sprintf('%s%s%s', $module ? $module.'/' : '', $controller . '/', $action);

        // 去除掉某些参数名
        if (isset($request['remove'])) {
            $request['remove'] = Hash::normalize((array)$request['remove']);
            $request = array_diff_key($request, $request['remove']);
            unset($request['remove']) ;
        }

        // $request
        if (!is_array($query)) {
            $query = explode('&', $query);
        }
        $request = array_diff_key($request, $query);

        // $query_arr
        if($query){
            foreach ((array)$query as $name => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $query_arr[] = "{$name}={$value}";
            }
        }
        return  html_entity_decode(sprintf("%s%s%s",$abs ? $_SERVER['REQUEST_SCHEME'] . '://'.$_SERVER['SERVER_NAME'] : '' , url($main, $request), $query_arr ? '?' . implode('&', $query_arr) : ''));
    }
}

if (!function_exists('static_cache_shallow')) {
    function static_cache_shallow($key) {
        if (!isset($GLOBALS[$key])) {
            $GLOBALS[$key] = read_file_cache($key);
        }
        if (!is_array($GLOBALS[$key])) {
            $GLOBALS[$key] = array();
        }
        $args = func_get_args();
        if (func_num_args() == 1) {
            return $GLOBALS[$key];
        }
        array_shift($args);

        return Hash::get($GLOBALS[$key], implode('.', $args));
    }
}

if (!function_exists('static_cache_deep')) {
    function static_cache_deep($key, $level1 = null, $level2 = null) {
        if (!isset($GLOBALS[$key])) {
            $GLOBALS[$key] = read_file_cache($key);
        }
        if (!is_array($GLOBALS[$key])) {
            $GLOBALS[$key] = array();
        }
        if (func_num_args() == 1) {
            return $GLOBALS[$key];
        } elseif (func_num_args() == 2) {
            if (isset($level1)) {
                if (array_key_exists($level1, $GLOBALS[$key])) {
                    return $GLOBALS[$key][$level1];
                } else {
                    if (isset($GLOBALS[$key]['list'][$level1])) {
                        return $GLOBALS[$key]['list'][$level1];
                    } else {
                        return null;
                    }
                }
            } else {
                return null;
            }
        } elseif (func_num_args() >= 3) {
            if (isset($level1) && isset($level2)) {
                if(array_key_exists($level1, $GLOBALS[$key])) {
                    return $GLOBALS[$key][$level1][$level2];
                }
                else {
                    if (isset($GLOBALS[$key]['list'][$level1][$level2])) {
                        return $GLOBALS[$key]['list'][$level1][$level2];
                    } else {
                       return null;
                    }
                }
            } else {
                return null;
            }
        }
    }
}

if (!function_exists('static_cache_closest')) {
    function static_cache_closest($key, $id) {
    	$ret = array($id);
    	$childrens = static_cache_deep($key, 'children', $id);
    	if (!$childrens) {
    	   return $ret;
        } else {
    		foreach ($childrens as $sub_id) {
    			$ret=array_merge($ret, static_cache_closest($key, $sub_id));
    		}
    	}
    	return $ret;
    }
}

/**
* 获取前台栏目数据
*/
if (!function_exists('menu')) {
    function menu() {
    	return call_user_func_array('static_cache_deep', array_merge(array('Menu'), func_get_args()));
    }
}

/**
* 获取后台栏目数据
*/
if (!function_exists('adminmenu')) {
    function adminmenu() {
    	return call_user_func_array('static_cache_deep', array_merge(array('AdminMenu'), func_get_args()));
    }
}

/**
* 获取权限节点数据
*/
if (!function_exists('powertree')) {
    function powertree() {
    	return call_user_func_array('static_cache_deep', array_merge(array('PowerTree'), func_get_args()));
    }
}

/**
* 获取用户栏目数据
*/
if (!function_exists('managemenu')) {
    function managemenu() {
    	return call_user_func_array('static_cache_deep', array_merge(array('ManageMenu'), func_get_args()));
    }
}

/**
* 获取字典数据
*/
if (!function_exists('dict')) {
    function dict() {
    	return call_user_func_array('static_cache_shallow', array_merge(array('Dictionary'), func_get_args()));
    }
}

/**
* 获取系统设置数据
*/
if (!function_exists('setting')) {
    function setting() {
    	return call_user_func_array('static_cache_shallow', array_merge(array('Setting'), func_get_args()));
    }
}

if (!function_exists('get_closest_menus')) {
    function get_closest_menus($menu_id) {
    	return call_user_func_array('static_cache_closest', array_merge(array('Menu'), func_get_args()));
    }
}

if (!function_exists('get_closest_family')) {
    function get_closest_family($key, $id , &$family = []){
        if (!isset($GLOBALS[$key])) {
            $GLOBALS[$key] = read_file_cache($key);
        }
    	if (!is_array($GLOBALS[$key])) {
            $family = $id;
            return false;
    	}
        $family[] = $id;

        //$closest_id = array_keys(reset($GLOBALS[$key]))[0];
        if ($GLOBALS[$key]['list'][$id]['parent_id']) {
            get_closest_family($key, $GLOBALS[$key]['list'][$id]['parent_id'], $family);
        } else {
            $family = array_reverse($family);
        }
    }
}

if (!function_exists('_get_first_letter')) {
    function _get_first_letter($str)
    {
    	$fchar = ord($str{0});
    	if($fchar >= ord("A") and $fchar <= ord("z")) {
    	   return strtoupper($str{0});
    	}
    	$s1 = @iconv("UTF-8", "gb2312", $str);
    	$s2 = @iconv("gb2312", "UTF-8", $s1);
    	if ($s2 == $str) {
    	   $s = $s1;
        }
    	else {
    	   $s = $str;
        }
    	$asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    	if($asc >= -20319 and $asc <= -20284) return "A";
    	if($asc >= -20283 and $asc <= -19776) return "B";
    	if($asc >= -19775 and $asc <= -19219) return "C";
    	if($asc >= -19218 and $asc <= -18711) return "D";
    	if($asc >= -18710 and $asc <= -18527) return "E";
    	if($asc >= -18526 and $asc <= -18240) return "F";
    	if($asc >= -18239 and $asc <= -17923) return "G";
    	if($asc >= -17922 and $asc <= -17418) return "H";
    	if($asc >= -17417 and $asc <= -16475) return "J";
    	if($asc >= -16474 and $asc <= -16213) return "K";
    	if($asc >= -16212 and $asc <= -15641) return "L";
    	if($asc >= -15640 and $asc <= -15166) return "M";
    	if($asc >= -15165 and $asc <= -14923) return "N";
    	if($asc >= -14922 and $asc <= -14915) return "O";
    	if($asc >= -14914 and $asc <= -14631) return "P";
    	if($asc >= -14630 and $asc <= -14150) return "Q";
    	if($asc >= -14149 and $asc <= -14091) return "R";
    	if($asc >= -14090 and $asc <= -13319) return "S";
    	if($asc >= -13318 and $asc <= -12839) return "T";
    	if($asc >= -12838 and $asc <= -12557) return "W";
    	if($asc >= -12556 and $asc <= -11848) return "X";
    	if($asc >= -11847 and $asc <= -11056) return "Y";
    	if($asc >= -11055 and $asc <= -10247) return "Z";
    	return null;
    }
}

/**
* 获取中文字首字母拼音
*/
if (!function_exists('get_first_letter')) {
    function get_first_letter($zh, $limit=1)
    {
    	$ret = "";
    	$s1 = @iconv("UTF-8", "gb2312", $zh);
    	$s2 = @iconv("gb2312", "UTF-8", $s1);
    	if ($s2 == $zh) {
    	   $zh = $s1;
        }
    	for ($i = 0; $i < strlen($zh); $i++) {
    		if (!$limit) {
    		   break;
    		}
    		$s1 = substr($zh,$i,1);
    		$p = ord($s1);
    		if ($p > 160) {
    			$s2 = substr($zh,$i++,2);
    			$new_charater = _get_first_letter($s2);
    			if(!$new_charater) {
                    continue;
    			}
    			$ret .= _get_first_letter($s2);
    		} else {
    			if(preg_match('/\s/',$s1)) {
	               continue;
    			}
    			$ret .= $s1;
    		}
    		$limit--;
    	}
    	return $ret;
    }
}

/**
* 返回字节数
*/
if (!function_exists('return_bytes')) {
    function return_bytes($val) {
    	$val = trim($val);
    	$last = strtolower($val{strlen($val)-1});
    	switch($last) {
    	   // The 'G' modifier is available since PHP 5.1.0
    	   case 'g':
    		   $val *= 1024;
    	   case 'm':
    		   $val *= 1024;
    	   case 'k':
    		   $val *= 1024;
    	}
    	return $val;
    }
}

/**
* 返回友好的文件大小数
*/
if (!function_exists('return_size')) {
    function return_size($bytes, $separator='')
    {
    	//utility functions
    	$kb = 1024;          //Kilobyte
    	$mb = 1024 * $kb;    //Megabyte
    	$gb = 1024 * $mb;    //Gigabyte
    	$tb = 1024 * $gb;    //Terabyte

    	if($bytes < $kb)
    		return $bytes."{$separator}B";
    	else if($bytes < $mb)
    		return round($bytes/$kb,2)."{$separator}KB";
    	else if($bytes < $gb)
    		return round($bytes/$mb,2)."{$separator}MB";
    	else if($bytes < $tb)
    		return round($bytes/$gb,2)."{$separator}GB";
    	else
    		return round($bytes/$tb,2)."{$separator}TB";
    }
}

/**
* 获取目录大小
*/
if (!function_exists('get_dir_size')) {
    function get_dir_size($dir)
    {
        $sizeResult = 0;
        $handle = opendir($dir);
        while (false !== ($FolderOrFile = readdir($handle)))
        {
            if($FolderOrFile != "." && $FolderOrFile != "..") {
                if (is_dir("$dir/$FolderOrFile")) {
                    $sizeResult += get_dir_size("$dir/$FolderOrFile");
                } else {
                    $sizeResult += filesize("$dir/$FolderOrFile");
                }
            }
        }
        closedir($handle);
        return $sizeResult;
    }
}

/**
* 删除目录
*/
if (!function_exists('remove_dir')) {
    function remove_dir($dir)
    {
        if (strpos($dir, 'app') !== false) {
            return false ;
        }
        if (!file_exists($dir)) {
            return false ;
        }

        $handle = opendir($dir);
        while (false !== ($FolderOrFile = readdir($handle)))
        {
            if($FolderOrFile != "." && $FolderOrFile != "..") {
                if (is_dir("$dir/$FolderOrFile")) {
                    remove_dir("$dir/$FolderOrFile");
                } else {
                    @unlink("$dir/$FolderOrFile");
                }
            }
        }
        closedir($handle);
    }
}

/**
* 非法图片检测
*/
if (!function_exists('check_hex')) {
    function check_hex($image) {
        if (file_exists($image)) {
            $resource = fopen($image, 'rb');
            $fileSize = filesize($image);
            fseek($resource, 0);
            if ($fileSize > 512) {
                $hexCode = bin2hex(fread($resource, 512));
                fseek($resource, $fileSize - 512);
                $hexCode .= bin2hex(fread($resource, 512));
            } else {
                $hexCode = bin2hex(fread($resource, $fileSize));
            }

            fclose($resource);

            if (preg_match("/(3c25.*?28.*?29.*?253e)|(3c3f.*?28.*?29.*?3f3e)|(3C534352495054)|(2F5343524950543E)|(3C736372697074)|(2F7363726970743E)/is", $hexCode)) {
                return true;
            }
        }
        return false;
    }
}

/**
* 文件上传
*/
if (!function_exists('upload_file')) {
    function upload_file($file_info, $folder = 'general', $filename = null, $forbidden_ext = ['exe', 'php', 'asp', 'bat', 'asa', 'vbs'])
    {
    	$GLOBALS['upload_file_error'] = '';
    	switch ($file_info['error']) {
    		case '0':
    			break;
    		case '1':
    			$GLOBALS['upload_file_error'] = '文件大小超过了php.ini定义的upload_max_filesize值';
    			break;
    		case '2':
    			$GLOBALS['bbot_upload_file_error'] = '文件大小超过了HTML定义的MAX_FILE_SIZE值';
    			break;
    		case '3':
    			$GLOBALS['upload_file_error'] = '文件上传不完全';
    			break;
    		case '4':
    			$GLOBALS['upload_file_error'] = '无文件上传';
    			break;
    		case '6':
    			$GLOBALS['upload_file_error'] = '缺少临时文件夹';
    			break;
    		case '7':
    			$GLOBALS['upload_file_error'] = '写文件失败';
    			break;
    		case '8':
    			$GLOBALS['upload_file_error'] = '上传被其它扩展中断';
    			break;
    		case '':
    			$GLOBALS['upload_file_error'] = '上传表单错误';
    			break;
    		case '999':
    		default:
    			$GLOBALS['upload_file_error'] = '未知错误';
    	}
    	if (!empty($GLOBALS['upload_file_error'])) {
    	   return false;
    	}
    	if (!is_uploaded_file($file_info['tmp_name'])) {
    		if (!empty($GLOBALS['last_upload'])) {
    			$GLOBALS['last_upload'] = null;
    			return $GLOBALS['last_upload'];
    		} else {
    			$GLOBALS['upload_file_error'] = '不是上传文件';
    			return false;
    		}
    	}
    	$ext = explode('.', $file_info['name']);
    	$ext = strtolower(array_pop($ext));
    	if (in_array($ext, $forbidden_ext)) {
    		$GLOBALS['upload_file_error'] = "不允许上传后缀名为[$ext]的文件";
    		return false;
    	}
        if (in_array($ext, ['gif', 'jpg', 'jpeg', 'png', 'bmp']) && check_hex($file_info['tmp_name'])) {
            $GLOBALS['upload_file_error'] = "上传文件非法";
    		return false;
        }
    	$basepath = WWW_ROOT . 'upload' . DS . $folder . DS;
    	if (!file_exists($basepath)) {
    	   mkdir($basepath);
    	}
    	$basepath = $basepath . date('Ym');
    	if (!file_exists($basepath)) {
    	   mkdir($basepath);
    	}
    	if (empty($filename)) {
    	   $filename = uniqid(mt_rand()) . '.' . $ext;
    	} else {
    	   $filename = $filename . '.' . $ext;
    	}
    	move_uploaded_file($file_info['tmp_name'], $basepath . DS . $filename);
    	$GLOBALS['last_upload'] = 'upload/' . $folder . '/' . date('Ym') . '/' . $filename;
    	return $GLOBALS['last_upload'];
    }
}

/**
* 二维码生成函数
*/
if (!function_exists('make_qrcode')) {
    function make_qrcode($text, $outfile = false, $logofile = false, $level = 'H', $size = 10, $margin = 2, $saveandprint = false, $resize = 0){
        $hphqrcode_file  =  \Env::get('root_path') . 'include' . DS .'phpqrcode-1.1.4' . DS . 'phpqrcode.php';
        include_once $hphqrcode_file;
        $filename = $defoutfile  = $outfile;
        $isLogo = false;
        if ($logofile && file_exists($logofile)) {
            $isLogo = true;
            if (!$outfile) $outfile = true ;
        }
        $basepath = WWW_ROOT . 'upload' . DS . 'qrcode' . DS;
    	if (!file_exists($basepath)) mkdir($basepath);
    	$basepath = $basepath . date('Ym') . DS;
    	if (!file_exists($basepath)) mkdir($basepath);
        $return_file  = uniqid(mt_rand()) . '.png';
        $filename = $basepath . $return_file;

        $qrcode = QRcode::png($text, $filename, $level, $size, $margin, $saveandprint);

        if ($isLogo && file_exists($filename)) {
            $QR = imagecreatefromstring(file_get_contents($filename));
            $logo = imagecreatefromstring(file_get_contents($logofile));
            $QR_width = imagesx($QR);
            $QR_height = imagesy($QR);
            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);
            $logo_qr_width = $QR_width / 5;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            $from_width = ($QR_width - $logo_qr_width) / 2;
            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
            imagepng($QR, $filename);
        }  else {
            $QR = imagecreatefrompng($filename);
        }

        if ($resize) {
            $image = \think\Image::open($filename);
            $image->thumb($resize, $resize)->save($filename);
            $QR = imagecreatefrompng($filename);
        }
        if (!$defoutfile) {
            @unlink($filename);
            header("P3P: CP='IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT'");
            header('Content-type: image/x-png');
    		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    		header("Cache-Control: no-cache");
    		header("Pragma: no-cache");
    		imagepng($QR);
            exit;
        }
        return 'upload/qrcode/' . date('Ym') . '/' . $return_file;
    }
}

/**
* 验证码（邮件或短信）生成
*/
if (!function_exists('get_verify_code')) {
    function get_verify_code($to, $length = 5, $expired = 300, $is_md5 = false)
    {
        $charBase = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $shuffleBase=range(0, count($charBase) - 1);
    	shuffle($shuffleBase);
    	$ret = '';
    	for($i = 0; $i < $length; $i++){
    		$ret .= $charBase[$shuffleBase[$i]];
    	}
        Db('Verify')->insert([
            'to' => $to,
            'code' => $ret,
            'is_verify' => 0,
            'expired' => $expired > 0 ? time() + $expired : 0,
            'created' => date('Y-m-d H:i:s')
        ]);
        if ($is_md5) {
            return md5($ret);
        } else {
            return $ret;
        }
    }
}

/**
* 通过get_verify_code 生成函数的验证
*/
if (!function_exists('check_verify_code')) {
    function check_verify_code($to, $code, $is_md5 = false)
    {
        $codeData = Db('Verify')->where([
            ['is_verify', '=', 0],
            ['to', '=', $to]
        ])->order(['id' => 'DESC'])->find();

        if (empty($codeData)) {
            return false;
        }

        if ($codeData['expired'] <= time() && $codeData['expired'] > 0) {
            return false;
        }

        Db('Verify')->where('id', '=', $codeData['id'])->update(['is_verify' => 1]);
        if ($is_md5) {
            $verify_code = md5($codeData['code']);
        } else {
            $verify_code = $codeData['code'];
        }
        return !!$verify_code == $code;
    }
}

/**
* 邮件发送
*/
if (!function_exists('send_email')) {
    function send_email($templateVar, $emailNumbers, $templateParam = [])
    {
        $template = Db('Email')->where('vari', '=', $templateVar)->find();
        if (empty($template)) {
            return '标识为[' . $templateVar . ']的邮件模板不存在';
        }

        $auth  = new \woo\helper\Auth();
        $logined = $auth->user();
        //准备变量
        $replace = array_merge([
            'date' => date('Y-m-d'),
            'datetime' => date('Y-m-d H:i:s'),
            'site_title' => setting('site_title'),
            'corp_title' => setting('corp_title'),
            'tel' => setting('tel'),
            'address' => setting('address'),
            'email' => setting('email'),
            'url' => $GLOBALS['controller']->absroot,
            'username' => $logined['username'],
            'nickname' => $logined['Member']['nickname'],
            'truename' => $logined['Member']['truename'],
            'headimg' => $logined['Member']['headimg']
        ], $templateParam);
        $real_replace = [];
        foreach ($replace as $key => $value) {
            if (substr($key, 0, 1) != '$') {
                $real_replace['$' . $key] = $value;
            } else {
                $real_replace[$key] = $value;
            }
        }

        //变量替换
        $template['content'] = strtr($template['content'], $real_replace);

        settype($emailNumbers, 'array');

        if (PHP_VERSION > 5.5) {
            $email_object  = new \woo\api\SendMailer5();
        } else {
            $email_object  = new \woo\api\SendMailer5();
        }

        $result = $email_object->send($emailNumbers, $template['email_title'], $template['content'], $template['fromname'] ? $template['fromname'] : '', $template['file'] ? $template['file'] : '');

        return $result;
    }
}

/**
* CURL请求函数
* @param  array $rq http请求信息
*                   url        : 请求的url地址
*                   method     : 请求方法，'get', 'post', 'put', 'delete', 'head'
*                   data       : 请求数据，如有设置，则method为post
*                   header     : 需要设置的http头部
*                   host       : 请求头部host
*                   timeout    : 请求超时时间
*                   cert       : ca文件路径
*                   ssl_version: SSL版本号
* @return string    http请求响应
*/
if (!function_exists('curl_send')) {
    function curl_send($rq) {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $rq['url']);
        switch (true) {
            case isset($rq['method']) && in_array(strtolower($rq['method']), array('get', 'post', 'put', 'delete', 'head')):
                $method = strtoupper($rq['method']);
                break;
            case isset($rq['data']):
                $method = 'POST';
                break;
            default:
                $method = 'GET';
        }
        $header = isset($rq['header']) ? $rq['header'] : array();
        $header[] = 'Method:' . $method;

        $header[] = "User-Agent:Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)";
        isset($rq['host']) && $header[] = 'Host:'.$rq['host'];
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $method);
        isset($rq['timeout']) && curl_setopt($curlHandle, CURLOPT_TIMEOUT, $rq['timeout']);
        isset($rq['data']) && in_array($method, array('POST', 'PUT')) && curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $rq['data']);

        $ssl = substr($rq['url'], 0, 8) == "https://" ? true : false;
        if( isset($rq['cert'])){
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER,true);
            curl_setopt($curlHandle, CURLOPT_CAINFO, $rq['cert']);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST,2);
            if (isset($rq['ssl_version'])) {
                curl_setopt($curlHandle, CURLOPT_SSLVERSION, $rq['ssl_version']);
            } else {
                curl_setopt($curlHandle, CURLOPT_SSLVERSION, 4);
            }
        }else if( $ssl ){
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER,false);   //true any ca
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST,1);       //check only host
            if (isset($rq['ssl_version'])) {
                curl_setopt($curlHandle, CURLOPT_SSLVERSION, $rq['ssl_version']);
            } else {
                curl_setopt($curlHandle, CURLOPT_SSLVERSION, 4);
            }
        }
        $return['content'] = curl_exec($curlHandle);
        $return['result'] = curl_getinfo($curlHandle);
        curl_close($curlHandle);
        return $return;
    }
}

/**
* 是否是json格式字符串
*/
if (!function_exists('is_json_validate')) {
    function is_json_validate($string) {
        /*json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
        */
        return !!preg_match('/^(\[|\{).*(\}|\])$/', $string);
    }
}

/**
* 获取栏目数据
*/
if (!function_exists('get_menu_data')) {
    function get_menu_data($menu_id, $limit = 4, $options = []) {
        $options = $options + [
            'family' => false,
            'with' => [],
            'where' => [],
            'field' => false,
            'order' => [],
            'cache' => false,// 是否缓存
            'cacheKey' => false//缓存标识，如果一个页面对同一栏目以不同方式提取了2次，就很必要了
        ];

        if ($options['cache'] === true) {
            $options['cache'] = 600;// 默认缓存时间
        }

        if ($options['cache']) {
            $options['cache'] = intval($options['cache']);
            $options['cacheKey'] = $options['cacheKey'] === false ? 'woo_menu_data_' . $menu_id : 'woo_menu_data_custom_' . strval($options['cacheKey']);

            $data = \Cache::get($options['cacheKey']);
            if (!empty($data)) {
                return $data;
            }
        }
        $menu_id = intval($menu_id);
        if ($menu_id <= 0) {
            return [];
        }
        $modelName = menu($menu_id, 'type');
        $limit = intval($limit);
        if (empty($modelName) || $limit < 1) {
            return [];
        }
        $queryModel = model($modelName);
        $where = $options['where'];
        if ($options['family']) {
            $where[] = ['menu_id', 'IN', get_closest_menus($menu_id)];
        } else {
            $where[] = ['menu_id', '=', $menu_id];
        }
        if ($queryModel->form['is_verify'] && setting('is_verify') && !where_exists('is_verify', $where)) {
            $where[] = ['is_verify', '=', 1];
        }
        $with = $options['with'];
        $field = $options['field'];

        if (is_array($field)) {
            $with = Hash::normalize($with);
            foreach ($with as $assocModel => $assocInfo) {
                if ($queryModel->assoc[$assocModel]['type'] == 'belongsTo') {
                    // belongsTo 关联，决解如果没有关联字段，将不会自动with关联
                    $foreignKey = $assocInfo['foreignKey'] ? $assocInfo['foreignKey'] : parse_name($assocModel) . '_id';
                    if (!in_array($foreignKey, $field)) {
                        $field[] = $foreignKey;
                    }
                }
            }
        }

        $order = $options['order'];
        if (empty($order)) {
            if (!empty($queryModel->form['list_order'])) {
                $order['list_order'] = 'DESC';
            }
            $order['id'] = 'DESC';
        }
        if (!isset($options['type'])) {
            if ($limit != 1) {
                $options['type'] = 'select';
            } else {
                $options['type'] = 'find';
            }
        }
        $type = in_array($options['type'], ['find', 'select']) ? $options['type'] : 'select';
        $data = $queryModel
            ->with($with)
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->$type();
        $data = !empty($data) ? $data->toArray() : [];
        if (!$options['cache'] || empty($data)) {
            return $data;
        } else {
             \Cache::set($options['cacheKey'], $data, $options['cache']);
             return $data;
        }
    }
}

/**
* 获取广告数据
*/
if (!function_exists('get_ad_data')) {
    function get_ad_data($vari, $limit = 0, $options = []) {
        $options = $options + [
            'cache' => false, // 是否缓存
            'cacheKey' => false //缓存标识，如果一个页面对同一栏目以不同方式提取了2次，就很必要了
        ];

        if ($options['cache'] === true) {
            $options['cache'] = 600;// 默认缓存时间
        }

        if ($options['cache']) {
            $options['cache'] = intval($options['cache']);
            $options['cacheKey'] = $options['cacheKey'] === false ? 'woo_ad_data' . $vari : 'woo_ad_data_custom_' . strval($options['cacheKey']);

            $data = \Cache::get($options['cacheKey']);
            if (!empty($data)) {
                return $data;
            }
        }

        $ap_position_moble  = model('AdPosition');
        $ad[$vari] = $ap_position_moble
            ->where('vari', '=', $vari)
            ->find();
        if (!empty($ad[$vari])) {
            $ad[$vari] = $ad[$vari]->toArray();
            $ad_model  = model('Ad');

            if ($limit && $ad[$vari]['limit'] && $limit > $ad[$vari]['limit']) {
                $limit = $ad[$vari]['limit'];
            } else {
                if ($limit == 0) {
                    $limit = $ad[$vari]['limit'];
                }
            }
            $ads = $ad_model
                ->field(array_keys($ad_model->form))
                ->where([
                    ['ad_position_id', '=', $ad[$vari]['id']],
                    ['is_verify', '=', 1]
                ])
                ->order(['list_order' => 'DESC', 'id' => 'DESC'])
                ->limit($limit)
                ->select()
                ->toArray();
            if (!empty($ads)) {
                foreach ($ads as &$each) {
                    if (!$each['thumb']) {
                        $each['thumb'] = $each['image'];
                    }
                    if (!$each['mobile_image']) {
                        $each['mobile_image'] = $each['image'];
                    }
                    if(!$each['mobile_thumb']) {
                       $each['mobile_thumb']  = $each['mobile_image'];
                    }
                }
            }
            $ad[$vari]['Ad'] = $ads;

            if (!$options['cache']) {
                return $ad;
            } else {
                 \Cache::set($options['cacheKey'], $ad, $options['cache']);
                 return $ad;
            }
         } else {
            return [];
         }
    }
}

/**
* 从内容中匹配图片src
*/
if (!function_exists('match_src')) {
    function match_src($str, $number = 1, $all = false) {
    	$result = preg_match_all("/<img[^>]*src\s*=\s*[\"|\']?\s*([^>\"\'\s]*)/i", str_ireplace("\\","",$str), $arr);
        if ($all === false) {
            if($result) {
        	   return $arr[1][$number-1];
        	}
        } else {
            if ($result) {
                return $arr[1];
            }
        }
    	return false;
    }
}

/**
* 将一个数组转换为 XML 结构的字符串
* @param array $arr 要转换的数组
* @param int $level 节点层级, 1 为 Root.
* @return string XML 结构的字符串
*/
if (!function_exists('array2xml')) {
    function array2xml($arr, $level = 1) {
    	$s = $level == 1 ? "<xml>" : '';
    	foreach($arr as $tagname => $value) {
    		if (is_numeric($tagname)) {
    			$tagname = $value['TagName'];
    			unset($value['TagName']);
    		}
    		if(!is_array($value)) {
    			$s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
    		} else {
    			$s .= "<{$tagname}>" . array2xml($value, $level + 1) . "</{$tagname}>";
    		}
    	}
    	$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
    	return $level == 1 ? $s . "</xml>" : $s;
    }
}

if (!function_exists('extract_tag')) {
    function extract_tag($html, $type='end') {
		$arr_single_tags = array('meta', 'img', 'br', 'link', 'area');
		preg_match_all('#<([a-z]+)(?: .*)?>|</([a-z]+)>#iU', $html, $matches);
		$tags=array('complete' => array(), 'tag' => array());
		foreach($matches[0] as $index=>$tag){
			if(in_array($matches[1][$index], $arr_single_tags)) {
                continue;
			}
			$tags['complete'][] = $tag;
			$tags['tag'][] = $matches[1][$index] ? $matches[1][$index] : '/' . $matches[2][$index];
		}
		$open_stack = [];
		foreach($tags['tag'] as $index=>$item){
			if($item{0} == '/'){
				$temp = substr($item, 1);
				if(count($open_stack) && $tags['tag'][end($open_stack)] == $temp) {
					array_pop($open_stack);
				}
			}
			else{
				array_push($open_stack, $index);
			}
		}
		if($type=='start') {
            return array_intersect_key($tags['complete'], array_flip($open_stack));
		} else {
		    return array_reverse(array_intersect_key($tags['tag'], array_flip($open_stack)));
		}
	}
}

if (!function_exists('close_tag')) {
	function close_tag($html) {
		$html = preg_replace('/<[^<>]*$/', '', $html);
		$closing_tags = extract_tag($html);
		return $html . ($closing_tags ? '</' . implode('></', $closing_tags) . '>' : '');
	}
}

if (!function_exists('get_page_count')) {
	function get_page_count($html, $delimiter = '/<div style="page-break-after:[^"]*"><span style="display:[^"]*">&nbsp;<\/span><\/div>/') {
		$pages = preg_split($delimiter, $html);
		return count($pages);
	}
}

if (!function_exists('extract_page')) {
	function extract_page($html, $page, $delimiter = '/<div style="page-break-after:[^"]*"><span style="display:[^"]*">&nbsp;<\/span><\/div>/') {
		$pages = preg_split($delimiter, $html);
		$conjected_pages = implode('', array_slice($pages, 0, $page));
		$opening_tags = extract_tag($conjected_pages, 'start');
		$conjected_pages = implode('', array_slice($pages, 0, $page + 1));
		$closing_tags = extract_tag($conjected_pages);
		return implode('', $opening_tags) . $pages[$page] . ($closing_tags ? '</' . implode('></', $closing_tags) . '>' : '');
	}
}

if (!function_exists('is_woo_installed')) {
    function is_woo_installed() {
        static $isWooInstalled;
        if (empty($isWooInstalled)) {
            $isWooInstalled = file_exists(ROOT_PATH. 'data' . DS . 'install.lock');
        }
        return $isWooInstalled;
    }
}

if (!function_exists('test_write_dir')) {
    function test_write_dir($dir)
    {
        $tfile = "_test.txt";
        $fp    = @fopen($dir . "/" . $tfile, "w");
        if (!$fp) {
            return false;
        }
        fclose($fp);
        $rs = @unlink($dir . "/" . $tfile);
        if ($rs) {
            return true;
        }
        return false;
    }
}

if (!function_exists('get_file_sql')) {
    function get_file_sql($file, $tablePre, $charset = 'utf8', $defaultTablePre = 'woo_', $defaultCharset = 'utf8')
    {
        if (file_exists($file)) {
            $sql = file_get_contents($file);
            $sql = str_replace("\r", "\n", $sql);
            $sql = str_replace("BEGIN;\n", '', $sql);//兼容 navicat 导出的 insert 语句
            $sql = str_replace("COMMIT;\n", '', $sql);//兼容 navicat 导出的 insert 语句
            $sql = str_ireplace($defaultCharset, $charset, $sql);
            $sql = preg_replace('/AUTO_INCREMENT=\d{1,}/i', 'AUTO_INCREMENT=1', $sql);
            $sql = trim($sql);
            $sql  = str_replace(" `{$defaultTablePre}", " `{$tablePre}", $sql);
            $sqls = explode(";\n", $sql);
            return $sqls;
        }
        return [];
    }
}

if (!function_exists('execute_sql')) {
    function execute_sql($db, $sql)
    {
        $sql = trim($sql);
        preg_match('/CREATE TABLE .+ `([^ ]*)`/', $sql, $matches);
        if ($matches) {
            $table_name = $matches[1];
            $msg        = "创建数据表{$table_name}";
            try {
                $db->execute($sql);
                return [
                    'error'   => 0,
                    'message' => $msg . ' 成功！'
                ];
            } catch (\Exception $e) {
                return [
                    'error'     => 1,
                    'message'   => $msg . ' 失败！',
                    'exception' => $e->getMessage()
                ];
            }

        } else {
            try {
                $db->execute($sql);
                return [
                    'error'   => 0,
                    'message' => 'SQL执行成功!'
                ];
            } catch (\Exception $e) {
                return [
                    'error'     => 1,
                    'message'   => 'SQL执行失败！',
                    'exception' => $e->getMessage()
                ];
            }
        }
    }
}

/**
* 读取EXCEL表格数据
*/
if (!function_exists('excel_reader')) {
    function excel_reader($file, $title = true) {
        $file = WWW_ROOT . $file;
        if(!is_file($file)){
            $GLOBALS['excel_read_error'] = '文件不存在';
            return false;
        }
        include_once ROOT_PATH . 'include' . DS . 'PHPExcel-1.8.0' . DS . 'PHPExcel.php';

        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if (!$PHPReader->canRead($file))
        {
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if (!$PHPReader->canRead($file))
            {
                $PHPReader = new \PHPExcel_Reader_CSV();
                if (!$PHPReader->canRead($file))
                {
                    $GLOBALS['excel_read_error'] = '不支持的导入格式';
                    return false;
                }
            }
        }

        $PHPExcel = $PHPReader->load($file);
        $currentSheet = $PHPExcel->getSheet(0);
        $allColumn = $currentSheet->getHighestDataColumn();
        $allRow = $currentSheet->getHighestRow();
        $maxColumnNumber = \PHPExcel_Cell::columnIndexFromString($allColumn);
        if ($title) {
            for ($currentRow = 1; $currentRow <= 1; $currentRow++)
            {
                for ($currentColumn = 0; $currentColumn < $maxColumnNumber; $currentColumn++)
                {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $val = trim($val);
                    $start = strpos($val, '#');
                    $o_fields[] = $val;
                    if ($start === false) {
                        $fields[] = $val;
                    } else {
                        $fields[] = trim(substr($val, $start + 1));
                    }
                }
            }
        }

        $list = [];
        for ($currentRow = $title ? 2 : 1; $currentRow <= $allRow; $currentRow++)
        {
            $values = [];
            $index = 0;
            for ($currentColumn = 0; $currentColumn < $maxColumnNumber; $currentColumn++)
            {
                $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                if ($title) {
                    $values[$fields[$index]] = is_null($val) ? '' : $val;
                } else {
                    $values[] = is_null($val) ? '' : $val;
                }
                $index++;
            }
            $list[] = $values;
        }
        return [
            'fields' => $o_fields,
            'list' => $list
        ];
    }
}

/**
* 新增用户积分
*/
if (!function_exists('add_user_score')) {
    function add_user_score($user_id, $score, $title) {
        $data['user_id'] = intval($user_id);
        $data['score'] = floatval($score);
        $data['title'] = trim($title);
        $model = model('UserScore');
        $rslt = $model->createData($data);
        if ($rslt) {
            return true;
        } else {
            return $model->getError();
        }

    }
}


/**
* 图片转base64
*/
if (!function_exists('base64_encode_image')) {
    function base64_encode_image($image_file) {
        $image_file = WWW_ROOT . $image_file;
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }
}

/**
* 目录复制
*/
if (!function_exists('copydirs')) {
    function copydirs($source, $dest) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
		if (!in_array(substr($dest, -1, 1), ['/', '\\'])) {
            $dest .= DS;
		}
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $sontDir = $dest . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . $iterator->getSubPathName());
            }
        }
    }
}

/**
* 删除目录
*/
if (!function_exists('rmdirs')) {
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }

}

/**
* 文件分隔符
*/
if (!function_exists('separator_real')) {
    function separator_real($path, $isHttp = false) {
        if (!$isHttp) {
            return str_replace('/', DS, $path);
        } else {
            return str_replace("\\", '/', $path);
        }
    }
}

if (!function_exists('query_field')) {
    function query_field($value, $info) {
        if (!isset($query_result_list)) {
            static $query_result_list = [];
        }
        list($foreign, $from_field, $to_value) = explode('_', $info);//格式:模型_原始字段_查询目标字段
        if (empty($from_field)) {
            $from_field = 'id';
        }
        if (empty($to_value)) {
            $to_value = 'title';
        }
        if (!isset($query_result_list[$foreign][$from_field][$to_value][$value])) {
            $query_result_list[$foreign][$from_field][$to_value][$value] = Db::name($foreign)->where($from_field, '=', $value)->value($to_value);
        }
        return $query_result_list[$foreign][$from_field][$to_value][$value];
    }
}

/**
* 栏目连接生成
*/
if (!function_exists('menu_link')) {
    function menu_link($id = 0, $kw = null, $kw_var = 'kw') {
        $menu_data = menu(intval($id));
        if (empty($menu_data)) {
            return '#';
        }
        if ($menu_data['type'] !== 'Exlink') {
            $alias = trim($menu_data['alias']);
            if (empty($alias)) {
                return url($menu_data['type'] . '/show', ['menu_id' => $menu_data['id'], $kw_var => $kw]);
            } else {
                if (empty($kw)) {
                    return get_request_absroot() . $alias . '.' .config('url_html_suffix');
                } else {
                    return get_request_absroot() . $alias . '/' . $kw_var . '/' . $kw . '.' .config('url_html_suffix');
                }
            }
        } else {
            return furl($menu_data['ex_link']);
        }
    }
}

/**
* 详情页面连接
*/
if (!function_exists('view_link')) {
    function  view_link($item, $mdl = null, $pk = 'id') {
        if (!is_array($item)) {
            return config('app_debug') ? '参数应该是数组，而非' . gettype($item) : '';
        }

        if (!isset($item[$pk])) {
            return config('app_debug') ? "你传的第一个参数中没有{$pk}属性" : '';
        }

        if (empty($mdl)) {
            $mdl = $GLOBALS['controller']->params['controller'];
        }
        if (empty($item['link'])) {
            if (!empty($item['menu_id'])) {
                $alias = trim(menu(intval($item['menu_id']), 'alias'));
                if (empty($alias)) {
                    return url($mdl . '/view', ['id' => $item[$pk]]);
                } else {
                    return get_request_absroot() . $alias . '/' . $item[$pk] . '.' . config('url_html_suffix');
                }
            } else {
                return url($mdl . '/view', ['id' => $item[$pk]]);
            }
        } else {
            return furl($item['link']);
        }
    }
}

if (!function_exists('search_link')) {
    function search_link($id = 0, $mdl = null, $kw = null, $kw_var = 'kw') {
        $id = intval($id);
        if ($id > 0 && menu($id)) {
            return menu_link($id, $kw, $kw_var);
        }
        $mdl = trim($mdl);
        if ($id <= 0 && empty($mdl)) {
            return config('app_debug') ? "id参数和mdl参数不能同时为空" : '';
        }

        return url($mdl. '/' . 'search', [$kw_var => $kw]);
    }
}

/**
* 传递一个url判断是否有权限
*/
if (!function_exists('check_power')) {
    function check_power($url = '') {
        if (!config('auth.power_check')) {
            return true;
        }
        if (!isset($static_power_list)) {
             static $static_power_list = [];
             static $static_powertree_list = [];
        }
        if (empty($static_power_list)) {
            $static_power_list = !empty($GLOBALS['static_power_list']) ? $GLOBALS['static_power_list'] : helper('Auth')->getPowerList();
            if (empty($static_power_list)) {
                $static_power_list = ['no'];
            }
        }

        if (in_array('all/all', $static_power_list)) {
            return true;
        }

        if (empty($static_powertree_list)) {
            $static_powertree_list = Cache::get('static_powertree_list');
            if (empty($static_powertree_list)) {
                $list = Db::name('PowerTree')->field(['id', 'together'])->where('together', '<>', '')->select();
                if ($list) {
                    $static_powertree_list = Hash::combine($list, '{n}.id', '{n}.together');
                    Cache::set('static_powertree_list', $static_powertree_list, 3600);
                } else {
                    $static_powertree_list = ['no'];
                    Cache::set('static_powertree_list', $static_powertree_list, 3600);
                }
            }
        }
        if (empty($static_powertree_list) || in_array('no', $static_powertree_list)) {
            return true;
        }
        if (in_array('no', $static_power_list)) {
            return false;
        }
        if (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false) {
            return true;
        }

        $url = trim($url);
        if (empty($url)) {
            return true;
        }
        if (substr($url, 0, 1) == '/') {
           $url = substr($url, 1);
        }
        if (substr($url, 0, 4) == 'run/') {
           $url = substr($url, 4);
        }
        $urlParts = explode('/', $url);
        $urlReelParts2 = [];
        $urlReelParts3 = [];
        $i = 1;
        foreach ($urlParts as $item) {
            if ($i <= 2) {
                array_push($urlReelParts2, strtolower($item));
            }
            if ($i <= 3) {
                array_push($urlReelParts3, strtolower($item));
            } else {
                break;
            }
            $i++;
        }
        $urlReelParts2 = implode('/', $urlReelParts2);
        $urlReelParts3 = implode('/', $urlReelParts3);
        if (!in_array($urlReelParts2, $static_powertree_list) && !in_array($urlReelParts3, $static_powertree_list)) {
            return true;
        }
        return in_array($urlReelParts2, $static_power_list) || in_array($urlReelParts3, $static_power_list);
    }
}
    /**
    * 按系统需求重写model函数
    */
    function model($name = '', $layer = 'model', $appendSuffix = false) {
        if ($layer != 'model') {
            return app()->model($name, $layer, $appendSuffix);
        }

        list($dir, $model) = explode('/', $name);
        if (empty($model)) {
            $model = $dir;
            $dir = '';
        }
        $model = parse_name($model, 1);
        $dir = trim(strtolower($dir));
        if (class_exists('app\\' . $GLOBALS['controller']->params['module'] . '\\model\\'  . ($dir ? $dir . '\\' : '') . $model)) {
            return  app()->model('app\\' . $GLOBALS['controller']->params['module'] . '\\model\\' . ($dir ? $dir . '\\' : '') . $model);
        } elseif (class_exists('app\\common\\model\\' . ($dir ? $dir . '\\' : '') . $model)) {
            return  app()->model('app\\common\\model\\' . ($dir ? $dir . '\\' : '') . $model);
        } elseif (class_exists('app\\' . $GLOBALS['controller']->params['module'] . '\\model\\'. $model)) {
            return  app()->model('app\\' . $GLOBALS['controller']->params['module'] . '\\model\\' . $model);
        } elseif (class_exists('app\\common\\model\\' . $model)) {
            return app()->model('app\\common\\model\\' . $model);
        } else {
            return app()->model($name, $layer, $appendSuffix);
        }
    }

// +-------------------------------------------------------------------------------------------------
// | 分割线
// +-------------------------------------------------------------------------------------------------

$GLOBALS['Model']       = read_file_cache('Model');
$GLOBALS['Model_title'] = Hash::combine($GLOBALS['Model'], '{n}.model','{n}.cname');
$GLOBALS['Model_map']   = Hash::combine($GLOBALS['Model'], '{n}[is_menu=1].model', '{n}[is_menu=1].cname');

// 路由注册
foreach($GLOBALS['Model_map'] as $model => $name){
    // 列表页路由:/article/show/menu_id/5  => /article/show/5
    \Route::rule(Loader::parseName($model) . '/show/:menu_id', $model.'/show')->pattern(['menu_id'=>'\d+']);
    // 详情页路由:/article/view/id/5 => /article/5
    \Route::rule(Loader::parseName($model) . '/:id', $model.'/view')->pattern(['id'=>'\d+']);
}

// 导航别名 /news.html=>article/show?menu_id=73
if(menu('alias')){
    foreach(menu('alias') as $id => $alias){
        \Route::rule($alias . '/:id', Loader::parseName(menu($id, 'type')).'/view')->pattern(['id'=>'\d+']);
        //\Route::rule($alias, Loader::parseName(menu($id, 'type')) . '/show?menu_id=' . $id);
        \Route::rule($alias,  Loader::parseName(menu($id, 'type')) . '/show')->append(['menu_id' => $id]);
    }
}
