<?php

namespace woo\model\traits;

trait WooUpload
{
    function processUpload()
    {
        if (isset($this['change'])) {
            foreach ($this['change'] as $field => $flag) {
                if (empty(trim($this[$field]))) {
                    continue;
                }
                
                if ($flag === 'true') {
                    $file = $this[$field];
                    if (!file_exists(WWW_ROOT . $file)) {
                        continue;
                    }
                    $filename_parts = explode('.', $file);
				    $ext = strtolower(array_pop($filename_parts));
                    if (isset($this->form[$field]['image']['thumb'])) { 
                        $thumb_width = intval(isset($this->form[$field]['image']['thumb']['width']) ? $this->form[$field]['image']['thumb']['width'] : 0);
						$thumb_height = intval(isset($this->form[$field]['image']['thumb']['height']) ? $this->form[$field]['image']['thumb']['height'] : 0);
						$thumb_method = intval(isset($this->form[$field]['image']['thumb']['method']) ? $this->form[$field]['image']['thumb']['method'] : 0);
                        
                        list($thumb_width, $thumb_height, $thumb_method) = $this->getThumbInfo([$thumb_width, $thumb_height, $thumb_method], $this, $this->getData());
                        
                        $file_size  = getimagesize(WWW_ROOT . $file);
                        $thumb_width = $thumb_width > 0 ? $thumb_width : $file_size[0];
                        $thumb_height = $thumb_height > 0 ? $thumb_height : $file_size[1];
                        $thumb_method = in_array($thumb_method, [1, 2, 3, 4, 5, 6]) ? $thumb_method : 3;
                        
                        $basepath = WWW_ROOT . 'upload' . DS . 'thumbs' . DS;
                    	if (!file_exists($basepath)) {
                    	   mkdir($basepath);
                    	}
                    	$basepath = $basepath . date('Ym');
                    	if (!file_exists($basepath)) {
                    	   mkdir($basepath);
                    	}                    
                        $filename = substr(array_pop(explode('/', $file)), 0, -(strlen($ext) + 1)) . '_' . mt_rand() . '_' . $thumb_width . '_' . $thumb_height . '_' . $thumb_method;
                        
                        $image = \think\Image::open(WWW_ROOT . $file);
                        $rslt  = $image->thumb($thumb_width, $thumb_height, $thumb_method)->save($basepath . DS . $filename . '.' . $ext);
                        if ($rslt) {
                            $thumb = 'upload/thumbs/' . date('Ym') . '/' . $filename . '.' . $ext;
                            if (!isset($this->form[$field]['image']['thumb']['field'])) {
    							$this->form[$field]['image']['thumb']['field'] = 'thumb';
    						}
                            $this[$this->form[$field]['image']['thumb']['field']] = $thumb;
                        }
                    }
                    
                    if (isset($this->form[$field]['image']['resize'])) {
                        $resize_width = intval(isset($this->form[$field]['image']['resize']['width']) ? $this->form[$field]['image']['resize']['width'] : 0);
						$resize_height = intval(isset($this->form[$field]['image']['resize']['height']) ? $this->form[$field]['image']['resize']['height'] : 0);
						$resize_method = intval(isset($this->form[$field]['image']['resize']['method']) ? $this->form[$field]['image']['resize']['method'] : 0);
                        
                        list($resize_width, $resize_height, $resize_method) = $this->getResizeInfo([$resize_width, $resize_height, $resize_method], $this, $this->getData());
                        
                        $file_size  = getimagesize(WWW_ROOT . $file);
                        $resize_width = $resize_width > 0 ? $resize_width : $file_size[0];
                        $resize_height = $resize_height > 0 ? $resize_height : $file_size[1];
                        $resize_method = in_array($resize_method, [1, 2, 3, 4, 5, 6]) ? $resize_method : 3;
                        
                        $basepath = WWW_ROOT . 'upload' . DS . $this->name . DS;
                    	if (!file_exists($basepath)) mkdir($basepath);
                    	$basepath = $basepath.date('Ym');
                    	if (!file_exists($basepath)) mkdir($basepath);                        
                        $filename = substr(array_pop(explode('/', $file)), 0, -(strlen($ext) + 1)) . '_' . mt_rand() . '_' . $resize_width . '_' . $resize_height . '_' . $resize_method;
                        
                        $image = \think\Image::open(WWW_ROOT . $file);
                        $rslt  = $image->thumb($resize_width, $resize_height, $resize_method)->save($basepath . DS . $filename . '.' . $ext);
                        if ($rslt) {
                            $old_file = $file;
                            $file = 'upload/' . $this->name . '/' . date('Ym') . '/' . $filename . '.' . $ext;
                            @unlink(WWW_ROOT . $old_file);
                        }
                    }
                    /*
                    if ($this->form[$field]['upload']['nameField']) {
                        $this[$this->form[$field]['upload']['nameField']] = array_pop(explode('/', $file));
                    }
                    */
                    if ($this->form[$field]['upload']['sizeField']) {
                        $this[$this->form[$field]['upload']['sizeField']] = filesize(WWW_ROOT . $file);
                    } 
                }
            }
            unset($this['change']);
        }
        
        
        $uploads_info = [];
        
        if (!isset($_FILES['data']['name'][$this->name])) {
            return true;
        }
        
        // 处理当前模型上传数据
        if ($_FILES['data']['name'][$this->name]) {
            foreach ($_FILES['data']['name'][$this->name]['upload'] as $field => $info) {
                $uploads_info[$field]['name'] = $_FILES['data']['name'][$this->name]['upload'][$field];
                $uploads_info[$field]['type'] = $_FILES['data']['type'][$this->name]['upload'][$field];
                $uploads_info[$field]['tmp_name'] = $_FILES['data']['tmp_name'][$this->name]['upload'][$field];
                $uploads_info[$field]['error'] = $_FILES['data']['error'][$this->name]['upload'][$field];
                $uploads_info[$field]['size'] = $_FILES['data']['size'][$this->name]['upload'][$field];
            }
            unset($_FILES['data']['name'][$this->name]);
        }
        
        if ($uploads_info) { 
            foreach ($uploads_info as $field => $info) {
                if ($info['error'] == 4) {
                    continue;
                }
                if ($info['error']) {
                    $error_map = array(
                        '1' => '文件大小超过了php.ini定义的upload_max_filesize值',
                        '2' => '文件大小超过了HTML定义的max_file_size值',
                        '3' => '文件上传不完全',
                        '4' => '无文件上传',
                        '6' => '缺少临时文件夹',
                        '7' => '写文件失败',
                        '8' => '上传被其它扩展中断',
                        '' => '上传表单错误'
                    );
                    $error = $error_map[$info['error']];
                    if (!isset($error)) {
                        $error = "未知错误[代码:{$info['error']}]";
                    }
                    $this->forceError($field, $error);
                    continue;
                }

                if (!is_uploaded_file($info['tmp_name'])) {
                    $this->forceError($field, '非上传文件');
                    continue;
                }

                list($elem, $sub_elem) = plugin_split($this->form[$field]['elem']);
                if (!$elem) {
                    $elem = $sub_elem;
                }

                $filename_parts = explode('.', $info['name']);
				$ext = strtolower(array_pop($filename_parts));
                
                if ($elem === 'image') {
                    if (!isset($this->form[$field]['upload']['validExt'])) {
                        $this->form[$field]['upload']['validExt'] = array('png', 'gif', 'jpg', 'jpeg');
                    }
                }
                settype($this->form[$field]['upload']['validExt'], 'array');
                if ($this->form[$field]['upload']['validExt'] && !in_array($ext, $this->form[$field]['upload']['validExt'])) {
                    $this->forceError($field, '上传文件只接受后缀名为' . implode(',', $this->form[$field]['upload']['validExt']) . '的文件');
                    continue;
                }
                if (!isset($this->form[$field]['upload']['notValidExt'])) {
                    $this->form[$field]['upload']['notValidExt'] = array('exe', 'php', 'asp', 'bat', 'asa', 'vbs');
                }
                settype($this->form[$field]['upload']['notValidExt'], 'array');
                if ($this->form[$field]['upload']['notValidExt'] && in_array($ext, $this->form[$field]['upload']['notValidExt'])) {
                    $this->forceError($field, '上传文件不接受后缀名为' . implode(',', $this->form[$field]['upload']['notValidExt']) . '的文件');
                    continue;
                }

                if ($this->form[$field]['upload']['maxSize'] && $info['size'] > $this->form[$field]['upload']['maxSize'] * 1024) {
                    $this->forceError($field, '上传文件大小[' . return_size($info['size']) . ']超过允许最大值' . return_size($this->form[$field]['upload']['maxSize'] * 1024));
                    continue;
                }

                $file = upload_file($info, $this->name, null, $this->form[$field]['upload']['notValidExt']);
                
                
                if (!$file) {
                    $this->forceError($field, $GLOBALS['upload_file_error']);
                    continue;
                }
                
                // 上传成功
                \Hook::listen('upload', [
                    'url'      => $file,
                    'ext'      => $ext,
                    'basename' => $info['name'],
                    'size'     => $info['size'],
                    'user_id'  => helper('Auth')->user('id'),
                    'model'    => $this->name
                ]);
                
                if ($elem === 'image') {
                    if (isset($this->form[$field]['image']['thumb'])) { 
                        $thumb_width = intval(isset($this->form[$field]['image']['thumb']['width']) ? $this->form[$field]['image']['thumb']['width'] : 0);
						$thumb_height = intval(isset($this->form[$field]['image']['thumb']['height']) ? $this->form[$field]['image']['thumb']['height'] : 0);
						$thumb_method = intval(isset($this->form[$field]['image']['thumb']['method']) ? $this->form[$field]['image']['thumb']['method'] : 0);
                        
                        
                        list($thumb_width, $thumb_height, $thumb_method) = $this->getThumbInfo([$thumb_width, $thumb_height, $thumb_method], $this, $this->getData());
                        
                        $file_size  = getimagesize(WWW_ROOT . $file);
                        $thumb_width = $thumb_width > 0 ? $thumb_width : $file_size[0];
                        $thumb_height = $thumb_height > 0 ? $thumb_height : $file_size[1];
                        $thumb_method = in_array($thumb_method, [1,2,3,4,5,6]) ? $thumb_method : 3;
                        
                        $basepath = WWW_ROOT . 'upload' . DS . 'thumbs' . DS;
                    	if (!file_exists($basepath)) {
                    	   mkdir($basepath);
                    	}
                    	$basepath = $basepath . date('Ym');
                    	if (!file_exists($basepath)) {
                    	   mkdir($basepath);
                    	}                    
                        $filename = substr(array_pop(explode('/', $file)), 0, -(strlen($ext) + 1)) . '_' . $thumb_width . '_' . $thumb_height . '_' . $thumb_method;
                        
                        $image = \think\Image::open(WWW_ROOT . $file);
                        $rslt  = $image->thumb($thumb_width, $thumb_height, $thumb_method)->save($basepath . DS . $filename . '.' . $ext);
                        if ($rslt) {
                            $thumb = 'upload/thumbs/' . date('Ym') . '/' . $filename . '.' . $ext;
                            if (!isset($this->form[$field]['image']['thumb']['field'])) {
    							$this->form[$field]['image']['thumb']['field'] = 'thumb';
    						}
                            $this[$this->form[$field]['image']['thumb']['field']] = $thumb;
                        }
                    }
                    
                    if (isset($this->form[$field]['image']['resize'])) {
                        $resize_width = intval(isset($this->form[$field]['image']['resize']['width']) ? $this->form[$field]['image']['resize']['width'] : 0);
						$resize_height = intval(isset($this->form[$field]['image']['resize']['height']) ? $this->form[$field]['image']['resize']['height'] : 0);
						$resize_method = intval(isset($this->form[$field]['image']['resize']['method']) ? $this->form[$field]['image']['resize']['method'] : 0);
                        
                        list($resize_width, $resize_height, $resize_method) = $this->getResizeInfo([$resize_width, $resize_height, $resize_method], $this, $this->getData());
                        
                        $file_size  = getimagesize(WWW_ROOT . $file);
                        $resize_width = $resize_width > 0 ? $resize_width : $file_size[0];
                        $resize_height = $resize_height > 0 ? $resize_height : $file_size[1];
                        $resize_method = in_array($resize_method, [1,2,3,4,5,6]) ? $resize_method : 3;
                        
                        $basepath = WWW_ROOT . 'upload' . DS . $this->name . DS;
                    	if (!file_exists($basepath)) mkdir($basepath);
                    	$basepath = $basepath.date('Ym');
                    	if (!file_exists($basepath)) mkdir($basepath);                        
                        $filename = substr(array_pop(explode('/', $file)), 0, -(strlen($ext) + 1)) . '_' . $resize_width . '_' . $resize_height . '_' . $resize_method;
                        
                        $image = \think\Image::open(WWW_ROOT . $file);
                        $rslt  = $image->thumb($resize_width, $resize_height, $resize_method)->save($basepath . DS . $filename . '.' . $ext);
                        if ($rslt) {
                            $old_file = $file;
                            $file = 'upload/' . $this->name . '/' . date('Ym') . '/' . $filename . '.' . $ext;
                            @unlink(WWW_ROOT . $old_file);
                        }
                    }
                    
                    ##图片水印
                    if (setting('is_water') && setting('water_model')) {
                        $water_model = json_decode(setting('water_model'), true);
                        if (in_array($this->name, $water_model)) {
                            $water_type = setting('water_type');
                            if ($water_type === 'text' && setting('water_text')){
                                $water_size = intval(setting('water_text_size')) > 0 ? intval(setting('water_text_size')) : 20;
                                $water_location = in_array(intval(setting('water_location')), [1,2,3,4,5,6,7,8,9]) ? intval(setting('water_location')) : 9;
                                $water_color  = setting('water_text_color') ? setting('water_text_color') : '#FFFFFF';
                                $water_font = WWW_ROOT . 'font' . DS . 'FZLTCXHJW.ttf';
                                $image = \think\Image::open(WWW_ROOT . $file);
                                $image->text(setting('water_text'), $water_font, $water_size, $water_color)->save(WWW_ROOT . $file);
                            }
                            
                            if ($water_type === 'image' && setting('water_image')) {
                                $water_location = in_array(intval(setting('water_location')), [1,2,3,4,5,6,7,8,9]) ? intval(setting('water_location')) : 9;
                                $water_image = trim(setting('water_image'));
                                $water_arr = explode('.', $water_image);
                                $water_ext = strtolower(array_pop($water_arr));
                                if (in_array($water_ext, ['jpg', 'png', 'gif', 'jpeg'])) {
                                    $water_opacity = intval(setting('water_image_opacity')) <= 100 ? intval(setting('water_image_opacity')) : 80;
                                    $image = \think\Image::open(WWW_ROOT . $file);
                                    $image->water(WWW_ROOT . $water_image, $water_location, $water_opacity)->save(WWW_ROOT . $file);
                                }
                            }
                        }
                    }
                    $this[$field] = $file;
                } elseif ($elem === 'file') {
                    $this[$field] = $file;
                } else {
                    $this[$field] = $file;
                }
                if ($this->form[$field]['upload']['nameField']) {
                    $this[$this->form[$field]['upload']['nameField']] = $info['name'];
                }
                if ($this->form[$field]['upload']['sizeField']) {
                    $this[$this->form[$field]['upload']['sizeField']] = $info['size'];
                }                
                
            }
        }
        return true;
    }
    
