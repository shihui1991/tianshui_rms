<?php
/* |------------------------------------------------------
 * | 后台控制初始化
 * |------------------------------------------------------
 * |
 * */
namespace app\system\controller;

use app\system\model\Roles;
use think\Session;

class Auth extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();

        /* ++++++++++ 检查操作权限 ++++++++++ */
        $priv_res=$this->check_priv($this->menu_id);
        if(!$priv_res){
            if(request()->isAjax()){
                $this->error('没有权限！');
            }else{
                die('没有权限！ <a href="javascript:window.history.back();">返回</a>');
            }
        }


    }

    /* ========== 检查操作权限 ========== */
    public function check_priv($menu_id='', $role_id=''){
        $controller=strtolower(request()->controller());
        $role_id=$role_id?(int)$role_id:\think\Session::get('userinfo.role_id');

        if(in_array($controller,['home','tools'])){
            return true;
        }
        $action=strtolower(request()->action());
        if($action=='gaopaiyi'){
            return true;
        }

        if(!$role_id){
            return false;
        }
        $role=Roles::field(['id','is_admin','menu_ids'])->where('id',$role_id)->where('status',1)->find();
        if(!$role){
            return false;
        }

        Session::set('userinfo.is_admin',$role->getData('is_admin'));
        Session::set('userinfo.is_admin',$role->getData('menu_ids'));

        if($role->getData('is_admin')){
            return true;
        }

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
