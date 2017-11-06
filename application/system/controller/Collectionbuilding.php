<?php
/* |------------------------------------------------------
 * | 入户摸底 建筑
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
use app\system\model\Collectionbuildings;
use app\system\model\Collectioncommunitys;
use app\system\model\Collections;
use app\system\model\Items;
use think\Db;

class Collectionbuilding extends Auth
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
        $field=['cb.id','cb.item_id','cb.community_id','cb.collection_id','cb.building','cb.unit','cb.floor','cb.number',
            'cb.real_num','cb.real_unit','cb.use_id','cb.struct_id','cb.status_id','cb.build_year','cb.deleted_at','i.name as i_name','i.is_top',
            'cc.address','cc.name as cc_name','c.building as c_building','c.unit as c_unit','c.floor as c_floor','c.number as c_number',
            'bu.name as bu_name','bs.name as bs_name','s.name as s_name'];

        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        if(is_numeric($item_id)){
            $where['collection_building.item_id']=$item_id;
            $datas['item_id']=$item_id;
        }
        /* ++++++++++ 片区 ++++++++++ */
        $community_id=input('community_id');
        if(is_numeric($community_id)){
            $where['collection_building.community_id']=$community_id;
            $datas['community_id']=$community_id;
        }
        /* ++++++++++ 几栋 ++++++++++ */
        $building=input('building');
        if(is_numeric($building)){
            $where['collection_building.building']=$building;
            $datas['building']=$building;
        }
        /* ++++++++++ 几单元 ++++++++++ */
        $unit=input('unit');
        if(is_numeric($unit)){
            $where['collection_building.unit']=$unit;
            $datas['unit']=$unit;
        }
        /* ++++++++++ 几楼 ++++++++++ */
        $floor=input('floor');
        if(is_numeric($floor)){
            $where['collection_building.floor']=$floor;
            $datas['floor']=$floor;
        }
        /* ++++++++++ 几号 ++++++++++ */
        $number=input('number');
        if(is_numeric($number)){
            $where['collection_building.number']=$number;
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
        $collectionbuilding_model=new Collectionbuildings();
        $deleted=input('deleted');
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $collectionbuilding_model=$collectionbuilding_model->onlyTrashed();
            }
        }else{
            $collectionbuilding_model=$collectionbuilding_model->withTrashed();
        }
        $collectionbuildings=$collectionbuilding_model
            ->alias('cb')
            ->field($field)
            ->join('item i','i.id=cb.item_id','left')
            ->join('collection_community cc','cc.id=cb.community_id','left')
            ->join('collection c','c.id=cb.collection_id','left')
            ->join('building_use bu','bu.id=cb.use_id','left')
            ->join('building_struct bs','bs.id=cb.struct_id','left')
            ->join('building_status s','s.id=cb.status_id','left')
            ->where($where)
            ->order(['item.is_top'=>'desc','collection_building.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['collectionbuildings']=$collectionbuildings;

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
        $model=new Collectionbuildings();
        if(request()->isPost()){
            $rules=[
                'use_id'=>'require',
                'item_id'=>'require',
                'community_id'=>'require',
                'real_num'=>'require|min:1',
            ];
            $msg=[
                'use_id.require'=>'请选择用途',
                'item_id.require'=>'请选择项目',
                'community_id.require'=>'请选择片区',
                'real_num.require'=>'实际数量不能为空',
                'real_num.min'=>'实际数量不能少于1',
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
            /* ++++++++++ 权属 ++++++++++ */
            $collections=Collections::field(['id','building','unit','floor','number','status'])->where('status',1)->select();
            /* ++++++++++ 使用性质 ++++++++++ */
            $buildinguses=Buildinguses::field(['id','name','status'])->where(['status'=>1])->select();
            /* ++++++++++ 状况 ++++++++++ */
            $buildingstatuss=Buildingstatuss::field(['id','name','status'])->where('status',1)->select();
            /* ++++++++++ 结构 ++++++++++ */
            $buildingstructs=Buildingstructs::field(['id','name','status'])->where('status',1)->select();

            return view('modify',[
                'model'=>$model,
                'items'=>$items,
                'collectioncommunitys'=>$collectioncommunitys,
                'collections'=>$collections,
                'buildinguses'=>$buildinguses,
                'buildingstatuss'=>$buildingstatuss,
                'buildingstructs'=>$buildingstructs,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collectionbuildings::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collectionbuildings();

        /* ++++++++++ 项目 ++++++++++ */
        $items=Items::field(['id','name','status','is_top'])->where(['status'=>1])->order('is_top desc')->select();
        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
        /* ++++++++++ 权属 ++++++++++ */
        $collections=Collections::field(['id','building','unit','floor','number','status'])->where('status',1)->select();
        /* ++++++++++ 使用性质 ++++++++++ */
        $buildinguses=Buildinguses::field(['id','name','status'])->where(['status'=>1])->select();
        /* ++++++++++ 状况 ++++++++++ */
        $buildingstatuss=Buildingstatuss::field(['id','name','status'])->where('status',1)->select();
        /* ++++++++++ 结构 ++++++++++ */
        $buildingstructs=Buildingstructs::field(['id','name','status'])->where('status',1)->select();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'items'=>$items,
            'collectioncommunitys'=>$collectioncommunitys,
            'collections'=>$collections,
            'buildinguses'=>$buildinguses,
            'buildingstatuss'=>$buildingstatuss,
            'buildingstructs'=>$buildingstructs,
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
            'use_id'=>'require',
            'item_id'=>'require',
            'community_id'=>'require',
            'real_num'=>'require|min:1',
        ];
        $msg=[
            'use_id.require'=>'请选择用途',
            'item_id.require'=>'请选择项目',
            'community_id.require'=>'请选择片区',
            'real_num.require'=>'实际数量不能为空',
            'real_num.min'=>'实际数量不能少于1',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $collectionbuilding_model=new Collectionbuildings();
        $other_datas=$collectionbuilding_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $collectionbuilding_model->isUpdate(true)->save($datas);
        if($collectionbuilding_model !== false){
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
        $res=Collectionbuildings::destroy($ids);
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

        $res=Db::table('collection_building')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Collectionbuildings::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
