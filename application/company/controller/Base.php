<?php
/* |------------------------------------------------------
 * | 基础控制器
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * */

namespace app\company\controller;

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
        $company=Session::get('company');
        if(!$company || time()-$company['time']>1800){
            $this->redirect('Index/index');
        }else{
            Session::set('company.time',time());
        }

    }

}