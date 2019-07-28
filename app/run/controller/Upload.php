<?php
namespace app\run\controller;


use app\common\controller\Run;
use think\Db;

class Upload extends Run
{
    public function multiImageUpload()
    {
        if ($this->request->isPost()) {
            config('app_trace', false);
            $post = $this->request->post();
            $mdl = trim($this->args['mdl']);
            $field = trim($post['field']);
            $x = intval($post['x']);
            $y = intval($post['y']);
            $m = intval(setting('thumb_method'));
                        
            $upload  = $_FILES['file']; 
            $error = '';           
            if ($mdl && $field) {
                try {
                    $this->loadModel($mdl);
                    $info  = $this->$mdl->form[$field];
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }                
                if (!empty($info)) {
                    if (isset($info['image']['resize']['width']) && $x <= 0) {
                        $x = intval($info['image']['resize']['width']);
                    }
                    if (isset($info['image']['resize']['height']) && $y <= 0) {
                        $y = intval($info['image']['resize']['height']);
                    }
                    if (isset($info['image']['resize']['method'])) {
                        $m = intval($info['image']['resize']['method']);
                    }
                    if ($upload['error'] == 0) {
                        $filename_parts = explode('.', $upload['name']);
				        $ext = strtolower(array_pop($filename_parts));
                        if (isset($info['upload']['maxSize']) && $upload['size'] > intval($info['upload']['maxSize']) * 1024) {
                            $error = '上传文件大小[' . return_size($upload['size']) . ']超过允许最大值' . return_size($info['upload']['maxSize'] * 1024);
                        }
                        if (isset($info['upload']['validExt']) && !in_array($ext, (array)$info['upload']['validExt'])) {
                           $error = '上传文件只接受后缀名为' . implode(',', $info['upload']['validExt']) . '的文件'; 
                        }
                    }
                }
            }
            
            if ($error) {
                $this->ajax('error', $error);
            }
            
            $filepath  = upload_file($upload, $mdl ? $mdl : 'upload');
            
            if ($filepath) {
                $ext = explode('.', $filepath);
    	        $ext = strtolower(array_pop($ext));
                
                if ($x && $y && $m) {
                    $image = \think\Image::open(WWW_ROOT . $filepath);
                    $rslt  = $image->thumb($x, $y, $m)->save(WWW_ROOT . $filepath);
                }
                
                $vfolder = Db::name('Vfolder')->where('title', '=', $mdl)->find();
                if (empty($vfolder)) {
                    $first = Db::name('Vfolder')->order(['id' => 'ASC'])->find();
                    $vfolder_id = Db::name('Vfolder')->insertGetId([
                        'title' => $mdl,
                        'parent_id' => $first['id'],
                        'is_forbid_modify' => 1,
                        'created' => date('Y-m-d H:i:s'),
                        'modified' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $vfolder_id = $vfolder['id'];
                }
                
                $data['vfolder_id'] = $vfolder_id;
                $data['basename']   = $upload['name'];
                $data['ext']        = $ext;
                $data['size']       = $upload['size'];
                $data['user_id']    = helper('Auth')->user('id');
                $data['type']       = 'file';
                $data['url']        = $filepath;
                $data['model']      = $mdl;
                
                if (in_array($ext, ['jpg', 'png', 'gif', 'jpeg'])) {
                    $data['type']   = 'image';
                    $file_size  = @getimagesize(separator_real(WWW_ROOT . $filepath));
                    $data['width']  = $file_size[0];
                    $data['height'] = $file_size[1];
                }
                
                $data['created']    = date('Y-m-d H:i:s');
                $data['modified']    = date('Y-m-d H:i:s');                
                Db::name('Upload')->insert($data);
                
                $this->ajax('success', '图片上传成功', ['url' => $filepath]); 
            } else {
                $this->ajax('error', $GLOBALS['upload_file_error']);
            }
        }
    }
    
    
    public function uploader()
    {
        if(isset($_GET['CKEditorFuncNum'])) {
            $CKEditorFuncNum = $_GET['CKEditorFuncNum'];
        } else {
            $CKEditorFuncNum = 0;
        }
        if (empty($this->args['model'])) {
            $this->args['model'] = 'upload';
        }
        
        $upload  = $_FILES['upload'];
        $filepath  = upload_file($upload, $this->args['model']);
        
        config('app_trace', false);
        
        if ($filepath) {
            $ext = explode('.', $filepath);
    	    $ext = strtolower(array_pop($ext));
            
            
            $vfolder = Db::name('Vfolder')->where('title', '=', trim($this->args['model']))->find();
            if (empty($vfolder)) {
                $first = Db::name('Vfolder')->order(['id' => 'ASC'])->find();
                $vfolder_id = Db::name('Vfolder')->insertGetId([
                    'title' => trim($this->args['model']),
                    'parent_id' => $first['id'],
                    'is_forbid_modify' => 1,
                    'created' => date('Y-m-d H:i:s'),
                    'modified' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $vfolder_id = $vfolder['id'];
            }
            
            $data['vfolder_id'] = $vfolder_id;
            $data['basename']   = $upload['name'];
            $data['ext']        = $ext;
            $data['size']       = $upload['size'];
            $data['user_id']    = helper('Auth')->user('id');
            $data['type']       = 'file';
            $data['url']        = $filepath;
            $data['model']      = trim($this->args['model']);
            
            if (in_array($ext, ['jpg', 'png', 'gif', 'jpeg'])) {
                $data['type']   = 'image';
                $file_size  = @getimagesize(separator_real(WWW_ROOT . $filepath));
                $data['width']  = $file_size[0];
                $data['height'] = $file_size[1];
            }
            
            $data['created']    = date('Y-m-d H:i:s');
            $data['modified']    = date('Y-m-d H:i:s');
            
            Db::name('Upload')->insert($data);
            
            echo json_encode([
                'uploaded' => 1,
                'url' =>  $this->root . $filepath,
                'message' => '文件上传成功'
            ]);
        } else {
            echo json_encode([
                'uploaded' => 0,
                'message' => '上传失败' . $GLOBALS['upload_file_error']
            ]);
        }
        
        /* 4.5.6
        if ($filepath) {
            $this->assign->functionNumber=$CKEditorFuncNum;
    		$this->assign->fileUrl = $filepath;
    		$this->assign->message = '上传成功';
        } else {
            $this->assign->functionNumber = $CKEditorFuncNum;
            $this->assign->message = $GLOBALS['upload_file_error'];
        }
        $this->fetch = 'upload_result';*/
    }
}
