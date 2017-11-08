<?php
/* |------------------------------------------------------
 * | 入户摸底 家庭情况
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

use app\system\model\Buildingstatuss;
use app\system\model\Buildingstructs;
use app\system\model\Buildinguses;
use app\system\model\Collectionholders;
use app\system\model\Collectioncommunitys;
use app\system\model\Collections;
use app\system\model\Items;
use app\system\model\Nations;
use think\Db;

class Collectionholder extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();


    }

    /* ========== 列表 ========== */
    public function index()
    {
        /* ********** 查询条件 ********** */
        $datas=[];
        $where=[];
        $field=['ch.id','ch.item_id','ch.community_id','ch.collection_id','ch.name','ch.address','ch.phone','ch.holder',
            'ch.portion','ch.relation','ch.gender','ch.birth','ch.nation','ch.live_addr','ch.deleted_at','i.name as i_name','i.is_top',
            'cc.address as cc_address','cc.name as cc_name','c.building as c_building','c.unit as c_unit','c.floor as c_floor','c.number as c_number'];

        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        if(is_numeric($item_id)){
            $where['collection_holder.item_id']=$item_id;
            $datas['item_id']=$item_id;
        }
        /* ++++++++++ 片区 ++++++++++ */
        $community_id=input('community_id');
        if(is_numeric($community_id)){
            $where['collection_holder.community_id']=$community_id;
            $datas['community_id']=$community_id;
        }
        /* ++++++++++ 几栋 ++++++++++ */
        $building=input('building');
        if(is_numeric($building)){
            $where['collection.building']=$building;
            $datas['building']=$building;
        }
        /* ++++++++++ 几单元 ++++++++++ */
        $unit=input('unit');
        if(is_numeric($unit)){
            $where['collection.unit']=$unit;
            $datas['unit']=$unit;
        }
        /* ++++++++++ 几楼 ++++++++++ */
        $floor=input('floor');
        if(is_numeric($floor)){
            $where['collection.floor']=$floor;
            $datas['floor']=$floor;
        }
        /* ++++++++++ 几号 ++++++++++ */
        $number=input('number');
        if(is_numeric($number)){
            $where['collection.number']=$number;
            $datas['number']=$number;
        }
        /* ++++++++++ 排序 ++++++++++ */
        $ordername=input('ordername');
        $ordername=$ordername?$ordername:'id';
        $datas['ordername']=$ordername;
        $orderby=input('orderby');
        $orderby=$orderby?$orderby:'asc';
        $datas['orderby']=$orderby;
        /* ++++++++++ 每页条数 ++++++++++ */
        $nums=[config('paginate.list_rows'),30,50,100,200,500];
        sort($nums);
        $datas['nums']=$nums;
        $display_num=input('display_num');
        $display_num=$display_num?$display_num:config('paginate.list_rows');
        $datas['display_num']=$display_num;
        /* ++++++++++ 查询 ++++++++++ */
        $collectionholder_model=new Collectionholders();
        $deleted=input('deleted');
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $collectionholder_model=$collectionholder_model->onlyTrashed();
            }
        }else{
            $collectionholder_model=$collectionholder_model->withTrashed();
        }
        $collectionholders=$collectionholder_model
            ->alias('ch')
            ->field($field)
            ->join('item i','i.id=ch.item_id','left')
            ->join('collection_community cc','cc.id=ch.community_id','left')
            ->join('collection c','c.id=ch.collection_id','left')
            ->where($where)
            ->order(['item.is_top'=>'desc','collection_holder.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['collectionholders']=$collectionholders;

        /* ++++++++++ 项目 ++++++++++ */
        $items=Items::field(['id','name','status','is_top'])->where(['status'=>1])->order('is_top desc')->select();
        $datas['items']=$items;
        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
        $datas['collectioncommunitys']=$collectioncommunitys;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Collectionholders();
        if(request()->isPost()){
            $rules=[
                'name'=>'require',
                'phone'=>'require',
                'item_id'=>'require',
                'community_id'=>'require',
                'collection_id'=>'require',
                'address'=>'require',
            ];
            $msg=[
                'name.require'=>'名称不能为空',
                'phone.require'=>'电话不能为空',
                'item_id.require'=>'请选择项目',
                'community_id.require'=>'请选择片区',
                'collection_id.require'=>'请选择权属',
                'address.require'=>'地址不能为空',
            ];
            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }
            $collection_info=Collections::field(['id','item_id','community_id'])->find(input('collection_id'));
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
            /* ++++++++++ 项目 ++++++++++ */
            $items=Items::field(['id','name','status','is_top'])->where(['status'=>1])->order('is_top desc')->select();
            /* ++++++++++ 片区 ++++++++++ */
            $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
            /* ++++++++++ 权属 ++++++++++ */
            $collections=Collections::field(['id','building','unit','floor','number','status'])->where('status',1)->select();
            /* ++++++++++ 常用民族 ++++++++++ */
            $nations=Nations::field(['id','name','status'])->where('status',1)->select();

            return view('modify',[
                'model'=>$model,
                'items'=>$items,
                'collectioncommunitys'=>$collectioncommunitys,
                'collections'=>$collections,
                'nations'=>$nations,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collectionholders::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collectionholders();

        /* ++++++++++ 项目 ++++++++++ */
        $items=Items::field(['id','name','status','is_top'])->where(['status'=>1])->order('is_top desc')->select();
        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
        /* ++++++++++ 权属 ++++++++++ */
        $collections=Collections::field(['id','building','unit','floor','number','status'])->where('status',1)->select();
        /* ++++++++++ 常用民族 ++++++++++ */
        $nations=Nations::field(['id','name','status'])->where('status',1)->select();


        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'items'=>$items,
            'collectioncommunitys'=>$collectioncommunitys,
            'collections'=>$collections,
            'nations'=>$nations,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $id=input('id');
        if(!$id){
            return $this->error('错误操作');
        }
        $datas=input();
        $rules=[
            'name'=>'require',
            'phone'=>'require',
            'item_id'=>'require',
            'community_id'=>'require',
            'collection_id'=>'require',
            'address'=>'require',
        ];
        $msg=[
            'name.require'=>'名称不能为空',
            'phone.require'=>'电话不能为空',
            'item_id.require'=>'请选择项目',
            'community_id.require'=>'请选择片区',
            'collection_id.require'=>'请选择权属',
            'address.require'=>'地址不能为空',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }
        $collection_info=Collections::field(['id','item_id','community_id'])->find(input('collection_id'));
        if(!$collection_info){
            return $this->error('选择权属不存在！');
        }
        if(input('item_id') != $collection_info->item_id || input('community_id') != $collection_info->community_id){
            return $this->error('选择权属与项目片区不一致');
        }

        $collectionholder_model=new Collectionholders();
        $other_datas=$collectionholder_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $collectionholder_model->isUpdate(true)->save($datas);
        if($collectionholder_model !== false){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

    /* ========== 删除 ========== */
    public function delete(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res=Collectionholders::destroy($ids);
        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }

    /* ========== 恢复 ========== */
    public function restore(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }

        $res=Db::table('collection_holder')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
        if($res){
            return $this->success('恢复成功','');
        }else{
            return $this->error('恢复失败');
        }
    }

    /* ========== 销毁 ========== */
    public function destroy(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res=Collectionholders::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