    protected  function getResizeInfo($info, $mdl, $data)
    {
        list($resize_width, $resize_height, $resize_method) = $info;
        
        if($resize_width === 0 || $resize_height === 0 || $resize_method === 0){
            if (isset($mdl->parentModel)) {
                
                if ($mdl->parentModel != 'parent') {
                    $parent_conj = isset($mdl->assoc[$mdl->parentModel]['foreignKey']) ? $mdl->assoc[$mdl->parentModel]['foreignKey'] : parse_name($mdl->parentModel) . '_id';
                    $parent_mdl = model($mdl->parentModel);
                }else{
                    $parent_conj = 'parent_id';
                    $parent_mdl = $mdl;
                }
                
                $parent_m = $parent_mdl->name;
                
                if (isset($parent_mdl->parentModel)) {
					if ($parent_mdl->parentModel != 'parent') {
                        $pp_conj = isset($parent_mdl->assoc[$parent_mdl->parentModel]['foreignKey']) ? $parent_mdl->assoc[$mdl->parentModel]['foreignKey'] : parse_name($parent_mdl->parentModel) . '_id';
					} else {
		                 $pp_conj = 'parent_id';
					}
				}
                
                if (!in_array($parent_m, ['Menu'])) {
                    $parent_fields = [];
                    foreach (['resize_width', 'resize_height', 'resize_method'] as $field){
						if (isset($parent_mdl->form[$field])) {
							$parent_fields[] = $field;
						}
					}
                    
					if(isset($pp_conj)){
						$parent_fields[] = $pp_conj;
					}
                    if ($data[$parent_conj]) {
                        $parent_data = $parent_mdl->where($parent_mdl->getPk(), '=', $data[$parent_conj])->field($parent_fields)->find();
                        if ($parent_data) {
                            $parent_data = $parent_data->toArray();
                        }
                        
                    } else {
                        $parent_data = [];
                    }
                    
                } else {
                    switch($parent_m){
						case 'Menu':
							$parent_data = menu($data[$parent_conj]);
                            if (!$parent_data) $parent_data = [];
							break;
						default:
							exception("未知的缓存读取方法[{$parent_m}]");
					}
                    
                }
                
                if ($parent_data) {
                    if ($resize_width === 0 && isset($parent_data['resize_width'])){
						$resize_width = intval($parent_data['resize_width']);
					}
					if ($resize_height === 0 && isset($parent_data['resize_height'])){
						$resize_height = intval($parent_data['resize_height']);
					}
					if ($resize_method === 0 && isset($parent_data['resize_method'])){
						$resize_method = intval($parent_data['resize_method']);
					}
                } else {
                    if($resize_width === 0)
						$resize_width = intval(setting('thumb_width'));
					if($resize_height === 0)
						$resize_height = intval(setting('thumb_height'));
					if($resize_method === 0)
						$resize_method = intval(setting('thumb_method'));
                }
                
                if($resize_width === 0 || $resize_height === 0 || $resize_method === 0){
					list($resize_width, $resize_height, $resize_method) = $this->getThumbInfo([$resize_width, $resize_height, $resize_method], $parent_mdl, $parent_data);
				}
            } else {
                if($resize_width === 0)
					$resize_width = intval(setting('thumb_width'));
				if($resize_height === 0)
					$resize_height = intval(setting('thumb_height'));
				if($resize_method === 0)
					$resize_method = intval(setting('thumb_method'));
            } 
        }
            
        return [$resize_width, $resize_height, $resize_method];
    }
    
