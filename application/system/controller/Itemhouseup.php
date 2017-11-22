<?php
/* |------------------------------------------------------
 * | 安置房价上浮设置
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

use app\system\model\Itemhouseups;
use think\Db;

class Itemhouseup extends Auth
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

        /* ++++++++++ 查询 ++++++++++ */
        $itemhouseup_model=new Itemhouseups();
        $itemhouseups=$itemhouseup_model
            ->withTrashed()
            ->where($where)
            ->select();

        $datas['itemhouseups']=$itemhouseups;
        
        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Itemhouseups();
        if(request()->isPost()){
            $rules=[
                'item_id'=>'require',
                'up_start'=>'require|unique:item_house_up,item_id='.input('item_id').'&up_start='.input('up_start'),
            ];
            $msg=[
                'item_id.require'=>'数据错误，请关闭窗口重试',
                'up_start.require'=>'请输入上浮起始面积',
                'up_start.unique'=>'上浮起始设置已存在',
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

            return view('modify',[
                'model'=>$model,
                'item_id'=>$item_id,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Itemhouseups::withTrashed()->find($id);

        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Itemhouseups();

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
            'up_start'=>'require|unique:item_house_up,item_id='.input('item_id').'&up_start='.input('up_start'),
        ];
        $msg=[
            'item_id.require'=>'数据错误，请关闭窗口重试',
            'up_start.require'=>'请输入上浮起始面积',
            'up_start.unique'=>'上浮起始设置已存在',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $itemhouseup_model=new Itemhouseups();
        $other_datas=$itemhouseup_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $itemhouseup_model->isUpdate(true)->save($datas);
        if($itemhouseup_model !== false){
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
        $res=Itemhouseups::destroy($ids);
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

        $res=Db::table('item_house_up')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Itemhouseups::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
