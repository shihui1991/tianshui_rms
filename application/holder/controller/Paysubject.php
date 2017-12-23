<?php
/* |------------------------------------------------------
 * | 兑付-补偿科目
 * |------------------------------------------------------
 * */
namespace app\holder\controller;

use app\system\model\Pays;
use app\system\model\Paysubjects;

class Paysubject extends Base
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

        $where['ps.pay_id']=$pay_id;
        $field=['ps.*','is.subject_id','s.name','s.num_from'];

        $paysubjects=Paysubjects::alias('ps')
            ->field($field)
            ->join('item_subject is','is.id=ps.item_subject_id','left')
            ->join('subject s','s.id=is.subject_id','left')
            ->where($where)
            ->select();

        $this->assign([
            'item_id'=>$item_id,
            'pay_id'=>$pay_id,
            'collection_id'=>$collection_id,
            'paysubjects'=>$paysubjects,
        ]);

        return view($this->theme.'/paysubject/index');
    }
}

