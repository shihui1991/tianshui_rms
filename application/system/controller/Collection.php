<?php
/* |------------------------------------------------------
 * | 入户摸底
 * |------------------------------------------------------
 * | 初始化操作 _initialize
 * | 列表 index
 * | 添加 add
 * | 详情 detail
 * | 修改 edit
 * | 状态 status
 * | 删除 delete
 * | 恢复 restore
 * | 销毁 destroy
 * | 审核 check
 * */
namespace app\system\controller;

use app\system\model\Buildinguses;
use app\system\model\Collectionbuildings;
use app\system\model\Collectioncommunitys;
use app\system\model\Collectionholdercrowds;
use app\system\model\Collectionholderhouses;
use app\system\model\Collectionholders;
use app\system\model\Collectionobjects;
use app\system\model\Collections;
use app\system\model\Items;
use app\system\model\Itemstatuss;
use app\system\model\Layouts;
use think\Db;
use think\Exception;

class Collection extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();

    }

    /* ========== 默认列表 ========== */
    public function index()
    {
        $where=[];
        $datas=[];
        /* ********** 是否弹出层 ********** */
        $l=input('l');
        $item_id=input('item_id');
        if($l){
            if(!$item_id){
                return $this->error('错误操作','');
            }
            $with='community,realuse';
            $view='index';
            /* ++++++++++ 项目信息 ++++++++++ */
            $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
            $datas['item_info']=$item_info;
            $where['item_id']=$item_id;
            /* ++++++++++ 项目列表 ++++++++++ */
            $items=Items::field(['id','name','status','is_top'])->order('is_top desc')->select();
            $datas['items']=$items;
        }else{
            if($item_id){
                $where['item_id']=$item_id;
                $datas['item_id']=$item_id;
            }
            $with='item,community,realuse';
            $view='all';
            /* ++++++++++ 项目列表 ++++++++++ */
            $items=Items::field(['id','name','status','is_top'])->order('is_top desc')->select();
            $datas['items']=$items;
        }

        /* ********** 查询条件 ********** */
        $field=['id','item_id','community_id','building','unit','floor','number','type','real_use','is_agree', 'compensate_way','compensate_price','status','deleted_at'];

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
            ->field($field)
            ->with($with)
            ->where($where)
            ->order([$ordername=>$orderby])
            ->paginate($display_num);

        $datas['collections']=$collections;

        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
        $datas['collectioncommunitys']=$collectioncommunitys;
        /* ++++++++++ 使用性质 ++++++++++ */
        $buildinguses=Buildinguses::field(['id','name','status'])->where(['status'=>1])->select();
        $datas['buildinguses']=$buildinguses;

        $this->assign($datas);

        return view($view);
    }

    /* ========== 添加 ========== */
    public function add(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
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

        $model=new Collections();
        if(request()->isPost()){
            $rules=[
                'community_id'=>'require',
            ];
            $msg=[
                'community_id.require'=>'请选择片区',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            Db::startTrans();
            try{
                $other_datas=$model->other_data(input());
                $datas=array_merge(input(),$other_datas);
                $model->save($datas);

                $status_data=[
                    'keyname'=>'collection_id',
                    'keyvalue'=>$model->id,
                    'user_id'=>session('userinfo.user_id'),
                    'role_id'=>session('userinfo.role_id'),
                    'role_parent_id'=>session('userinfo.role_parent_id'),
                    'status'=>0
                ];
                $status_model=new Itemstatuss();
                $status_model->save($status_data);

                $res=true;
                $msg='保存成功';
                Db::commit();
            }catch (\Exception $exception){
                $msg=$exception->getMessage();
                $res=false;
                Db::rollback();
            }

            if($res){
                return $this->success($msg,'');
            }else{
                return $this->error($msg);
            }
        }else{
            /* ++++++++++ 片区 ++++++++++ */
            $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
            /* ++++++++++ 使用性质 ++++++++++ */
            $buildinguses=Buildinguses::field(['id','name','status'])->where(['status'=>1])->select();
            /* ++++++++++ 户型 ++++++++++ */
            $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();

            return view('modify',[
                'model'=>$model,
                'item_info'=>$item_info,
                'collectioncommunitys'=>$collectioncommunitys,
                'buildinguses'=>$buildinguses,
                'layouts'=>$layouts,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collections::withTrashed() ->with('item,community,realuse')->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collections();

        /* ++++++++++ 使用性质 ++++++++++ */
        $buildinguses=Buildinguses::field(['id','name','status'])->where(['status'=>1])->select();
        /* ++++++++++ 户型 ++++++++++ */
        $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'buildinguses'=>$buildinguses,
            'layouts'=>$layouts,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
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
            return $this->error('错误操作');
        }

        $status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$id])->order('created_at desc')->value('status');
        if($status==8){
            return $this->error('已通过审核，禁止修改！');
        }

        Db::startTrans();
        try{
            $collection_model=new Collections();
            $other_datas=$collection_model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $collection_model->except(['item_id','community_id'])->save($datas,['id'=>$id]);

            $status_data=[
                'keyname'=>'collection_id',
                'keyvalue'=>$id,
                'user_id'=>session('userinfo.user_id'),
                'role_id'=>session('userinfo.role_id'),
                'role_parent_id'=>session('userinfo.role_parent_id'),
                'status'=>1
            ];
            $status_model=new Itemstatuss();
            $status_model->save($status_data);

            $res=true;
            $msg='修改成功';
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

    /* ========== 状态 ========== */
    public function status(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }

        if(request()->isPost()){
            $item_id=input('item_id');
            if(!$item_id){
                return $this->error('错误操作','');
            }
            /* ++++++++++ 项目 ++++++++++ */
            $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
            if($item_info->getData('status') ==2){
                $msg='项目已完成，禁止操作！';
                if(request()->isAjax()){
                    return $this->error($msg,'');
                }else{
                    return $msg;
                }
            }
            $status=input('status');
            if(!in_array($status,[0,1])){
                return $this->error('错误操作');
            }

            $model=new Collections();
            Db::startTrans();
            try{
                $status_code=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$id])->order('created_at desc')->value('status');
                if($status_code==8){
                    throw new Exception('已通过审核，禁止修改！');
                }
                $status_data=[
                    'keyname'=>'collection_id',
                    'keyvalue'=>$id,
                    'user_id'=>session('userinfo.user_id'),
                    'role_id'=>session('userinfo.role_id'),
                    'role_parent_id'=>session('userinfo.role_parent_id'),
                    'status'=>1
                ];
                $status_model=new Itemstatuss();
                $status_model->save($status_data);

                $model->allowField(['status','updated_at'])->save(['status'=>$status],['id'=>$id]);
                $res=true;
                $msg='修改成功';
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
        }else{
            $datas['id']=$id;
            $item_id=input('item_id');
            if(!$item_id){
                return $this->error('错误操作','');
            }
            /* ++++++++++ 项目 ++++++++++ */
            $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
            $datas['item_info']=$item_info;

            $model=new Itemstatuss();
            $datas['model']=$model;

            $statuss=$model->with('user,role')->where(['keyname'=>'collection_id','keyvalue'=>$id])->order('created_at asc')->paginate();
            $datas['statuss']=$statuss;

            $this->assign($datas);

            return view();
        }
    }


    /* ========== 删除 ========== */
    public function delete(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if($item_info->getData('status') ==2){
            $msg='项目已完成，禁止操作！';
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
        if(is_array($ids)){
            $ids=implode(',',$ids);
        }
        Db::startTrans();
        try{
            $lists=db()->query('SELECT * FROM (SELECT * FROM `item_status` WHERE `keyname`=\'collection_id\' AND `keyvalue` IN ('.$ids.') ORDER BY `created_at` DESC) cs GROUP BY `keyvalue`');
            $status_data=[];
            foreach ($lists as $list){
                if($list['status']==8){
                    throw new Exception('存在审核通过项，修改失败！');
                    break;
                }
                $status_data[]=[
                    'keyname'=>'collection_id',
                    'keyvalue'=>$list['keyvalue'],
                    'user_id'=>session('userinfo.user_id'),
                    'role_id'=>session('userinfo.role_id'),
                    'role_parent_id'=>session('userinfo.role_parent_id'),
                    'status'=>2
                ];
            }
            $status_model=new Itemstatuss();
            $status_model->saveAll($status_data);

            Collections::destroy(['id'=>['in',$ids]]);

            $res=true;
            $msg='删除成功';
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

    /* ========== 恢复 ========== */
    public function restore(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if($item_info->getData('status') ==2){
            $msg='项目已完成，禁止操作！';
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
        if(is_string($ids)){
            $ids=[$ids];
        }
        Db::startTrans();
        try{
            $status_data=[];
            foreach ($ids as $id){
                $status_data[]=[
                    'keyname'=>'collection_id',
                    'keyvalue'=>$id,
                    'user_id'=>session('userinfo.user_id'),
                    'role_id'=>session('userinfo.role_id'),
                    'role_parent_id'=>session('userinfo.role_parent_id'),
                    'status'=>3
                ];
            }
            $status_model=new Itemstatuss();
            $status_model->saveAll($status_data);

            Db::table('collection')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);

            $res=true;
            $msg='恢复成功';
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

    /* ========== 销毁 ========== */
    public function destroy(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        Db::startTrans();
        try{
            $ids=Collections::onlyTrashed()->whereIn('id',$ids)->value('id');
            if(!$ids){
                throw new Exception('只能销毁已删除的数据！');
            }
            $status_data=[];
            foreach ($ids as $id){
                $status_data[]=[
                    'keyname'=>'collection_id',
                    'keyvalue'=>$id,
                    'user_id'=>session('userinfo.user_id'),
                    'role_id'=>session('userinfo.role_id'),
                    'role_parent_id'=>session('userinfo.role_parent_id'),
                    'status'=>4
                ];
            }
            $status_model=new Itemstatuss();
            $status_model->saveAll($status_data);

            $collection_ids=Collections::onlyTrashed()->whereIn('id',$ids)->column('id');

            Collectionobjects::withTrashed()->whereIn('collection_id',$collection_ids)->delete(true);
            Collectionholderhouses::withTrashed()->whereIn('collection_id',$collection_ids)->delete(true);
            Collectionholders::withTrashed()->whereIn('collection_id',$collection_ids)->delete(true);
            Collectionholdercrowds::withTrashed()->whereIn('collection_id',$collection_ids)->delete(true);
            Collectionbuildings::withTrashed()->whereIn('collection_id',$collection_ids)->delete(true);
            Collections::onlyTrashed()->whereIn('id',$collection_ids)->delete(true);

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

    /* ========== 审核 ========== */
    public function check(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if($item_info->getData('status') ==2){
            $msg='项目已完成，禁止操作！';
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
        $check=input('check');
        if(!in_array($check,[8,9])){
            return $this->error('错误操作','');
        }
        Db::startTrans();
        try{
            $last_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$id])->order('created_at desc')->find();
            if($last_status->role_parent_id!=session('userinfo.role_id') && $last_status->role_parent_id!=session('userinfo.role_parent_id')){
                throw new Exception('审核流程已超出权限！');
            }
            $status_model=new Itemstatuss();
            $status_data=[
                'keyname'=>'collection_id',
                'keyvalue'=>$id,
                'user_id'=>session('userinfo.user_id'),
                'role_id'=>session('userinfo.role_id'),
                'role_parent_id'=>session('userinfo.role_parent_id'),
                'status'=>$check
            ];
            $status_model->save($status_data);

            $res=true;
            $msg='操作成功';
            Db::commit();
        }catch (\Exception $exception){
            $msg=$exception->getMessage();
            $res=false;
            Db::rollback();
        }

        if($res){
            return $this->success($msg,'');
        }else{
            return $this->error($msg,'');
        }
    }

    /* ========== 房屋征收摸底汇总----Excel导出 ========== */
    public function statis(){
        $item_id = input('item_id');
        if(!$item_id){
            return $this->error('请先选择项目');
        }

       $collections =  model('Collections')
           ->alias('c')
           ->field(['ch.name as holder_name','count(ch.id) as holder_count','ch.cardnum','ch.gender','ch.nation'])
           ->join('collection_holder ch','ch.collection_id = c.id','left')
           ->join('collection_holder_crowd chc','chc.holder_id = chc.id','left')
            ->where('c.item_id',$item_id)
           ->group('c.id')
           ->select();
        dump($collections);
    }
}
