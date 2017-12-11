<?php
/* |------------------------------------------------------
 * | 控制台
 * |------------------------------------------------------
 * */

namespace app\holder\controller;


use app\system\model\Items;

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
        $items=Items::whereIn('id',session('holderinfo.item_ids'))->field(['id','name','area','is_top','status'])->order('is_top desc')->select();
        $datas['items']=$items;

        $this->assign($datas);

        return view();
    }
}