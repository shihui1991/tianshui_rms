<?php
/* |------------------------------------------------------
 * | 入户摸底 建筑
 * |------------------------------------------------------
 * | 初始化操作 _initialize
 * | 列表 index
 * | 添加 add
 * | 详情 detail
 * | 修改 edit
 * | 删除 delete
 * | 恢复 restore
 * | 销毁 destroy
 * | 认定 status
 * */
namespace app\system\controller;

use app\system\model\Buildingstatuss;
use app\system\model\Buildingstructs;
use app\system\model\Buildinguses;
use app\system\model\Collectionbuildings;
use app\system\model\Collectioncommunitys;
use app\system\model\Collections;
use app\system\model\Items;
use app\system\model\Itemstatuss;
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
        $where=[];
        $datas=[];
        /* ********** 是否弹出层 ********** */
        $l=input('l');
        $collection_id=input('collection_id');
        if($l){
            if(!$collection_id){
                return $this->error('错误操作','');
            }
            $with='realuse,buildingstruct,buildingstatus';
            $view='index';
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

            $where['item_id']=$collection_info->item_id;
            $where['community_id']=$collection_info->community_id;
            $where['collection_id']=$collection_info->id;
        }else{
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
            /* ++++++++++ 权属 ++++++++++ */
            if(is_numeric($collection_id)){
                $where['collection_id']=$collection_id;
                $datas['collection_id']=$collection_id;
            }
            $with='item,community,collection,realuse,buildingstruct,buildingstatus';
            $view='all';
            /* ++++++++++ 项目 ++++++++++ */
            $items=Items::field(['id','name','status','is_top'])->order('is_top desc')->select();
            $datas['items']=$items;
            /* ++++++++++ 片区 ++++++++++ */
            $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
            $datas['collectioncommunitys']=$collectioncommunitys;
            /* ++++++++++ 入户摸底 ++++++++++ */
            $collections=Collections::field(['id','building','unit','floor','number','status'])->select();
            $datas['collections']=$collections;
        }


        /* ********** 查询条件 ********** */
        $field=['id','item_id','community_id','collection_id','building','unit','floor','number',
            'real_num','real_unit','use_id','struct_id','status_id','build_year','remark','deleted_at'];

        /* ++++++++++ 使用性质 ++++++++++ */
        $use_id=input('use_id');
        if(is_numeric($use_id)){
            $where['use_id']=$use_id;
            $datas['use_id']=$use_id;
        }
        /* ++++++++++ 建筑状况 ++++++++++ */
        $status_id=input('status_id');
        if(is_numeric($status_id)){
            $where['status_id']=$status_id;
            $datas['status_id']=$status_id;
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
            ->field($field)
            ->with($with)
            ->where($where)
            ->order([$ordername=>$orderby])
            ->paginate($display_num);

        $datas['collectionbuildings']=$collectionbuildings;

        /* ++++++++++ 使用性质 ++++++++++ */
        $buildinguses=Buildinguses::field(['id','name','status'])->where(['status'=>1])->select();
        $datas['buildinguses']=$buildinguses;
        /* ++++++++++ 状况 ++++++++++ */
        $buildingstatuss=Buildingstatuss::field(['id','name','status'])->where('status',1)->select();
        $datas['buildingstatuss']=$buildingstatuss;

        $this->assign($datas);

        return view($this->theme.'/collectionbuilding/'.$view);
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


        $model=new Collectionbuildings();
        if(request()->isPost()){
            $rules=[
                'use_id'=>'require',
                'real_num'=>'require|min:1',
                'real_unit'=>'require',
            ];
            $msg=[
                'use_id.require'=>'请选择用途',
                'struct_id.require'=>'请选择结构',
                'real_num.require'=>'实际数量不能为空',
                'real_num.min'=>'实际数量不能少于1',
                'real_unit.require'=>'输入数量单位',
            ];
            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $datas['item_id']=$collection_info->item_id;
            $datas['community_id']=$collection_info->community_id;
            $datas['collection_id']=$collection_info->id;

            $model->save($datas);
            if($model !== false){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{

            /* ++++++++++ 使用性质 ++++++++++ */
            $buildinguses=Buildinguses::field(['id','name','status'])->where(['status'=>1])->select();
            /* ++++++++++ 结构 ++++++++++ */
            $buildingstructs=Buildingstructs::field(['id','name','status'])->where('status',1)->select();

            return view($this->theme.'/collectionbuilding/modify',[
                'model'=>$model,
                'collection_info'=>$collection_info,
                'buildinguses'=>$buildinguses,
                'buildingstructs'=>$buildingstructs,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collectionbuildings::withTrashed()->with('item,community,collection,realuse,buildingstruct,buildingstatus')->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collectionbuildings();

        /* ++++++++++ 使用性质 ++++++++++ */
        $buildinguses=Buildinguses::field(['id','name','status'])->where(['status'=>1])->select();
        /* ++++++++++ 结构 ++++++++++ */
        $buildingstructs=Buildingstructs::field(['id','name','status'])->where('status',1)->select();

        return view($this->theme.'/collectionbuilding/modify',[
            'model'=>$model,
            'infos'=>$infos,
            'buildinguses'=>$buildinguses,
            'buildingstructs'=>$buildingstructs,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $id=input('id');
        if(!$id){
            return $this->error('错误操作');
        }
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

        $datas=input();
        $rules=[
            'use_id'=>'require',
            'real_num'=>'require|min:1',
            'real_unit'=>'require',
        ];
        $msg=[
            'use_id.require'=>'请选择用途',
            'real_num.require'=>'实际数量不能为空',
            'real_num.min'=>'实际数量不能少于1',
            'real_unit.require'=>'输入数量单位',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $collectionbuilding_model=new Collectionbuildings();
        $other_datas=$collectionbuilding_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $collectionbuilding_model->except(['item_id','community_id','collection_id','status_id'])->save($datas,['id'=>$id]);
        if($collectionbuilding_model !== false){
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
        $res=Collectionbuildings::destroy(['id'=>['in',$ids]]);
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

        $res=Db::table('collection_building')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Collectionbuildings::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }

    /* ========== 合法性认定 ========== */
    public function status(){
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

        $id=input('id');
        if(!$id){
            return $this->error('错误操作','');
        }
        if(request()->isPost()){
            Db::startTrans();
            try{
                $model=new Collectionbuildings();
                $model->save(['status_id'=>input('status')],['id'=>$id]);
                $res=true;
                Db::commit();
            }catch (\Exception $exception){
                $res=false;
                Db::rollback();
            }
            if($res){
                return $this->success('认定成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            $datas['id']=$id;
            $datas['collection_info']=$collection_info;
            /* ++++++++++ 建筑状况 ++++++++++ */
            $buildingstatuss=Buildingstatuss::field(['id','name','status'])->where('status',1)->select();
            $datas['buildingstatuss']=$buildingstatuss;

            $this->assign($datas);
            return view($this->theme.'/collectionbuilding/status');
        }
    }

    /* ========== 高拍仪页面 ========== */
    public function gaopaiyi(){
        return view($this->theme.'/collectionbuilding/gaopaiyi');
    }
}
