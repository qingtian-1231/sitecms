<?php
namespace addons\woofinder\controller;

use woo\addons\controller\Controller;

class Index extends Controller
{
    protected $error=array(
		/*general*/
        '000' => '请求错误',
		'001' => '操作失败',
		'002' => '没有登录或者登陆超时',
		'003' => '超出访问范围',
        '004' => '缺少上传目录',
		'009' => '上传失败',
        '010' => '提交数据格式错误',
		
		/*form*/
		'101' => '上传文件大小超过限制',
		'102' => '上传文件大小超过表单限制',
		'103' => '上传文件不完整,请重新上传',
		'104' => '无文件上传',
		'106' => '缺少临时文件夹',
		'107' => '写入文件失败',
		'108' => '上传被其它扩展中断',
		'109' => '上传表单类型错误',
		'199' => '未知错误',
		
		/*OK*/
		'200' => '上传成功',
		'201' => '获得文件列表成功',
		'202' => '获得文件夹列表成功',
        '203' => '文件编辑成功',
		
		/*Not Allow*/
		'301' => '上传文件的后缀名未被允许',
		'302' => '上传的文件不是图像',
		'303' => '请上传图像文件',
        '304' => '上传文件过大',
		
		/*user*/
		'400' => "文件或文件夹名中不能包含以下字符\\n\\ \/ : * ? < > \"",
		'401' => '拒绝修改',
		'402' => '拒绝删除',
		'403' => '拒绝新建',
		'404' => '文件或文件夹不存在',
		'405' => '同名文件或文件夹已存在',
        '406' => '不支持使用文件目录名称',
        
		
		/*server*/
		'501' => '数据库错误',
		'502' => '无法上传到目标路径,可能无权限',
		'503' => '缩放图片时出错',
		'504' => '生成缩略图时出错',
		'505' => '操作失败,可能无权限',
		
		/*success*/
		'994' => '已刷新文件夹信息',
		'995' => '文件移动成功',
		'996' => '文件夹创建成功',
		'997' => '删除成功',
		'998' => '重命名成功',
		'999' => '上传成功',
	);
	
	public static $rootFolder = "upload";
    public static $thumbFolder = 'thumbs';
    private $thumbSize = [120, 120];
     
    private $permission = 0777;
	
	private $allowedExtensions = [
		'7z',
		'bmp',
		'csv', 'css',
		'doc', 'docx',
		'flv',
		'gif',
		'jpg', 'jpeg',
		'html', 'htm',
		'mp3', 'mp4',
		'pdf', 'png',
		'ppt', 'pptx',
		'rar',
		'swf',
		'txt',
		'wma',
		'xls', 'xlsx', 'xml',
		'zip'
	];
	private $imageExtensions = [
		'gif',
		'jpg', 'jpeg',
		'png'
	];
    
    
    protected function initialize()
    {
        call_user_func(['parent', __FUNCTION__]);
        $this->loadModel('Vfolder');
        $this->loadModel('Upload');
        
        $this->assign->addJs('/js/jquery-3.2.1.min.js');
        $this->assign->addJs('/addons/woofinder/files/layui-v2.4.4/layui.js');
        $this->assign->addCss('/addons/woofinder/files/layui-v2.4.4/css/layui.css'); 
        $this->assign->addCss('/addons/woofinder/css/global.css');      
        $this->assign->addCss('/files/awesome-4.7.0/css/font-awesome.min.css');
    }
        
