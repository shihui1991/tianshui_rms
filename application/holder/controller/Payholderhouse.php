<?php
/* |------------------------------------------------------
 * | 兑付-安置房
 * |------------------------------------------------------
 * */
namespace app\holder\controller;


use app\system\model\Payholderhouses;
use app\system\model\Payholders;
use app\system\model\Pays;

class Payholderhouse extends Base
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
            return $this->error('非法访问');
        }
        $pay_id=input('pay_id');
        if(!$pay_id){
            return $this->error('非法访问');
        }

        /* ++++++++++ 兑付汇总 ++++++++++ */
        $pay_info=Pays::alias('p')
            ->field(['p.id','p.item_id','p.community_id','p.collection_id','p.assess_id','p.compensate_way','p.pay_way','c.type'])
            ->join('collection c','c.id=p.collection_id','left')
            ->where('p.id',$pay_id)
            ->find();

        if(!$pay_info){
            return $this->error('兑付数据不存在');
        }

        /* ++++++++++ 兑付-产权人或承租人 ++++++++++ */
        $payholders=Payholders::alias('ph')
            ->field(['ph.id','ph.item_id','ph.community_id','ph.collection_id','ph.pay_id','ph.collection_holder_id','ph.holder','ph.portion','ph.total_amount','ph.house_amount','ch.name'])
            ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
            ->where('ph.pay_id',$pay_id)
            ->order('ph.portion desc')
            ->select();

        $model=new Payholderhouses();

        $this->assign([
            'item_id'=>$item_id,
            'pay_id'=>$pay_id,
            'collection_id'=>$pay_info->collection_id,
            'pay_info'=>$pay_info,
            'payholders'=>$payholders,
            'model'=>$model,
        ]);

        return view($this->theme.'/payholderhouse/index');
    }
}

