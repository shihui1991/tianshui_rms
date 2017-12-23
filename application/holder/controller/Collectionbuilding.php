<?php
/* |------------------------------------------------------
 * | 入户摸底 建筑
 * |------------------------------------------------------
 * */
namespace app\holder\controller;

use app\system\model\Collectionbuildings;

class Collectionbuilding extends Base
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

        $field=['id','item_id','community_id','collection_id','building','unit','floor','number',
            'real_num','real_unit','use_id','struct_id','status_id','build_year','remark','deleted_at'];
        $collectionbuildings=Collectionbuildings::field($field)
            ->with('realuse,buildingstruct,buildingstatus')
            ->where([
                'item_id'=>$item_id,
                'collection_id'=>$collection_id
            ])
            ->select();

        $datas=[
            'item_id'=>$item_id,
            'collection_id'=>$collection_id,
            'collectionbuildings'=>$collectionbuildings
        ];

        $this->assign($datas);

        return view($this->theme.'/collectionbuilding/index');
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

        return view($this->theme.'/collectionbuilding/detail');
    }

}

