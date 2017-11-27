<?php
/* |------------------------------------------------------
 * | 安置房选择
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;

use app\system\model\Collectionholderhouses;
use app\system\model\Collectionholders;
use app\system\model\Collections;
use app\system\model\Itemhouses;
use app\system\model\Itemstatuss;
use think\Db;

class Collectionholderhouse extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();


    }

    /* ========== 列表 ========== */
    public function index()
    {
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 入户摸底信息 ++++++++++ */
        $collection_info=Collections::withTrashed()
            ->field(['id','item_id','community_id','building','unit','floor','number','type'])
            ->with('item,community')
            ->where('id',$collection_id)
            ->find();
        $datas['collection_info']=$collection_info;
        /* ++++++++++ 入户摸底状态 ++++++++++ */
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$collection_id])->order('created_at desc')->value('status');
        $datas['collection_status']=$collection_status;

        $where['chh.item_id']=$collection_info->item_id;
        $where['chh.community_id']=$collection_info->community_id;
        $where['chh.collection_id']=$collection_info->id;

        /* ********** 查询条件 ********** */
       
        $field=['chh.*','ch.name','ch.address','ch.phone','ch.holder',
            'h.community_id as h_community_id','h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.total_floor','h.has_lift','h.status as house_status',
            'hc.address as hc_address','hc.name as hc_name','l.name as l_name'];

        /* ++++++++++ 查询 ++++++++++ */
        $collectionholderhouse_model=new Collectionholderhouses();
        $collectionholderhouses=$collectionholderhouse_model
            ->withTrashed()
            ->alias('chh')
            ->field($field)
            ->join('collection_holder ch','ch.id=chh.collection_holder_id','left')
            ->join('house h','h.id=chh.house_id','left')
            ->join('house_community hc','hc.id=h.community_id','left')
            ->join('layout l','l.id=h.layout_id','left')
            ->where($where)
            ->order('chh.sort asc')
            ->select();

        $datas['collectionholderhouses']=$collectionholderhouses;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 入户摸底信息 ++++++++++ */
        $collection_info=Collections::withTrashed()
            ->field(['id','item_id','community_id','building','unit','floor','number','type'])
            ->with('item,community')
            ->where('id',$collection_id)
            ->find();

        if($collection_info->item->getData('status') !=1){
            switch ($collection_info->item->getData('status')){
                case 2:
                    $msg='项目已完成，禁止操作！';
                    break;
                case 3:
                    $msg='项目已取消，禁止操作！';
                    break;
                default:
                    $msg='项目未进行，禁止操作！';
            }
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }

        /* ++++++++++ 入户摸底状态 ++++++++++ */
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$collection_id])->order('created_at desc')->value('status');
        if($collection_status == 8){
            $msg='入户摸底数据已审核通过，禁止操作！';
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }


        $model=new Collectionholderhouses();
        if(request()->isPost()){
            $rules=[
                'collection_holder_id'=>'require',
                'house_id'=>'require|unique:collection_holder_house,collection_id='.input('collection_id').'&house_id='.input('house_id'),
            ];
            $msg=[
                'collection_holder_id.require'=>'请选择被征收人',
                'house_id.require'=>'请选择安置房',
                'house_id.unique'=>'安置房已选择',
            ];
            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $datas['item_id']=$collection_info->item_id;
            $datas['community_id']=$collection_info->community_id;

            $model->save($datas);
            if($model !== false){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            /* ++++++++++ 成员 ++++++++++ */
            $collectionholders=Collectionholders::alias('ch')
                ->field(['ch.id','ch.item_id','ch.community_id','ch.collection_id','ch.name','ch.holder','c.type'])
                ->join('collection c','c.id=ch.collection_id','left')
                ->where('ch.collection_id',$collection_id)
                ->where('ch.holder','in','1,2')
                ->select();

            /* ++++++++++ 安置房 ++++++++++ */
            $houses=Itemhouses::alias('ih')
                ->field(['ih.item_id','ih.house_id','h.community_id','h.building','h.unit','h.floor','h.number','h.layout_id',
                    'h.area','h.total_floor','h.has_lift','h.status','hc.address','hc.name','l.name as l_name'])
                ->join('house h','h.id=ih.house_id','left')
                ->join('house_community hc','hc.id=h.community_id','left')
                ->join('layout l','l.id=h.layout_id','left')
                ->where('ih.item_id',$collection_info->item_id)
                ->where('h.status',0)
                ->select();

            return view('add',[
                'model'=>$model,
                'collection_info'=>$collection_info,
                'collectionholders'=>$collectionholders,
                'houses'=>$houses,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collectionholderhouses::withTrashed()
            ->alias('chh')
            ->field(['chh.*','ch.name','h.community_id as h_community_id','h.building','h.unit','h.floor','h.number','h.layout_id',
                'h.area','h.total_floor','h.has_lift','h.status','l.name as l_name','hc.address','hc.name as hc_name','ch.name as ch_name','ch.holder'])
            ->join('collection_holder ch','ch.id=chh.collection_holder_id','left')
            ->join('house h','h.id=chh.house_id','left')
            ->join('house_community hc','hc.id=h.community_id','left')
            ->join('layout l','l.id=h.layout_id','left')
            ->where('chh.id',$id)
            ->find();
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collectionholderhouses();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 入户摸底信息 ++++++++++ */
        $collection_info=Collections::withTrashed()
            ->field(['id','item_id','community_id','building','unit','floor','number','type'])
            ->with('item,community')
            ->where('id',$collection_id)
            ->find();

        if($collection_info->item->getData('status') !=1){
            switch ($collection_info->item->getData('status')){
                case 2:
                    $msg='项目已完成，禁止操作！';
                    break;
                case 3:
                    $msg='项目已取消，禁止操作！';
                    break;
                default:
                    $msg='项目未进行，禁止操作！';
            }
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }

        /* ++++++++++ 入户摸底状态 ++++++++++ */
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$collection_id])->order('created_at desc')->value('status');
        if($collection_status == 8){
            $msg='入户摸底数据已审核通过，禁止操作！';
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }


        $id=input('id');
        if(!$id){
            return $this->error('错误操作');
        }

        $collectionholderhouse_model=new Collectionholderhouses();
        $other_datas=$collectionholderhouse_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $collectionholderhouse_model->isUpdate(true)->allowField(['sort','updated_at'])->save($datas);
        if($collectionholderhouse_model !== false){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

    /* ========== 删除 ========== */
    public function delete(){
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 入户摸底信息 ++++++++++ */
        $collection_info=Collections::withTrashed()
            ->field(['id','item_id','community_id','building','unit','floor','number','type'])
            ->with('item,community')
            ->where('id',$collection_id)
            ->find();

        if($collection_info->item->getData('status') !=1){
            switch ($collection_info->item->getData('status')){
                case 2:
                    $msg='项目已完成，禁止操作！';
                    break;
                case 3:
                    $msg='项目已取消，禁止操作！';
                    break;
                default:
                    $msg='项目未进行，禁止操作！';
            }
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }

        /* ++++++++++ 入户摸底状态 ++++++++++ */
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$collection_id])->order('created_at desc')->value('status');
        if($collection_status == 8){
            $msg='入户摸底数据已审核通过，禁止操作！';
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }


        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res=Collectionholderhouses::destroy($ids);
        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }

    /* ========== 恢复 ========== */
    public function restore(){
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 入户摸底信息 ++++++++++ */
        $collection_info=Collections::withTrashed()
            ->field(['id','item_id','community_id','building','unit','floor','number','type'])
            ->with('item,community')
            ->where('id',$collection_id)
            ->find();

        if($collection_info->item->getData('status') !=1){
            switch ($collection_info->item->getData('status')){
                case 2:
                    $msg='项目已完成，禁止操作！';
                    break;
                case 3:
                    $msg='项目已取消，禁止操作！';
                    break;
                default:
                    $msg='项目未进行，禁止操作！';
            }
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }

        /* ++++++++++ 入户摸底状态 ++++++++++ */
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$collection_id])->order('created_at desc')->value('status');
        if($collection_status == 8){
            $msg='入户摸底数据已审核通过，禁止操作！';
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }


        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }

        $res=Db::table('collection_holder_house')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
        if($res){
            return $this->success('恢复成功','');
        }else{
            return $this->error('恢复失败');
        }
    }

    /* ========== 销毁 ========== */
    public function destroy(){
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 入户摸底信息 ++++++++++ */
        $collection_info=Collections::withTrashed()
            ->field(['id','item_id','community_id','building','unit','floor','number','type'])
            ->with('item,community')
            ->where('id',$collection_id)
            ->find();

        if($collection_info->item->getData('status') !=1){
            switch ($collection_info->item->getData('status')){
                case 2:
                    $msg='项目已完成，禁止操作！';
                    break;
                case 3:
                    $msg='项目已取消，禁止操作！';
                    break;
                default:
                    $msg='项目未进行，禁止操作！';
            }
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }

        /* ++++++++++ 入户摸底状态 ++++++++++ */
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$collection_id])->order('created_at desc')->value('status');
        if($collection_status == 8){
            $msg='入户摸底数据已审核通过，禁止操作！';
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }


        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res=Collectionholderhouses::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
