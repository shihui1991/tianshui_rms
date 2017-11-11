<?php
/* |------------------------------------------------------
 * | 项目重要补偿科目
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

use app\system\model\Itemsubjects;
use app\system\model\Subjects;
use think\Db;

class Itemsubject extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();


    }

    /* ========== 列表 ========== */
    public function index()
    {
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作');
        }
      
        /* ********** 查询条件 ********** */
        $datas=[
            'item_id'=>$item_id,
        ];
        $where=[
            'item_id'=>$item_id,
        ];
        $field=['is.id','is.item_id','is.subject_id','is.default','is.deleted_at','s.name as s_name','s.num_from','s.unit'];

        /* ++++++++++ 查询 ++++++++++ */
        $itemsubject_model=new Itemsubjects();
        $itemsubjects=$itemsubject_model
            ->withTrashed()
            ->alias('is')
            ->field($field)
            ->join('subject s','s.id=is.subject_id','left')
            ->where($where)
            ->select();

        $datas['itemsubjects']=$itemsubjects;
        
        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Itemsubjects();
        if(request()->isPost()){
            $rules=[
                'item_id'=>'require',
                'subject_id'=>'require|unique:item_subject,item_id='.input('item_id').'&subject_id='.input('subject_id'),
            ];
            $msg=[
                'item_id.require'=>'数据错误，请关闭窗口重试',
                'subject_id.require'=>'请选择补偿科目',
                'subject_id.unique'=>'补偿科目已存在',
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
            $item_id=input('item_id');
            if(!$item_id){
                return $this->error('错误操作');
            }
           
            /* ++++++++++ 补偿科目 ++++++++++ */
            $subjects=Subjects::field(['id','name'])->select();

            return view('modify',[
                'model'=>$model,
                'item_id'=>$item_id,
                'subjects'=>$subjects,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Itemsubjects::withTrashed()
            ->alias('is')
            ->field(['is.*','s.name as s_name'])
            ->join('subject s','s.id=is.subject_id','left')
            ->where('is.id',$id)
            ->find();

        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Itemsubjects();

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
            'item_id'=>'require',
            'subject_id'=>'require|unique:item_subject,item_id='.input('item_id').'&subject_id='.input('subject_id'),
        ];
        $msg=[
            'item_id.require'=>'数据错误，请关闭窗口重试',
            'subject_id.require'=>'请选择补偿科目',
            'subject_id.unique'=>'补偿科目已存在',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $itemsubject_model=new Itemsubjects();
        $other_datas=$itemsubject_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $itemsubject_model->isUpdate(true)->save($datas);
        if($itemsubject_model !== false){
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
        $res=Itemsubjects::destroy($ids);
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

        $res=Db::table('item_subject')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Itemsubjects::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
