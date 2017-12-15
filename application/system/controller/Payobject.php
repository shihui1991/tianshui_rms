<?php
/* |------------------------------------------------------
 * | 兑付 其他补偿事项
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 批量修改
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;

use app\system\model\Items;
use app\system\model\Payholders;
use app\system\model\Pays;
use app\system\model\Payobjects;
use think\Db;

class Payobject extends Auth
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
        $where['pay_object.pay_id']=$pay_id;
        $field=['po.*','co.object_id','o.name'];

        $payobjects=Payobjects::withTrashed()
            ->alias('po')
            ->field($field)
            ->join('collection_object co','co.id=po.collection_object_id','left')
            ->join('object o','o.id=co.object_id','left')
            ->where($where)
            ->select();

        $datas['payobjects']=$payobjects;

        $this->assign($datas);

        return view();
    }

    /* ========== 批量修改 ========== */
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



        $pay_id=input('pay_id');
        if(!$pay_id){
            return $this->error('错误操作');
        }
        $inputs=input();
        if(!isset($inputs['datas'])){
            return $this->error('没有数据！');
        }
        $values=[];
        foreach ($inputs['datas'] as $id=>$data){
            if($data['price']<0){
                return $this->error('补偿单价不低于0');
                break;
            }
            if($data['number']<0){
                return $this->error('数量不低于0');
                break;
            }
            $values[]=[
                'id'=>$id,
                'price'=>$data['price'],
                'number'=>$data['number'],
                'amount'=>$data['price']*$data['number'],
                'updated_at'=>time()
            ];
        }
        $sqls=batch_update_sql('pay_object',['id','price','number','amount','updated_at'],$values,['price','number','amount','updated_at'],'id');
        if(!$sqls){
            return $this->error('数据错误，请关闭重试');
        }

        $model=new Payobjects();
        Db::startTrans();
        try{
            /* ********** 更新补偿事项 ********** */
            foreach ($sqls as $sql){
                db()->execute($sql);
            }
            /* ********** 获取兑付基本数据 ********** */
            $pay_info=Pays::withTrashed()
                ->alias('p')
                ->field(['p.id','p.item_id','p.community_id','p.collection_id','p.estate_amount','p.assets_amount','p.public_avg','p.subject_amount','p.total','c.type'])
                ->join('collection c','c.id=p.item_id','left')
                ->where('p.id',$pay_id)
                ->find();

            /* ********** 补偿事项总和 ********** */
            $object_amount=Payobjects::where('pay_id',$pay_info->id)->sum('amount');
            $pay_data['object_amount']=$object_amount;
            $pay_data['total']=$pay_info->estate_amount+$pay_info->assets_amount+$pay_info->public_avg+$pay_info->subject_amount+$object_amount;
            /* ********** 兑付 更新 ********** */
            $pay_model=new Pays();
            $pay_model->save($pay_data,['id'=>$pay_info->id]);
            /* ********** 兑付 产权人或承租人 ********** */
            $payholders=Payholders::field(['id','holder','portion','estate_amount','assets_amount','public_avg','subject_amount','total_amount'])->where('pay_id',$pay_info->id)->select();
            $i=0;
            $pay_holder_data=[];
            foreach ($payholders as $payholder){
                if($pay_info->getData('type')){
                    if($payholder->getData('holder')==1){
                        $pay_holder_data[$i]['object_amount']=0;
                    }else{
                        $pay_holder_data[$i]['object_amount']=$object_amount;
                    }
                }else{
                    $pay_holder_data[$i]['object_amount']=$payholder->portion/100*$object_amount;
                }
                $pay_holder_data[$i]['id']=$payholder->id;
                $pay_holder_data[$i]['total_amount']=($payholder->estate_amount+$payholder->assets_amount+$payholder->public_avg+$payholder->subject_amount+$pay_holder_data[$i]['object_amount']);
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

        $model=new Payobjects();
        Db::startTrans();
        try{
            /* ********** 删除补偿事项 ********** */
            Payobjects::destroy($ids);

            /* ********** 获取兑付基本数据 ********** */
            $pay_info=Payobjects::withTrashed()
                ->alias('po')
                ->field(['po.pay_id','p.id','p.item_id','p.community_id','p.collection_id','p.estate_amount','p.assets_amount','p.public_avg','p.subject_amount','p.total','c.type'])
                ->join('pay p','p.id=po.pay_id','left')
                ->join('collection c','c.id=p.item_id','left')
                ->where('po.id','in',$ids)
                ->find();

            /* ********** 补偿事项总和 ********** */
            $object_amount=Payobjects::where('pay_id',$pay_info->id)->sum('amount');
            $pay_data['object_amount']=$object_amount;
            $pay_data['total']=$pay_info->estate_amount+$pay_info->assets_amount+$pay_info->public_avg+$pay_info->subject_amount+$object_amount;
            /* ********** 兑付 更新 ********** */
            $pay_model=new Pays();
            $pay_model->save($pay_data,['id'=>$pay_info->id]);
            /* ********** 兑付 产权人或承租人 ********** */
            $payholders=Payholders::field(['id','holder','portion','estate_amount','assets_amount','public_avg','subject_amount','total_amount'])->where('pay_id',$pay_info->id)->select();
            $i=0;
            $pay_holder_data=[];
            foreach ($payholders as $payholder){
                if($pay_info->getData('type')){
                    if($payholder->getData('holder')==1){
                        $pay_holder_data[$i]['object_amount']=0;
                    }else{
                        $pay_holder_data[$i]['object_amount']=$object_amount;
                    }
                }else{
                    $pay_holder_data[$i]['object_amount']=$payholder->portion/100*$object_amount;
                }
                $pay_holder_data[$i]['id']=$payholder->id;
                $pay_holder_data[$i]['total_amount']=($payholder->estate_amount+$payholder->assets_amount+$payholder->public_avg+$payholder->subject_amount+$pay_holder_data[$i]['object_amount']);
                $i++;
            }
            $payholder_model=new Payholders();
            $payholder_model->isUpdate(true)->saveAll($pay_holder_data);
            $res=true;
            Db::commit();
        }catch (\Exception $exception){
            $res=false;dump($exception);
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

        $model=new Payobjects();
        Db::startTrans();
        try{
            /* ********** 恢复补偿事项 ********** */
            Db::table('pay_object')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);

            /* ********** 获取兑付基本数据 ********** */
            $pay_info=Payobjects::withTrashed()
                ->alias('po')
                ->field(['po.pay_id','p.id','p.item_id','p.community_id','p.collection_id','p.estate_amount','p.assets_amount','p.public_avg','p.subject_amount','p.total','c.type'])
                ->join('pay p','p.id=po.pay_id','left')
                ->join('collection c','c.id=p.item_id','left')
                ->where('po.id','in',$ids)
                ->find();

            /* ********** 补偿事项总和 ********** */
            $object_amount=Payobjects::where('pay_id',$pay_info->id)->sum('amount');
            $pay_data['object_amount']=$object_amount;
            $pay_data['total']=$pay_info->estate_amount+$pay_info->assets_amount+$pay_info->public_avg+$pay_info->subject_amount+$object_amount;
            /* ********** 兑付 更新 ********** */
            $pay_model=new Pays();
            $pay_model->save($pay_data,['id'=>$pay_info->id]);
            /* ********** 兑付 产权人或承租人 ********** */
            $payholders=Payholders::field(['id','holder','portion','estate_amount','assets_amount','public_avg','subject_amount','total_amount'])->where('pay_id',$pay_info->id)->select();
            $i=0;
            $pay_holder_data=[];
            foreach ($payholders as $payholder){
                if($pay_info->getData('type')){
                    if($payholder->getData('holder')==1){
                        $pay_holder_data[$i]['object_amount']=0;
                    }else{
                        $pay_holder_data[$i]['object_amount']=$object_amount;
                    }
                }else{
                    $pay_holder_data[$i]['object_amount']=$payholder->portion/100*$object_amount;
                }
                $pay_holder_data[$i]['id']=$payholder->id;
                $pay_holder_data[$i]['total_amount']=($payholder->estate_amount+$payholder->assets_amount+$payholder->public_avg+$payholder->subject_amount+$pay_holder_data[$i]['object_amount']);
                $i++;
            }
            $payholder_model=new Payholders();
            $payholder_model->isUpdate(true)->saveAll($pay_holder_data);
            $res=true;
            Db::commit();
        }catch (\Exception $exception){
            $res=false;dump($exception);
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
        $res=Payobjects::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
