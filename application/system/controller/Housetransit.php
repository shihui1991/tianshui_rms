<?php
/* |------------------------------------------------------
 * | 房源临时安置
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
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
        return view($this->theme.'/housetransit/index');
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
                ->field(['i.id','h.community_id as h_community_id','h.is_transit'])
                ->join('house h','h.id = i.house_id','left')
                ->where('i.house_id',$datas['house_id'])
                ->where('i.item_id',$datas['item_id'])
                ->find();
            if(!$house_info->id){
                return $this->error('安置房源不存在');
            }
            if($house_info->getData('is_transit')==0){
                return $this->error('安置房源类型为非过渡');
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
            return view($this->theme.'/housetransit/add');
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id = input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $housetransit_info = model('Housetransits')
            ->withTrashed()
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
            ->where('ih.house_id',$housetransit_info->house_id)
            ->find();
        return view($this->theme.'/housetransit/modify',
            ['infos'=>$housetransit_info,
                'pays'=>$pays,
                'itemhouse'=>$itemhouses,
                ]);
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

    /* ========== 过渡房屋使用情况明细Excel导出 ========== */
    public function statis(){
        $where = [];
        $item_id = input('item_id');
        if($item_id){
            $where['hs.item_id'] = $item_id;
            $orders = 'hs.house_id asc,hs.start_at asc';
        }else{
            $orders = 'hs.item_id asc,hs.house_id asc,hs.start_at asc';
        }
        /*++++++++++ 查询值 ++++++++++*/
        $houseresettle_list = model('Housetransits')
            ->alias('hs')
            ->field(['i.name as item_name','hc.address','hc.name as hc_name','h.building','h.unit','h.floor','h.number','l.name as l_name','h.area',
               'hs.start_at','hs.end_at','hs.house_id','ch.name as collection_holder_name','hs.pay_id as hpay_id'])
            ->join('item i', 'i.id=hs.item_id', 'left')
            ->join('house h','hs.house_id=h.id','left')
            ->join('house_community hc','h.community_id=hc.id','left')
            ->join('layout l','h.layout_id=l.id','left')
            ->join('item_house phh','phh.house_id=hs.house_id','left')
            ->join('pay_holder ph','ph.pay_id = hs.pay_id','left')
            ->join('collection_holder ch','ch.id = ph.collection_holder_id','left')
            ->where($where)
            ->order($orders)
            ->select();
        if(empty($houseresettle_list)){
            return $this->error('暂无数据');
        }
        /*++++++++++ 【拼装数据】 ++++++++++*/
        /*++++++++++ 归类分组++++++++++*/
        $new_array = [];
        foreach ($houseresettle_list as $k=>$v){
            $new_array[$v->house_id][$v->hpay_id][] = $v;
        }
        /*++++++++++ 拼装值++++++++++*/
        $datas_array = [];
        $i = 0;
        foreach ($new_array as $k=>$v){
            $i++;
            foreach ($v as $key=>$val){
                $building = $val[0]->building?$val[0]->building.'栋':'';
                $unit = $val[0]->unit?$val[0]->unit.'单元':'';
                $floor =  $val[0]->floor?$val[0]->floor.'楼':'';
                $number = $val[0]->number?$val[0]->number.'号':'';
                $end_at = $val[0]->end_at?'---'.$val[0]->end_at:'';
                   $datas_array[$k]['xuhao'] = $i;
                   $datas_array[$k]['item_name'] = $val[0]->item_name;
                   $datas_array[$k]['pq_address'] = $val[0]->hc_name.'('.$val[0]->address.')';
                   $datas_array[$k]['building_num'] = $building.$unit.$floor.$number;
                   $datas_array[$k]['l_name'] = $val[0]->l_name;
                   $datas_array[$k]['area'] = $val[0]->area;
                   $datas_array[$k]['collection_holder_name'.$key] = $val[0]->collection_holder_name;
                   $datas_array[$k]['start_at'.$key] = $val[0]->start_at.$end_at;
             }
        }

       /*---------- 过渡次数 ----------*/
        $count_data = [];
      foreach ($datas_array as $k=>$v){
          $count_data[] = count($v);
      }
        arsort($count_data);
        $count_data = array_values($count_data)[0]-6;
        /*---------- 标题 ----------*/
        $housetransit_title = [];
        $housetransit_title[0][0] = '序号';
        $housetransit_title[0][1] = '项目名称';
        $housetransit_title[0][2] = '小区名称(地点)';
        $housetransit_title[0][3] = '房号';
        $housetransit_title[0][4] = '户型';
        $housetransit_title[0][5] = '面积(㎡)';
        for ($i=1;$i<=$count_data/2;$i++){
            if($i==1){
                $housetransit_title[0][6] = '过渡人(第1次过渡)';
            }else{
                $housetransit_title[0][4+$i*2] = '过渡人(第'.$i.'次过渡)';
            }
            $housetransit_title[0][5+$i*2] = '过渡时间(第'.$i.'次过渡)';
        }
        $xls_data = array_merge($housetransit_title,$datas_array);
        create_housetransit_xls($xls_data,'过渡房屋使用情况明细'.date('Ymd'));
    }
}