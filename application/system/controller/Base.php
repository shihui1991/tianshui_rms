<?php
/* |------------------------------------------------------
 * | 房源物业管理费
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * */

namespace app\system\controller;


use think\Controller;

class Base extends Controller
{
    public $theme;
    public $url;
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


//        if(request()->isMobile()){
//            $this->theme='mobile';
//        }else{
//            $this->theme='pc';
//        }
    }

}