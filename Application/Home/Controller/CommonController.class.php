<?php
namespace Home\Controller;
use  Think\Controller;
use  Org\Util\Rbac;
class CommonController extends Controller {
    //图片单文件上传
    protected function _upload($item) {
        $config = array(
            'maxSize'       =>  2097152, //上传的文件大小限制 (0-不做限制，1M为1048576字节)
            'exts'          =>  array('jpg','jpeg','png','gif','JPEG','JPG','PNG','GIF','bmp','BMP'), //允许上传的文件后缀
            'autoSub'       =>  true, //自动子目录保存文件
            'subName'       =>  array('date', 'Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath'      =>  './Public/', //保存根路径
            'savePath'      =>  '/Uploads/'.$item.'/', //保存路径
            'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'replace'       =>  true, //存在同名是否覆盖
        );
        $upload = new \Think\Upload($config);
        $info = $upload->upload();
        if (!$info) {
            //捕获上传异常
            $this->error($upload->getError());
        } else {
            //取得成功上传的文件信息
            foreach($info as $file){
                $imgurl = $file['savepath'].$file['savename'];
            }
            //图片缩略
            if(file_exists('./Public'.$imgurl)){
                $image = new \Think\Image();
                $image->open('./Public'.$imgurl);
                //大尺寸
                $url = str_replace('.','_big.',$imgurl,$i);
                if(!file_exists('./Public'.$url)){
                    $image->thumb(1920, 550,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                }
                //中尺寸
                $url1 = str_replace('.','_mid.',$imgurl,$i);
                if(!file_exists('./Public'.$url1)){
                    $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url1);
                }
                //小尺寸
                $url2 = str_replace('.','_small.',$imgurl,$i);
                if(!file_exists('./Public'.$url2)){
                    $image->thumb(200, 200,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url2);
                }
                //小尺寸
                $url3 = str_replace('.','_little.',$imgurl,$i);
                if(!file_exists('./Public'.$url3)){
                    $image->thumb(100, 100,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url3);
                }
            }
            return $imgurl;
        }
    }

    //视频文件上传
    protected function _uploadTv($item) {
        $config = array(
            'maxSize'       =>  300*1048576, //上传的文件大小限制 (0-不做限制，1M为1048576字节)
            'exts'          =>  array('rm','rmvb','wmv','mp4','3gp','mkv','avi'), //允许上传的文件后缀
            'autoSub'       =>  true, //自动子目录保存文件
            'subName'       =>  array('date', 'Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath'      =>  './Public/', //保存根路径
            'savePath'      =>  '/Uploads/'.$item.'/', //保存路径
            'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'replace'       =>  true, //存在同名是否覆盖
        );
        $upload = new \Think\Upload($config);
        $info = $upload->upload();
        if (!$info) {
            //捕获上传异常
            $this->error($upload->getError());
        } else {
            //取得成功上传的文件信息
            foreach($info as $file){
                $imgurl = $file['savepath'].$file['savename'];
            }
            return $imgurl;
        }
    }

    /**图片多文件文件上传
     * 2017-1-12
     * @param $item
     * @return array
     */
    protected function _uploads($item) {
        $config = array(
            'maxSize'       =>  2097152, //上传的文件大小限制 (0-不做限制，1M为1048576字节)
            'exts'          =>  array('jpg','jpeg','png','gif','JPEG','JPG','PNG','GIF','bmp','BMP'), //允许上传的文件后缀
            'autoSub'       =>  true, //自动子目录保存文件
            'subName'       =>  array('date', 'Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath'      =>  './Public/', //保存根路径
            'savePath'      =>  '/Uploads/'.$item.'/', //保存路径
            'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'replace'       =>  true, //存在同名是否覆盖
        );
        $upload = new \Think\Upload($config);
        $info = $upload->upload();
//        var_dump($info);
        if (!$info) {
            //捕获上传异常
            $this->error($upload->getError());
        } else {
            //取得成功上传的文件信息
            $imgurl = array();
            foreach($info as $file){
                $imgurl[] = $file['savepath'].$file['savename'];
                $yurl = $file['savepath'].$file['savename'];
                //图片缩略
                if(file_exists('./Public'.$yurl)){
                    $image = new \Think\Image();
                    $image->open('./Public'.$yurl);
                    //大尺寸
                    $url = str_replace('.','_big.',$yurl,$i);
                    if(!file_exists('./Public'.$url)){
                        $image->thumb(1920, 550,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    }
                    //中尺寸
                    $url1 = str_replace('.','_mid.',$yurl,$i);
                    if(!file_exists('./Public'.$url1)){
                        $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url1);
                    }
                    //小尺寸
                    $url2 = str_replace('.','_small.',$yurl,$i);
                    if(!file_exists('./Public'.$url2)){
                        $image->thumb(200, 200,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url2);
                    }
                    //小尺寸
                    $url3 = str_replace('.','_little.',$yurl,$i);
                    if(!file_exists('./Public'.$url3)){
                        $image->thumb(100, 100,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url3);
                    }
                }
            }
            return $imgurl;
        }
    }

    /**图片多文件文件上传，对应不同字段
     * 2017-1-12
     * @param $item
     * @return array
     */
    protected function _uploads1($item) {
        $config = array(
            'maxSize'       =>  2097152, //上传的文件大小限制 (0-不做限制，1M为1048576字节)
            'exts'          =>  array('jpg','jpeg','png','gif','JPEG','JPG','PNG','GIF','bmp','BMP'), //允许上传的文件后缀
            'autoSub'       =>  true, //自动子目录保存文件
            'subName'       =>  array('date', 'Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath'      =>  './Public/', //保存根路径
            'savePath'      =>  '/Uploads/'.$item.'/', //保存路径
            'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'replace'       =>  true, //存在同名是否覆盖
        );
        $upload = new \Think\Upload($config);
        $info = $upload->upload();
//        var_dump($info);
        if (!$info) {
            //捕获上传异常
            $this->error($upload->getError());
        } else {
            //取得成功上传的文件信息
            $imgurl = array();
            foreach($info as $key=>$file){
                $imgurl[$key] = $file['savepath'].$file['savename'];
                $yurl = $file['savepath'].$file['savename'];
                //图片缩略
                if(file_exists('./Public'.$yurl)){
                    $image = new \Think\Image();
                    $image->open('./Public'.$yurl);
                    //大尺寸
                    $url = str_replace('.','_big.',$yurl,$i);
                    if(!file_exists('./Public'.$url)){
                        $image->thumb(1920, 550,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url);
                    }
                    //中尺寸
                    $url1 = str_replace('.','_mid.',$yurl,$i);
                    if(!file_exists('./Public'.$url1)){
                        $image->thumb(400, 400,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url1);
                    }
                    //小尺寸
                    $url2 = str_replace('.','_small.',$yurl,$i);
                    if(!file_exists('./Public'.$url2)){
                        $image->thumb(200, 200,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url2);
                    }
                    //小尺寸
                    $url3 = str_replace('.','_little.',$yurl,$i);
                    if(!file_exists('./Public'.$url3)){
                        $image->thumb(100, 100,\Think\Image::IMAGE_THUMB_SCALE)->save('./Public'.$url3);
                    }
                }
            }
            return $imgurl;
        }
    }
}
?>