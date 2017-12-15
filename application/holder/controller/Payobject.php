<?php
/* |------------------------------------------------------
 * | 兑付-补偿事项
 * |------------------------------------------------------
 * */
namespace app\holder\controller;

use app\system\model\Payobjects;
use app\system\model\Pays;

class Payobject extends Base
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

        $where['po.pay_id']=$pay_id;
        $field=['po.*','co.object_id','co.number','o.name'];

        $payobjects=Payobjects::alias('po')
            ->field($field)
            ->join('collection_object co','co.id=po.collection_object_id','left')
            ->join('object o','o.id=co.object_id','left')
            ->where($where)
            ->select();

        $this->assign([
            'item_id'=>$item_id,
            'pay_id'=>$pay_id,
            'collection_id'=>$collection_id,
            'payobjects'=>$payobjects,
        ]);

        return view();
    }
}

