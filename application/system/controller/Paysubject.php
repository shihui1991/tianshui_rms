<?php
/* |------------------------------------------------------
 * | 兑付 重要补偿科目
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

use app\system\model\Items;
use app\system\model\Itemsubjects;
use app\system\model\Payholders;
use app\system\model\Pays;
use app\system\model\Paysubjects;
use think\Db;

class Paysubject extends Auth
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
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
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
        $datas['item_info']=$item_info;


        $pay_id=input('pay_id');
        if(!$pay_id){
            return $this->error('错误操作');
        }
        /* ********** 查询条件 ********** */
        $datas['pay_id']=$pay_id;
        $where['pay_subject.pay_id']=$pay_id;
        $field=['ps.*','is.subject_id','s.name','s.num_from'];

        $paysubjects=Paysubjects::withTrashed()
            ->alias('ps')
            ->field($field)
            ->join('item_subject is','is.id=ps.item_subject_id','left')
            ->join('subject s','s.id=is.subject_id','left')
            ->where($where)
            ->select();

        $datas['paysubjects']=$paysubjects;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
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


        $model=new Paysubjects();
        if(request()->isPost()){
            $rules=[
                'pay_id'=>'require',
                'item_subject_id'=>'require|unique:pay_subject,pay_id='.input('pay_id').'&item_subject_id='.input('item_subject_id'),
                'number'=>'require|min:0',
                'price'=>'require|min:0',
                'unit'=>'require',
                'times'=>'require|min:0',
            ];
            $msg=[
                'pay_id.require'=>'错误操作，请关闭重试',
                'item_subject_id.require'=>'请选择科目',
                'item_subject_id.unique'=>'科目已存在',
                'number.require'=>'数量不能为空',
                'number.min'=>'数量不能低于0',
                'price.require'=>'补偿单价不能为空',
                'price.min'=>'补偿单价不能低于0',
                'unit.require'=>'数量单位不能为空',
                'times.require'=>'补偿次数不能为空',
                'times.min'=>'补偿次数不能低于0',
            ];
            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            Db::startTrans();
            try{
                $other_datas=$model->other_data(input());
                $datas=array_merge(input(),$other_datas);

                /* ********** 获取兑付基本数据 ********** */
                $pay_info=Pays::withTrashed()
                    ->alias('p')
                    ->field(['p.id','p.item_id','p.community_id','p.collection_id','p.estate_amount','p.assets_amount','p.public_avg','p.object_amount','p.total','c.type'])
                    ->join('collection c','c.id=p.item_id','left')
                    ->where('p.id',input('pay_id'))
                    ->find();
                $datas['item_id']=$pay_info->item_id;
                $datas['community_id']=$pay_info->community_id;
                $datas['collection_id']=$pay_info->collection_id;
                /* ********** 添加科目 ********** */
                $model->save($datas);
                /* ********** 科目总和 ********** */
                $subject_amount=Paysubjects::where('pay_id',$pay_info->id)->sum('amount');
                $pay_data['subject_amount']=$subject_amount;
                $pay_data['total']=$pay_info->estate_amount+$pay_info->assets_amount+$pay_info->public_avg+$subject_amount+$pay_info->object_amount;
                /* ********** 兑付 更新 ********** */
                $pay_model=new Pays();
                $pay_model->save($pay_data,['id'=>$pay_info->id]);
                /* ********** 兑付 产权人或承租人 ********** */
                $payholders=Payholders::field(['id','holder','portion','estate_amount','assets_amount','public_avg','object_amount','total_amount'])->where('pay_id',$pay_info->id)->select();
                $i=0;
                $pay_holder_data=[];
                foreach ($payholders as $payholder){
                    $pay_holder_data[$i]['id']=$payholder->id;
                    if($pay_info->getData('type')){
                        if($payholder->getData('holder')==1){
                            $pay_holder_data[$i]['subject_amount']=0;
                        }else{
                            $pay_holder_data[$i]['subject_amount']=$subject_amount;
                        }
                    }else{
                        $pay_holder_data[$i]['subject_amount']=$payholder->portion/100*$subject_amount;
                    }
                    $pay_holder_data[$i]['total_amount']=($payholder->estate_amount+$payholder->assets_amount+$payholder->public_avg+$pay_holder_data[$i]['subject_amount']+$payholder->object_amount);
                    $i++;
                }
                $payholder_model=new Payholders();
                $payholder_model->isUpdate(true)->saveAll($pay_holder_data);
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
            $pay_id=input('pay_id');
            if(!$pay_id){
                return $this->error('错误操作，请关闭重试');
            }
            /* ********** 查询项目ID ********** */
            $item_id=Pays::where('id',$pay_id)->value('item_id');
            if(!$item_id){
                return $this->error('错误操作，请关闭重试');
            }
            /* ********** 获取已有科目 ********** */
            $subject_ids=Paysubjects::where('pay_id',$pay_id)->column('item_subject_id');
            $itemsubject_model=new Itemsubjects();
            if($subject_ids){
                $itemsubject_model=$itemsubject_model->where('item_subject.id','not in',$subject_ids);
            }
            $itemsubjects=$itemsubject_model->alias('is')
                ->field(['is.*','s.name','s.num_from','s.unit','s.infos'])
                ->join('subject s','s.id=is.subject_id','left')
                ->where('item_id',$item_id)
                ->select();

            return view('add',[
                'model'=>$model,
                'pay_id'=>$pay_id,
                'item_info'=>$item_info,
                'itemsubjects'=>$itemsubjects,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }

        $infos=Paysubjects::withTrashed()
            ->alias('ps')
            ->field(['ps.*','is.subject_id','s.name','s.num_from','s.infos'])
            ->join('item_subject is','is.id=ps.item_subject_id','left')
            ->join('subject s','s.id=is.subject_id','left')
            ->where('ps.id',$id)
            ->find();

        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Paysubjects();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
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
            return $this->error('错误操作');
        }
        $datas=input();
        $rules=[
            'number'=>'require|min:0',
            'price'=>'require|min:0',
            'times'=>'require|min:0',
        ];
        $msg=[
            'number.require'=>'数量不能为空',
            'number.min'=>'数量不能低于0',
            'price.require'=>'补偿单价不能为空',
            'price.min'=>'补偿单价不能低于0',
            'times.require'=>'补偿次数不能为空',
            'times.min'=>'补偿次数不能低于0',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }
        $model=new Paysubjects();
        Db::startTrans();
        try{
            $paysubject_model=$model;
            $other_datas=$paysubject_model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            /* ********** 更新科目 ********** */
            $paysubject_model->isUpdate(true)->save($datas);

            /* ********** 获取兑付基本数据 ********** */
            $pay_info=Paysubjects::withTrashed()
                ->alias('ps')
                ->field(['ps.pay_id','p.id','p.item_id','p.community_id','p.collection_id','p.estate_amount','p.assets_amount','p.public_avg','p.object_amount','p.total','c.type'])
                ->join('pay p','p.id=ps.pay_id','left')
                ->join('collection c','c.id=p.item_id','left')
                ->where('ps.id',$id)
                ->find();

            /* ********** 科目总和 ********** */
            $subject_amount=Paysubjects::where('pay_id',$pay_info->id)->sum('amount');
            $pay_data['subject_amount']=$subject_amount;
            $pay_data['total']=$pay_info->estate_amount+$pay_info->assets_amount+$pay_info->public_avg+$subject_amount+$pay_info->object_amount;
            /* ********** 兑付 更新 ********** */
            $pay_model=new Pays();
            $pay_model->save($pay_data,['id'=>$pay_info->id]);
            /* ********** 兑付 产权人或承租人 ********** */
            $payholders=Payholders::field(['id','holder','portion','estate_amount','assets_amount','public_avg','object_amount','total_amount'])->where('pay_id',$pay_info->id)->select();
            $i=0;
            $pay_holder_data=[];
            foreach ($payholders as $payholder){
                $pay_holder_data[$i]['id']=$payholder->id;
                if($pay_info->getData('type')){
                    if($payholder->getData('holder')==1){
                        $pay_holder_data[$i]['subject_amount']=0;
                    }else{
                        $pay_holder_data[$i]['subject_amount']=$subject_amount;
                    }
                }else{
                    $pay_holder_data[$i]['subject_amount']=$payholder->portion/100*$subject_amount;
                }
                $pay_holder_data[$i]['id']=$payholder->id;
                $pay_holder_data[$i]['total_amount']=($payholder->estate_amount+$payholder->assets_amount+$payholder->public_avg+$pay_holder_data[$i]['subject_amount']+$payholder->object_amount);
                $i++;
            }
            $payholder_model=new Payholders();
            $payholder_model->isUpdate(true)->saveAll($pay_holder_data);
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
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
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

        $model=new Paysubjects();
        Db::startTrans();
        try{
            /* ********** 删除科目 ********** */
            Paysubjects::destroy(['id'=>['in',$ids]]);

            /* ********** 获取兑付基本数据 ********** */
            $pay_info=Paysubjects::withTrashed()
                ->alias('ps')
                ->field(['ps.pay_id','p.id','p.item_id','p.community_id','p.collection_id','p.estate_amount','p.assets_amount','p.public_avg','p.object_amount','p.total','c.type'])
                ->join('pay p','p.id=ps.pay_id','left')
                ->join('collection c','c.id=p.item_id','left')
                ->where('ps.id','in',$ids)
                ->find();

            /* ********** 科目总和 ********** */
            $subject_amount=Paysubjects::where('pay_id',$pay_info->id)->sum('amount');
            $pay_data['subject_amount']=$subject_amount;
            $pay_data['total']=$pay_info->estate_amount+$pay_info->assets_amount+$pay_info->public_avg+$subject_amount+$pay_info->object_amount;
            /* ********** 兑付 更新 ********** */
            $pay_model=new Pays();
            $pay_model->save($pay_data,['id'=>$pay_info->id]);
            /* ********** 兑付 产权人或承租人 ********** */
            $payholders=Payholders::field(['id','holder','portion','estate_amount','assets_amount','public_avg','object_amount','total_amount'])->where('pay_id',$pay_info->id)->select();
            $i=0;
            $pay_holder_data=[];
            foreach ($payholders as $payholder){
                $pay_holder_data[$i]['id']=$payholder->id;
                if($pay_info->getData('type')){
                    if($payholder->getData('holder')==1){
                        $pay_holder_data[$i]['subject_amount']=0;
                    }else{
                        $pay_holder_data[$i]['subject_amount']=$subject_amount;
                    }
                }else{
                    $pay_holder_data[$i]['subject_amount']=$payholder->portion/100*$subject_amount;
                }
                $pay_holder_data[$i]['id']=$payholder->id;
                $pay_holder_data[$i]['total_amount']=($payholder->estate_amount+$payholder->assets_amount+$payholder->public_avg+$pay_holder_data[$i]['subject_amount']+$payholder->object_amount);
                $i++;
            }
            $payholder_model=new Payholders();
            $payholder_model->isUpdate(true)->saveAll($pay_holder_data);
            $res=true;
            Db::commit();
        }catch (\Exception $exception){
            $res=false;
            Db::rollback();
        }

        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }

    /* ========== 恢复 ========== */
    public function restore(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
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

        $model=new Paysubjects();
        Db::startTrans();
        try{
            /* ********** 恢复科目 ********** */
            Db::table('pay_subject')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);

            /* ********** 获取兑付基本数据 ********** */
            $pay_info=Paysubjects::withTrashed()
                ->alias('ps')
                ->field(['ps.pay_id','p.id','p.item_id','p.community_id','p.collection_id','p.estate_amount','p.assets_amount','p.public_avg','p.object_amount','p.total','c.type'])
                ->join('pay p','p.id=ps.pay_id','left')
                ->join('collection c','c.id=p.item_id','left')
                ->where('ps.id','in',$ids)
                ->find();

            /* ********** 科目总和 ********** */
            $subject_amount=Paysubjects::where('pay_id',$pay_info->id)->sum('amount');
            $pay_data['subject_amount']=$subject_amount;
            $pay_data['total']=$pay_info->estate_amount+$pay_info->assets_amount+$pay_info->public_avg+$subject_amount+$pay_info->object_amount;
            /* ********** 兑付 更新 ********** */
            $pay_model=new Pays();
            $pay_model->save($pay_data,['id'=>$pay_info->id]);
            /* ********** 兑付 产权人或承租人 ********** */
            $payholders=Payholders::field(['id','holder','portion','estate_amount','assets_amount','public_avg','object_amount','total_amount'])->where('pay_id',$pay_info->id)->select();
            $i=0;
            $pay_holder_data=[];
            foreach ($payholders as $payholder){
                $pay_holder_data[$i]['id']=$payholder->id;
                if($pay_info->getData('type')){
                    if($payholder->getData('holder')==1){
                        $pay_holder_data[$i]['subject_amount']=0;
                    }else{
                        $pay_holder_data[$i]['subject_amount']=$subject_amount;
                    }
                }else{
                    $pay_holder_data[$i]['subject_amount']=$payholder->portion/100*$subject_amount;
                }
                $pay_holder_data[$i]['id']=$payholder->id;
                $pay_holder_data[$i]['total_amount']=($payholder->estate_amount+$payholder->assets_amount+$payholder->public_avg+$pay_holder_data[$i]['subject_amount']+$payholder->object_amount);
                $i++;
            }
            $payholder_model=new Payholders();
            $payholder_model->isUpdate(true)->saveAll($pay_holder_data);
            $res=true;
            Db::commit();
        }catch (\Exception $exception){
            $res=false;
            Db::rollback();
        }

        if($res){
            return $this->success('恢复成功','');
        }else{
            return $this->error('恢复失败');
        }
    }

    /* ========== 销毁 ========== */
    public function destroy(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
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
        $res=Paysubjects::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
