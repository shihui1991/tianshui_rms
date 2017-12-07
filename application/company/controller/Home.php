<?php
/* |------------------------------------------------------
 * | 控制台
 * |------------------------------------------------------
 * */

namespace app\company\controller;


class Home extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
    }

    /* ========== 框架 ========== */
    public function index(){
        return view();
    }

    /* ========== 控制台 ========== */
    public function console(){
        return view();
    }
}