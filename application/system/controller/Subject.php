<?php
/* |------------------------------------------------------
 * | 重要补偿科目
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

use app\system\model\Subjects;
use think\Db;

class Subject extends Auth
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
        $field=['id','name','num_from','unit','infos','deleted_at'];
        /* ++++++++++ 名称 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['name']=['like','%'.$name.'%'];
            $datas['name']=$name;
        }
        /* ++++++++++ 数量来源 ++++++++++ */
        $num_from=input('num_from');
        if(is_numeric($num_from) && in_array($num_from,[0,1])){
            $where['num_from']=$num_from;
            $datas['num_from']=$num_from;
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
        /* ++++++++++ 是否删除 ++++++++++ */
        $deleted=input('deleted');
        $subject_model=new Subjects();
        $datas['model']=$subject_model;
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $subject_model=$subject_model->onlyTrashed();
            }
        }else{
            $subject_model=$subject_model->withTrashed();
        }
        $subjects=$subject_model->where($where)->field($field)->order([$ordername=>$orderby])->paginate($display_num);

        $datas['subjects']=$subjects;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Subjects();
        if(request()->isPost()){
            $rules=[
                'name'=>'require',
                'unit'=>'require',
            ];
            $msg=[
                'name.require'=>'名称不能为空',
                'unit.require'=>'数量单位不能为空',
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
        $infos=Subjects::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Subjects();

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
            'name'=>'require',
            'unit'=>'require',
        ];
        $msg=[
            'name.require'=>'名称不能为空',
            'unit.require'=>'数量单位不能为空',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $subject_model=new Subjects();
        $other_datas=$subject_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $subject_model->isUpdate(true)->save($datas);
        if($subject_model !== false){
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
        $res=Subjects::destroy($ids);
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

        $res=Db::table('subject')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Subjects::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
