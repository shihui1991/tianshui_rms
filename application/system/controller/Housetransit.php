<?php
/* |------------------------------------------------------
 * | 房源临时安置
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 添加-项目房源列表
 * | 添加-兑付列表
 * | 详情
 * | 修改
 * | 销毁
 * */
namespace app\system\controller;


use think\Db;

class Housetransit extends Auth
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

        $housetransit_list = model('Housetransits')
            ->alias('hs')
            ->field(['hs.*','i.name as item_name','cc.name as pq_name', 'c.building as c_building',
                'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number','p.transit_way as p_transit_way',
                'h.community_id','hc.address','hc.name as hc_name',
                'h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.status as house_status','h.is_real','h.is_buy',
                'h.is_transit','h.is_public','l.name as l_name'])
            ->join('item i', 'i.id=hs.item_id', 'left')
            ->join('pay p', 'p.id=hs.pay_id', 'left')
            ->join('collection c', 'c.id=hs.collection_id', 'left')
            ->join('collection_community cc', 'cc.id=hs.collection_community_id', 'left')
            ->join('house h','hs.house_id=h.id','left')
            ->join('house_community hc','h.community_id=hc.id','left')
            ->join('layout l','h.layout_id=l.id','left')
            ->where($where)
            ->order([ 'hs.' . $ordername => $orderby])
            ->paginate($display_num);
        $datas['housetransit_list'] =$housetransit_list;
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
                ['house_id', 'require', '请勾选安置房源'],
                ['start_at', 'require', '请选择开始时间'],
                ['exp_end', 'require', '请选择预计结束时间']
            ];
            $result = $this->validate($datas, $rule);
            if (true !== $result) {
                return $this->error($result);
            }
            /*+++++ 数据对比 +++++*/
            $pay_info = model('Pays')->field(['transit_way','community_id','collection_id'])->where('id',$datas['pay_id'])->where('item_id',$datas['item_id'])->find();
            if(empty($pay_info->getData('transit_way'))&&$pay_info->getData('transit_way')!=0){
                return $this->error('兑付数据不存在');
            }
            $house_info = model('Itemhouses')
                ->alias('i')
                ->field(['i.id','h.community_id as h_community_id'])
                ->join('house h','h.id = i.house_id','left')
                ->where('i.house_id',$datas['house_id'])
                ->where('i.item_id',$datas['item_id'])
                ->find();
            if(!$house_info->id){
                return $this->error('安置房源不存在');
            }

            $start_at = strtotime(input('start_at'));
            $end_at = strtotime(input('end_at'));
            if(input('end_at')){
                if($start_at>$end_at){
                    return $this->error('结束时间不能早于开始时间');
                }
            }

            /*+++++ 判断过渡方式 +++++*/
            if($pay_info->getData('transit_way') == 0){
                return $this->error('过渡方式为货币过渡，无法添加');
            }else{
                Db::startTrans();
                try{
                    $res = model('Housetransits')->save([
                        'item_id'=>$datas['item_id'],
                        'pay_id'=>$datas['pay_id'],
                        'collection_id'=>$pay_info->collection_id,
                        'collection_community_id'=>$pay_info->community_id,
                        'house_id'=>$datas['house_id'],
                        'house_community_id'=>$house_info->h_community_id,
                        'start_at'=>$datas['start_at'],
                        'exp_end'=>$datas['exp_end'],
                        'end_at'=>$datas['end_at']
                    ]);
                    model('Houses')->save(['status'=>1],['id'=>$datas['house_id']]);
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

    /* ========== 项目房源列表 ========== */
    public function search_house(){
        /* ********** 查询条件 ********** */
        $datas=[];
        $where=[];
        $field=['ih.*','i.name as i_name','i.status as i_status','i.is_top','h.community_id','c.address','c.name as c_name',
            'h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.status as house_status','h.is_real','h.is_buy',
            'h.is_transit','h.is_public','l.name as l_name'];

        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        $where['item_id']=$item_id;
        $datas['item_id']=$item_id;
        $inputs=input();
        /* ++++++++++ 地址 ++++++++++ */
        $community_ids_a=isset($inputs['community_ids_a'])?$inputs['community_ids_a']:[];
        $datas['community_ids_a']=$community_ids_a;
        /* ++++++++++ 小区 ++++++++++ */
        $community_ids_c=isset($inputs['community_ids_c'])?$inputs['community_ids_c']:[];
        $datas['community_ids_c']=$community_ids_c;
        $community_ids=array_filter(array_merge($community_ids_a,$community_ids_c));
        if($community_ids){
            $where['house.community_id']=['in',$community_ids];
            $datas['community_ids']=$community_ids;
        }
        /* ++++++++++ 几栋 ++++++++++ */
        $building=input('building');
        if(is_numeric($building)){
            $where['house.building']=$building;
            $datas['building']=$building;
        }
        /* ++++++++++ 几单元 ++++++++++ */
        $unit=input('unit');
        if(is_numeric($unit)){
            $where['house.unit']=$unit;
            $datas['unit']=$unit;
        }
        /* ++++++++++ 几楼 ++++++++++ */
        $floor=input('floor');
        if(is_numeric($floor)){
            $where['house.floor']=$floor;
            $datas['floor']=$floor;
        }
        /* ++++++++++ 几号 ++++++++++ */
        $number=input('number');
        if(is_numeric($number)){
            $where['house.number']=$number;
            $datas['number']=$number;
        }
        /* ++++++++++ 户型 ++++++++++ */
        $layout_id=input('layout_id');
        if($layout_id){
            $where['house.layout_id']=$layout_id;
            $datas['layout_id']=$layout_id;
        }
        /* ++++++++++ 面积 ++++++++++ */
        $area_start=input('area_start');
        if($area_start){
            $where['house.area']=['>=',$area_start];
            $datas['area_start']=$area_start;
        }
        $area_end=input('area_end');
        if($area_end){
            $where['house.area']=['<=',$area_end];
            $datas['area_end']=$area_end;
        }
        /* ++++++++++ 期房、现房 ++++++++++ */
        $is_real=input('is_real');
        if(is_numeric($is_real) && in_array($is_real,[0,1])){
            $where['house.is_real']=$is_real;
            $datas['is_real']=$is_real;
        }
        /* ++++++++++ 是否购置房 ++++++++++ */
        $is_buy=input('is_buy');
        if(is_numeric($is_buy) && in_array($is_buy,[0,1])){
            $where['house.is_buy']=$is_buy;
            $datas['is_buy']=$is_buy;
        }
        /* ++++++++++ 是否过渡房 ++++++++++ */
        $is_transit=input('is_transit');
        if(is_numeric($is_transit) && in_array($is_transit,[0,1])){
            $where['house.is_transit']=$is_transit;
            $datas['is_transit']=$is_transit;
        }
        /* ++++++++++ 是否共用 ++++++++++ */
        $is_public=input('is_public');
        if(is_numeric($is_public) && in_array($is_public,[0,1])){
            $where['house.is_public']=$is_public;
            $datas['is_public']=$is_public;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1,2,3])){
            $where['house.status']=$status;
            $datas['status']=$status;
        }
        $where['h.status']=0;
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
        /* ++++++++++ 查询 ++++++++++ */
        $itemhouse_model=model('Itemhouses');
        $datas['model']=$itemhouse_model;
        $itemhouses=$itemhouse_model
            ->alias('ih')
            ->field($field)
            ->join('item i','ih.item_id=i.id','left')
            ->join('house h','ih.house_id=h.id','left')
            ->join('house_community c','h.community_id=c.id','left')
            ->join('layout l','h.layout_id=l.id','left')
            ->where($where)
            ->order(['item.is_top'=>'desc','item_house.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['itemhouses']=$itemhouses;
        /* ++++++++++ 小区列表 ++++++++++ */
        $communitys=model('Housecommunitys')->field(['id','address','name','status'])->where('status',1)->select();
        $datas['communitys']=$communitys;
        /* ++++++++++ 户型列表 ++++++++++ */
        $layouts=model('Layouts')->field(['id','name','status'])->where('status',1)->select();
        $datas['layouts']=$layouts;

        $this->assign($datas);

        return view();
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
        $housetransit_houseids = model('Housetransits')->column('pay_id');
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
        $housetransit_info = model('Housetransits')
            ->alias('hs')
            ->field(['hs.*','i.name as item_name'])
            ->join('item i', 'i.id=hs.item_id', 'left')
            ->where('hs.id',$id)
            ->find();

        $pays=model('Pays')
            ->alias('p')
            ->field(['p.*','c.community_id','c.building','c.unit','c.floor','c.number','c.type','c.real_use','cc.address','cc.name as pq_name','bu.name as bu_name'])
            ->join('collection c','c.id=p.collection_id','left')
            ->join('collection_community cc','cc.id=p.community_id','left')
            ->join('building_use bu','bu.id=c.real_use','left')
            ->where('p.id',$housetransit_info->pay_id)
            ->find();

        $itemhouses=model('Itemhouses')
            ->alias('ih')
            ->field(['ih.*','i.name as i_name','i.status as i_status','i.is_top','h.community_id','c.address','c.name as c_name',
                'h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.status as house_status','h.is_real','h.is_buy',
                'h.is_transit','h.is_public','l.name as l_name'])
            ->join('item i','ih.item_id=i.id','left')
            ->join('house h','ih.house_id=h.id','left')
            ->join('house_community c','h.community_id=c.id','left')
            ->join('layout l','h.layout_id=l.id','left')
            ->where('ih.id',$housetransit_info->house_id)
            ->find();
        if($housetransit_info->end_at == '1970-01-01'){
            $end_at = '';
        }else{
            $end_at = $housetransit_info->end_at;
        }
        return view('modify',
            ['infos'=>$housetransit_info,
                'pays'=>$pays,
                'itemhouse'=>$itemhouses,
                'end_at'=>$end_at]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $datas = input();
        $rule = [
            ['start_at', 'require', '请选择开始时间'],
            ['exp_end', 'require', '请选择预计结束时间']
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
              $rs = model('Housetransits')->save([
                    'start_at'=>$datas['start_at'],
                    'exp_end'=>$datas['exp_end'],
                    'end_at'=>$datas['end_at'],
                    ],['id'=>$datas['id']]);
            $house_id =  model('Housetransits')
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
                $rs = model('Housetransits')->save([
                    'start_at'=>$datas['start_at'],
                    'exp_end'=>$datas['exp_end'],
                    'end_at'=>$datas['end_at']
                ],['id'=>$datas['id']]);
                $house_id =  model('Housetransits')
                    ->where('id',$datas['id'])
                    ->value('house_id');
                model('Houses')->save(['status'=>1],['id'=>$house_id]);
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
            $house_ids =  model('Housetransits')
                ->withTrashed()
                ->where('id','in',$ids)
                ->column('house_id');
            $res = model('Housetransits')->withTrashed()->whereIn('id',$ids)->delete(true);
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