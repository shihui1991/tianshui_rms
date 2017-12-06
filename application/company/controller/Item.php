<?php
/* |------------------------------------------------------
 * | 项目
 * |------------------------------------------------------
 * */

namespace app\company\controller;


use app\system\model\Items;

class Item extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
        $items='';
        if(session('company.item_ids')){
            $items=Items::field(['id','name','area'])
                ->withCount('collection')
                ->whereIn('id',session('company.item_ids'))
                ->where('status',1)
                ->order(['is_top'=>'desc'])
                ->select();
        }

        $this->assign([
            'items'=>$items
        ]);

        return view();
    }

}