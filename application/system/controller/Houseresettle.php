<?php
/* |------------------------------------------------------
 * | 房源安置
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 添加-兑付列表
 * | 详情
 * | 修改
 * | 销毁
 * */
namespace app\system\controller;


use think\Db;

class Houseresettle extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
        /* ********** 查询条件 ********** */
        $datas = [];
        $where = [];
        /* ++++++++++ 项目 ++++++++++ */
        $item_id = input('item_id');
        if (is_numeric($item_id)) {
            $where['hs.item_id'] = $item_id;
            $datas['item_id'] = $item_id;
        }
        /* ++++++++++ 排序 ++++++++++ */
        $ordername = input('ordername');
        $ordername = $ordername ? $ordername : 'id';
        $datas['ordername'] = $ordername;
        $orderby = input('orderby');
        $orderby = $orderby ? $orderby : 'asc';
        $datas['orderby'] = $orderby;
        /* ++++++++++ 每页条数 ++++++++++ */
        $nums = [config('paginate.list_rows'), 30, 50, 100, 200, 500];
        sort($nums);
        $datas['nums'] = $nums;
        $display_num = input('display_num');
        $display_num = $display_num ? $display_num : config('paginate.list_rows');
        $datas['display_num'] = $display_num;

        $houseresettle_list = model('Houseresettles')
            ->alias('hs')
            ->field(['hs.*','i.name as item_name','ch.name as ch_name','ch.holder',
                'h.community_id','hc.address','hc.name as hc_name',
                'h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.status as house_status','h.is_real','h.is_buy',
                'h.is_transit','h.is_public','l.name as l_name'])
            ->join('collection_holder ch','ch.id=hs.collection_holder_id','left')
            ->join('item i', 'i.id=hs.item_id', 'left')
            ->join('house h','hs.house_id=h.id','left')
            ->join('house_community hc','h.community_id=hc.id','left')
            ->join('layout l','h.layout_id=l.id','left')
            ->where($where)
            ->order([ 'hs.' . $ordername => $orderby])
            ->paginate($display_num);
        $datas['houseresettle_list'] =$houseresettle_list;
        /* ++++++++++ 项目列表 ++++++++++ */
        $items = model('Items')->field(['id', 'name', 'status'])->order('is_top desc')->select();
        $datas['items'] = $items;
        $this->assign($datas);
        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        if (request()->isPost()) {
            $datas = input();
            $rule = [
                ['item_id', 'require', '请选择项目'],
                ['pay_id', 'require', '请勾选兑付'],
                ['collection_holder_id', 'require', '请选择被征收人'],
                ['house_id', 'require', '请选择安置房源'],
                ['start_at', 'require', '请选择开始时间']
            ];
            $result = $this->validate($datas, $rule);
            if (true !== $result) {
                return $this->error($result);
            }
            /*+++++ 查询数据 +++++*/
           $pay_infos =  model('Pays')->field('community_id,collection_id')->where('id',$datas['pay_id'])->find();
           $house_infos = model('Houses')->field('community_id')->where('id',$datas['house_id'])->find();
            /*+++++ 数据对比 +++++*/
            $pay_info = model('Pays')
                ->field(['compensate_way','community_id','collection_id'])
                ->where('id',$datas['pay_id'])
                ->where('item_id',$datas['item_id'])
                ->find();
            if(empty($pay_info->getData('compensate_way'))&&$pay_info->getData('compensate_way')!=0){
                return $this->error('兑付数据不存在');
            }
            $start_at = strtotime(input('start_at'));
            $end_at = strtotime(input('end_at'));
            if(input('end_at')){
                if($start_at>$end_at){
                    return $this->error('结束时间不能早于开始时间');
                }
            }
            if($pay_info->getData('compensate_way') == 0){
                return $this->error('补偿方式为货币补偿，无法添加');
            }
            Db::startTrans();
            try{
                $res = model('Houseresettles')->save([
                    'item_id'=>$datas['item_id'],
                    'pay_id'=>$datas['pay_id'],
                    'collection_id'=>$pay_infos->collection_id,
                    'collection_community_id'=>$pay_infos->community_id,
                    'collection_holder_id'=>$datas['collection_holder_id'],
                    'house_id'=>$datas['house_id'],
                    'house_community_id'=>$house_infos->community_id,
                    'start_at'=>$datas['start_at'],
                    'end_at'=>$datas['end_at']
                ]);
                model('Houses')->save(['status'=>2],['id'=>$datas['house_id']]);
                if(!$res){
                    $rs = false;
                    Db::rollback();
                }else{
                    $rs = true;
                    Db::commit();
                }
            }catch (\Exception $e){
                $rs = false;
                Db::rollback();
            }
            if($rs){
                return $this->success('添加成功','');
            }else{
                return $this->error('添加失败');
            }
        }else{
            /* ++++++++++ 项目列表 ++++++++++ */
            $items = model('Items')->field(['id', 'name', 'status'])->where('status', 1)->order('is_top desc')->select();
            $this->assign('items',$items);
            return view();
        }

    }

    /* ========== 兑付列表 ========== */
    public function search_pay(){
        /* ********** 查询条件 ********** */
        $datas=[];
        $where=[];
        $field=['p.*','i.name as i_name','i.is_top','c.community_id','c.building','c.unit','c.floor','c.number','c.type','c.real_use','cc.address','cc.name as cc_name','bu.name as bu_name'];

        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        $where['p.item_id']=$item_id;
        $datas['item_id']=$item_id;
        /* ++++++++++ 片区 ++++++++++ */
        $community_id=input('community_id');
        if(is_numeric($community_id)){
            $where['pay.community_id']=$community_id;
            $datas['community_id']=$community_id;
        }
        /* ++++++++++ 权属 ++++++++++ */
        $collection_id=input('collection_id');
        if(is_numeric($collection_id)){
            $where['pay.collection_id']=$collection_id;
            $datas['collection_id']=$collection_id;
        }
        /* ++++++++++ 排序 ++++++++++ */
        $ordername=input('ordername');
        $ordername=$ordername?$ordername:'id';
        $datas['ordername']=$ordername;
        $orderby=input('orderby');
        $orderby=$orderby?$orderby:'asc';
        $datas['orderby']=$orderby;
        /* ++++++++++ 每页条数 ++++++++++ */
        $nums=[config('paginate.list_rows'),30,50,100,200,500];
        sort($nums);
        $datas['nums']=$nums;
        $display_num=input('display_num');
        $display_num=$display_num?$display_num:config('paginate.list_rows');
        $datas['display_num']=$display_num;
        /* ++++++++++ 是否删除 ++++++++++ */
        $deleted=input('deleted');
        $pay_model=model('Pays');
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $pay_model=$pay_model->onlyTrashed();
            }
        }else{
            $pay_model=$pay_model->withTrashed();
        }
        /* ++++++++++ 查询所有的房源临时安置 ++++++++++ */
        $housetransit_houseids = model('Houseresettles')->column('pay_id');
        $where['pay.id']=array('not in',$housetransit_houseids);
        $pays=$pay_model
            ->alias('p')
            ->field($field)
            ->join('item i','i.id=p.item_id','left')
            ->join('collection c','c.id=p.collection_id','left')
            ->join('collection_community cc','cc.id=p.community_id','left')
            ->join('building_use bu','bu.id=c.real_use','left')
            ->where($where)
            ->order(['item.is_top'=>'desc','pay.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['pays']=$pays;

        /* ++++++++++ 项目 ++++++++++ */
        $items=model('Items')->field(['id','name','status','is_top'])->where(['status'=>1])->order('is_top desc')->select();
        $datas['items']=$items;
        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys=model('Collectioncommunitys')->field(['id','address','name'])->select();
        $datas['collectioncommunitys']=$collectioncommunitys;
        /* ++++++++++ 权属 ++++++++++ */
        $collections=model('Collections')->field(['id','building','unit','floor','number','status'])->where('status',1)->select();
        $datas['collections']=$collections;

        $this->assign($datas);

        return view();
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id = input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $houseresettle_info = model('Houseresettles')
            ->alias('hs')
            ->field(['hs.*','i.name as item_name','ch.name as ch_name','ch.holder',
                'h.community_id as house_community_id','h.id as h_id','h.building','h.unit',
                'h.floor','h.number','h.layout_id','h.area','h.status as house_status',
                'hc.address','hc.name as hc_name','l.name as l_name'])
            ->join('item i', 'i.id=hs.item_id', 'left')
            ->join('collection_holder ch','ch.id=hs.collection_holder_id','left')
            ->join('house h','h.id=hs.house_id','left')
            ->join('house_community hc','hc.id=h.community_id','left')
            ->join('layout l','l.id=h.layout_id','left')
            ->where('hs.id',$id)
            ->find();
        $pays=model('Pays')
            ->alias('p')
            ->field(['p.*','c.community_id','c.building','c.unit','c.floor','c.number','c.type','c.real_use','cc.address','cc.name as pq_name','bu.name as bu_name'])
            ->join('collection c','c.id=p.collection_id','left')
            ->join('collection_community cc','cc.id=p.community_id','left')
            ->join('building_use bu','bu.id=c.real_use','left')
            ->where('p.id',$houseresettle_info->pay_id)
            ->find();
        if($houseresettle_info->end_at == '1970-01-01'){
            $end_at = '';
        }else{
            $end_at = $houseresettle_info->end_at;
        }
        return view('modify', [
            'infos'=>$houseresettle_info,
            'pays'=>$pays,
            'end_at'=>$end_at
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $datas = input();
        $rule = [
            ['start_at', 'require', '请选择开始时间']
        ];
        $result = $this->validate($datas, $rule);
        if (true !== $result) {
            return $this->error($result);
        }

        $start_at = strtotime(input('start_at'));
        $end_at = strtotime(input('end_at'));
        if(input('end_at')) {
            if ($start_at > $end_at) {
                return $this->error('结束时间不能早于开始时间');
            }
        }
        $end_at_end = strtotime(date('Y-m-d')." 23:59:59");
        if($end_at<=$end_at_end){
            Db::startTrans();
            try{
                $rs = model('Houseresettles')->save([
                    'start_at'=>$datas['start_at'],
                    'end_at'=>$datas['end_at']
                ],['id'=>$datas['id']]);
                $house_id =  model('Houseresettles')
                    ->where('id',$datas['id'])
                    ->value('house_id');
                model('Houses')->save(['status'=>0],['id'=>$house_id]);
                if(!$rs){
                    $res = false;
                    Db::rollback();
                }else{
                    $res = true;
                    Db::commit();
                }
            }catch (\Exception $e){
                $res = false;
                Db::rollback();
            }
        }else{
            Db::startTrans();
            try{
                $rs = model('Houseresettles')->save([
                    'start_at'=>$datas['start_at'],
                    'end_at'=>$datas['end_at']
                ],['id'=>$datas['id']]);
                $house_id =  model('Houseresettles')
                    ->where('id',$datas['id'])
                    ->value('house_id');
                model('Houses')->save(['status'=>2],['id'=>$house_id]);
                if(!$rs){
                    $res = false;
                    Db::rollback();
                }else{
                    $res = true;
                    Db::commit();
                }
            }catch (\Exception $e){
                $res = false;
                Db::rollback();
            }
        }
        if($res){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
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
            $house_ids =  model('Houseresettles')
                ->withTrashed()
                ->where('id','in',$ids)
                ->column('house_id');
            $res = model('Houseresettles')->withTrashed()->whereIn('id',$ids)->delete(true);
            model('Houses')->save(['status'=>0],['id'=>['in',$house_ids]]);
            if($res){
                $res = true;
                Db::commit();
            }else{
                $res = false;
                Db::rollback();
            }
        }catch (\Exception $e){
            $res = false;
            Db::rollback();
        }
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败！');
        }
    }

}