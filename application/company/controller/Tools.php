<?php
/* |------------------------------------------------------
 * | 常用工具
 * |------------------------------------------------------
 * | 默认上传
 * | kindeditor 上传
 * */
namespace app\system\controller;

class Tools extends Base
{
    /* ========== 默认上传 ========== */
    public function upl(){
        $files=request()->file();
        $key=array_keys($files);
        $file = $files[$key[0]];
        if($file){
            $info = $file->move( './uploads/default');
            if($info){
                $file_name=str_replace('\\','/',$info->getSaveName());
                $datas = '/uploads/default/'.$file_name;
                return $this->success('','',$datas);
            }else{
                return $this->error($file->getError(),'');
            }
        }
        return $this->error('请选择上传文件！');
    }

    /* ========== kindeditor 上传 ========== */
    public function uploads(){
        $files=request()->file();
        $key=array_keys($files);
        $file = $files[$key[0]];
        $dir=input('dir')?trim(input('dir')):'kindeditor';
        if($file){
            $info = $file->move('./uploads/'.$dir);
            if($info){
                $file_name=str_replace('\\','/',$info->getSaveName());
                $url = '/uploads/'.$dir.'/'.$file_name;
                $data['error']=0;
                $data['url']=$url;
            }else{
                $data['error']=1;
                $data['message']='上传失败！';
            }
        }else{
            $data['error']=1;
            $data['message']='请选择上传文件！';
        }
        exit(json_encode($data));
    }
}