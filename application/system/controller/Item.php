<?php
/* |------------------------------------------------------
 * | 项目管理
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 状态
 * */
namespace app\system\controller;

use app\system\model\Items;

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
        $field=['id','name','record_num','household','funds','house','is_top','status'];
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
        if(is_numeric($status) && in_array($status,[0,1])){
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
        $deleted=input('deleted');
        $item_model=new Items();
        $datas['model']=$item_model;
        $items=$item_model->where($where)->field($field)->order(['is_top'=>'desc',$ordername=>$orderby])->paginate($display_num);

        $datas['items']=$items;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
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
            return view('modify',[
                'model'=>$model,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Items::find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Items();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
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

        $item_model=new Items();
        $other_datas=$item_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $item_model->isUpdate(true)->save($datas);
        if($item_model !== false){
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
        $model=new Items();
        $res=$model->allowField(['status','updated_at'])->save(['status'=>$status],['id'=>['in',$ids]]);
        if($res){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

    /* ========== 置顶 ========== */
    public function istop(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        $top=input('top');

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        if(!in_array($top,[0,1])){
            return $this->error('错误操作');
        }
        $model=new Items();
        $res=$model->allowField(['is_top','updated_at'])->save(['is_top'=>$top],['id'=>['in',$ids]]);
        if($res){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }
}
