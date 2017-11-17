<?php
/* |------------------------------------------------------
 * | 评估公司选票
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 详情
 * */

namespace app\system\controller;

use app\system\model\Companys;
use app\system\model\Itemcompanyvotes;
use app\system\model\Items;

class Itemcompanyvote extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
        $on[]='c.id=icv.company_id';
        $item_id=input('item_id');
        if($item_id){
            $on[]='icv.item_id='.$item_id;
            $datas['item_id']=$item_id;

            $item_name=Items::where('id',$item_id)->value('name');
            $datas['item_name']=$item_name;
        }
        $on=implode(' and ',$on);

        $companys=Companys::alias('c')
            ->field(['c.id','c.name','count(icv.id) as vote'])
            ->join('item_company_vote icv',$on,'left')
            ->where('c.type',0)
            ->where('c.status',1)
            ->order('count(icv.id) desc')
            ->group('c.id')
            ->select();

        $datas['companys']=$companys;

        /* ++++++++++ 项目列表 ++++++++++ */
        $items=Items::field(['id','name','is_top'])->where('status',1)->order('is_top desc')->select();
        $datas['items']=$items;

        $this->assign($datas);

        return view();
    }

    /* ========== 详情 ========== */
    public function detail(){
        $company_id=input('company_id');
        if(!$company_id){
            return $this->error('错误操作');
        }
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作');
        }
        $holders=Itemcompanyvotes::alias('icv')
            ->field(['icv.*','ch.name','ch.address','ch.phone','ch.holder','cc.address as cc_address','cc.name as cc_name','c.building','c.unit','c.floor','c.number','c.type'])
            ->join('collection_holder ch','ch.id=icv.collection_holder_id','left')
            ->join('collection_community cc','cc.id=icv.community_id','left')
            ->join('collection c','c.id=icv.collection_id','left')
            ->where('icv.item_id',$item_id)
            ->where('icv.company_id',$company_id)
            ->select();

        $item_name=Items::where('id',$item_id)->value('name');
        $company_name=Companys::where('id',$company_id)->value('name');

        return view('detail',[
            'item_id'=>$item_id,
            'company_id'=>$company_id,
            'item_name'=>$item_name,
            'company_name'=>$company_name,
            'holders'=>$holders,
        ]);
    }

}