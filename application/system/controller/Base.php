<?php
/* |------------------------------------------------------
 * | 基础控制器
 * |------------------------------------------------------
 * | 初始化操作
 * */

namespace app\system\controller;


use think\Controller;
use think\Session;

class Base extends Controller
{
    public $theme;
    public $url;
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        /* ++++++++++ 未登录或操作超时 ++++++++++ */
        $userinfo=Session::get('userinfo');
        if(!$userinfo || time()-$userinfo['time']>1800){
            $this->redirect('Index/index');
        }else{
            Session::set('userinfo.time',time());
        }


//        if(request()->isMobile()){
//            $this->theme='mobile';
//        }else{
//            $this->theme='pc';
//        }
    }

}