<?php
/* |------------------------------------------------------
 * | 入户摸底
 * |------------------------------------------------------
 * */

namespace app\holder\controller;


use app\system\model\Collections;
use app\system\model\Items;

class Collection extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        $collection_holders=session('holderinfo.collection_holders');
        $collection_ids=array_keys($collection_holders);
        $collections=Collections::field(['id','item_id','community_id','building','unit','floor','number','type','status','has_assets'])
            ->with('community')
            ->where([
                'item_id'=>$item_id,
                'id'=>['in',$collection_ids],
                'status'=>1
            ])->select();

        $this->assign([
            'collections'=>$collections
        ]);

        return view();
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('非法访问','');
        }

        $collection=Collections::alias('c')
            ->field(['c.*','cc.address','cc.name','du.name as du_name','ru.name as ru_name','l.name as l_name'])
            ->join('collection_community cc','cc.id=c.community_id','left')
            ->join('building_use du','du.id=c.default_use','left')
            ->join('building_use ru','ru.id=c.real_use','left')
            ->join('layout l','l.id=c.rebuild_layout_id','left')
            ->where('c.id',$id)
            ->find();
        $this->assign([
            'infos'=>$collection,
        ]);

        return view();
    }
}