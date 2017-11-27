<?php
/* |------------------------------------------------------
 * | 入户摸底 特殊人群
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

use app\system\model\Collectionholdercrowds;
use app\system\model\Collectioncommunitys;
use app\system\model\Collectionholders;
use app\system\model\Collections;
use app\system\model\Crowds;
use app\system\model\Items;
use app\system\model\Itemstatuss;
use think\Db;

class Collectionholdercrowd extends Auth
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
        $holder_id=input('holder_id');
        if($l){
            if(!$holder_id){
                return $this->error('错误操作','');
            }
            $with='crowdgroup,crowd';
            $view='index';
            /* ++++++++++ 成员信息 ++++++++++ */
            $holder_info=Collectionholders::withTrashed()
                ->field(['id','item_id','community_id','collection_id','name','address','phone','holder'])
                ->with('item,community,collection')
                ->where('id',$holder_id)
                ->find();
            $datas['holder_info']=$holder_info;
            /* ++++++++++ 入户摸底状态 ++++++++++ */
            $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$holder_info->collection_id])->order('created_at desc')->value('status');
            $datas['collection_status']=$collection_status;

            $where['item_id']=$holder_info->item_id;
            $where['community_id']=$holder_info->community_id;
            $where['collection_id']=$holder_info->collection_id;
            $where['holder_id']=$holder_info->id;
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
            $collection_id=input('collection_id');
            if(is_numeric($collection_id)){
                $where['collection_id']=$collection_id;
                $datas['collection_id']=$collection_id;
            }
            /* ++++++++++ 成员 ++++++++++ */
            if(is_numeric($holder_id)){
                $where['holder_id']=$holder_id;
                $datas['holder_id']=$holder_id;
            }
            $with='item,community,collection,holder,crowdgroup,crowd';
            $view='all';
            /* ++++++++++ 项目 ++++++++++ */
            $items=Items::field(['id','name','status','is_top'])->where(['status'=>1])->order('is_top desc')->select();
            $datas['items']=$items;
            /* ++++++++++ 片区 ++++++++++ */
            $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
            $datas['collectioncommunitys']=$collectioncommunitys;
            /* ++++++++++ 入户摸底 ++++++++++ */
            $collections=Collections::field(['id','building','unit','floor','number','status'])->where('status',1)->select();
            $datas['collections']=$collections;
            /* ++++++++++ 成员 ++++++++++ */
            $collectionholders=Collectionholders::field(['id','name','address'])->select();
            $datas['collectionholders']=$collectionholders;
        }
        
        /* ********** 查询条件 ********** */

        /* ++++++++++ 特殊人群分组 ++++++++++ */
        $crowd_parent_id=input('crowd_parent_id');
        if(is_numeric($crowd_parent_id)){
            $where['crowd_parent_id']=$crowd_parent_id;
            $datas['crowd_parent_id']=$crowd_parent_id;
        }
        /* ++++++++++ 特殊人群 ++++++++++ */
        $crowd_id=input('crowd_id');
        if(is_numeric($crowd_id)){
            $where['crowd_id']=$crowd_id;
            $datas['crowd_id']=$crowd_id;
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
        $collectionholdercrowd_model=new Collectionholdercrowds();
        $deleted=input('deleted');
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $collectionholdercrowd_model=$collectionholdercrowd_model->onlyTrashed();
            }
        }else{
            $collectionholdercrowd_model=$collectionholdercrowd_model->withTrashed();
        }
        $collectionholdercrowds=$collectionholdercrowd_model
            ->with($with)
            ->where($where)
            ->order([$ordername=>$orderby])
            ->paginate($display_num);

        $datas['collectionholdercrowds']=$collectionholdercrowds;
        
        /* ++++++++++ 特殊人群 ++++++++++ */
        $crowds=Crowds::field(['id','name','status','parent_id'])->where('status',1)->select();
        $datas['crowds']=$crowds;
        
        $this->assign($datas);

        return view($view);
    }

    /* ========== 添加 ========== */
    public function add(){
        $holder_id=input('holder_id');
        if(!$holder_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 成员信息 ++++++++++ */
        $holder_info=Collectionholders::withTrashed()
            ->field(['id','item_id','community_id','collection_id','name','address','phone','holder'])
            ->with('item,community,collection')
            ->where('id',$holder_id)
            ->find();

        if($holder_info->item->getData('status') !=1){
            switch ($holder_info->item->getData('status')){
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
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$holder_info->collection_id])->order('created_at desc')->value('status');
        if($collection_status == 8){
            $msg='入户摸底数据已审核通过，禁止操作！';
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }


        $model=new Collectionholdercrowds();
        if(request()->isPost()){
            $rules=[
                'crowd_id'=>'require|unique:collection_holder_crowd,holder_id='.input('holder_id').'&crowd_id='.input('crowd_id'),
                'crowd_parent_id'=>'require',
                'picture'=>'require',
            ];
            $msg=[
                'crowd_id.require'=>'请选择特殊人群',
                'crowd_id.unique'=>'特殊人群已存在',
                'crowd_parent_id.require'=>'发生错误，缺少分类',
                'picture.require'=>'请选择上传相关证件',
            ];
            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $datas['item_id']=$holder_info->item_id;
            $datas['community_id']=$holder_info->community_id;
            $datas['collection_id']=$holder_info->collection_id;
            $datas['holder_id']=$holder_info->id;

            $model->save($datas);
            if($model !== false){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            /* ++++++++++ 特殊人群 ++++++++++ */
            $crowds=Crowds::field(['id','name','status','parent_id'])->where('parent_id','<>',0)->where('status',1)->select();

            return view('modify',[
                'model'=>$model,
                'holder_info'=>$holder_info,
                'crowds'=>$crowds,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collectionholdercrowds::withTrashed()->with('item,community,collection,holder,crowdgroup,crowd')->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collectionholdercrowds();

        /* ++++++++++ 特殊人群 ++++++++++ */
        $crowds=Crowds::field(['id','name','status','parent_id'])->where('parent_id','<>',0)->where('status',1)->select();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'crowds'=>$crowds,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $holder_id=input('holder_id');
        if(!$holder_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 成员信息 ++++++++++ */
        $holder_info=Collectionholders::withTrashed()
            ->field(['id','item_id','community_id','collection_id','name','address','phone','holder'])
            ->with('item,community,collection')
            ->where('id',$holder_id)
            ->find();

        if($holder_info->item->getData('status') !=1){
            switch ($holder_info->item->getData('status')){
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
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$holder_info->collection_id])->order('created_at desc')->value('status');
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

        $rules=[
            'crowd_id'=>'require|unique:collection_holder_crowd,holder_id='.input('holder_id').'&crowd_id='.input('crowd_id'),
            'crowd_parent_id'=>'require',
            'picture'=>'require',
        ];
        $msg=[
            'crowd_id.require'=>'请选择特殊人群',
            'crowd_id.unique'=>'特殊人群已存在',
            'crowd_parent_id.require'=>'发生错误，缺少分类',
            'picture.require'=>'请选择上传相关证件',
        ];
        $result=$this->validate(input(),$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $collectionholdercrowd_model=new Collectionholdercrowds();
        $other_datas=$collectionholdercrowd_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $collectionholdercrowd_model->isUpdate(true)->save($datas);
        if($collectionholdercrowd_model !== false){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

    /* ========== 删除 ========== */
    public function delete(){
        $holder_id=input('holder_id');
        if(!$holder_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 成员信息 ++++++++++ */
        $holder_info=Collectionholders::withTrashed()
            ->field(['id','item_id','community_id','collection_id','name','address','phone','holder'])
            ->with('item,community,collection')
            ->where('id',$holder_id)
            ->find();

        if($holder_info->item->getData('status') !=1){
            switch ($holder_info->item->getData('status')){
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
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$holder_info->collection_id])->order('created_at desc')->value('status');
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
        $res=Collectionholdercrowds::destroy($ids);
        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }

    /* ========== 恢复 ========== */
    public function restore(){
        $holder_id=input('holder_id');
        if(!$holder_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 成员信息 ++++++++++ */
        $holder_info=Collectionholders::withTrashed()
            ->field(['id','item_id','community_id','collection_id','name','address','phone','holder'])
            ->with('item,community,collection')
            ->where('id',$holder_id)
            ->find();

        if($holder_info->item->getData('status') !=1){
            switch ($holder_info->item->getData('status')){
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
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$holder_info->collection_id])->order('created_at desc')->value('status');
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

        $res=Db::table('collection_holder_crowd')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
        if($res){
            return $this->success('恢复成功','');
        }else{
            return $this->error('恢复失败');
        }
    }

    /* ========== 销毁 ========== */
    public function destroy(){
        $holder_id=input('holder_id');
        if(!$holder_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 成员信息 ++++++++++ */
        $holder_info=Collectionholders::withTrashed()
            ->field(['id','item_id','community_id','collection_id','name','address','phone','holder'])
            ->with('item,community,collection')
            ->where('id',$holder_id)
            ->find();

        if($holder_info->item->getData('status') !=1){
            switch ($holder_info->item->getData('status')){
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
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$holder_info->collection_id])->order('created_at desc')->value('status');
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
        $res=Collectionholdercrowds::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
