<?php
/* |------------------------------------------------------
 * | 评估师
 * |------------------------------------------------------
 * */

namespace app\company\controller;


use app\system\model\Companyvaluers;
use think\Db;

class Companyvaluer extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index()
    {
        /* ********** 查询条件 ********** */
        $datas=[];
        $where['company_id']=session('company.company_id');
        /* ++++++++++ 姓名 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['v.name']=['like','%'.$name.'%'];
            $datas['name']=$name;
        }
        /* ++++++++++ 注册号 ++++++++++ */
        $register_num=trim(input('register_num'));
        if($register_num){
            $where['register_num']=['like','%'.$register_num.'%'];
            $datas['register_num']=$register_num;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1])){
            $where['v.status']=$status;
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
        /* ++++++++++ 是否删除 ++++++++++ */
        $deleted=input('deleted');
        $companyvaluer_model=new Companyvaluers();
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $companyvaluer_model=$companyvaluer_model->onlyTrashed();
            }
        }else{
            $companyvaluer_model=$companyvaluer_model->withTrashed();
        }
        $companyvaluers=$companyvaluer_model
            ->where($where)
            ->order([$ordername=>$orderby])
            ->paginate($display_num);

        $datas['companyvaluers']=$companyvaluers;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        $model=new Companyvaluers();
        if(request()->isPost()){
            $rules=[
                'name'=>'require',
                'register_num'=>'require|unique:company_valuer',
                'valid_at'=>'require',
            ];
            $msg=[
                'name.require'=>'姓名不能为空',
                'register_num.require'=>'注册号不能为空',
                'register_num.unique'=>'注册号已存在',
                'valid_at.require'=>'请选择有效期',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $datas['company_id']=session('company.company_id');
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
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Companyvaluers::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Companyvaluers();

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
            'register_num'=>'require|unique:company_valuer,register_num,'.$id.',id',
            'valid_at'=>'require',
        ];
        $msg=[
            'name.require'=>'姓名不能为空',
            'register_num.require'=>'注册号不能为空',
            'register_num.unique'=>'注册号已存在',
            'valid_at.require'=>'请选择有效期',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $companyvaluer_model=new Companyvaluers();
        $other_datas=$companyvaluer_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $companyvaluer_model->isUpdate(true)->except('company_id')->save($datas);
        if($companyvaluer_model !== false){
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
        $model=new Companyvaluers();
        $res=$model->save(['status'=>$status],['id'=>['in',$ids]]);
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
        $res=Companyvaluers::destroy(['id'=>['in',$ids]]);
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

        $res=Db::table('company_valuer')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Companyvaluers::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }

}