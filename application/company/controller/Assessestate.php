<?php
/* |------------------------------------------------------
 * | 房产评估
 * |------------------------------------------------------
 * */

namespace app\company\controller;


use app\system\model\Assessestatebuildings;
use app\system\model\Assessestates;
use app\system\model\Assessestatevaluers;
use app\system\model\Assesss;
use app\system\model\Collectionbuildings;
use app\system\model\Collections;
use app\system\model\Items;
use think\Db;

class Assessestate extends Base
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

        $estates=Assessestates::withTrashed()->with('company')->where($where)->select();

        $datas['estates']=$estates;

        $this->assign($datas);

        return view();
    }


    /* ========== 添加 ========== */
    public function add(){
        $item_id = input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目信息 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if(!$item_info){
            return $this->error('选择项目不存在');
        }
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
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


        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问');
        }
        if(request()->isPost()){
            $inputs=input();
            $rules=[
                'report_at'=>'require',
                'price'=>'require',
                'ids'=>'require',
            ];
            $msg=[
                'report_at.require'=>'请输入报告时间',
                'price.require'=>'建筑数据不能为空',
                'ids.require'=>'请评估师',
            ];

            $result=$this->validate($inputs,$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $prices=$inputs['price'];
            $values=array_filter(array_values($inputs['price']));
            if(empty($values)){
                return $this->error('请输入评估单价','');
            }

            Db::startTrans();
            try{
                $collection_info=Collections::field(['id','item_id','community_id','status'])->where('id',$collection_id)->find();
                if(!$collection_info){
                    throw new \Exception('数据异常，请关闭后重试',404404);
                }


                $building_ids=array_keys($prices);
                $building_areas=Collectionbuildings::whereIn('id',$building_ids)->column('real_num','id');
                if(!$building_areas){
                    throw new \Exception('数据异常，请关闭后重试',404404);
                }

                $assess=Assesss::where(['item_id'=>$item_id,'collection_id'=>$collection_id])->find();
                if(!$assess){
                    $assess=Assesss::create([
                        'item_id'=>$item_id,
                        'community_id'=>$collection_info->community_id,
                        'collection_id'=>$collection_id,
                        'estate'=>0,
                        'assets'=>0,
                    ]);
                }

                $model=new Assessestates();

                $other_datas=$model->other_data($inputs);
                $datas=array_merge(input(),$other_datas);

                $datas['status']=0;
                $datas['total']=0;
                $datas['community_id']=$collection_info->community_id;
                $datas['assess_id']=$assess->id;
                $datas['company_id']=session('company.company_id');

                $model->save($datas);

                $building_data=[];
                $total=0;
                foreach ($building_areas as $building_id => $area){
                    $building_data[]=[
                        'item_id'=>$item_id,
                        'community_id'=>$collection_info->community_id,
                        'collection_id'=>$collection_id,
                        'assess_id'=>$assess->id,
                        'estate_id'=>$model->id,
                        'building_id'=>$building_id,
                        'price'=>$prices[$building_id],
                        'amount'=>$prices[$building_id]*$area,
                    ];
                    $total +=$prices[$building_id]*$area;
                }
                $building_model=new Assessestatebuildings();
                $building_model->saveAll($building_data);

                $model->total=$total;
                $model->save();


                $valuer_data=[];
                foreach ($inputs['ids'] as $valuer_id){
                    $valuer_data[]=[
                        'item_id'=>$item_id,
                        'collection_id'=>$collection_id,
                        'assess_id'=>$assess->id,
                        'estate_id'=>$model->id,
                        'company_id'=>session('company.company_id'),
                        'valuer_id'=>$valuer_id,
                    ];
                }
                $valuer_model=new Assessestatevaluers();
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

            return view('add');
        }
    }


    /* ========== 详情 ========== */
    public function detail()
    {
        $id=input('id');
        if(!$id){
            return $this->error('非法访问','');
        }
        $model=new Assessestates();
        $datas['model']=$model;

        $infos=Assessestates::withTrashed()->find($id);
        $datas['infos']=$infos;

        $buildings=Assessestatebuildings::with(['collectionbuilding'=>['realuse','buildingstruct','buildingstatus']])->where(['estate_id'=>$id])->select();
        $datas['buildings']=$buildings;

        $valuers=Assessestatevaluers::with('valuer')->where(['estate_id'=>$id])->select();
        $datas['valuers']=$valuers;
        $datas['valuer_ids']=[];

        $this->assign($datas);

        return view('modify');
    }

    /* ========== 修改 ========== */
    public function edit(){
        $item_id = input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目信息 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if(!$item_info){
            return $this->error('选择项目不存在');
        }
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
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


        $id=input('id');
        if(!$id){
            return $this->error('非法访问','');
        }

        $inputs=input();
        $rules=[
            'report_at'=>'require',
            'price'=>'require',
            'ids'=>'require',
        ];
        $msg=[
            'report_at.require'=>'请输入报告时间',
            'price.require'=>'建筑数据不能为空',
            'ids.require'=>'请评估师',
        ];

        $result=$this->validate($inputs,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $prices=$inputs['price'];
        $values=array_filter(array_values($inputs['price']));
        if(empty($values)){
            return $this->error('请输入评估单价','');
        }

        Db::startTrans();
        try{
            $building_ids=array_keys($prices);
            $building_areas=Collectionbuildings::whereIn('id',$building_ids)->column('real_num','id');
            if(!$building_areas){
                throw new \Exception('数据异常，请关闭后重试',404404);
            }

            $model=Assessestates::withTrashed()->find($id);
            if(!$model){
                throw new \Exception('数据异常，请关闭后重试',404404);
            }

            $building_data=[];
            $total=0;
            foreach ($building_areas as $building_id => $area){
                $building_data[]=[
                    'estate_id'=>$model->id,
                    'building_id'=>$building_id,
                    'price'=>$prices[$building_id],
                    'amount'=>$prices[$building_id]*$area,
                    'updated_at'=>time()
                ];
                $total +=$prices[$building_id]*$area;
            }

            $sqls=batch_update_sql('assess_estate_building',['estate_id','building_id','price','amount','updated_at'],$building_data,['price','amount','updated_at'],['estate_id','building_id']);
            if(!$sqls){
                throw new \Exception('数据异常',404404);
            }
            foreach ($sqls as $sql){
                db()->execute($sql);
            }

            $other_datas=$model->other_data($inputs);
            $datas=array_merge(input(),$other_datas);
            $datas['total']=$total;
            $model->isUpdate(true)->allowField(['report_at','method','total','picture','updated_at'])->save($datas,['id'=>$id]);

            if($model->getData('status')){
                Assesss::where(['id'=>$model->assess_id])->update(['estate'=>$total]);
            }

            $valuer_data=[];
            foreach ($inputs['ids'] as $valuer_id){
                $valuer_data[]=[
                    'item_id'=>$model->item_id,
                    'collection_id'=>$model->collection_id,
                    'assess_id'=>$model->assess_id,
                    'estate_id'=>$id,
                    'company_id'=>session('company.company_id'),
                    'valuer_id'=>$valuer_id,
                ];
            }
            Assessestatevaluers::withTrashed()->where('estate_id',$id)->delete(true);
            $valuer_model=new Assessestatevaluers();
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
        $item_id = input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目信息 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if(!$item_info){
            return $this->error('选择项目不存在');
        }
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
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


        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res=Assessestates::where('status',0)->whereIn('id',$ids)->update(['deleted_at'=>time(),'updated_at'=>time()]);
        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }

    /* ========== 恢复 ========== */
    public function restore(){
        $item_id = input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目信息 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if(!$item_info){
            return $this->error('选择项目不存在');
        }
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
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


        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }

        $res=Db::table('assess_estate')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
        if($res){
            return $this->success('恢复成功','');
        }else{
            return $this->error('恢复失败');
        }
    }

    /* ========== 销毁 ========== */
    public function destroy(){
        $item_id = input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目信息 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if(!$item_info){
            return $this->error('选择项目不存在');
        }
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
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


        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        Db::startTrans();
        try{
            Assessestates::onlyTrashed()->whereIn('id',$ids)->delete(true);
            Assessestatevaluers::whereIn('estate_id',$ids)->delete(true);
            Collectionbuildings::whereIn('estate_id',$ids)->delete(true);

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