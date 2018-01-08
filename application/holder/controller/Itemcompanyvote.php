<?php
/* |------------------------------------------------------
 * | 评估公司投票
 * |------------------------------------------------------
 * */
namespace app\holder\controller;


use app\system\model\Collections;
use app\system\model\Companys;
use app\system\model\Itemcompanyvotes;
use app\system\model\Itemprocesss;
use think\Db;

class Itemcompanyvote extends Base
{
    public $itemprocess_status;
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
        $this->itemprocess_status=Itemprocesss::where(['item_id'=>$this->item_id,'process_id'=>2])->value('status');
        if(!$this->itemprocess_status){
            return $this->error('投票还未开始……');
        }

    }

    /* ========== 列表 ========== */
    public function index()
    {
        if(!$this->item_id){
            return $this->error('非法访问','');
        }
        if(!$this->collection_id){
            return $this->error('非法访问','');
        }

        $risk_status=Itemprocesss::where(['item_id'=>$this->item_id,'process_id'=>7])->value('status');

        $holders=session('holderinfo.collection_holders');
        $holder_id=$holders[$this->collection_id];

        $company_id=Itemcompanyvotes::where([
            'item_id'=>$this->item_id,
            'collection_id'=>$this->collection_id,
            'collection_holder_id'=>$holder_id,
            ])
            ->value('company_id');

        $companys=Companys::alias('c')
            ->field(['c.id','c.name','c.logo','c.address','count(icv.id) as vote'])
            ->join('item_company_vote icv','c.id=icv.company_id and icv.item_id='.$this->item_id,'left')
            ->where('c.type',0)
            ->where('c.status',1)
            ->order('count(icv.id) desc')
            ->group('c.id')
            ->select();

        $this->assign([
            'itemprocess_status'=>$this->itemprocess_status,
            'risk_status'=>$risk_status,
            'holder_id'=>$holder_id,
            'company_id'=>$company_id,
            'companys'=>$companys,
        ]);

        return view($this->theme.'/itemcompanyvote/index');
    }


    /* ========== 添加 ========== */
    public function add(){
        if(!$this->item_id){
            return $this->error('非法访问','');
        }
        if(!$this->collection_id){
            return $this->error('非法访问','');
        }

        $company_id=input('company_id');
        if(!$company_id){
            return $this->error('非法访问','');
        }

        $holders=session('holderinfo.collection_holders');
        $holder_id=$holders[$this->collection_id];

        Db::startTrans();
        try{
            $itemcompanyvote=Itemcompanyvotes::where([
                'item_id'=>$this->item_id,
                'collection_id'=>$this->collection_id,
                'collection_holder_id'=>$holder_id,
            ])
                ->find();

            if($itemcompanyvote){
                $itemcompanyvote->company_id=$company_id;
                $itemcompanyvote->save();
            }else{
                $community_id=Collections::where(['item_id'=>$this->item_id,'id'=>$this->collection_id])->value('community_id');

                $itemcompanyvote=new Itemcompanyvotes();
                $itemcompanyvote->save([
                    'item_id'=>$this->item_id,
                    'community_id'=>$community_id,
                    'collection_id'=>$this->collection_id,
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
            return $this->success('投票成功','');
        }else{
            return $this->error('投票失败');
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
        $risk_status=Itemprocesss::where(['item_id'=>$this->item_id,'process_id'=>7])->value('status');

        $this->assign([
            'infos'=>$infos,
            'url'=>url('Itemcompanyvote/index'),
            'risk_status'=>$risk_status,
        ]);

        return view($this->theme.'/itemcompanyvote/detail');
    }
}

