<?php
/* |------------------------------------------------------
 * | 后台控制初始化
 * |------------------------------------------------------
 * |
 * */
namespace app\system\controller;

use think\Controller;
use think\Session;

class Auth extends Controller
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
//        /* ++++++++++ 未登录或操作超时 ++++++++++ */
//        $userinfo=Session::get('userinfo');
//        if(!$userinfo || time()-$userinfo['time']>1800){
//            $this->redirect('system/Index/index');
//        }else{
//            Session::set('userinfo.time',time());
//        }
//
//        /* ++++++++++ 检查操作权限 ++++++++++ */
//        $priv_res=$this->check_priv();
//        if(!$priv_res){
//            if(request()->isAjax()){
//                $this->error('没有权限！');
//            }else{
//                die('没有权限！ <a href="javascript:window.history.back();">返回</a>');
//            }
//        }

    }

    /* ========== 检查操作权限 ========== */
    public function check_priv($url='', $role_id=''){
        $controller=strtolower(request()->controller());
        $role_id=$role_id?(int)$role_id:\think\Session::get('userinfo.role_id');
        $url=$url?$url:request()->path();
        if(in_array($controller,['home','tools'])){
            return true;
        }
        if(!$role_id){
            return false;
        }
        $role=\app\system\model\Roles::field(['id','is_admin','menu_ids'])->where('id',$role_id)->where('status',1)->find();
        if(!$role){
            return false;
        }

        Session::set('userinfo.is_admin',$role->getData('is_admin'));
        Session::set('userinfo.is_admin',$role->getData('menu_ids'));

        if($role->getData('is_admin')){
            return true;
        }

        $params=request()->route();
        if($params){
            foreach ($params as $key => $value){
                $url=str_replace('/'.$key.'/'.$value,'',$url);
            }
        }
        $url=$url=='/'?'/':'/'.$url;
        $menu_id=\app\system\model\Menus::where('status',1)->where('url',$url)->value('id');
        if(!$menu_id){
            return false;
        }

        if(in_array($menu_id,$role->menu_ids)){
            return true;
        }else{
            return false;
        }
    }

    /* ========== 清除缓存 ========== */
    public function delete_cache()
    {
//        exec('rm -rf ../runtime/*');
        $this->del_DirAndFile('../runtime');

        if(request()->isAjax()){
            $this->success('清除缓存完成！');
            die;
        }else{
            die('清除缓存完成！');
        }
    }

    /* ========== 清除目录下所有文件及目录 ========== */
    public function del_DirAndFile($dirName){
        if(is_dir($dirName)){
            if ( $handle = opendir( "$dirName" ) ) {
                while ( false !== ( $item = readdir( $handle ) ) ) {
                    if ( $item != "." && $item != ".." ) {
                        if ( is_dir( "$dirName/$item" ) ) {
                            $this->del_DirAndFile( "$dirName/$item" );
                        } else {
                            unlink( "$dirName/$item" );
                        }
                    }
                }
                closedir($handle);
                rmdir($dirName);
            }
        }
    }
}
