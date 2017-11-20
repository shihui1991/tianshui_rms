<?php
/* |------------------------------------------------------
 * | 评估公司选票
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 删除
 * */

namespace app\system\controller;

use app\system\model\Collectioncommunitys;
use app\system\model\Collections;
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

    /* ========== 添加 ========== */
    public function add(){
        $model=new Itemcompanyvotes();
        if(request()->isPost()){
            $rules=[
                'item_id'=>'require',
                'community_id'=>'require',
                'collection_id'=>'require',
                'collection_holder_id'=>'require|unique:item_company_vote',
                'company_id'=>'require',
            ];
            $msg=[
                'item_id.require'=>'请选择项目',
                'community_id.require'=>'请选择片区',
                'collection_id.require'=>'请选择权属',
                'collection_holder_id.require'=>'请选择投票人',
                'collection_holder_id.unique'=>'投票人已投票',
                'company_id.require'=>'请选择评估公司',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }
            $collection_info=Collections::field(['id','item_id','community_id','type'])->find(input('collection_id'));
            if(!$collection_info){
                return $this->error('选择权属不存在！');
            }
            if(input('item_id') != $collection_info->item_id || input('community_id') != $collection_info->community_id){
                return $this->error('选择权属与项目片区不一致');
            }

            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $model->save($datas);
            if($model !== false){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            /* ++++++++++ 项目列表 ++++++++++ */
            $items=Items::field(['id','name','is_top'])->where('status',1)->order('is_top desc')->select();
            /* ++++++++++ 片区 ++++++++++ */
            $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
            /* ++++++++++ 权属 ++++++++++ */
            $collections=Collections::field(['id','building','unit','floor','number','status'])->where('status',1)->where('real_use','neq',3)->select();
            /* ++++++++++ 房产评估公司 ++++++++++ */
            $companys=Companys::field(['id','name','status'])->where('status',1)->where('type',0)->order('sort asc')->select();
            return view('add',[
                'model'=>$model,
                'items'=>$items,
                'collectioncommunitys'=>$collectioncommunitys,
                'collections'=>$collections,
                'companys'=>$companys,
            ]);
        }
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
            ->paginate();

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

    /* ========== 删除 ========== */
    public function delete(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res=Itemcompanyvotes::destroy($ids);
        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }
}