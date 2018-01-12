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
use app\system\model\Itemstatuss;
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
        $datas['collection_info']=$collection_info;
        /* ++++++++++ 入户摸底状态 ++++++++++ */
        $collection_status=Itemstatuss::where(['keyname'=>'collection_id','keyvalue'=>$collection_id])->order('created_at desc')->value('status');
        $datas['collection_status']=$collection_status;

        /* ********** 查询条件 ********** */

        $where=[
            'collection_object.item_id'=>$collection_info->item_id,
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

        return view($this->theme.'/collectionobject/index');
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


        $model=new Collectionobjects();
        if(request()->isPost()){
            $rules=[
                'object_id'=>'require|unique:collection_object,collection_id='.input('collection_id').'&object_id='.input('object_id'),
                'number'=>'require|min:1',
            ];
            $msg=[
                'object_id.require'=>'请选择补偿事项',
                'object_id.unique'=>'补偿事项已存在',
                'number.require'=>'请输入数量',
                'number.min'=>'数量至少为1',
            ];
            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $datas['item_id']=$collection_info->item_id;
            $datas['collection_id']=$collection_info->id;


            $model->save($datas);
            if($model !== false){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            /* ++++++++++ 其他补偿事项 ++++++++++ */
            $objects=Objects::field(['id','name'])->select();

            return view($this->theme.'/collectionobject/modify',[
                'model'=>$model,
                'collection_info'=>$collection_info,
                'objects'=>$objects,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
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

        return view($this->theme.'/collectionobject/modify',[
            'model'=>$model,
            'infos'=>$infos,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
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

        $id=input('id');
        if(!$id){
            return $this->error('错误操作');
        }
        $datas=input();
        $rules=[
            'number'=>'require|min:1',
        ];
        $msg=[
            'number.require'=>'请输入数量',
            'number.min'=>'数量至少为1',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
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
        $res=Collectionobjects::destroy(['id'=>['in',$ids]]);
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

        $res=Db::table('collection_object')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Collectionobjects::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }

    /* ========== 高拍仪页面 ========== */
    public function gaopaiyi(){
        return view($this->theme.'/collectionobject/gaopaiyi');
    }
}
