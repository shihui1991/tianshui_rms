<?php
/* |------------------------------------------------------
 * | 兑付-分权兑付
 * |------------------------------------------------------
 * */
namespace app\holder\controller;


use app\system\model\Payholders;
use app\system\model\Pays;

class Payholder extends Base
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
        $pay_id=input('pay_id');
        if(!$pay_id){
            return $this->error('非法访问','');
        }

        $collection_id=Pays::where('id',$pay_id)->value('collection_id');

        $where['ph.pay_id']=$pay_id;
        $field=['ph.*','ch.name','ch.address','ch.phone'];
        /* ++++++++++ 查询 ++++++++++ */
        $payholder_model=new Payholders();
        $payholders=$payholder_model
            ->alias('ph')
            ->field($field)
            ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
            ->where($where)
            ->order('ph.portion desc')
            ->select();

        $this->assign([
            'item_id'=>$item_id,
            'pay_id'=>$pay_id,
            'collection_id'=>$collection_id,
            'payholders'=>$payholders,
        ]);

        return view($this->theme.'/payholder/index');
    }
}

