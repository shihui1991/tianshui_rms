<?php
/* |------------------------------------------------------
 * | 项目管理
 * |------------------------------------------------------
 * | 初始化操作 _initialize
 * | 列表 index
 * | 添加 add
 * | 详情 detail
 * | 修改 edit
 * | 状态 status
 * | 置顶 istop
 * | 审核 check
 * */
namespace app\system\controller;

use app\system\model\Items;
use app\system\model\Itemstatuss;
use app\system\model\Itemtimes;
use think\Db;
use think\Exception;

class Item extends Auth
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
        $field=['id','name','record_num','household','funds','house','is_top','status','deleted_at'];
        /* ++++++++++ 名称 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['name']=['like','%'.$name.'%'];
            $datas['name']=$name;
        }
        /* ++++++++++ 档案编号 ++++++++++ */
        $record_num=trim(input('record_num'));
        if($record_num){
            $where['record_num']=['like','%'.$record_num.'%'];
            $datas['record_num']=$record_num;
        }
        /* ++++++++++ 征收范围 ++++++++++ */
        $area=trim(input('area'));
        if($area){
            $where['area']=['like','%'.$area.'%'];
            $datas['area']=$area;
        }
        /* ++++++++++ 置顶 ++++++++++ */
        $is_top=input('is_top');
        if(is_numeric($is_top) && in_array($is_top,[0,1])){
            $where['is_top']=$is_top;
            $datas['is_top']=$is_top;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1,2,3])){
            $where['status']=$status;
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
        $item_model=new Items();
        $datas['model']=$item_model;

        $items=$item_model->withTrashed()->where($where)->field($field)->order(['is_top'=>'desc',$ordername=>$orderby])->paginate($display_num);

        $datas['items']=$items;

        $this->assign($datas);

        return view($this->theme.'/item/index');
    }

    /* ========== 添加 ========== */
    public function add(){
        $model=new Items();
        if(request()->isPost()){
            $rules=[
                'name'=>'require|unique:item',
                'record_num'=>'require|unique:item',
            ];
            $msg=[
                'name.require'=>'名称不能为空',
                'name.unique'=>'名称已存在',
                'record_num.require'=>'档案编号不能为空',
                'record_num.unique'=>'档案编号已存在',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result,'');
            }

            Db::startTrans();
            try{

                $other_datas=$model->other_data(input());
                $datas=array_merge(input(),$other_datas);
                $datas['status']=0;
                $model->save($datas);

                $status_model=new Itemstatuss();
                $status_data=[
                    'keyname'=>'item_id',
                    'keyvalue'=>$model->id,
                    'user_id'=>session('userinfo.user_id'),
                    'role_id'=>session('userinfo.role_id'),
                    'role_parent_id'=>session('userinfo.role_parent_id'),
                    'status'=>0
                ];
                $status_model->save($status_data);

                $res=true;
                $msg='保存成功';
                Db::commit();
            }catch (\Exception $exception){
                $res=false;
                $msg=$exception->getMessage();
                Db::rollback();
            }

            if($res){
                return $this->success($msg,'');
            }else{
                return $this->error($msg,'');
            }
        }else{
            return view($this->theme.'/item/modify',[
                'model'=>$model,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项','');
        }
        $infos=Items::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在','');
        }

        $model=new Items();

        return view($this->theme.'/item/modify',[
            'model'=>$model,
            'infos'=>$infos,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $id=input('id');
        if(!$id){
            return $this->error('错误操作','');
        }

        $status=Itemstatuss::where(['keyname'=>'item_id','keyvalue'=>$id])->order('created_at desc')->value('status');
        if($status==8){
            return $this->error('已通过审核，禁止修改！','');
        }

        $datas=input();
        $rules=[
            'name'=>'require|unique:item,name,'.$id.',id',
            'record_num'=>'require|unique:item,record_num,'.$id.',id',
        ];
        $msg=[
            'name.require'=>'名称不能为空',
            'name.unique'=>'名称已存在',
            'record_num.require'=>'档案编号不能为空',
            'record_num.unique'=>'档案编号已存在',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $model=new Items();
        Db::startTrans();
        try{
            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $model->except('status')->save($datas,['id'=>$id]);

            $status_model=new Itemstatuss();
            $status_data=[
                'keyname'=>'item_id',
                'keyvalue'=>$id,
                'user_id'=>session('userinfo.user_id'),
                'role_id'=>session('userinfo.role_id'),
                'role_parent_id'=>session('userinfo.role_parent_id'),
                'status'=>1
            ];
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
            return $this->error($msg,'');
        }
    }

    /* ========== 状态 ========== */
    public function status(){
        $id=input('id');
        if(!$id){
            return $this->error('错误操作','');
        }
        if(request()->isPost()){
            $status=input('status');
            if(!in_array($status,[0,1,2,3])){
                return $this->error('错误操作','');
            }

            if(!session('userinfo.is_admin')){
                return $this->error('没有操作权限','');
            }

            $model=new Items();
            Db::startTrans();
            try{
                $model->allowField(['status','updated_at'])->save(['status'=>$status],['id'=>$id]);

                $status_model=new Itemstatuss();
                $status_data=[
                    'keyname'=>'item_id',
                    'keyvalue'=>$id,
                    'user_id'=>session('userinfo.user_id'),
                    'role_id'=>session('userinfo.role_id'),
                    'role_parent_id'=>session('userinfo.role_parent_id'),
                    'status'=>1
                ];
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
                return $this->error($msg,'');
            }
        }else{
            $datas['id']=$id;

            $model=new Itemstatuss();
            $datas['model']=$model;

            $statuss=$model->with('user,role')->where(['keyname'=>'item_id','keyvalue'=>$id])->order('created_at asc')->paginate();
            $datas['statuss']=$statuss;

            $this->assign($datas);

            return view($this->theme.'/item/status');
        }
    }

    /* ========== 置顶 ========== */
    public function istop(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        $top=input('top');

        if(empty($ids)){
            return $this->error('至少选择一项','');
        }
        if(!in_array($top,[0,1])){
            return $this->error('错误操作','');
        }
        $model=new Items();
        $res=$model->allowField(['is_top','updated_at'])->save(['is_top'=>$top],['id'=>['in',$ids]]);
        if($res){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败','');
        }
    }

    /* ========== 审核 ========== */
    public function check(){
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
            $last_status=Itemstatuss::where(['keyname'=>'item_id','keyvalue'=>$id])->order('created_at desc')->find();
            if($last_status->role_parent_id!=session('userinfo.role_id') && $last_status->role_parent_id!=session('userinfo.role_parent_id')){
                throw new Exception('审核流程已超出权限！');
            }
            $status_model=new Itemstatuss();
            $status_data=[
                'keyname'=>'item_id',
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

    /* ========== 重要时间 ========== */
    public function itemtime(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问');
        }
        $item_info=Items::where('id',$item_id)->field(['id','name','status'])->find();
        if(!$item_info){
            return $this->error('选择项目不存在');
        }
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

        if(request()->isPost()) {
            $model=new Itemtimes();
            Db::startTrans();
            try{
                if(input('id')){
                    $model->save(input(),['id'=>input('id')]);
                }else{
                    $model->save(input());
                }
                $res=true;
                $msg='保存成功';
                Db::commit();
            }catch (\Exception $exception){
                $res=false;
                $msg=$exception->getMessage();
                Db::commit();
            }
            if($res){
                return $this->success($msg,'');
            }else{
                return $this->error($msg,'');
            }
        }else{
            $datas['item_info']=$item_info;
            $infos=Itemtimes::where('item_id',$item_id)->find();
            if($infos){
                $datas['infos']=$infos;
            }

            $this->assign($datas);
            return view($this->theme.'/item/itemtime');
        }
    }

    /* ========== 高拍仪页面 ========== */
    public function gaopaiyi(){
        return view($this->theme.'/item/gaopaiyi');
    }
}