    public function index()
    {
        $this->assign->addJs('/addons/woofinder/files/contextMenu/js/contextMenu.js');
        $this->assign->addJs('/files/webuploader/webuploader.min.js');
        $this->assign->addJs('/addons/woofinder/js/global.js');
        
        $this->assign->addCss('/files/webuploader/webuploader');
        $this->assign->addCss('/addons/woofinder/files/contextMenu/css/contextMenu');
        
        $vfolder = $this->Vfolder->getCache();
        
        $this->assign->vfolder = $vfolder;      
        
        $this->assign->allowedExtensions = $this->allowedExtensions;
        $this->assign->upload_max_size = min(return_bytes(ini_get('upload_max_filesize')), return_bytes(ini_get('post_max_size')));
        
        $list = [];
        if ($this->args['model']) {
            $vfolder = $this->Vfolder->where('title', '=', trim($this->args['model']))->find();
            if (!empty($vfolder)) {
                $this->args['vfolder_id'] = $vfolder['id'];
                $children = $this->Vfolder->getFollowIds($vfolder['id']);
                if (count($children) <= 1) {
                    $where[] = ['vfolder_id', '=', $vfolder['id']];
                } else {
                    $where[] = ['vfolder_id', 'IN', $children];
                }
                
                if (!empty($this->args['filetype']) && in_array(trim($this->args['filetype']), ['image', 'file'])) {
                    $where[] = ['type', '=', trim($this->args['filetype'])];
                }
                
                if (!empty($this->args['order'])) {
                    $order = [trim($this->args['order']) => (in_array(strtoupper($this->args['ordertype']), ['DESC', 'ASC']) ? strtoupper($this->args['ordertype']) : 'DESC')];
                } else {
                    $order = ['id' => 'DESC'];
                }
                $list = $this->Upload->getPageList([
                    'where' => $where,
                    'order' => $order,
                    'field' => [],
                    'limit' => isset($this->args['limit']) ? intval($this->args['limit']) : 10
                ]);
                unset($list['render']);
            }
        }
        
        $this->assign->list = $list;        
        
        
        $this->addTitle('文件管理');
        $this->fetch = true;
    }
    
    public function editFile()
    {
        $this->assign->addJs('/addons/woofinder/files/caman/caman.full');
        $this->assign->addJs('/addons/woofinder/js/global.js');
        
        $this->assign->addJs('/files/jquery-ui-1.12.1/jquery-ui.min.js');
        $this->assign->addCss('/files/jquery-ui-1.12.1/jquery-ui.min.css');
        
        $this->assign->addJs('/addons/woofinder/files/rcrop/rcrop.min.js');
        $this->assign->addCss('/addons/woofinder/files/rcrop/rcrop.min.css');
                
        $data = $this->Upload->where(['id' => intval($this->args['id'])])->find();
        
        if (!empty($data)) {
            $data = $data->toArray();
        } 
        
        $this->assign->data = $data;
        $this->fetch = true;
    }
    
    public function ajaxLoadFile()
    {
        if (!$this->request->isAjax()) {
            $this->_error('000');
        }
        if ($this->args['vfolder_id'] == 'VFOLDERID') {
            unset($this->args['vfolder_id']);
        }
        if ($this->args['keywords'] == 'KEYWORDS') {
            unset($this->args['keywords']);
        }
        if ($this->args['order'] == 'ORDER') {
            unset($this->args['order']);
        }
        if ($this->args['ordertype'] == 'ORDERTYPE') {
            unset($this->args['ordertype']);
        }
        if ($this->args['page'] == 'PAGE') {
            unset($this->args['page']);
        }
        if ($this->args['limit'] == 'LIMIT') {
            unset($this->args['limit']);
        }
        if ($this->args['filetype'] == 'FILETYPE') {
            unset($this->args['filetype']);
        }
        if ($this->args['vfolder_id']) {
            $children = $this->Vfolder->getFollowIds(intval($this->args['vfolder_id']));
            if (count($children) <= 1) {
                $where[] = ['vfolder_id', '=', intval($this->args['vfolder_id'])];
            } else {
                $where[] = ['vfolder_id', 'IN', $children];
            }
        }
        
        if (!empty($this->args['filetype']) && in_array(trim($this->args['filetype']), ['image', 'file'])) {
            $where[] = ['type', '=', trim($this->args['filetype'])];
        }
        
        if (!empty($this->args['keywords'])) {
            $where[] = ['basename', 'LIKE', '%' . trim($this->args['keywords']) . '%'];
        }
        
        if (!empty($this->args['order'])) {
            $order = [trim($this->args['order']) => (in_array(strtoupper($this->args['ordertype']), ['DESC', 'ASC']) ? strtoupper($this->args['ordertype']) : 'DESC')];
        } else {
            $order = ['id' => 'DESC'];
        }
        
        try {
            $list = $this->Upload->getPageList([
                'where' => $where,
                'order' => $order,
                'field' => [],
                'limit' => isset($this->args['limit']) ? intval($this->args['limit']) : 10
            ]);
            unset($list['render']);
            $this->ajax('success', '', $list);
        } catch (\Exception $e) {
            $this->ajax('error', '你的传参，可能导致SQL错误：' . $e->getMessage());
        }
    }
    
