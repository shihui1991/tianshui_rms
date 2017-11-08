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
        /* ********** 查询条件 ********** */
        $datas=[];
        $where=[];
        $field=['chc.id','chc.item_id','chc.community_id','chc.collection_id','chc.holder_id','chc.crowd_id','chc.crowd_parent_id','chc.deleted_at',
            'i.name as i_name','i.is_top','cc.address as cc_address','cc.name as cc_name','c.building as c_building','c.unit as c_unit','c.floor as c_floor','c.number as c_number',
            'ch.name as ch_name','ch.address as ch_address','ch.phone as ch_phone','cr.name as cr_name','crp.name as crp_name'];

        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        if(is_numeric($item_id)){
            $where['collection_holder_crowd.item_id']=$item_id;
            $datas['item_id']=$item_id;
        }
        /* ++++++++++ 片区 ++++++++++ */
        $community_id=input('community_id');
        if(is_numeric($community_id)){
            $where['collection_holder_crowd.community_id']=$community_id;
            $datas['community_id']=$community_id;
        }
        /* ++++++++++ 权属 ++++++++++ */
        $collection_id=input('collection_id');
        if(is_numeric($collection_id)){
            $where['collection_holder_crowd.collection_id']=$collection_id;
            $datas['collection_id']=$collection_id;
        }
        /* ++++++++++ 成员 ++++++++++ */
        $holder_id=input('holder_id');
        if(is_numeric($holder_id)){
            $where['collection_holder_crowd.holder_id']=$holder_id;
            $datas['holder_id']=$holder_id;
        }
        /* ++++++++++ 特殊人群分组 ++++++++++ */
        $crowd_parent_id=input('crowd_parent_id');
        if(is_numeric($crowd_parent_id)){
            $where['collection_holder_crowd.crowd_parent_id']=$crowd_parent_id;
            $datas['crowd_parent_id']=$crowd_parent_id;
        }
        /* ++++++++++ 特殊人群 ++++++++++ */
        $crowd_id=input('crowd_id');
        if(is_numeric($crowd_id)){
            $where['collection_holder_crowd.crowd_id']=$crowd_id;
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
            ->alias('chc')
            ->field($field)
            ->join('item i','i.id=chc.item_id','left')
            ->join('collection_community cc','cc.id=chc.community_id','left')
            ->join('collection c','c.id=chc.collection_id','left')
            ->join('collection_holder ch','ch.id=chc.holder_id','left')
            ->join('crowd cr','cr.id=chc.crowd_id','left')
            ->join('crowd crp','crp.id=chc.crowd_parent_id','left')
            ->where($where)
            ->order(['item.is_top'=>'desc','collection_holder_crowd.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['collectionholdercrowds']=$collectionholdercrowds;

        /* ++++++++++ 项目 ++++++++++ */
        $items=Items::field(['id','name','status','is_top'])->where(['status'=>1])->order('is_top desc')->select();
        $datas['items']=$items;
        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
        $datas['collectioncommunitys']=$collectioncommunitys;
        /* ++++++++++ 权属 ++++++++++ */
        $collections=Collections::field(['id','building','unit','floor','number','status'])->where('status',1)->select();
        $datas['collections']=$collections;
        /* ++++++++++ 成员 ++++++++++ */
        $collectionholders=Collectionholders::field(['id','name','address'])->select();
        $datas['collectionholders']=$collectionholders;
        /* ++++++++++ 特殊人群 ++++++++++ */
        $crowds=Crowds::field(['id','name','status','parent_id'])->where('status',1)->select();
        $datas['crowds']=$crowds;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        $model=new Collectionholdercrowds();
        if(request()->isPost()){
            $rules=[
                'item_id'=>'require',
                'community_id'=>'require',
                'collection_id'=>'require',
                'holder_id'=>'require',
                'crowd_parent_id'=>'require',
                'crowd_id'=>'require|unique:collection_holder_crowd,holder_id='.input('holder_id').'&collection_id='.input('collection_id').'&community_id='.input('community_id').'&item_id='.input('item_id'),
                'picture'=>'require',
            ];
            $msg=[
                'item_id.require'=>'错误操作，缺少项目参数',
                'community_id.require'=>'错误操作，缺少片区参数',
                'collection_id.require'=>'错误操作，缺少权属参数',
                'holder_id.require'=>'错误操作，缺少成员参数',
                'crowd_parent_id.require'=>'发生错误，缺少分类',
                'crowd_id.require'=>'请选择特殊人群',
                'crowd_id.unique'=>'特殊人群已存在',
                'picture.require'=>'请选择上传相关证件',
            ];
            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }
            $holder_info=Collectionholders::field(['id','item_id','community_id','collection_id'])->find(input('holder_id'));
            if(!$holder_info){
                return $this->error('错误操作，成员不存在！');
            }
            if(input('item_id') != $holder_info->item_id || input('community_id') != $holder_info->community_id || input('collection_id') != $holder_info->collection_id){
                return $this->error('错误操作，数据不一致！');
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
            if(!input('holder_id')){
                if(request()->isAjax()){
                    return $this->error('请选择成员！');
                }else{
                    die('请选择成员！');
                }
            }
            /* ++++++++++ 成员 ++++++++++ */
            $collectionholders=Collectionholders::alias('ch')
                ->field(['ch.id','ch.item_id','ch.community_id','ch.collection_id','ch.name','ch.address','i.name as i_name',
                    'cc.name as cc_name','cc.address as cc_address', 'c.building','c.unit','c.floor','c.number'])
                ->join('item i','i.id=ch.item_id','left')
                ->join('collection_community cc','cc.id=ch.community_id','left')
                ->join('collection c','c.id=ch.collection_id','left')
                ->where('ch.id',input('holder_id'))
                ->find();
            if(!$collectionholders){
                if(request()->isAjax()){
                    return $this->error('成员不存在！');
                }else{
                    die('成员不存在！');
                }
            }
            /* ++++++++++ 特殊人群 ++++++++++ */
            $crowds=Crowds::field(['id','name','status','parent_id'])->where('parent_id','<>',0)->where('status',1)->select();

            return view('modify',[
                'model'=>$model,
                'collectionholders'=>$collectionholders,
                'crowds'=>$crowds,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collectionholdercrowds::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collectionholdercrowds();

        /* ++++++++++ 成员 ++++++++++ */
        $collectionholders=Collectionholders::alias('ch')
            ->field(['ch.id','ch.item_id','ch.community_id','ch.collection_id','ch.name','ch.address','i.name as i_name',
                'cc.name as cc_name','cc.address as cc_address', 'c.building','c.unit','c.floor','c.number'])
            ->join('item i','i.id=ch.item_id','left')
            ->join('collection_community cc','cc.id=ch.community_id','left')
            ->join('collection c','c.id=ch.collection_id','left')
            ->where('ch.id',$infos->holder_id)
            ->find();
        if(!$collectionholders){
            if(request()->isAjax()){
                return $this->error('成员不存在！');
            }else{
                die('成员不存在！');
            }
        }
        /* ++++++++++ 特殊人群 ++++++++++ */
        $crowds=Crowds::field(['id','name','status','parent_id'])->where('parent_id','<>',0)->where('status',1)->select();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'collectionholders'=>$collectionholders,
            'crowds'=>$crowds,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $id=input('id');
        if(!$id){
            return $this->error('错误操作');
        }

        $rules=[
            'item_id'=>'require',
            'community_id'=>'require',
            'collection_id'=>'require',
            'holder_id'=>'require',
            'crowd_parent_id'=>'require',
            'crowd_id'=>'require|unique:collection_holder_crowd,holder_id='.input('holder_id').'&collection_id='.input('collection_id').'&community_id='.input('community_id').'&item_id='.input('item_id'),
            'picture'=>'require',
        ];
        $msg=[
            'item_id.require'=>'错误操作，缺少项目参数',
            'community_id.require'=>'错误操作，缺少片区参数',
            'collection_id.require'=>'错误操作，缺少权属参数',
            'holder_id.require'=>'错误操作，缺少成员参数',
            'crowd_parent_id.require'=>'发生错误，缺少分类',
            'crowd_id.require'=>'请选择特殊人群',
            'crowd_id.unique'=>'特殊人群已存在',
            'picture.require'=>'请选择上传相关证件',
        ];
        $result=$this->validate(input(),$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }
        $holder_info=Collectionholders::field(['id','item_id','community_id','collection_id'])->find(input('holder_id'));
        if(!$holder_info){
            return $this->error('错误操作，成员不存在！');
        }
        if(input('item_id') != $holder_info->item_id || input('community_id') != $holder_info->community_id || input('collection_id') != $holder_info->collection_id){
            return $this->error('错误操作，数据不一致！');
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
