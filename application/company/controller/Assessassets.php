<?php
/* |------------------------------------------------------
 * | 资产评估
 * |------------------------------------------------------
 * */

namespace app\company\controller;


use app\system\model\Assessassetss;
use app\system\model\Assessassetsvaluers;
use app\system\model\Assesss;
use app\system\model\Collections;
use think\Db;
use think\Exception;
use think\Request;

class Assessassets extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问');
        }

        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问');
        }

        $datas['item_id']=$item_id;
        $datas['collection_id']=$collection_id;
        $datas['company_id']=session('company.company_id');

        $where['item_id']=$item_id;
        $where['collection_id']=$collection_id;
        $where['company_id']=session('company.company_id');

        $assetss=Assessassetss::withTrashed()->with('company')->where($where)->select();

        $datas['assetss']=$assetss;

        $this->assign($datas);

        return view();
    }


    /* ========== 添加 ========== */
    public function add(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问');
        }

        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问');
        }
        if(request()->isPost()){
            $inputs=input();
            $rules=[
                'report_at'=>'require',
                'total'=>'require|min:0',
                'ids'=>'require',
            ];
            $msg=[
                'report_at.require'=>'请输入报告时间',
                'total.require'=>'请输入评估总额',
                'total.min'=>'评估总额不能少于0',
                'ids.require'=>'请评估师',
            ];

            $result=$this->validate($inputs,$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            Db::startTrans();
            try{
                $collection_info=Collections::field(['id','item_id','community_id','status'])->where('id',$collection_id)->find();
                if(!$collection_info){
                    throw new \Exception('数据异常，请关闭后重试',404404);
                }
                $assess=Assesss::where(['item_id'=>$item_id,'collection_id'=>$collection_id])->find();
                if(!$assess){
                    $assess=Assesss::create([
                        'item_id'=>$item_id,
                        'community_id'=>$collection_info->community_id,
                        'collection_id'=>$collection_id,
                        'estate'=>0,
                        'assets'=>input('total'),
                    ]);
                }

                $model=new Assessassetss();

                $other_datas=$model->other_data($inputs);
                $datas=array_merge(input(),$other_datas);

                $datas['status']=0;
                $datas['community_id']=$collection_info->community_id;
                $datas['assess_id']=$assess->id;
                $datas['company_id']=session('company.company_id');

                $model->save($datas);

                $valuer_data=[];
                foreach ($inputs['ids'] as $valuer_id){
                    $valuer_data[]=[
                        'item_id'=>$item_id,
                        'collection_id'=>$collection_id,
                        'assess_id'=>$assess->id,
                        'assets_id'=>$model->id,
                        'company_id'=>session('company.company_id'),
                        'valuer_id'=>$valuer_id,
                    ];
                }
                $valuer_model=new Assessassetsvaluers();
                $valuer_model->saveAll($valuer_data);

                $res=true;
                $msg='保存成功';
                Db::commit();
            }catch (\Exception $exception){
                $res=false;
                if($exception->getCode()==404404){
                    $msg=$exception->getMessage();
                }else{
                    $msg='保存失败';
                }
                Db::rollback();
            }

            if($res){
                return $this->success($msg,'');
            }else{
                return $this->error($msg,'');
            }

        }else{

            $datas['item_id']=$item_id;
            $datas['collection_id']=$collection_id;
            $datas['company_id']=session('company.company_id');
            $this->assign($datas);

            return view('modify');
        }
    }


    /* ========== 详情 ========== */
    public function detail()
    {
        $id=input('id');
        if(!$id){
            return $this->error('非法访问','');
        }
        $model=new Assessassetss();
        $datas['model']=$model;

        $infos=Assessassetss::withTrashed()->find($id);
        $datas['infos']=$infos;

        $valuers=Assessassetsvaluers::with('valuer')->where(['assets_id'=>$id])->select();
        $datas['valuers']=$valuers;
        $datas['valuer_ids']=[];

        $this->assign($datas);

        return view('modify');
    }

    /* ========== 修改 ========== */
    public function edit(){
        $id=input('id');
        if(!$id){
            return $this->error('非法访问','');
        }

        $inputs=input();
        $rules=[
            'report_at'=>'require',
            'total'=>'require|min:0',
            'ids'=>'require',
        ];
        $msg=[
            'report_at.require'=>'请输入报告时间',
            'total.require'=>'请输入评估总额',
            'total.min'=>'评估总额不能少于0',
            'ids.require'=>'请评估师',
        ];

        $result=$this->validate($inputs,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        Db::startTrans();
        try{
            $model=Assessassetss::withTrashed()->find($id);
            if(!$model){
                throw new \Exception('数据异常',404404);
            }
            $other_datas=$model->other_data($inputs);
            $datas=array_merge(input(),$other_datas);

            $model->isUpdate(true)->allowField(['report_at','method','total','picture','updated_at'])->save($datas,['id'=>$id]);

            $valuer_data=[];
            foreach ($inputs['ids'] as $valuer_id){
                $valuer_data[]=[
                    'item_id'=>$model->item_id,
                    'collection_id'=>$model->collection_id,
                    'assess_id'=>$model->assess_id,
                    'assets_id'=>$id,
                    'company_id'=>session('company.company_id'),
                    'valuer_id'=>$valuer_id,
                ];
            }
            Assessassetsvaluers::withTrashed()->where('assets_id',$id)->delete(true);
            $valuer_model=new Assessassetsvaluers();
            $valuer_model->saveAll($valuer_data);

            $res=true;
            $msg='保存成功';
            Db::commit();
        }catch (\Exception $exception){
            $res=false;
            if($exception->getCode()==404404){
                $msg=$exception->getMessage();
            }else{
                $msg='保存失败';
            }
            Db::rollback();
        }

        if($res){
            return $this->success($msg,'');
        }else{
            return $this->error($msg,'');
        }
    }


    /* ========== 删除 ========== */
    public function delete(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res=Assessassetss::destroy($ids);
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

        $res=Db::table('assess_assets')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        Db::startTrans();
        try{
            Assessassetss::onlyTrashed()->whereIn('id',$ids)->delete(true);
            Assessassetsvaluers::whereIn('assets_id',$ids)->delete(true);

            $res=true;
            $msg='操作成功';
            Db::commit();
        }catch (\Exception $exception){
            $res=false;
            $msg='操作失败';
            Db::rollback();
        }

        if($res){
            return $this->success($msg,'');
        }else{
            return $this->error($msg);
        }
    }
}