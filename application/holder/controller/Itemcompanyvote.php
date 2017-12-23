<?php
/* |------------------------------------------------------
 * | 评估公司投票
 * |------------------------------------------------------
 * */
namespace app\holder\controller;


use app\system\model\Collections;
use app\system\model\Companys;
use app\system\model\Itemcompanyvotes;
use think\Db;

class Itemcompanyvote extends Base
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
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问','');
        }

        $holders=session('holderinfo.collection_holders');
        $holder_id=$holders[$collection_id];

        $company_id=Itemcompanyvotes::where([
            'item_id'=>$item_id,
            'collection_id'=>$collection_id,
            'collection_holder_id'=>$holder_id,
            ])
            ->value('company_id');

        $companys=Companys::alias('c')
            ->field(['c.id','c.name','c.address','count(icv.id) as vote'])
            ->join('item_company_vote icv','c.id=icv.company_id and icv.item_id='.$item_id,'left')
            ->where('c.type',0)
            ->where('c.status',1)
            ->order('count(icv.id) desc')
            ->group('c.id')
            ->select();

        $this->assign([
            'item_id'=>$item_id,
            'collection_id'=>$collection_id,
            'holder_id'=>$holder_id,
            'company_id'=>$company_id,
            'companys'=>$companys,
        ]);

        return view($this->theme.'/itemcompanyvote/index');
    }


    /* ========== 添加 ========== */
    public function add(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问','');
        }
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问','');
        }

        $company_id=input('company_id');
        if(!$company_id){
            return $this->error('非法访问','');
        }

        $holders=session('holderinfo.collection_holders');
        $holder_id=$holders[$collection_id];

        Db::startTrans();
        try{
            $itemcompanyvote=Itemcompanyvotes::where([
                'item_id'=>$item_id,
                'collection_id'=>$collection_id,
                'collection_holder_id'=>$holder_id,
            ])
                ->find();

            if($itemcompanyvote){
                $itemcompanyvote->company_id=$company_id;
                $itemcompanyvote->save();
            }else{
                $community_id=Collections::where(['item_id'=>$item_id,'id'=>$collection_id])->value('community_id');

                $itemcompanyvote=new Itemcompanyvotes();
                $itemcompanyvote->save([
                    'item_id'=>$item_id,
                    'community_id'=>$community_id,
                    'collection_id'=>$collection_id,
                    'collection_holder_id'=>$holder_id,
                    'company_id'=>$company_id,
                ]);
            }

            $res=true;
            Db::commit();
        }catch (\Exception $exception){
            $res=false;
            Db::rollback();
        }

        if($res){
            return $this->success('保存成功','');
        }else{
            return $this->error('保存失败');
        }
    }


    /* ========== 公司简介 ========== */
    public function detail(){
        $company_id=input('company_id');
        if(!$company_id){
            return $this->error('非法访问','');
        }

        $infos=Companys::where(['id'=>$company_id,'type'=>0,'status'=>1])->find();

        if(!$infos){
            return $this->error('非法访问','');
        }

        $this->assign(['infos'=>$infos]);

        return view($this->theme.'/itemcompanyvote/detail');
    }
}

