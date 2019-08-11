<?php
namespace app\home\controller;

use app\common\controller\Home;

class Download extends Home
{
    public function initialize()
    {
        call_user_func(array('parent', __FUNCTION__));
    }


    public function view()
    {
        call_user_func(array('parent', __FUNCTION__));
        if(empty($this->assign->data['content'])) {
            $this->download();
        }
    }

    function download()
    {

        $id=intval($this->args['id']);
		if (empty($id)) {
            return $this->notFound();
		}

        $data = $this->mdl->field(['id', 'file', 'title', 'file_name', 'size', 'link', 'download_permission', 'score'])->where('id', '=', $id)->find();
        if (empty($data)) {
            return $this->notFound();
        }

        $data = $data->toArray();


        if (empty($data['file']) || !file_exists(WWW_ROOT . $data['file'])) {
			if (!empty($data['link'])) {
                $this->redirect($data['link']);
				exit;
			} else {
				return $this->message('error', '文件不存在');
			}
			exit('该文件不存在');
		}

        //20190414新增 下载权限
        $downloadset = !empty($data['download_permission']) ? strtolower(trim($data['download_permission'])) : 'free';

        if ($downloadset != 'free' && empty($this->login)) {
            return $this->message('error', '请先登录', ['马上登录' => ['User/login']]);
        }

        $data['score'] = floatval($data['score']);
        if ($downloadset == 'score' && $this->login['user_score_sum'] < $data['score']) {
            return $this->message('error', '抱歉！您的积分不足' . $data['score'] .'，下载失败');
        }

        if ($downloadset == 'scoreremove') {
            if ($this->login['user_score_sum'] < $data['score']) {
                return $this->message('error', '抱歉！您的积分不足' . $data['score'] .'，下载失败');
            }
            add_user_score($this->login['id'], -$data['score'], "下载[{$data['title']}]扣除");
            helper('Auth')->relogin();
        }

        if ($downloadset == 'specify') {
            $this->loadModel('DownloadPermission');
            $count = $this->DownloadPermission->where([
                ['user_id', '=', $this->login['id']],
                ['download_id', '=', $data['id']]
            ])->count();
            if ($count < 1 ){
                return $this->message('error', '抱歉！您当前还没有下载权限，下载失败');
            }
        }

        //下载统计
        if ($this->mdl->form['download_count']) {
            $this->mdl->where('id', '=', $id)->setInc('download_count');
        }

        $ua = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match("/MSIE/", $ua)) {
			$filename = urlencode($data['file_name']);
			$filename = str_replace("+", "%20", $filename);
		}else {
			$filename = $data['file_name'];
		}

        $content_url = WWW_ROOT . $data['file'] ;//下载文件地址,可以是网络地址,也可以是本地物理路径或者虚拟路径
        ob_end_clean(); //函数ob_end_clean 会清除缓冲区的内容，并将缓冲区关闭，但不会输出内容。
        header("Content-Type: application/force-download;"); //告诉浏览器强制下载
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".$data['size']);
        header("Content-Disposition: attachment; filename=$filename");
        header("Expires: 0");
        header("Cache-control: private");
        header("Pragma: no-cache"); //不缓存页面
        readfile($content_url);
        exit;
    }
}
