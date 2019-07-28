<?php
namespace addons\kindeditor\controller;

use woo\addons\controller\Controller;


class Index extends Controller 
{
    private $conf = [
        'imageMaxSize' => 1024000,
        'imageAllowFiles' => ['gif', 'jpg', 'jpeg', 'png', 'bmp'],
        
        'flashMaxSize' => 2048000,
        'flashAllowFiles' => ['swf', 'flv'],
        
        'mediaMaxSize' => 51200000,
        'mediaAllowFiles' => ['swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb', 'mp4'],
        
        'fileMaxSize' => 2048000,
        'fileAllowFiles' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'],
                
    ];
    
    
    public function index()
    {
        $this->assign->addJs('jquery-1.11.1.min');
        $this->assign->addJs('/kindeditor/NKeditor-all.js');
        $this->assign->addJs('/kindeditor/lang/zh-CN.js');
        
        
        
        $this->addTitle('Kindeditor演示');
        $this->fetch = true;
    }
    
    public function server()
    {
        config('app_trace', false);
        $args = $this->args;
        
        $this->loadModel('Vfolder');
        $this->loadModel('Upload');   
        
        $base64 = trim($_POST['base64']);
        if (empty($base64)) {
            // 上传
            $type = trim($args['fileType']) ? strtolower(trim($args['fileType'])) : 'image';
            
            if (!in_array($type, ['image', 'flash', 'media', 'file'])) {
                $type = 'image';
            }
            
            if (empty($_FILES)) {
                return $this->rslt('001', '未发现上传信息');
            }
            
            if (empty($args['model'])) {
                $args['model'] = 'upload';
            }
            $file  = $_FILES['imgFile'];
            $vfolder = $this->Vfolder->where('title', '=', trim($args['model']))->find();
            if (empty($vfolder)) {
                $first = $this->Vfolder->order(['id' => 'ASC'])->find();
                $rslt = $this->Vfolder->save([
                    'title' => trim($args['model']),
                    'parent_id' => $first['id'],
                    'is_forbid_modify' => 1
                ]);
            }
            
            $ext = explode('.', $file['name']);
    	    $ext = strtolower(array_pop($ext));
            
            $allowExt = $this->conf[$type. 'AllowFiles'];
            $maxSize = $this->conf[$type . 'MaxSize'];
            
            if (!in_array($ext, $allowExt)) {
                return $this->rslt('001', '上传格式不正确');
            }
            
            if ($file['size'] > $maxSize) {
                return $this->rslt('001', '文件大小不能超过' . return_size($maxSize));
            }
            $filepath = upload_file($file, trim($args['model']));
            
            if (!$filepath) {
                return $this->rslt('001', $GLOBALS['upload_file_error']);
            }
            $data['vfolder_id'] = empty($vfolder['id']) ? $this->Vfolder->id : $vfolder['id'];
            $data['basename']   = $file['name'];
            $data['ext']        = $ext;
            $data['size']       = $file['size'];
            $data['user_id']    = helper('Auth')->user('id');
            $data['type']       = $type == 'image' ? 'image' : 'file';
            $data['url']        = $filepath;
            $data['model']      = trim($args['model']);
            
            if (in_array($ext, $this->conf['imageAllowFiles'])) {
                $data['type']   = 'image';
                $file_size  = @getimagesize(separator_real(WWW_ROOT . $filepath));
                $data['width']  = $file_size[0];
                $data['height'] = $file_size[1];
            }
            $this->Upload->save($data);
            return $this->rslt('000', '上传成功', [
                'url' => $this->root . $filepath
            ]);
            
        } else {
            // 涂鸦base64
            $imageData = $this->request->param('img_base64_data');
            if ($imageData && preg_match('/^(data:\s*image\/(\w+);base64,)/', $imageData, $match)) {
                $file_data = base64_decode(str_replace($match[1], '', $imageData));
                if (strlen($file_data) > $this->conf['imageMaxSize']) {
                    return $this->rslt('001', '文件大小不能超过' . return_size($this->conf['imageMaxSize']));
                }
                
                $vfolder = $this->Vfolder->where('title', '=', trim($args['model']))->find();
                if (empty($vfolder)) {
                    $first = $this->Vfolder->order(['id' => 'ASC'])->find();
                    $rslt = $this->Vfolder->save([
                        'title' => trim($args['model']),
                        'parent_id' => $first['id'],
                        'is_forbid_modify' => 1
                    ]);
                }
                $folder = trim($args['model']);
                $ext = 'png';
                $basepath = WWW_ROOT . 'upload' . DS . $folder . DS;
            	if (!file_exists($basepath)) {
            	   mkdir($basepath);
            	}
            	$basepath = $basepath . date('Ym');
            	if (!file_exists($basepath)) {
            	   mkdir($basepath);
            	}
                $filename = uniqid(mt_rand()) . '.' . $ext;
                $basepath = $basepath . DS . $filename;
                $filepath = 'upload/' . $folder . '/' . date('Ym') . '/' . $filename;
                
                $rslt = file_put_contents(separator_real($basepath), $file_data);
                if (!$rslt || !file_exists(separator_real($basepath))) {
                    return $this->rslt('001', '文件上传失败');
                }
                
                $data['vfolder_id'] = empty($vfolder['id']) ? $this->Vfolder->id : $vfolder['id'];
                $data['basename']   = $filename;
                $data['ext']        = $ext;
                $data['size']       = strlen($file_data);
                $data['user_id']    = helper('Auth')->user('id');
                $data['type']       = 'image';
                $data['url']        = $filepath;
                $data['model']      = trim($args['model']);
                $file_size  = @getimagesize(separator_real(WWW_ROOT . $filepath));
                $data['width']  = $file_size[0];
                $data['height'] = $file_size[1];
                 
                $this->Upload->save($data);
                
                return $this->rslt('000', '上传成功', [
                    'url' => $this->root . $data['url']
                ]);
            }
        }
    }
    
    public function getlist()
    {
        config('app_trace', false);
        $this->loadModel('Vfolder');
        $this->loadModel('Upload'); 
        $args = $this->args;
        
        $where = [];
        if ($args['fileType'] == 'image') {
            $where[] = ['type', '=', 'image'];
        }
        
        $order = ['id' => 'DESC'];
        $list = $this->Upload->getPageList([
            'where' => $where,
            'order' => $order,
            'field' => [],
            'limit' => 20,
            'paginate' => [
            ]
        ]);
        unset($list['render']);
        
        if (empty($list['list'])) {
            return $this->rslt('001', '数据不存在');
        }
        $filelist = [];
        foreach($list['list'] as $item) {
            $filelist[] = [
                'oriURL' => $this->root . $item['url'],
                'thumbURL' => $this->root . $item['url'],
                'filesize' => $item['size'],
                'width' => $item['width'],
                'height' => $item['height'],
            ];
        }
        echo json_encode([
            'code' => '000',
            'message' => '',
            'data' => $filelist,
            'count' => $list['page']['total'],
            'page' => $list['page']['current_page'],
            'pagesize' => $list['page']['per_page']
        ],JSON_UNESCAPED_UNICODE);exit;
    }
    
    protected function rslt($code, $message, $data = [])
    {
        echo json_encode([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ],JSON_UNESCAPED_UNICODE);exit;
    }
}