    protected  function getThumbInfo($info, $mdl, $data)
    {
        list($thumb_width, $thumb_height, $thumb_method) = $info;
        
        if($thumb_width === 0 || $thumb_height === 0 || $thumb_method === 0){
            if (isset($mdl->parentModel)) {
                
                if ($mdl->parentModel != 'parent') {
                    $parent_conj = isset($mdl->assoc[$mdl->parentModel]['foreignKey']) ? $mdl->assoc[$mdl->parentModel]['foreignKey'] : parse_name($mdl->parentModel) . '_id';
                    $parent_mdl = model($mdl->parentModel);
                }else{
                    $parent_conj = 'parent_id';
                    $parent_mdl = $mdl;
                }
                
                $parent_m = $parent_mdl->name;
                
                if (isset($parent_mdl->parentModel)) {
					if ($parent_mdl->parentModel != 'parent') {
                        $pp_conj = isset($parent_mdl->assoc[$parent_mdl->parentModel]['foreignKey']) ? $parent_mdl->assoc[$mdl->parentModel]['foreignKey'] : parse_name($parent_mdl->parentModel) . '_id';
					} else {
		                 $pp_conj = 'parent_id';
					}
				}
                
                if (!in_array($parent_m, ['Menu'])) {
                    $parent_fields = [];
                    foreach (['thumb_width', 'thumb_height', 'thumb_method'] as $field){
						if (isset($parent_mdl->form[$field])) {
							$parent_fields[] = $field;
						}
					}
                    
					if(isset($pp_conj)){
						$parent_fields[] = $pp_conj;
					}
                    if ($data[$parent_conj]) {
                        $parent_data = $parent_mdl->where($parent_mdl->getPk(), '=', $data[$parent_conj])->field($parent_fields)->find();
                        if ($parent_data) {
                            $parent_data = $parent_data->toArray();
                        }
                        
                    } else {
                        $parent_data = [];
                    }
                    
                } else {
                    switch($parent_m){
						case 'Menu':
							$parent_data = menu($data[$parent_conj]);
                            if (!$parent_data) $parent_data = [];
							break;
						default:
							exception("未知的缓存读取方法[{$parent_m}]");
					}
                    
                }
                
                if ($parent_data) {
                    if ($thumb_width === 0 && isset($parent_data['thumb_width'])){
						$thumb_width = intval($parent_data['thumb_width']);
					}
					if ($thumb_height === 0 && isset($parent_data['thumb_height'])){
						$thumb_height = intval($parent_data['thumb_height']);
					}
					if ($thumb_method === 0 && isset($parent_data['thumb_method'])){
						$thumb_method = intval($parent_data['thumb_method']);
					}
                } else {
                    if($thumb_width === 0)
						$thumb_width = intval(setting('thumb_width'));
					if($thumb_height === 0)
						$thumb_height = intval(setting('thumb_height'));
					if($thumb_method === 0)
						$thumb_method = intval(setting('thumb_method'));
                }
                
                if($thumb_width === 0 || $thumb_height === 0 || $thumb_method === 0){
					list($thumb_width, $thumb_height, $thumb_method) = $this->getThumbInfo([$thumb_width, $thumb_height, $thumb_method], $parent_mdl, $parent_data);
				}
            } else {
                if($thumb_width === 0)
					$thumb_width = intval(setting('thumb_width'));
				if($thumb_height === 0)
					$thumb_height = intval(setting('thumb_height'));
				if($thumb_method === 0)
					$thumb_method = intval(setting('thumb_method'));
            } 
        }  
        return [$thumb_width, $thumb_height, $thumb_method];
    }
}
