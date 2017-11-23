<?php
/* |------------------------------------------------------
 * | 项目控制流程
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

use app\system\model\Itemprocesss;
use app\system\model\Processs;
use think\Db;

class Itemprocess extends Auth
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
        $field=['ip.*','p.name','p.infos'];

        /* ++++++++++ 查询 ++++++++++ */
        $itemprocess_model=new Itemprocesss();
        $itemprocesss=$itemprocess_model
            ->withTrashed()
            ->alias('ip')
            ->field($field)
            ->join('process p','p.id=ip.process_id','left')
            ->where($where)
            ->order('ip.sort asc')
            ->select();

        $datas['itemprocesss']=$itemprocesss;
        
        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Itemprocesss();
        if(request()->isPost()){
            $rules=[
                'item_id'=>'require',
                'process_id'=>'require|unique:item_process,item_id='.input('item_id').'&process_id='.input('process_id'),
            ];
            $msg=[
                'item_id.require'=>'数据错误，请关闭窗口重试',
                'process_id.require'=>'请选择流程',
                'process_id.unique'=>'控制流程已存在',
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
           
            /* ++++++++++ 控制流程 ++++++++++ */
            $processs=Processs::field(['id','name'])->select();

            return view('modify',[
                'model'=>$model,
                'item_id'=>$item_id,
                'processs'=>$processs,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Itemprocesss::withTrashed()
            ->alias('ip')
            ->field(['ip.*','p.name','p.infos'])
            ->join('process p','p.id=ip.process_id','left')
            ->where('ip.id',$id)
            ->find();

        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Itemprocesss();

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

        $itemprocess_model=new Itemprocesss();
        $other_datas=$itemprocess_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $itemprocess_model->isUpdate(true)->allowField(['sort','status','updated_at'])->save($datas);
        if($itemprocess_model !== false){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

    /* ========== 排序 ========== */
    public function sort(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        $sorts=isset($inputs['sorts'])?$inputs['sorts']:[];
        if(empty($ids)||empty($sorts)){
            return $this->error('至少选择一项');
        }
        $datas=[];
        $i=0;
        $time=time();
        foreach ($ids as $id){
            $datas[$i]['id']=$id;
            $datas[$i]['sort']=(int)$sorts[$id];
            $datas[$i]['updated_at']=$time;
            $i++;
        }
        $sqls=batch_update_sql('item_process',['id','sort','updated_at'],$datas,['sort','updated_at'],'id');
        $res=false;
        if($sqls){
            foreach ($sqls as $sql){
                $res=db()->execute($sql);
            }
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
        $res=Itemprocesss::destroy($ids);
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

        $res=Db::table('item_process')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Itemprocesss::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
