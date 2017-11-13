<?php
/* |------------------------------------------------------
 * | 入户摸底 其他补偿事项
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

use app\system\model\Collectionobjects;
use app\system\model\Collections;
use app\system\model\Objects;
use think\Db;

class Collectionobject extends Auth
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
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('错误操作');
        }
        /* ********** 查询条件 ********** */
        $datas=[
            'item_id'=>$item_id,
            'collection_id'=>$collection_id
        ];
        $where=[
            'collection_object.item_id'=>$item_id,
            'collection_object.collection_id'=>$collection_id
        ];
        $field=['co.id','co.item_id','co.collection_id','co.object_id','co.number','co.deleted_at', 'o.name as o_name'];

        /* ++++++++++ 查询 ++++++++++ */
        $collectionobject_model=new Collectionobjects();
        $collectionobjects=$collectionobject_model
            ->withTrashed()
            ->alias('co')
            ->field($field)
            ->join('object o','o.id=co.object_id','left')
            ->where($where)
            ->select();

        $datas['collectionobjects']=$collectionobjects;
        
        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Collectionobjects();
        if(request()->isPost()){
            $rules=[
                'item_id'=>'require',
                'collection_id'=>'require',
                'object_id'=>'require|unique:collection_object,collection_id='.input('collection_id').'&object_id='.input('object_id'),
                'number'=>'require|min:1',
            ];
            $msg=[
                'item_id.require'=>'数据错误，请关闭窗口重试',
                'collection_id.require'=>'数据错误，请关闭窗口重试',
                'object_id.require'=>'请选择补偿事项',
                'object_id.unique'=>'补偿事项已存在',
                'number.require'=>'请输入数量',
                'number.min'=>'数量至少为1',
            ];
            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }
            $collection_info=Collections::field(['id','item_id'])->find(input('collection_id'));
            if(!$collection_info){
                return $this->error('数据错误，请关闭窗口重试！');
            }
            if(input('item_id') != $collection_info->item_id ){
                return $this->error('数据错误，请关闭窗口重试');
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
            $collection_id=input('collection_id');
            if(!$collection_id){
                return $this->error('错误操作');
            }
            /* ++++++++++ 其他补偿事项 ++++++++++ */
            $objects=Objects::field(['id','name'])->select();

            return view('modify',[
                'model'=>$model,
                'item_id'=>$item_id,
                'collection_id'=>$collection_id,
                'objects'=>$objects,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collectionobjects::withTrashed()
            ->alias('co')
            ->field(['co.*','o.name as o_name'])
            ->join('object o','o.id=co.object_id','left')
            ->where('co.id',$id)
            ->find();

        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collectionobjects();

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
            'collection_id'=>'require',
            'object_id'=>'require|unique:collection_object,collection_id='.input('collection_id').'&object_id='.input('object_id'),
            'number'=>'require|min:1',
        ];
        $msg=[
            'item_id.require'=>'数据错误，请关闭窗口重试',
            'collection_id.require'=>'数据错误，请关闭窗口重试',
            'object_id.require'=>'请选择补偿事项',
            'object_id.unique'=>'补偿事项已存在',
            'number.require'=>'请输入数量',
            'number.min'=>'数量至少为1',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }
        $collection_info=Collections::field(['id','item_id'])->find(input('collection_id'));
        if(!$collection_info){
            return $this->error('数据错误，请关闭窗口重试！');
        }
        if(input('item_id') != $collection_info->item_id ){
            return $this->error('数据错误，请关闭窗口重试');
        }

        $collectionobject_model=new Collectionobjects();
        $other_datas=$collectionobject_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $collectionobject_model->isUpdate(true)->save($datas);
        if($collectionobject_model !== false){
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
        $res=Collectionobjects::destroy($ids);
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

        $res=Db::table('collection_object')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Collectionobjects::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
