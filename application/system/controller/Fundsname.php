<?php
/* |------------------------------------------------------
 * | 资金款项
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 状态
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;

use app\system\model\Fundsnames;
use think\Db;

class Fundsname extends Auth
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
        $field=['id','name','infos','status','deleted_at'];
        /* ++++++++++ 名称 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['name']=['like','%'.$name.'%'];
            $datas['name']=$name;
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
        /* ++++++++++ 是否删除 ++++++++++ */
        $deleted=input('deleted');
        $fundsname_model=new Fundsnames();
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $fundsname_model=$fundsname_model->onlyTrashed();
            }
        }else{
            $fundsname_model=$fundsname_model->withTrashed();
        }
        $fundsnames=$fundsname_model->where($where)->field($field)->order([$ordername=>$orderby])->paginate($display_num);

        $datas['fundsnames']=$fundsnames;

        $this->assign($datas);

        return view($this->theme.'/fundsname/index');
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Fundsnames();
        if(request()->isPost()){
            $rules=[
                'name'=>'require|unique:funds_name',
            ];
            $msg=[
                'name.require'=>'名称不能为空',
                'name.unique'=>'名称已存在',
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
            return view($this->theme.'/fundsname/modify',[
                'model'=>$model,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Fundsnames::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Fundsnames();

        return view($this->theme.'/fundsname/modify',[
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
            'name'=>'require|unique:funds_name,name,'.$id.',id',
        ];
        $msg=[
            'name.require'=>'名称不能为空',
            'name.unique'=>'名称已存在',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $fundsname_model=new Fundsnames();
        $other_datas=$fundsname_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $fundsname_model->isUpdate(true)->save($datas);
        if($fundsname_model !== false){
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
        $model=new Fundsnames();
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

        /*----- 当删除条数为1条时 -----*/
        if(count($ids)==1){
            if(is_array($ids)){
                $funds_ids = $ids[0];
            }else{
                $funds_ids = $ids;
            }
            $fundsin_count = model('Fundsins')->withTrashed()->where('name_id',$funds_ids)->count();
            $fundsout_count = model('Fundsouts')->withTrashed()->where('name_id',$funds_ids)->count();
            if($fundsin_count||$fundsout_count){
                return $this->error('当前资金款项正在被使用，删除失败');
            }
            $rs =  model('Fundsnames')->destroy(['id'=>$funds_ids]);
            if($rs){
                return $this->success('删除成功','');
            }else{
                return $this->error('删除失败');
            }
        }else{
            /*----- 当删除条数为多条时 -----*/
            $num = 0;
            $del_num = 0;
            $del_ids = [];
            foreach ($ids as $k=>$v){
                $fundsin_count = model('Fundsins')->withTrashed()->where('name_id',$v)->count();
                $fundsout_count = model('Fundsouts')->withTrashed()->where('name_id',$v)->count();
                if(!$fundsin_count&&!$fundsout_count){
                    $del_ids[] = $v;
                    $del_num += 1;
                }else{
                    $num += 1;
                }
            }
            if($del_ids){
                model('Fundsnames')->destroy(['id'=>['in',$del_ids]]);
            }
            if($num==count($ids)){
                return $this->error('选中资金款项正在被使用，删除失败');
            }
            if($del_num==count($ids)){
                return $this->success('删除成功','');
            }else{
                return $this->success('删除成功'.$del_num.'条,其他资金款项正在被使用','');
            }
        }

        $res=Fundsnames::destroy(['id'=>['in',$ids]]);
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

        $res=Db::table('funds_name')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Fundsnames::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
