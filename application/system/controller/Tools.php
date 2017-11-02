<?php
/* |------------------------------------------------------
 * | 常用工具
 * |------------------------------------------------------
 * | 默认上传
 * | kindeditor 上传
 * | kindeditor 文件管理
 * | 房源户型图
 * | 接口分组公共参数
 * | 生成GUID
 * */
namespace app\system\controller;


class Tools extends Auth
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

    /* ========== kindeditor 文件管理 ========== */
    public function filelist()
    {
        $path=$_GET['path'];
        $dir=input('dir')?trim(input('dir')):'';

        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $root_path = $dir?'./uploads/'.$dir.'/':'./uploads/';
        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $root_url = request()->domain();
        //图片扩展名
        $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp','ico');

        $current_path=$path?$path:$root_path;
        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.') continue;
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }
        //排序形式，name or size or type
        global $order;
        $order= empty($_GET['order']) ? 'name' : strtolower($_GET['order']);


        usort($file_list, 'cmp_func');

        $result = array();
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $root_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_path;
        //当前目录的URL
        $result['current_url'] = $root_url.ltrim($path,'.');
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;

        //输出JSON字符串
        exit(json_encode($result));
    }

    /* ========== 房源户型图 ========== */
    public function houselayoutpic(){
        $community_id=input('community_id');
        $layout_id=input('layout_id');
        if(!$community_id){
            return $this->error('请选择小区');
        }
        if(!$layout_id){
            return $this->error('请选择户型');
        }
        $houselayoutpics=model('Houselayoutpics')
            ->alias('p')
            ->field(['p.id','community_id','layout_id','remark','picture','p.status','p.deleted_at','l.name as l_name'])
            ->join('layout l','l.id=p.layout_id','left')
            ->where('community_id',$community_id)
            ->where('layout_id',$layout_id)
            ->where('p.status',1)
            ->select();

        $data=$houselayoutpics?$houselayoutpics:[];
        return $this->success('获取成功','',$data);
    }

    /* ========== 接口分组公共参数 ========== */
    public function params(){
        $parent_id=input('parentid');
        if(empty($parent_id)){
            return $this->error('请选择接口分组');
        }
        $parent_params=model('Apis')->where('id',$parent_id)->where('status',1)->value('params');
        $params=json_decode($parent_params,true);
        if(empty($params)){
            return $this->error('没有设置公共参数');
        }else{
            return $this->success('获取成功','',$params);
        }
    }

    /* ========== 生成GUID ========== */
    public function getguid(){
        return $this->success('获取成功','',create_guid());
    }
}