<?php
/* |------------------------------------------------------
 * | 入户摸底 特殊人群
 * |------------------------------------------------------
 * */
namespace app\holder\controller;

use app\system\model\Collectionholdercrowds;
use app\system\model\Collectionholders;

class Collectionholdercrowd extends Base
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
        $holder_id=input('holder_id');
        if(!$holder_id){
            return $this->error('非法访问','');
        }

        /* ++++++++++ 成员信息 ++++++++++ */
        $holder_info=Collectionholders::field(['id','item_id','community_id','collection_id','name','address','phone','holder'])->find($holder_id);
        if(!$holder_info){
            return $this->error('非法访问','');
        }

        $collectionholdercrowds=Collectionholdercrowds::with('crowdgroup,crowd')
            ->where([
                'item_id'=>$item_id,
                'holder_id'=>$holder_id
            ])
            ->order(['crowd_parent_id'=>'asc'])
            ->select();

        $datas=[
            'item_id'=>$item_id,
            'holder_info'=>$holder_info,
            'collectionholdercrowds'=>$collectionholdercrowds
        ];

        $this->assign($datas);

        return view($this->theme.'/collectionholdercrowd/index');
    }


    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('非法访问');
        }
        $infos=Collectionholdercrowds::with('crowdgroup,crowd')->find($id);
        if(!$infos){
            return $this->error('非法访问');
        }

        return view($this->theme.'/collectionholdercrowd/detail',[
            'infos'=>$infos,
        ]);
    }

}

