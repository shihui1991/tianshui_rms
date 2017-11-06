<?php
/* |------------------------------------------------------
 * | 入户摸底
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 状态
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;

use app\system\model\Buildinguses;
use app\system\model\Collectioncommunitys;
use app\system\model\Collections;
use app\system\model\Items;
use app\system\model\Layouts;
use think\Db;

class Collection extends Auth
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
        $field=['c.id','item_id','community_id','building','unit','floor','number','type','real_use','is_agree',
            'compensate_way','compensate_price','c.status','c.deleted_at','i.name as i_name','i.is_top','cc.address','cc.name as cc_name','b.name as b_name'];

        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        if(is_numeric($item_id)){
            $where['item_id']=$item_id;
            $datas['item_id']=$item_id;
        }
        /* ++++++++++ 片区 ++++++++++ */
        $community_id=input('community_id');
        if(is_numeric($community_id)){
            $where['community_id']=$community_id;
            $datas['community_id']=$community_id;
        }
        /* ++++++++++ 几栋 ++++++++++ */
        $building=input('building');
        if(is_numeric($building)){
            $where['building']=$building;
            $datas['building']=$building;
        }
        /* ++++++++++ 几单元 ++++++++++ */
        $unit=input('unit');
        if(is_numeric($unit)){
            $where['unit']=$unit;
            $datas['unit']=$unit;
        }
        /* ++++++++++ 几楼 ++++++++++ */
        $floor=input('floor');
        if(is_numeric($floor)){
            $where['floor']=$floor;
            $datas['floor']=$floor;
        }
        /* ++++++++++ 几号 ++++++++++ */
        $number=input('number');
        if(is_numeric($number)){
            $where['number']=$number;
            $datas['number']=$number;
        }
        /* ++++++++++ 类型 ++++++++++ */
        $type=input('type');
        if(is_numeric($type) && in_array($type,[0,1])){
            $where['type']=$type;
            $datas['type']=$type;
        }
        /* ++++++++++ 使用性质 ++++++++++ */
        $real_use=input('real_use');
        if(is_numeric($real_use)){
            $where['real_use']=$real_use;
            $datas['real_use']=$real_use;
        }
        /* ++++++++++ 拆迁意见 ++++++++++ */
        $is_agree=input('is_agree');
        if(is_numeric($is_agree) && in_array($is_agree,[0,1])){
            $where['is_agree']=$is_agree;
            $datas['is_agree']=$is_agree;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1])){
            $where['c.status']=$status;
            $datas['status']=$status;
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
        $deleted=input('deleted');
        $collection_model=new Collections();
        $datas['model']=$collection_model;
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $collection_model=$collection_model->onlyTrashed();
            }
        }else{
            $collection_model=$collection_model->withTrashed();
        }
        $collections=$collection_model
            ->alias('c')
            ->field($field)
            ->join('item i','i.id=c.item_id','left')
            ->join('collection_community cc','cc.id=c.community_id','left')
            ->join('building_use b','b.id=c.real_use','left')
            ->where($where)
            ->order(['item.is_top'=>'desc','collection.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['collections']=$collections;

        /* ++++++++++ 项目 ++++++++++ */
        $items=Items::field(['id','name','status','is_top'])->where(['status'=>1])->order('is_top desc')->select();
        $datas['items']=$items;
        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
        $datas['collectioncommunitys']=$collectioncommunitys;
        /* ++++++++++ 使用性质 ++++++++++ */
        $buildinguses=Buildinguses::field(['id','name','status'])->where(['status'=>1])->select();
        $datas['buildinguses']=$buildinguses;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Collections();
        if(request()->isPost()){
            $rules=[
                'item_id'=>'require',
                'community_id'=>'require',
            ];
            $msg=[
                'item_id.require'=>'请选择项目',
                'community_id.require'=>'请选择片区',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
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
            /* ++++++++++ 使用性质 ++++++++++ */
            $buildinguses=Buildinguses::field(['id','name','status'])->where(['status'=>1])->select();
            /* ++++++++++ 户型 ++++++++++ */
            $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();

            return view('modify',[
                'model'=>$model,
                'items'=>$items,
                'collectioncommunitys'=>$collectioncommunitys,
                'buildinguses'=>$buildinguses,
                'layouts'=>$layouts,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collections::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collections();

        /* ++++++++++ 项目 ++++++++++ */
        $items=Items::field(['id','name','status','is_top'])->where(['status'=>1])->order('is_top desc')->select();
        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
        /* ++++++++++ 使用性质 ++++++++++ */
        $buildinguses=Buildinguses::field(['id','name','status'])->where(['status'=>1])->select();
        /* ++++++++++ 户型 ++++++++++ */
        $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'items'=>$items,
            'collectioncommunitys'=>$collectioncommunitys,
            'buildinguses'=>$buildinguses,
            'layouts'=>$layouts,
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
            'item_id'=>'require',
            'community_id'=>'require',
        ];
        $msg=[
            'item_id.require'=>'请选择项目',
            'community_id.require'=>'请选择片区',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $collection_model=new Collections();
        $other_datas=$collection_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $collection_model->isUpdate(true)->save($datas);
        if($collection_model !== false){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

    /* ========== 状态 ========== */
    public function status(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        $status=input('status');

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        if(!in_array($status,[0,1])){
            return $this->error('错误操作');
        }
        $model=new Collections();
        $res=$model->allowField(['status','updated_at'])->save(['status'=>$status],['id'=>['in',$ids]]);
        if($res){
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
        $res=Collections::destroy($ids);
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

        $res=Db::table('collection')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Collections::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
