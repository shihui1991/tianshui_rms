<?php
/* |------------------------------------------------------
 * | 兑付 产权人或承租人
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 详情
 * | 修改
 * */
namespace app\system\controller;

use app\system\model\Items;
use app\system\model\Payholders;
use think\Db;


class Payholder extends Auth
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
        $datas['pay_id']=$pay_id;

        $where['pay_id']=$pay_id;
        $field=['ph.*','ch.name','ch.address','ch.phone'];
        /* ++++++++++ 查询 ++++++++++ */
        $payholder_model=new Payholders();
        $payholders=$payholder_model
            ->alias('ph')
            ->field($field)
            ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
            ->where($where)
            ->order('ph.portion desc')
            ->select();

        $datas['payholders']=$payholders;
        
        $this->assign($datas);

        return view($this->theme.'/payholder/index');
    }


    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Payholders::withTrashed()
            ->alias('ph')
            ->field(['ph.*','p.estate_amount as estate_total','p.assets_amount as assets_total','p.subject_amount as subject_total','p.object_amount as object_total','c.type','ch.name','ch.address','ch.phone'])
            ->join('pay p','p.id=ph.pay_id','left')
            ->join('collection c','c.id=ph.collection_id','left')
            ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
            ->where('ph.id',$id)
            ->find();
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Payholders();

        return view($this->theme.'/payholder/modify',[
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
            'portion'=>'between:0,100',
        ];
        $msg=[
            'portion.between'=>'补偿份额在0-100之间',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $model=new Payholders();
        Db::startTrans();
        try{
            $payholder_model=Payholders::alias('ph')
                ->field(['ph.*','p.estate_amount as estate_total','p.assets_amount as assets_total','p.subject_amount as subject_total','p.object_amount as object_total','c.type'])
                ->join('pay p','p.id=ph.pay_id','left')
                ->join('collection c','c.id=ph.collection_id','left')
                ->where('ph.id',$id)
                ->find();

            $portion=Payholders::where('pay_id',$payholder_model->pay_id)->where('id','neq',$id)->sum('portion');
            if((input('portion')+$portion)>100){
                return $this->error('补偿份额总和不能超过100%');
            }

            $payholder_model->portion=input('portion');
            $payholder_model->estate_amount=input('portion')/100*$payholder_model->estate_total;
            if($payholder_model->getData('type')){
                if($payholder_model->getData('holder')==1){
                    $payholder_model->assets_amount=0;
                    $payholder_model->subject_amount=0;
                    $payholder_model->object_amount=0;
                    $payholder_model->total_amount=$payholder_model->estate_amount;
                }else{
                    $payholder_model->assets_amount=$payholder_model->assets_total;
                    $payholder_model->subject_amount=$payholder_model->subject_total;
                    $payholder_model->object_amount=$payholder_model->object_total;
                    $payholder_model->total_amount=($payholder_model->estate_amount+$payholder_model->assets_amount+$payholder_model->subject_amount+$payholder_model->object_amount);
                }
            }else{
                $payholder_model->assets_amount=input('portion')/100*$payholder_model->assets_total;
                $payholder_model->public_avg=input('portion')/100*$payholder_model->public_amount/$payholder_model->public_num;
                $payholder_model->subject_amount=input('portion')/100*$payholder_model->subject_total;
                $payholder_model->object_amount=input('portion')/100*$payholder_model->object_total;
                $payholder_model->total_amount=($payholder_model->estate_amount+$payholder_model->assets_amount+$payholder_model->public_avg+$payholder_model->subject_amount+$payholder_model->object_amount);
            }

            $payholder_model->save();
            $res=true;
            Db::commit();
        }catch (\Exception $exception){
            $res=false;
            Db::commit();
        }


        if($res){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

}
