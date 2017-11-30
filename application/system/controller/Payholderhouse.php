<?php
/* |------------------------------------------------------
 * | 兑付-安置房
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 排序
 * | 删除
 * */

namespace app\system\controller;

use app\system\model\Collectionholderhouses;
use app\system\model\Houseprices;
use app\system\model\Itemhouseups;
use app\system\model\Items;
use app\system\model\Payholderhouses;
use app\system\model\Payholderhouseups;
use app\system\model\Payholders;
use app\system\model\Pays;

class Payholderhouse extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
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
            return $this->error('错误操作，请关闭后重试');
        }
        $datas['pay_id']=$pay_id;
        /* ++++++++++ 兑付汇总 ++++++++++ */
        $pay_info=Pays::alias('p')
            ->field(['p.id','p.item_id','p.community_id','p.collection_id','p.assess_id','p.compensate_way','p.pay_way','c.type'])
            ->join('collection c','c.id=p.collection_id','left')
            ->where('p.id',$pay_id)
            ->find();

        if(!$pay_info){
            return $this->error('兑付数据不存在');
        }
        $datas['pay_info']=$pay_info;
        /* ++++++++++ 兑付-产权人或承租人 ++++++++++ */
        $payholders=Payholders::alias('ph')
            ->field(['ph.id','ph.item_id','ph.community_id','ph.collection_id','ph.pay_id','ph.collection_holder_id','ph.holder','ph.portion','ph.total_amount','ph.house_amount','ch.name'])
            ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
            ->where('ph.pay_id',$pay_id)
            ->order('ph.portion desc')
            ->select();

        $datas['payholders']=$payholders;

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


        if(request()->isPost()){
            $rules=[
                'pay_id'=>'require',
                'pay_holder_id'=>'require',
                'house_id'=>'require|unique:pay_holder_house,house_id='.input('house_id').'&pay_id='.input('pay_id'),
                'sort'=>'require|integer',
            ];
            $msg=[
                'pay_id.require'=>'数据错误，请关闭后重试',
                'pay_holder_id.require'=>'请选择被征收人',
                'house_id.require'=>'请选择安置房',
                'house_id.unique'=>'安置房已选择',
                'sort.require'=>'输入选择排序',
                'sort.integer'=>'选择排序必须是整数',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $model=new Payholderhouses();
            $model->startTrans();
            try{
                /* ++++++++++ 兑付汇总 ++++++++++ */
                $pay_info=Pays::alias('p')
                    ->field(['p.id','p.item_id','p.community_id','p.collection_id','p.assess_id','p.total','p.compensate_way','p.pay_way','c.type','i.created_at as item_time'])
                    ->join('collection c','c.id=p.collection_id','left')
                    ->join('item i','i.id=p.item_id','left')
                    ->where('p.id',input('pay_id'))
                    ->find();
                /* ++++++++++ 安置房单价 ++++++++++ */
                $houseprice=Houseprices::alias('hp')
                    ->field(['hp.*','h.area'])
                    ->join('house h','h.id=hp.house_id','left')
                    ->where([
                        'house_id'=>input('house_id'),
                        'start_at'=>['<=',$pay_info->item_time],
                        'end_at'=>['>=',$pay_info->item_time],
                    ])->find();
                /* ++++++++++ 保存安置房 ++++++++++ */
                $payholderhouse_model=$model;
                $datas['item_id']=$pay_info->item_id;
                $datas['community_id']=$pay_info->community_id;
                $datas['collection_id']=$pay_info->collection_id;
                $datas['pay_id']=$pay_info->id;
                $datas['pay_holder_id']=input('pay_holder_id');
                $datas['house_id']=input('house_id');
                $datas['sort']=input('sort');
                $datas['price']=$houseprice->price;
                $datas['area']=$houseprice->area;
                $datas['amount']=$houseprice->area*$houseprice->price;
                $datas['amount_up']=0;
                $datas['total']= $datas['amount']+$datas['amount_up'];
                $payholderhouse_model->save($datas);
                /* ++++++++++ 更新 兑付-产权人或承租人 ++++++++++ */
                $payholder_model=new Payholders();
                $payholder_model->save(['house_amount'=>$payholderhouse_model->total],['id'=>input('pay_holder_id')]);
                /* ++++++++++ 兑付-产权人或承租人 ++++++++++ */
                $payholders=Payholders::alias('ph')
                    ->field(['ph.id','ph.item_id','ph.community_id','ph.collection_id','ph.pay_id','ph.collection_holder_id','ph.holder','ph.portion','ph.total_amount','ph.house_amount','ch.name'])
                    ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
                    ->where('ph.pay_id',$pay_info->id)
                    ->order('ph.portion desc')
                    ->select();
                /* ++++++++++ 安置房价上浮设置 ++++++++++ */
                $itemhouseups=Itemhouseups::where('item_id',$pay_info->item_id)->order('up_start asc')->select();
                $last=$pay_info->total;
                /* ++++++++++ 兑付-产权人或承租人 ++++++++++ */
                foreach ($payholders as $payholder){
                    /* ++++++++++ 有选择安置房，公产承租人或私产产权人 ++++++++++ */
                    if($payholder->house_amount && (($pay_info->getData('type') && $payholder->getData('holder')==2) || (!$pay_info->getData('type')&& $payholder->getData('holder')==1))){
                        /* ++++++++++ 安置房 ++++++++++ */
                        $payholderhouses=Payholderhouses::alias('phh')
                            ->field(['phh.*','h.community_id as house_community_id','h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.status as house_status','hc.address','hc.name as hc_name','l.name as l_name'])
                            ->join('house h','h.id=phh.house_id','left')
                            ->join('house_community hc','hc.id=h.community_id','left')
                            ->join('layout l','l.id=h.layout_id','left')
                            ->where('phh.pay_holder_id',$payholder->id)
                            ->order('phh.sort asc')
                            ->select();

                        /* ++++++++++ 计算上浮 ++++++++++ */
                        if($pay_info->getData('type') || !$pay_info->getData('pay_way')){
                            /* ++++++++++ 公产或分权兑付，重置补偿款 ++++++++++ */
                            $last=$payholder->total_amount;
                        }

                        $house_amount=0;
                        if($payholderhouses) {
                            foreach ($payholderhouses as $payholderhouse) {
                                Payholderhouseups::destroy($payholderhouse->id, true);
                                /* ++++++++++ 安置房价值与补偿款差额 ++++++++++ */
                                $last = $last - $payholderhouse->amount;
                                /* ++++++++++ 安置房价值低于补偿款，无上浮 ++++++++++ */
                                if ($last >= 0) {
                                    $payholderhouse->amount_up = 0;
                                    $payholderhouse->total = $payholderhouse->amount;
                                    $payholderhouse->save();
                                } /* ++++++++++ 安置房价值高于补偿款，计算上浮 ++++++++++ */
                                else {
                                    /* ++++++++++ 剩余补偿款可换安置房面积 ++++++++++ */
                                    $default_area = ($last + $payholderhouse->amount) / $payholderhouse->price;
                                    /* ++++++++++ 上浮面积 ++++++++++ */
                                    $last_area = $payholderhouse->area - $default_area;
                                    $up_datas = [];
                                    $up_amount = 0;
                                    /* ++++++++++ 上浮计算 ++++++++++ */
                                    foreach ($itemhouseups as $itemhouseup) {
                                        /* ++++++++++ 在上浮区间 ++++++++++ */
                                        if ($itemhouseup->up_end && $itemhouseup->up_rate) {
                                            /* ++++++++++ 超出当前上浮区间 ++++++++++ */
                                            if ($last_area > $itemhouseup->up_end) {
                                                $up_datas[] = [
                                                    'pay_holder_house_id' => $payholderhouse->id,
                                                    'up_start' => $itemhouseup->up_start,
                                                    'up_end' => $itemhouseup->up_end,
                                                    'up_area' => ($itemhouseup->up_end - $itemhouseup->up_start),
                                                    'up_rate' => $itemhouseup->up_rate,
                                                    'price' => $payholderhouse->price,
                                                    'amount' => $payholderhouse->price * ($itemhouseup->up_rate/100) * ($itemhouseup->up_end - $itemhouseup->up_start),
                                                ];
                                                $up_amount += $payholderhouse->price * ($itemhouseup->up_rate/100) * ($itemhouseup->up_end - $itemhouseup->up_start);
                                            } /* ++++++++++ 在当前上浮区间 ++++++++++ */
                                            else {
                                                $up_datas[] = [
                                                    'pay_holder_house_id' => $payholderhouse->id,
                                                    'up_start' => $itemhouseup->up_start,
                                                    'up_end' => $itemhouseup->up_end,
                                                    'up_area' => ($last_area - $itemhouseup->up_start),
                                                    'up_rate' => $itemhouseup->up_rate,
                                                    'price' => $payholderhouse->price,
                                                    'amount' => $payholderhouse->price * ($itemhouseup->up_rate/100) * ($last_area - $itemhouseup->up_start),
                                                ];
                                                $up_amount += $payholderhouse->price * ($itemhouseup->up_rate/100) * ($last_area - $itemhouseup->up_start);
                                                break;
                                            }
                                        } /* ++++++++++ 超出上浮区间，按市场评估单价计算差额 ++++++++++ */
                                        else {
                                            /* ++++++++++ 安置房市场评估单价 ++++++++++ */
                                            $marketprice = Houseprices::where([
                                                'house_id' => $payholderhouse->house_id,
                                                'start_at' => ['<=', $pay_info->item_time],
                                                'end_at' => ['>=', $pay_info->item_time],
                                            ])->value('market_price');

                                            $up_datas[] = [
                                                'pay_holder_house_id' => $payholderhouse->id,
                                                'up_start' => $itemhouseup->up_start,
                                                'up_end' => $itemhouseup->up_end,
                                                'up_area' => ($last_area - $itemhouseup->up_start),
                                                'up_rate' => $itemhouseup->up_rate,
                                                'price' => ($marketprice - $payholderhouse->price),
                                                'amount' => ($marketprice - $payholderhouse->price) * ($last_area - $itemhouseup->up_start),
                                            ];
                                            $up_amount += ($marketprice - $payholderhouse->price) * ($last_area - $itemhouseup->up_start);
                                        }
                                    }
                                    $payholderhouseup_model = new Payholderhouseups();
                                    $payholderhouseup_model->saveAll($up_datas);
                                    $payholderhouse->amount_up = $up_amount;
                                    $payholderhouse->total = $payholderhouse->amount + $payholderhouse->amount_up;
                                }
                                $payholderhouse->save();
                                $house_amount += $payholderhouse->total;
                            }
                        }
                        $payholder->house_amount=$house_amount;
                        $payholder->save();
                    }
                    /* ++++++++++ 无选择安置房或公产产权人或私产承租人 ++++++++++ */
                    else{
                        $payholder->house_amount=0;
                        $payholder->save();
                        $payholderhouse_ids=Payholderhouses::where('pay_holder_id',$payholder->id)->column('id');
                        if($payholderhouse_ids){
                            Payholderhouseups::destroy($payholderhouse_ids,true);
                            Payholderhouses::where('id','in',$payholderhouse_ids)->delete();
                        }
                    }
                }
                $res=true;
                $model->commit();
            }catch (\Exception $exception){
                $res=false;
                $model->rollback();
            }

            if($res){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }
        else{
            $pay_id=input('pay_id');
            if(!$pay_id){
                return $this->error('错误操作，请关闭后重试');
            }
            /* ++++++++++ 兑付汇总 ++++++++++ */
            $pay_info=Pays::alias('p')
                ->field(['p.id','p.item_id','p.community_id','p.collection_id','p.assess_id','p.compensate_way','p.pay_way','c.type'])
                ->join('collection c','c.id=p.collection_id','left')
                ->where('p.id',$pay_id)
                ->find();

            if($pay_info->getData('compensate_way')==0){
                if(request()->isAjax()){
                    return $this->error('当前为货币补偿方式，不能选择安置房！');
                }else{
                    return '当前为货币补偿方式，不能选择安置房！';
                }
            }
            /* ++++++++++ 兑付-产权人或承租人 ++++++++++ */
            $payholders=Payholders::alias('ph')
                ->field(['ph.id','ph.item_id','ph.community_id','ph.collection_id','ph.pay_id','ph.collection_holder_id','ph.holder','ch.name'])
                ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
                ->where('ph.pay_id',$pay_id)
                ->order('ph.portion desc')
                ->select();
            /* ++++++++++ 兑付-当前已选安置房 ++++++++++ */
            $payhouse_ids=Payholderhouses::where('pay_id',$pay_id)->column('house_id');
            /* ++++++++++ 安置房选择 ++++++++++ */
            $where['chh.collection_id']=$pay_info->collection_id;
            if($payhouse_ids){
                $where['chh.house_id']=['not in',$payhouse_ids];
            }
            $houses=Collectionholderhouses::alias('chh')
                ->field(['chh.collection_id','chh.sort','chh.house_id','h.community_id as house_community_id','h.building','h.unit',
                    'h.floor','h.number','h.layout_id','h.area','h.status as house_status','hc.address','hc.name as hc_name','l.name as l_name'])
                ->join('house h','h.id=chh.house_id and h.status=0','left')
                ->join('house_community hc','hc.id=h.community_id','left')
                ->join('layout l','l.id=h.layout_id','left')
                ->where($where)
                ->order('chh.sort asc')
                ->select();

            return view('add',[
                'pay_id'=>$pay_id,
                'pay_info'=>$pay_info,
                'payholders'=>$payholders,
                'houses'=>$houses,
            ]);
        }
    }

    /* ========== 排序 ========== */
    public function sort(){
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




        $inputs=input();
        $sorts=isset($inputs['sort'])?$inputs['sort']:[];
        if(!$sorts){
            return $this->error('错误操作');
        }
        $sort_data=[];
        $time=time();
        foreach ($sorts as $id=>$sort){
            $sort_data[]=[
                'id'=>$id,
                'sort'=>(int)$sort,
                'updated_at'=>$time
            ];
        }
        $sqls=batch_update_sql('pay_holder_house',['id','sort','updated_at'],$sort_data,['sort','updated_at'],'id');
        $res=false;
        if($sqls){
            $model=new Payholderhouses();
            $model->startTrans();
            try{
                foreach ($sqls as $sql){
                    db()->execute($sql);
                }
                /* ++++++++++ 兑付汇总 ++++++++++ */
                $pay_info=Pays::alias('p')
                    ->field(['p.id','p.item_id','p.community_id','p.collection_id','p.assess_id','p.total','p.compensate_way','p.pay_way','c.type','i.created_at as item_time'])
                    ->join('collection c','c.id=p.collection_id','left')
                    ->join('item i','i.id=p.item_id','left')
                    ->where('p.id',input('pay_id'))
                    ->find();

                /* ++++++++++ 兑付-产权人或承租人 ++++++++++ */
                $payholders=Payholders::alias('ph')
                    ->field(['ph.id','ph.item_id','ph.community_id','ph.collection_id','ph.pay_id','ph.collection_holder_id','ph.holder','ph.portion','ph.total_amount','ph.house_amount','ch.name'])
                    ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
                    ->where('ph.pay_id',$pay_info->id)
                    ->order('ph.portion desc')
                    ->select();
                /* ++++++++++ 安置房价上浮设置 ++++++++++ */
                $itemhouseups=Itemhouseups::where('item_id',$pay_info->item_id)->order('up_start asc')->select();
                $last=$pay_info->total;
                /* ++++++++++ 兑付-产权人或承租人 ++++++++++ */
                foreach ($payholders as $payholder){
                    /* ++++++++++ 有选择安置房，公产承租人或私产产权人 ++++++++++ */
                    if($payholder->house_amount && (($pay_info->getData('type') && $payholder->getData('holder')==2) || (!$pay_info->getData('type')&& $payholder->getData('holder')==1))){
                        /* ++++++++++ 安置房 ++++++++++ */
                        $payholderhouses=Payholderhouses::alias('phh')
                            ->field(['phh.*','h.community_id as house_community_id','h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.status as house_status','hc.address','hc.name as hc_name','l.name as l_name'])
                            ->join('house h','h.id=phh.house_id','left')
                            ->join('house_community hc','hc.id=h.community_id','left')
                            ->join('layout l','l.id=h.layout_id','left')
                            ->where('phh.pay_holder_id',$payholder->id)
                            ->order('phh.sort asc')
                            ->select();

                        /* ++++++++++ 计算上浮 ++++++++++ */
                        if($pay_info->getData('type') || !$pay_info->getData('pay_way')){
                            /* ++++++++++ 公产或分权兑付，重置补偿款 ++++++++++ */
                            $last=$payholder->total_amount;
                        }

                        $house_amount=0;
                        if($payholderhouses) {
                            foreach ($payholderhouses as $payholderhouse) {
                                Payholderhouseups::destroy($payholderhouse->id, true);
                                /* ++++++++++ 安置房价值与补偿款差额 ++++++++++ */
                                $last = $last - $payholderhouse->amount;
                                /* ++++++++++ 安置房价值低于补偿款，无上浮 ++++++++++ */
                                if ($last >= 0) {
                                    $payholderhouse->amount_up = 0;
                                    $payholderhouse->total = $payholderhouse->amount;
                                    $payholderhouse->save();
                                } /* ++++++++++ 安置房价值高于补偿款，计算上浮 ++++++++++ */
                                else {
                                    /* ++++++++++ 剩余补偿款可换安置房面积 ++++++++++ */
                                    $default_area = ($last + $payholderhouse->amount) / $payholderhouse->price;
                                    /* ++++++++++ 上浮面积 ++++++++++ */
                                    $last_area = $payholderhouse->area - $default_area;
                                    $up_datas = [];
                                    $up_amount = 0;
                                    /* ++++++++++ 上浮计算 ++++++++++ */
                                    foreach ($itemhouseups as $itemhouseup) {
                                        /* ++++++++++ 在上浮区间 ++++++++++ */
                                        if ($itemhouseup->up_end && $itemhouseup->up_rate) {
                                            /* ++++++++++ 超出当前上浮区间 ++++++++++ */
                                            if ($last_area > $itemhouseup->up_end) {
                                                $up_datas[] = [
                                                    'pay_holder_house_id' => $payholderhouse->id,
                                                    'up_start' => $itemhouseup->up_start,
                                                    'up_end' => $itemhouseup->up_end,
                                                    'up_area' => ($itemhouseup->up_end - $itemhouseup->up_start),
                                                    'up_rate' => $itemhouseup->up_rate,
                                                    'price' => $payholderhouse->price,
                                                    'amount' => $payholderhouse->price * ($itemhouseup->up_rate/100) * ($itemhouseup->up_end - $itemhouseup->up_start),
                                                ];
                                                $up_amount += $payholderhouse->price * ($itemhouseup->up_rate/100) * ($itemhouseup->up_end - $itemhouseup->up_start);
                                            } /* ++++++++++ 在当前上浮区间 ++++++++++ */
                                            else {
                                                $up_datas[] = [
                                                    'pay_holder_house_id' => $payholderhouse->id,
                                                    'up_start' => $itemhouseup->up_start,
                                                    'up_end' => $itemhouseup->up_end,
                                                    'up_area' => ($last_area - $itemhouseup->up_start),
                                                    'up_rate' => $itemhouseup->up_rate,
                                                    'price' => $payholderhouse->price,
                                                    'amount' => $payholderhouse->price * ($itemhouseup->up_rate/100) * ($last_area - $itemhouseup->up_start),
                                                ];
                                                $up_amount += $payholderhouse->price * ($itemhouseup->up_rate/100) * ($last_area - $itemhouseup->up_start);
                                                break;
                                            }
                                        } /* ++++++++++ 超出上浮区间，按市场评估单价计算差额 ++++++++++ */
                                        else {
                                            /* ++++++++++ 安置房市场评估单价 ++++++++++ */
                                            $marketprice = Houseprices::where([
                                                'house_id' => $payholderhouse->house_id,
                                                'start_at' => ['<=', $pay_info->item_time],
                                                'end_at' => ['>=', $pay_info->item_time],
                                            ])->value('market_price');

                                            $up_datas[] = [
                                                'pay_holder_house_id' => $payholderhouse->id,
                                                'up_start' => $itemhouseup->up_start,
                                                'up_end' => $itemhouseup->up_end,
                                                'up_area' => ($last_area - $itemhouseup->up_start),
                                                'up_rate' => $itemhouseup->up_rate,
                                                'price' => ($marketprice - $payholderhouse->price),
                                                'amount' => ($marketprice - $payholderhouse->price) * ($last_area - $itemhouseup->up_start),
                                            ];
                                            $up_amount += ($marketprice - $payholderhouse->price) * ($last_area - $itemhouseup->up_start);
                                        }
                                    }
                                    $payholderhouseup_model = new Payholderhouseups();
                                    $payholderhouseup_model->saveAll($up_datas);
                                    $payholderhouse->amount_up = $up_amount;
                                    $payholderhouse->total = $payholderhouse->amount + $payholderhouse->amount_up;
                                }
                                $payholderhouse->save();
                                $house_amount += $payholderhouse->total;
                            }
                        }
                        $payholder->house_amount=$house_amount;
                        $payholder->save();
                    }
                    /* ++++++++++ 无选择安置房或公产产权人或私产承租人 ++++++++++ */
                    else{
                        $payholder->house_amount=0;
                        $payholder->save();
                        $payholderhouse_ids=Payholderhouses::where('pay_holder_id',$payholder->id)->column('id');
                        if($payholderhouse_ids){
                            Payholderhouseups::destroy($payholderhouse_ids,true);
                            Payholderhouses::where('id','in',$payholderhouse_ids)->delete();
                        }
                    }
                }

                $res=true;
                $model->commit();
            }catch (\Exception $exception){
                $res=false;
                $model->rollback();
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




        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }

        $model=new Payholderhouses();
        $model->startTrans();
        try{
            Payholderhouseups::destroy($ids,true);
            Payholderhouses::where('id','in',$ids)->delete();

            /* ++++++++++ 兑付汇总 ++++++++++ */
            $pay_info=Pays::alias('p')
                ->field(['p.id','p.item_id','p.community_id','p.collection_id','p.assess_id','p.total','p.compensate_way','p.pay_way','c.type','i.created_at as item_time'])
                ->join('collection c','c.id=p.collection_id','left')
                ->join('item i','i.id=p.item_id','left')
                ->where('p.id',input('pay_id'))
                ->find();

            /* ++++++++++ 兑付-产权人或承租人 ++++++++++ */
            $payholders=Payholders::alias('ph')
                ->field(['ph.id','ph.item_id','ph.community_id','ph.collection_id','ph.pay_id','ph.collection_holder_id','ph.holder','ph.portion','ph.total_amount','ph.house_amount','ch.name'])
                ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
                ->where('ph.pay_id',$pay_info->id)
                ->order('ph.portion desc')
                ->select();
            /* ++++++++++ 安置房价上浮设置 ++++++++++ */
            $itemhouseups=Itemhouseups::where('item_id',$pay_info->item_id)->order('up_start asc')->select();
            $last=$pay_info->total;
            /* ++++++++++ 兑付-产权人或承租人 ++++++++++ */
            foreach ($payholders as $payholder){
                /* ++++++++++ 有选择安置房，公产承租人或私产产权人 ++++++++++ */
                if($payholder->house_amount && (($pay_info->getData('type') && $payholder->getData('holder')==2) || (!$pay_info->getData('type')&& $payholder->getData('holder')==1))){
                    /* ++++++++++ 安置房 ++++++++++ */
                    $payholderhouses=Payholderhouses::alias('phh')
                        ->field(['phh.*','h.community_id as house_community_id','h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.status as house_status','hc.address','hc.name as hc_name','l.name as l_name'])
                        ->join('house h','h.id=phh.house_id','left')
                        ->join('house_community hc','hc.id=h.community_id','left')
                        ->join('layout l','l.id=h.layout_id','left')
                        ->where('phh.pay_holder_id',$payholder->id)
                        ->order('phh.sort asc')
                        ->select();

                    /* ++++++++++ 计算上浮 ++++++++++ */
                    if($pay_info->getData('type') || !$pay_info->getData('pay_way')){
                        /* ++++++++++ 公产或分权兑付，重置补偿款 ++++++++++ */
                        $last=$payholder->total_amount;
                    }

                    $house_amount=0;
                    if($payholderhouses){
                        foreach ($payholderhouses as $payholderhouse){
                            Payholderhouseups::destroy($payholderhouse->id,true);
                            /* ++++++++++ 安置房价值与补偿款差额 ++++++++++ */
                            $last=$last-$payholderhouse->amount;
                            /* ++++++++++ 安置房价值低于补偿款，无上浮 ++++++++++ */
                            if($last>=0){
                                $payholderhouse->amount_up=0;
                                $payholderhouse->total=$payholderhouse->amount;
                                $payholderhouse->save();
                            }
                            /* ++++++++++ 安置房价值高于补偿款，计算上浮 ++++++++++ */
                            else{
                                /* ++++++++++ 剩余补偿款可换安置房面积 ++++++++++ */
                                $default_area=($last+$payholderhouse->amount)/$payholderhouse->price;
                                /* ++++++++++ 上浮面积 ++++++++++ */
                                $last_area=$payholderhouse->area-$default_area;
                                $up_datas=[];
                                $up_amount=0;
                                /* ++++++++++ 上浮计算 ++++++++++ */
                                foreach ($itemhouseups as $itemhouseup){
                                    /* ++++++++++ 在上浮区间 ++++++++++ */
                                    if($itemhouseup->up_end && $itemhouseup->up_rate){
                                        /* ++++++++++ 超出当前上浮区间 ++++++++++ */
                                        if($last_area>$itemhouseup->up_end){
                                            $up_datas[]=[
                                                'pay_holder_house_id'=>$payholderhouse->id,
                                                'up_start'=>$itemhouseup->up_start,
                                                'up_end'=>$itemhouseup->up_end,
                                                'up_area'=>($itemhouseup->up_end-$itemhouseup->up_start),
                                                'up_rate'=>$itemhouseup->up_rate,
                                                'price'=>$payholderhouse->price,
                                                'amount'=>$payholderhouse->price* ($itemhouseup->up_rate/100) *($itemhouseup->up_end-$itemhouseup->up_start),
                                            ];
                                            $up_amount +=$payholderhouse->price* ($itemhouseup->up_rate/100) *($itemhouseup->up_end-$itemhouseup->up_start);
                                        }
                                        /* ++++++++++ 在当前上浮区间 ++++++++++ */
                                        else{
                                            $up_datas[]=[
                                                'pay_holder_house_id'=>$payholderhouse->id,
                                                'up_start'=>$itemhouseup->up_start,
                                                'up_end'=>$itemhouseup->up_end,
                                                'up_area'=>($last_area-$itemhouseup->up_start),
                                                'up_rate'=>$itemhouseup->up_rate,
                                                'price'=>$payholderhouse->price,
                                                'amount'=>$payholderhouse->price* ($itemhouseup->up_rate/100) *($last_area-$itemhouseup->up_start),
                                            ];
                                            $up_amount +=$payholderhouse->price* ($itemhouseup->up_rate/100) *($last_area-$itemhouseup->up_start);
                                            break;
                                        }
                                    }
                                    /* ++++++++++ 超出上浮区间，按市场评估单价计算差额 ++++++++++ */
                                    else{
                                        /* ++++++++++ 安置房市场评估单价 ++++++++++ */
                                        $marketprice=Houseprices::where([
                                            'house_id'=>$payholderhouse->house_id,
                                            'start_at'=>['<=',$pay_info->item_time],
                                            'end_at'=>['>=',$pay_info->item_time],
                                        ])->value('market_price');

                                        $up_datas[]=[
                                            'pay_holder_house_id'=>$payholderhouse->id,
                                            'up_start'=>$itemhouseup->up_start,
                                            'up_end'=>$itemhouseup->up_end,
                                            'up_area'=>($last_area-$itemhouseup->up_start),
                                            'up_rate'=>$itemhouseup->up_rate,
                                            'price'=>($marketprice-$payholderhouse->price),
                                            'amount'=>($marketprice-$payholderhouse->price)*($last_area-$itemhouseup->up_start),
                                        ];
                                        $up_amount +=($marketprice-$payholderhouse->price)*($last_area-$itemhouseup->up_start);
                                    }
                                }
                                $payholderhouseup_model=new Payholderhouseups();
                                $payholderhouseup_model->saveAll($up_datas);
                                $payholderhouse->amount_up=$up_amount;
                                $payholderhouse->total=$payholderhouse->amount+$payholderhouse->amount_up;
                            }
                            $payholderhouse->save();
                            $house_amount+=$payholderhouse->total;
                        }
                    }

                    $payholder->house_amount=$house_amount;
                    $payholder->save();
                }
                /* ++++++++++ 无选择安置房或公产产权人或私产承租人 ++++++++++ */
                else{
                    $payholder->house_amount=0;
                    $payholder->save();
                    $payholderhouse_ids=Payholderhouses::where('pay_holder_id',$payholder->id)->column('id');
                    if($payholderhouse_ids){
                        Payholderhouseups::destroy($payholderhouse_ids,true);
                        Payholderhouses::where('id','in',$payholderhouse_ids)->delete();
                    }
                }
            }

            $res=true;
            $model->commit();
        }catch (\Exception $exception){
            $res=false;
            $model->rollback();
        }

        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }
}