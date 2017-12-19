<?php
/* |------------------------------------------------------
 * | 基础控制器
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * */

namespace app\holder\controller;

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
        $userinfo=Session::get('holderinfo');
        if(!$userinfo || time()-$userinfo['time']>3600){
            $this->redirect('Index/index');
        }else{
            Session::set('holderinfo.time',time());
        }
    }

}