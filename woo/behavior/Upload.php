<?php
namespace woo\behavior;

class Upload
{
    public function upload($params)
    {
        $vfolder = db('Vfolder')->where('title', '=', trim($params['model']))->find();
        if (empty($vfolder)) {
            $first = db('Vfolder')->order(['id' => 'ASC'])->find();
            $vfolder_id = db('Vfolder')->insertGetId([
                'title' => trim($params['model']),
                'parent_id' => $first['id'],
                'is_forbid_modify' => 1,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $vfolder_id = $vfolder['id'];
        }
        $params['vfolder_id'] = $vfolder_id;
        
        $params['type'] = 'file';
        if (in_array($params['ext'], ['gif','jpg', 'jpeg', 'png'])) {
            $params['type'] = 'image';
            $file_size  = @getimagesize(WWW_ROOT . $params['url']);
            $params['width']  = $file_size[0];
            $params['height'] = $file_size[1];
            /*
            $basepath = WWW_ROOT . 'upload' . DS . 'thumbs' . DS;
        	if (!file_exists($basepath)) {
        	   mkdir($basepath);
        	}
        	$basepath = $basepath . date('Ym');
        	if (!file_exists($basepath)) {
        	   mkdir($basepath);
        	}                    
            $filename = substr(array_pop(explode('/', $params['url'])), 0, -(strlen($params['ext']) + 1)) . '_finder_120_120_3';
            
            $image = \think\Image::open(WWW_ROOT . $params['url']);
            $rslt  = $image->thumb(120, 120, 3)->save($basepath . DS . $filename . '.' . $params['ext']);
            $params['thumb'] = 'upload/thumbs/' . date('Ym') . '/' . $filename . '.' . $params['ext'];
            */
        }
        $params['created']    = date('Y-m-d H:i:s');
        $params['modified']    = date('Y-m-d H:i:s');
        
        db('Upload')->insert($params); 
        
    }  
}
