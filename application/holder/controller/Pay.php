<?php
/* |------------------------------------------------------
 * | 兑付汇总
 * |------------------------------------------------------
 * */
namespace app\holder\controller;

use app\system\model\Itemprocesss;
use app\system\model\Items;
use app\system\model\Payholderhouses;
use app\system\model\Payholders;
use app\system\model\Payobjects;
use app\system\model\Pays;
use app\system\model\Paysubjects;

class Pay extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
        $itemprocess_status=Itemprocesss::where(['item_id'=>$this->item_id,'process_id'=>8])->value('status');
        if(!$itemprocess_status){
            return $this->error('数据采集中……');
        }
        $this->assign(['itemprocess_status'=>$itemprocess_status]);
    }

    /* ========== 列表 ========== */
    public function index()
    {
        if(request()->isMobile()){
            /* ++++++++++ 兑付信息 ++++++++++ */
            $infos=Pays::alias('p')
                ->field(['p.*','cc.address','cc.name','c.building','c.unit','c.floor','c.number','c.type','c.real_use','bu.name as bu_name'])
                ->join('collection c','c.id=p.collection_id','left')
                ->join('collection_community cc','cc.id=p.community_id','left')
                ->join('building_use bu','bu.id=c.real_use','left')
                ->where([
                    'p.item_id'=>$this->item_id,
                    'p.collection_id'=>$this->collection_id
                ])
                ->find();
            if(!$infos){
                return $this->error('数据采集中……');
            }

            /* ++++++++++ 兑付人 ++++++++++ */
            $payholders=Payholders::alias('ph')
                ->field(['ph.*','ch.name','ch.address','ch.phone'])
                ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
                ->where(['ph.pay_id'=>$infos->id])
                ->order('ph.portion desc')
                ->select();

            /* ++++++++++ 兑付补偿科目 ++++++++++ */
            $paysubjects=Paysubjects::alias('ps')
                ->field(['ps.*','is.subject_id','s.name','s.num_from'])
                ->join('item_subject is','is.id=ps.item_subject_id','left')
                ->join('subject s','s.id=is.subject_id','left')
                ->where(['ps.pay_id'=>$infos->id])
                ->select();

            /* ++++++++++ 兑付其他事项 ++++++++++ */
            $payobjects=Payobjects::alias('po')
                ->field(['po.*','co.object_id','co.number','o.name'])
                ->join('collection_object co','co.id=po.collection_object_id','left')
                ->join('object o','o.id=co.object_id','left')
                ->where(['po.pay_id'=>$infos->id])
                ->select();

            $payholderhouse_model=new Payholderhouses();

            $this->assign([
                'infos'=>$infos,
                'payholders'=>$payholders,
                'paysubjects'=>$paysubjects,
                'payobjects'=>$payobjects,
                'payholderhouse_model'=>$payholderhouse_model,
            ]);

            return view($this->theme.'/pay/index');
        }else{
            return '非法访问';
        }
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
            exit('数据采集中！');
        }

        $this->assign([
           'item_id'=>$item_id,
           'collection_id'=>$collection_id,
           'infos'=>$infos,
        ]);

        return view($this->theme.'/pay/detail');
    }
}

