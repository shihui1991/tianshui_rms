<?php
/* |------------------------------------------------------
 * | 入户摸底
 * |------------------------------------------------------
 * */

namespace app\holder\controller;


use app\system\model\Collectionbuildings;
use app\system\model\Collectionholders;
use app\system\model\Collectionobjects;
use app\system\model\Collections;
use app\system\model\Items;

class Collection extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
        if($this->process_status !=2){
            return $this->error('数据采集中……');
        }
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

        if(request()->isMobile()){
            return $this->error('非法访问');
        }else{
            $this->assign([
                'collections'=>$collections
            ]);

            return view($this->theme.'/collection/index');
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        if(request()->isMobile()){
            $collection=Collections::alias('c')
                ->field(['c.*','cc.address','cc.name','du.name as du_name','ru.name as ru_name','l.name as l_name'])
                ->join('collection_community cc','cc.id=c.community_id','left')
                ->join('building_use du','du.id=c.default_use','left')
                ->join('building_use ru','ru.id=c.real_use','left')
                ->join('layout l','l.id=c.rebuild_layout_id','left')
                ->where('c.id',$this->collecton_id)
                ->find();

            $collectionbuildings=Collectionbuildings::alias('cb')
                ->field(['cb.*','du.name du_name','ru.name as ru_name','bst.name as struct_name','bss.name as status_name'])
                ->join('building_use du','du.id=cb.default_use','left')
                ->join('building_use ru','ru.id=cb.use_id','left')
                ->join('building_struct bst','bst.id=cb.struct_id','left')
                ->join('building_status bss','bss.id=cb.status_id','left')
                ->where('cb.collection_id',$this->collecton_id)
                ->order(['register'=>'desc'])
                ->select();

            $collectionholders=Collectionholders::with(['holdercrowd'=>['crowd']])
                ->where('collection_id',$this->collecton_id)
                ->order(['portion'=>'desc'])
                ->select();

            $collectionobjects=Collectionobjects::alias('co')
                ->field(['co.*','o.name','o.infos'])
                ->join('object o','o.id=co.object_id','left')
                ->where('co.collection_id',$this->collecton_id)
                ->select();

            $this->assign([
                'collection'=>$collection,
                'collectionbuildings'=>$collectionbuildings,
                'collectionholders'=>$collectionholders,
                'collectionobjects'=>$collectionobjects,
            ]);

        }else{
            $id=input('collection_id');
            if(!$id){
                return $this->error('非法访问');
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
        }
        return view($this->theme.'/collection/detail');
    }
}