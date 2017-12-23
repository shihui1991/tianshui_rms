<?php
/* |------------------------------------------------------
 * | 兑付汇总
 * |------------------------------------------------------
 * */
namespace app\holder\controller;

use app\system\model\Items;
use app\system\model\Pays;

class Pay extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();


    }

    /* ========== 列表 ========== */
    public function index()
    {
        return '非法访问';
    }


    /* ========== 详情 ========== */
    public function detail(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问','');
        }
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问','');
        }

        $infos=Pays::alias('p')
            ->field(['p.*','c.building','c.unit','c.floor','c.number','c.type','c.real_use','bu.name as bu_name'])
            ->join('collection c','c.id=p.collection_id','left')
            ->join('building_use bu','bu.id=c.real_use','left')
            ->where([
                'p.item_id'=>$item_id,
                'p.collection_id'=>$collection_id
            ])
            ->find();
        if(!$infos){
            exit('没有兑付数据！');
        }

        $this->assign([
           'item_id'=>$item_id,
           'collection_id'=>$collection_id,
           'infos'=>$infos,
        ]);

        return view($this->theme.'/pay/detail');
    }
}