    public function ajaxMoveFile() {
        if (!$this->request->isAjax()) {
            $this->_error('000');
        }
        $find = $this->Upload->where(['id' => intval($this->args['file_id'])])->find();
        if (empty($find)) {
            $this->_error('404');
        }
        
        $rslt = $this->Upload->save([
            'vfolder_id' => intval($this->args['folder_id'])
        ], [
            'id' => $find['id']
        ]);
        
        if ($rslt) {
            $this->_success('995');
        } else {
            $this->_error('001');
        }
    }
    
    public function ajaxFolderCreate()
    {
        if (!$this->request->isAjax()) {
            $this->_error('000');
        }
        
        $title = input('post.title');
        $parent_id = intval(input('post.parent_id'));
        
        if (preg_match('/[\/:*?"<>|]/', $title)) {
            $this->_error('400');
        }
        if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $title)) {
            $this->_error('406');
        }
        
        $count = $this->Vfolder->where(['title' => $title])->count();
        if ($count) {
            $this->_error('405');
        }
        
        $rslt = $this->Vfolder->save([
            'title' => $title,
            'parent_id' => $parent_id
        ]);
        
        if ($rslt) {
            $data = $this->Vfolder->where(['id' => $this->Vfolder->id])->find()->toArray();
            $this->_success('996' ,$data);
        } else { 
            $this->_error('001');
        }
    }
    
    public function ajaxFolderModify()
    {
        if (!$this->request->isAjax()) {
            $this->_error('000');
        }
        
        $title = trim(input('post.title'));
        $id = intval(input('post.id'));  
         
        if (preg_match('/[\/:*?"<>|]/', $title)) {
            $this->_error('400');
        }
        if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $title)) {
            $this->_error('406');
        }
        
        $find = $this->Vfolder->where(['id' => $id])->find();
        if (empty($find)) {
            $this->_error('404');
        }
        
        
        if ($find['is_forbid_modify']) {
            $this->_error('401');
        }
        
        $rslt = $this->Vfolder->save([
            'title' => $title
        ], [
            'id' => $id
        ]);
        
        if ($rslt) {
            $this->_success('998');
        } else {
            $this->_error('001');
        }
    }
    
    public function ajaxFileModify()
    {
        if (!$this->request->isAjax()) {
            $this->_error('000');
        }
        
        $title = trim(input('post.title'));
        $id = intval(input('post.id'));
        
        if (preg_match('/^\s{0,}$/', $title)) {
            $this->_error('400');
        }
        
        
        $find = $this->Upload->where(['id' => $id])->find();
        if (empty($find)) {
            $this->_error('404');
        }
        $rslt = $this->Upload->save([
            'basename' => $title
        ], [
            'id' => $id
        ]);
        
        if ($rslt) {
            $this->_success('998');
        } else {
            $this->_error('001');
        }
    }
    
    public function ajaxFolderDelete()
    {
        if (!$this->request->isAjax()) {
            $this->_error('000');
        }
        $id = intval(input('post.id')); 
        
        $my = $this->Vfolder->where(['id' => $id])->find()->toArray();
        if (empty($my)) {
            $this->_error('404');
        }
        
        $first  = $this->Vfolder->order(['id' => 'ASC'])->find();
        if ($first['id'] == $id) {
            $this->_error('402');
        }
        
        if ($my['is_forbid_modify']) {
            $this->_error('402');
        }
        
        $delete_ids = $this->Vfolder->getFollowIds($id);        
        $rslt = $this->Vfolder->destroy($delete_ids);
        if ($rslt) {
            $this->_success('997');
        } else {
            $this->_error('001');
        }
    }
    
    public function ajaxFileDelete()
    {
        if (!$this->request->isAjax()) {
            $this->_error('000');
        }
        $id = intval(input('post.id')); 
        
        $find = $this->Upload->where(['id' => $id])->find();
        if (empty($find)) {
            $this->_error('404');
        }
        $rslt = $find->delete();
        if ($rslt) {
            if ($this->config['delete_file_together']) {
               @unlink(separator_real(WWW_ROOT . $find['url'])); 
            }
            $this->_success('997');
        } else {
            $this->_error('001');
        }
    }
    
    public function fileDownload()
    {
        $find = $this->Upload->where(['id' => intval($this->args['id'])])->find();
        if (empty($find)) {
            return $this->message('error', '文件记录不存在');
        }
        
        if (!file_exists(separator_real(WWW_ROOT . $find['url']))) {
            return $this->message('error', '下载的文件不存在');
        }
        
        $content_url = separator_real(WWW_ROOT . $find['url']);//下载文件地址,可以是网络地址,也可以是本地物理路径或者虚拟路径
        ob_end_clean(); //函数ob_end_clean 会清除缓冲区的内容，并将缓冲区关闭，但不会输出内容。
        header("Content-Type: application/force-download;"); //告诉浏览器强制下载
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . $find['size']);
        header("Content-Disposition: attachment; filename=" . $find['basename']); 
        header("Expires: 0");
        header("Cache-control: private");
        header("Pragma: no-cache"); //不缓存页面
        readfile($content_url);         
        exit;

    }
    
    public function ajaxUpdateImage()
    {
        $args = $this->request->param();   
        $image = $args['image'];
        $reExt = '(' . implode('|', $this->imageExtensions) . ')';
        
        if (!preg_match('/^data:image\/' . $reExt . '/i', $image, $matched)) {
            $this->_error('010');
        }
            
        $find = $this->Upload->where(['id' => intval($args['upload_id'])])->find();
        
        if (empty($find)) {
            $this->_error('001');
        }
        $find = $find->toArray();
        
        $ext = explode('.', $find['url']);
   	    $ext = strtolower(array_pop($ext));
        if (!in_array($ext, $this->imageExtensions)) {
            $this->_error('001');
        }
        
        if ($args['replace'] === 'true') {
            $filepath = $find['url'];
            $basepath = WWW_ROOT . $filepath;
        } else { 
            $folder = $this->Vfolder->where(['id' => $find['vfolder_id']])->value('title');
            $folder = $folder ? $folder : $find['model'];
            
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
        }
        
        $file_data = base64_decode(substr($image, strpos($image, 'base64,') + 7));
        $rslt = file_put_contents(separator_real($basepath), $file_data);
        if (!$rslt || !file_exists(separator_real($basepath))) {
           $this->_error('001'); 
        }
        
        if ($args['replace'] === 'true') {
            $file_size  = @getimagesize(separator_real(WWW_ROOT . $filepath));
            $data['width'] = $file_size[0];
            $data['height'] = $file_size[1];
            $data['size'] = strlen($file_data);
            
            $this->Upload->save($data, ['id' => $find['id']]);
        } else {
            $data['vfolder_id'] = $find['vfolder_id'];
            $data['basename']   = '[副本]' . $find['basename'];
            $data['ext']        = $ext;
            $data['size']       = strlen($file_data);
            $data['user_id']    = helper('Auth')->user('id');
            $data['type']       = 'image';
            $data['url']        = $filepath;
            $data['model']      = $find['model'];
            //$data['thumb']  = $this->makeThumb($filepath);
            $file_size  = @getimagesize(separator_real(WWW_ROOT . $filepath));
            $data['width']  = $file_size[0];
            $data['height'] = $file_size[1];
             
            $this->Upload->save($data);
        }
        
        $this->_success(203);
    }
    
    public function ckuploader() 
    {
        config('app_trace', false);
        $args = $this->args;
        $file  = $_FILES['upload'];
        
        $vfolder = $this->Vfolder->where('title', '=', trim($args['model']))->find();
        if (empty($vfolder)) {
            $first = $this->Vfolder->order(['id' => 'ASC'])->find();
            $rslt = $this->Vfolder->save([
                'title' => trim($args['model']),
                'parent_id' => $first['id'],
                'is_forbid_modify' => 1
            ]);
        }
        $filepath = $this->uploadFile($file, $error, trim($args['model']), $this->args['type'] ? trim($this->args['type']) : 'file', $this->args['max'] ? intval(trim($this->args['max'])) : 0);
        
        if ($error) {
            echo json_encode([
                'uploaded' => 0,
                'message' => '上传失败' . $this->error[$error]
            ]);
        } else {
            $ext = explode('.', $filepath);
    	    $ext = strtolower(array_pop($ext));
            
            $data['vfolder_id'] = empty($vfolder['id']) ? $this->Vfolder->id : $vfolder['id'];
            $data['basename']   = $file['name'];
            $data['ext']        = $ext;
            $data['size']       = $file['size'];
            $data['user_id']    = helper('Auth')->user('id');
            $data['type']       = 'file';
            $data['url']        = $filepath;
            $data['model']      = trim($args['model']);
            
            if (in_array($ext, $this->imageExtensions)) {
                //$data['thumb']  = $this->makeThumb($filepath);
                $data['type']   = 'image';
                $file_size  = @getimagesize(separator_real(WWW_ROOT . $filepath));
                $data['width']  = $file_size[0];
                $data['height'] = $file_size[1];
            }
             
            $this->Upload->save($data);
            echo json_encode([
                'uploaded' => 1,
                'url' =>  $this->root . $filepath,
                'message' => '文件上传成功'
            ]);
            exit;
        }
    }
    
    public function webuploader()
    {
        $req = $this->request->post();
        $file = $_FILES['file'];
        
        $vfolder = $this->Vfolder->where('id', '=', intval($req['folder_id']))->find();
        if (empty($vfolder)) {
            $this->_error('004');
        }
        $filepath = $this->uploadFile($file, $error, $vfolder['title'], $this->args['type'] ? trim($this->args['type']) : 'file', $this->args['max'] ? intval(trim($this->args['max'])) : 0);
        
        if ($error) {
            $this->_error($error);
        } else {
            $ext = explode('.', $filepath);
    	    $ext = strtolower(array_pop($ext));
            
            $data['vfolder_id'] = $vfolder['id'];
            $data['basename']   = $req['name'] ? $req['name'] : $file['name'];
            $data['ext']        = $ext;
            $data['size']       = $file['size'];
            $data['user_id']    = helper('Auth')->user('id');
            $data['type']       = 'file';
            $data['url']        = $filepath;
            $data['model']      = 'Woofinder';
            
            if (in_array($ext, $this->imageExtensions)) {
                //$data['thumb']  = $this->makeThumb($filepath);
                $data['type']   = 'image';
                $file_size  = @getimagesize(separator_real(WWW_ROOT . $filepath));
                $data['width']  = $file_size[0];
                $data['height'] = $file_size[1];
            }
            $this->Upload->save($data); 
            $this->_success(200, ['file' => $filepath]);
        }
    }
    
    private function uploadFile($file, &$error = 0, $folder = 'file', $type = 'file', $maxSize = 0) {
        switch($file['error']){
			case 0:
				break;
			case 1:
			case 2:
			case 3:
			case 4:
			case 6:
			case 7:
			case 8:
				$error = '10' . $file['error'];
				break;
			case '':
				$error =  '109';
				break;
			default:
				$error =  '199';
				break;
		}
        
        if ($error) {
            return false;
        }
        
        $ext = explode('.', $file['name']);
    	$ext = strtolower(array_pop($ext));        
        
        if ($type == 'image' && !in_array($ext, $this->imageExtensions)) {
            return $error = '303';
        }
        
        if (!in_array($ext, $this->allowedExtensions)) {
            return $error = '301';
        }
        
        if ($maxSize > 0 && $file['size'] > $maxSize) {
            return $error = '304';
        }
        
        $basepath = WWW_ROOT . 'upload' . DS . $folder . DS;
    	if (!file_exists($basepath)) {
    	   mkdir($basepath);
    	}
    	$basepath = $basepath . date('Ym');
    	if (!file_exists($basepath)) {
    	   mkdir($basepath);
    	}
        
        $filename = uniqid(mt_rand()) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $basepath . DS . $filename);
        return 'upload/' . $folder . '/' . date('Ym') . '/' . $filename;
        
    }
    

    private function makeThumb($file, $width = 120, $height = 120, $method = 3)
    {
        $ext = explode('.', $file);
    	$ext = strtolower(array_pop($ext));
        
        $basepath = WWW_ROOT . 'upload' . DS . 'thumbs' . DS;
    	if (!file_exists($basepath)) {
    	   mkdir($basepath);
    	}
    	$basepath = $basepath . date('Ym');
    	if (!file_exists($basepath)) {
    	   mkdir($basepath);
    	}                    
        $filename = substr(array_pop(explode('/', $file)), 0, -(strlen($ext) + 1)) . '_finder_' . $width . '_' . $height . '_' . $method;
        
        $image = \think\Image::open(separator_real(WWW_ROOT . $file));
        $rslt  = $image->thumb($width, $height, $method)->save($basepath . DS . $filename . '.' . $ext);
        if ($rslt) {
            return 'upload/thumbs/' . date('Ym') . '/' . $filename . '.' . $ext;
        } else {
            return '';
        }
    }
    
    private function _error($code = '001', $data = null)
    {
		$this->ajax('error', $this->error[$code], $data);
	}

	private function _success($code, $data = null)
    {
		$this->ajax('success', $this->error[$code], $data);
	}
    
}
