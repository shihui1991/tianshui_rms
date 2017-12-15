<?php
/* |------------------------------------------------------
 * | 入户摸底 建筑
 * |------------------------------------------------------
 * */
namespace app\holder\controller;

use app\system\model\Collectionbuildings;
use app\system\model\Collectionobjects;

class Collectionobject extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();


    }

    /* ========== 列表 ========== */
    public function index()
    {
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问','');
        }
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问','');
        }

        $field=['co.*','o.name','o.infos'];
        $collectionobjects=Collectionobjects::alias('co')
            ->field($field)
            ->join('object o','o.id=co.object_id','left')
            ->where([
                'item_id'=>$item_id,
                'collection_id'=>$collection_id
            ])
            ->select();

        $datas=[
            'item_id'=>$item_id,
            'collection_id'=>$collection_id,
            'collectionobjects'=>$collectionobjects
        ];

        $this->assign($datas);

        return view();
    }


    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('非法访问','');
        }

        $infos=Collectionbuildings::alias('cb')
            ->field(['cb.*','du.name du_name','ru.name as ru_name','bst.name as struct_name','bss.name as status_name'])
            ->join('building_use du','du.id=cb.default_use','left')
            ->join('building_use ru','ru.id=cb.use_id','left')
            ->join('building_struct bst','bst.id=cb.struct_id','left')
            ->join('building_status bss','bss.id=cb.status_id','left')
            ->where('cb.id',$id)
            ->find();
        if(!$infos){
            return $this->error('不存在');
        }

        $this->assign([
            'infos'=>$infos,
        ]);

        return view();
    }

}

