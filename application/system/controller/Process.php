<?php
/* |------------------------------------------------------
 * | 流程控制
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

use app\system\model\Processs;
use app\system\model\Processurls;
use think\Db;

class Process extends Auth
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
        $field=['id','name','infos','deleted_at'];
        /* ++++++++++ 名称 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['name']=['like','%'.$name.'%'];
            $datas['name']=$name;
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
        $process_model=new Processs();
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $process_model=$process_model->onlyTrashed();
            }
        }else{
            $process_model=$process_model->withTrashed();
        }
        $processs=$process_model->where($where)->field($field)->order([$ordername=>$orderby])->paginate($display_num);

        $datas['processs']=$processs;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Processs();
        if(request()->isPost()){
            $rules=[
                'name'=>'require|unique:process',
                'url'=>'require',
            ];
            $msg=[
                'name.require'=>'名称不能为空',
                'name.unique'=>'名称已存在',
                'url.require'=>'地址不能为空',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            Db::startTrans();
            $inputs=input();
            try{
                $process_model=$model;
                $other_datas=$process_model->other_data(input());
                $datas=array_merge(input(),$other_datas);
                $process_model->save($datas);

                $urls=array_values(array_unique(array_filter($inputs['url'])));
                $url_data=[];
                foreach ($urls as $url){
                    $url_data[]=[
                        'process_id'=>$process_model->id,
                        'url'=>$url,
                    ];
                }
                $processurl_model=new Processurls();
                $processurl_model->saveAll($url_data);
                $res=true;
                Db::commit();
            }catch (\Exception $exception){
                $res=false;
                Db::rollback();
            }

            if($res){
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
        $infos=Processs::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $processurls=Processurls::field(['id','url'])->where('process_id',$id)->select();

        $model=new Processs();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'processurls'=>$processurls,
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
            'name'=>'require|unique:process,name,'.$id.',id',
            'url'=>'require',
        ];
        $msg=[
            'name.require'=>'名称不能为空',
            'name.unique'=>'名称已存在',
            'url.require'=>'地址不能为空',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }
        $model=new Processs();
        Db::startTrans();
        $inputs=input();
        try{
            $process_model=$model;
            $other_datas=$process_model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $process_model->isUpdate(true)->save($datas);

            $urls=array_values(array_unique(array_filter($inputs['url'])));

            Processurls::where('process_id',$process_model->id)->delete(true);

            $url_data=[];
            foreach ($urls as $url){
                $url_data[]=[
                    'process_id'=>$process_model->id,
                    'url'=>$url,
                ];
            }
            $processurl_model=new Processurls();
            $processurl_model->saveAll($url_data);
            $res=true;
            Db::commit();
        }catch (\Exception $exception){
            $res=false;
            Db::rollback();
        }

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
        $res=Processs::destroy($ids);
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

        $res=Db::table('process')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $model=new Processs();
        Db::startTrans();
        try{
            Processs::onlyTrashed()->whereIn('id',$ids)->delete(true);
            Processurls::whereIn('process_id',$ids)->delete(true);
            $res=true;
            Db::commit();
        }catch (\Exception $exception){
            $res=false;
            Db::rollback();
        }

        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
