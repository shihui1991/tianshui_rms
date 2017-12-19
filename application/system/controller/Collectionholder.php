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

use app\system\model\Collectionholdercrowds;
use app\system\model\Collectionholderhouses;
use app\system\model\Collectionholders;
use app\system\model\Collectioncommunitys;
use app\system\model\Collections;
use app\system\model\Items;
use app\system\model\Itemstatuss;
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
        $where=[];
        $datas=[];
        /* ********** 是否弹出层 ********** */
        $l=input('l');
        $collection_id=input('collection_id');
        if($l){
            if(!$collection_id){
                return $this->error('错误操作','');
            }
            $with='';
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
            $with='item,community,collection';
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
        /* ++++++++++ 名称 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['name']=['like','%'.$name.'%'];
            $datas['name']=$name;
        }
        /* ++++++++++ 地址 ++++++++++ */
        $address=trim(input('address'));
        if($address){
            $where['address']=['like','%'.$address.'%'];
            $datas['address']=$address;
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
            ->with($with)
            ->where($where)
            ->order([$ordername=>$orderby])
            ->paginate($display_num);

        $datas['collectionholders']=$collectionholders;
        
        $this->assign($datas);

        return view($this->theme.'/collectionholder/'.$view);
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


        $model=new Collectionholders();
        if(request()->isPost()){
            $rules=[
                'name'=>'require',
                'phone'=>'require',
                'address'=>'require',
                'portion'=>'between:0,100',
            ];
            $msg=[
                'name.require'=>'名称不能为空',
                'phone.require'=>'电话不能为空',
                'address.require'=>'地址不能为空',
                'portion.between'=>'补偿份额在0-100之间',
            ];
            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            if($collection_info->getData('type') && in_array(input('holder'),[1,2])){
                $holders=Collectionholders::where([
                    'item_id'=>$collection_info->item_id,
                    'community_id'=>$collection_info->community_id,
                    'collection_id'=>$collection_info->id,
                    'holder'=>input('holder')
                ])->count();
                if($holders){
                    return $this->error('公产房屋只能有一个产权人和一个承租人');
                }
            }
            $portion=Collectionholders::where([
                'item_id'=>$collection_info->item_id,
                'community_id'=>$collection_info->community_id,
                'collection_id'=>$collection_info->id
            ])->sum('portion');
            if((input('portion')+$portion)>100){
                return $this->error('补偿份额总和不能超过100%');
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
            /* ++++++++++ 常用民族 ++++++++++ */
            $nations=Nations::field(['id','name','status'])->where('status',1)->select();

            return view($this->theme.'/collectionholder/modify',[
                'model'=>$model,
                'collection_info'=>$collection_info,
                'nations'=>$nations,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collectionholders::withTrashed()->with('item,community,collection')->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collectionholders();

        /* ++++++++++ 常用民族 ++++++++++ */
        $nations=Nations::field(['id','name','status'])->where('status',1)->select();


        return view($this->theme.'/collectionholder/modify',[
            'model'=>$model,
            'infos'=>$infos,
            'nations'=>$nations,
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
            'name'=>'require',
            'phone'=>'require',
            'address'=>'require',
            'portion'=>'between:0,100',
        ];
        $msg=[
            'name.require'=>'名称不能为空',
            'phone.require'=>'电话不能为空',
            'address.require'=>'地址不能为空',
            'portion.between'=>'补偿份额在0-100之间',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        if($collection_info->getData('type') && in_array(input('holder'),[1,2])){
            $holders=Collectionholders::where([
                'item_id'=>$collection_info->item_id,
                'community_id'=>$collection_info->community_id,
                'collection_id'=>$collection_info->id,
                'holder'=>input('holder')
            ])->where('id','neq',$id)
                ->count();
            if($holders){
                return $this->error('公产房屋只能有一个产权人和一个承租人');
            }
        }
        $portion=Collectionholders::where([
            'item_id'=>$collection_info->item_id,
            'community_id'=>$collection_info->community_id,
            'collection_id'=>$collection_info->id
        ])->where('id','neq',$id)
            ->sum('portion');
        if((input('portion')+$portion)>100){
            return $this->error('补偿份额总和不能超过100%');
        }

        $collectionholder_model=new Collectionholders();
        $other_datas=$collectionholder_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $collectionholder_model->isUpdate(true)->except(['item_id','community_id','collection_id'])->save($datas);
        if($collectionholder_model !== false){
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
        $res=Collectionholders::destroy(['id'=>['in',$ids]]);
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

        $res=Db::table('collection_holder')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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

        Db::startTrans();
        try{
            $holder_ids=Collectionholders::onlyTrashed()->whereIn('id',$ids)->column('id');
            Collectionholdercrowds::withTrashed()->whereIn('holder_id',$holder_ids)->delete(true);
            Collectionholders::onlyTrashed()->whereIn('id',$holder_ids)->delete(true);
            Collectionholderhouses::withTrashed()->whereIn('collection_holder_id',$holder_ids)->delete(true);

            $res=true;
            $msg='销毁成功';
            Db::commit();
        }catch (\Exception $exception){
            $res=false;
            $msg=$exception->getMessage();
            Db::rollback();
        }
        if($res){
            return $this->success($msg,'');
        }else{
            return $this->error($msg);
        }
    }
}
