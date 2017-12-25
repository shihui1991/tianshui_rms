<?php
/* |------------------------------------------------------
 * | 入户摸底 产权人及家庭成员
 * |------------------------------------------------------
 * */
namespace app\holder\controller;

use app\system\model\Collectionholders;

class Collectionholder extends Base
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

        $collectionholders=Collectionholders::where([
            'item_id'=>$item_id,
            'collection_id'=>$collection_id
        ])
            ->order(['portion'=>'desc'])
            ->select();

        $datas=[
            'item_id'=>$item_id,
            'collection_id'=>$collection_id,
            'collectionholders'=>$collectionholders
        ];

        $this->assign($datas);

        return view($this->theme.'/collectionholder/index');
    }


    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('非法访问','');
        }

        $infos=Collectionholders::find($id);
        if(!$infos){
            return $this->error('不存在');
        }

        $this->assign([
            'infos'=>$infos,
        ]);

        return view($this->theme.'/collectionholder/detail');
    }

}

