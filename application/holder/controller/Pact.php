<?php
/* |------------------------------------------------------
 * | 兑付-协议
 * |------------------------------------------------------
 * */
namespace app\holder\controller;

use app\system\model\Pacts;
use app\system\model\Pays;

class Pact extends Base
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

        $field=['pact.id','pact.item_id','pact.community_id','pact.collection_id','pact.pay_id','pact.pay_holder_id','pact.name','pact.status',
            'ph.collection_holder_id','ph.holder','ch.name as ch_name','ch.address','ch.phone'];
        $pacts=Pacts::field($field)
            ->join('pay_holder ph','ph.id=pact.pay_holder_id','left')
            ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
            ->where([
                'pact.pay_id'=>$pay_id,
                'pact.item_id'=>$item_id
            ])
            ->select();

        $this->assign([
            'item_id'=>$item_id,
            'pay_id'=>$pay_id,
            'collection_id'=>$collection_id,
            'pacts'=>$pacts,
        ]);

        return view($this->theme.'/pact/index');
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('非法访问');
        }
        $field=['pact.*','ph.collection_holder_id','ch.name as ch_name','ch.address'];
        $infos=Pacts::field($field)
            ->join('pay_holder ph','ph.id=pact.pay_holder_id','left')
            ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
            ->where('pact.id',$id)
            ->find();

        if(!$infos){
            return $this->error('非法访问');
        }

        $this->assign([
            'infos'=>$infos,
        ]);

        return view($this->theme.'/pact/detail');
    }
}

