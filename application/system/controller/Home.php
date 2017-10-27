<?php
/* |------------------------------------------------------
 * | 后台导航框架
 * |------------------------------------------------------
 * | 初始化操作
 * | 框架主页
 * | 控制台
 * */
namespace app\system\controller;

use think\Session;

class Home extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();

    }

    /* ========== 框架主页 ========== */
    public function index()
    {
        $userinfo=Session::get('userinfo');
        $menus_ids=json_decode($userinfo['menu_ids'],true);
        $menus=[];
        $field=['id','parent_id','name','icon','url','display','status'];
        if($userinfo['is_admin']){
            $menus=model('Menus')->field($field)->where('display',1)->select();
        }elseif($menus_ids){
            $menus=model('Menus')->field($field)->where('display',1)->whereIn('id',$menus_ids)->select();
        }
        $nav_menus='';
        if($menus){
            $nav_menus=get_nav_li_list($menus);
        }

        return view('index',[
            'nav_menus'=>$nav_menus,
        ]);
    }

    /* ========== 控制台 ========== */
    public function console(){
        return view();
    }
}
