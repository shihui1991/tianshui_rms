<?php
/* |------------------------------------------------------
 * | 后台控制初始化
 * |------------------------------------------------------
 * |
 * */
namespace app\system\controller;

use app\system\model\Itemprocesss;
use app\system\model\Processurls;
use think\Controller;
use think\Session;

class Auth extends Controller
{
    public $theme;
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        /* ++++++++++ 未登录或操作超时 ++++++++++ */
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

        $url=request()->path();
        $params=request()->route();
        if($params){
            foreach ($params as $key => $value){
                $url=str_replace('/'.$key.'/'.$value,'',$url);
            }
        }
        $url=$url=='/'?'/':'/'.$url;
        /* ++++++++++ 流程控制 ++++++++++ */
        if(request()->isPost()){
            $process_id=Processurls::where('url',$url)->value('process_id');
            if($process_id){
                $itemprocess=Itemprocesss::where(['item_id'=>input('item_id'),'process_id'=>$process_id])->find();
                if($itemprocess){
                    if($itemprocess->getData('status') == 0){
                        if(request()->isAjax()){
                            return $this->error('当前项目，此流程尚未启动！');
                        }else{
                            exit('当前项目，此流程尚未启动！');
                        }
                    }elseif ($itemprocess->getData('status') == 2){
                        if(request()->isAjax()){
                            return $this->error('当前项目，此流程已完成，禁止操作！');
                        }else{
                            exit('当前项目，此流程已完成，禁止操作！');
                        }
                    }

                }else{
                    if(request()->isAjax()){
                        return $this->error('当前项目，无此流程，禁止操作！');
                    }else{
                        exit('当前项目，无此流程，禁止操作！');
                    }
                }
            }
        }


//        if(request()->isMobile()){
//            $this->theme='mobile';
//        }else{
//            $this->theme='pc';
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

}
