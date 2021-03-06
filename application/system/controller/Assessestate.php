<?php
/* |------------------------------------------------------
 * | 房产评估
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 状态
 * */
namespace app\system\controller;
use app\system\model\Assessestates;
use app\system\model\Collectionbuildings;
use app\system\model\Items;
use app\system\model\Itemstatuss;
use think\Db;
use think\Exception;

class Assessestate extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index()
    {
        /* ********** 查询条件 ********** */
        $datas = [];
        $where = [];
        $field = ['ass.*', 'i.name as item_name', 'cc.name as pq_name', 'c.building as c_building',
            'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number', 'c.id as c_id', 'cy.name as cy_name'];

        $assess_id = input('assess_id');
        $this->assign('assess_id',$assess_id);
        if($assess_id){
            $where['ass.assess_id'] = $assess_id;
        }

        /* ********** 是否弹出层 ********** */
        $l=input('l');
        $item_id=input('item_id');
        if($l){
            if(!$item_id){
                return $this->error('错误操作','');
            }
            $view='index';
            /* ++++++++++ 项目信息 ++++++++++ */
            $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
            $datas['item_info']=$item_info;
            $where['ass.item_id']=$item_id;

            /* ++++++++++ 入户评估状态 ++++++++++ */
            $assess_status=Itemstatuss::where(['keyname'=>'assess_id','keyvalue'=>$assess_id])->order('created_at desc')->value('status');
            $datas['assess_status']=$assess_status;
        }else{
            if($item_id){
                $where['ass.item_id']=$item_id;
                $datas['item_id']=$item_id;
            }
            $view='all';
            /* ++++++++++ 项目列表 ++++++++++ */
            $items=Items::field(['id','name','status','is_top'])->order('is_top desc')->select();
            $datas['item_list']=$items;
        }
        /* ++++++++++ 片区 ++++++++++ */
        $community_id = input('community_id');
        if (is_numeric($community_id)) {
            $where['ass.community_id'] = $community_id;
            $datas['community_id'] = $community_id;
        }
        /* ++++++++++ 权属 ++++++++++ */
        $collection_id = input('collection_id');
        if (is_numeric($collection_id)) {
            $where['ass.collection_id'] = $collection_id;
            $datas['collection_id'] = $collection_id;
        }
        /* ++++++++++ 评估公司 ++++++++++ */
        $company_id = input('company_id');
        if (is_numeric($company_id)) {
            $where['ass.company_id'] = $company_id;
            $datas['company_id'] = $company_id;
        }
        /* ++++++++++ 报告时间 ++++++++++ */
        $report_at = input('report_at');
        if($report_at){
            $report_at_start = strtotime($report_at." 00:00:00");
            $report_at_end = strtotime($report_at." 23:59:59");
            $where['ass.report_at'] = [['<=',$report_at_end],['>=',$report_at_start]];
            $datas['report_at'] = $report_at;
        }
        /* ++++++++++ 价值时点 ++++++++++ */
        $valued_at = input('valued_at');
        if($valued_at){
            $valued_at_start = strtotime($valued_at." 00:00:00");
            $valued_at_end = strtotime($valued_at." 23:59:59");
            $where['ass.valued_at'] = [['<=',$valued_at_end],['>=',$valued_at_start]];
            $datas['valued_at'] = $valued_at;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status = input('status');
        if (is_numeric($status)) {
            $where['ass.status'] = $status;
            $datas['status'] = $status;
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
        /* ++++++++++ 查询 ++++++++++ */
        $assessestate_model = new Assessestates();
        $deleted = input('deleted');
        if (is_numeric($deleted) && in_array($deleted, [0, 1])) {
            $datas['deleted'] = $deleted;
            if ($deleted == 1) {
                $assessestate_model = $assessestate_model->onlyTrashed();
            }
        } else {
            $assessestate_model = $assessestate_model->withTrashed();
        }
        $assessestate_list = $assessestate_model
            ->alias('ass')
            ->field($field)
            ->join('item i', 'i.id=ass.item_id', 'left')
            ->join('collection_community cc', 'cc.id=ass.community_id', 'left')
            ->join('collection c', 'c.id=ass.collection_id', 'left')
            ->join('assess ess', 'ess.id=ass.assess_id', 'left')
            ->join('item_company ic', 'ic.id=ass.company_id', 'left')
            ->join('company cy', 'cy.id=ic.company_id', 'left')
            ->where($where)
            ->order(['i.is_top' => 'desc', 'ass.' . $ordername => $orderby])
            ->paginate($display_num);
        $datas['assessestate_list'] = $assessestate_list;

        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys = model('Collectioncommunitys')->field(['id', 'address', 'name'])->select();
        $datas['collectioncommunity_list'] = $collectioncommunitys;
        /* ++++++++++ 权属 ++++++++++ */
        $collections = model('Collections')->field(['id', 'building', 'unit','floor','number'])->select();
        $datas['collections_list'] = $collections;
        /* ++++++++++ 评估公司 ++++++++++ */
        $companys = model('Companys')->field(['id','name'])->where('status',1)->where('type',0)->select();
        $datas['company_list'] = $companys;
        $this->assign($datas);
        return view($this->theme.'/assessestate/'.$view);
    }

    /* ========== 添加 ========== */
    public function add()
    {
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

        /* ++++++++++ 入户评估状态 ++++++++++ */
        $assess_status=Itemstatuss::where(['keyname'=>'assess_id','keyvalue'=>input('assess_id')])->order('created_at desc')->value('status');
        if($assess_status == 8){
            $msg='入户评估数据已审核通过，禁止操作！';
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }

        $collection_id=input('collection_id');
        $count=Collectionbuildings::where('collection_id',$collection_id)->where('status_id',0)->count();
        if($count){
            return $this->error('房屋合法性认定未完成，暂时不能评估！','');
        }


        if (request()->isPost()) {
            $model = new Assessestates();
            $datas = input();
            $rule = [
                ['community_id', 'require', '请选择片区'],
                ['collection_id', 'require', '请选择权属'],
                ['price', 'require', '建筑数据不能为空'],
                ['company_id', 'require', '请选择评估公司'],
                ['ids', 'require', '请选择评估师'],
                ['report_at', 'require', '报告时间不能为空'],
                ['valued_at', 'require', '价值时点不能为空'],
                ['method', 'require', '评估方法不能为空'],
                ['picture', 'require', '评估报告不能为空']
            ];
            $result = $this->validate($datas, $rule);
            if (true !== $result) {
                return $this->error($result);
            }

            $values=array_filter(array_values($datas['price']));
            if(empty($values)){
                return $this->error('请输入评估单价','');
            }

            $collection_info=model('Collections')->field(['id','item_id','community_id'])->find(input('collection_id'));
            if(!$collection_info){
                return $this->error('选择权属不存在！');
            }
            if(input('item_id') != $collection_info->item_id || input('community_id') != $collection_info->community_id){
                return $this->error('选择权属与项目片区不一致');
            }
            $assessestate_count = model('Assessestates')
                ->where('item_id', $datas['item_id'])
                ->where('community_id', $datas['community_id'])
                ->where('collection_id', $datas['collection_id'])
                ->where('company_id', $datas['company_id'])
                ->count();
            if ($assessestate_count) {
                return $this->error('数据重复,请不要重复添加', '');
            }

            $building_info = $datas['price'];
            Db::startTrans();
            try {
                /*----- 查询入户评估总表 -----*/
                $search_assess = model('Assesss')
                    ->where('item_id', $datas['item_id'])
                    ->where('collection_id', $datas['collection_id'])
                    ->value('id');
                if ($search_assess == 0) {
                    model('Assesss')->save([
                        'item_id' => $datas['item_id'],
                        'community_id' => $datas['community_id'],
                        'collection_id' => $datas['collection_id']
                    ]);
                    $assess_id = model('Assesss')->getLastInsID();
                } else {
                    model('Assesss')->save(['updated_at' => time()], ['id' => $search_assess]);
                    $assess_id = $search_assess;
                }
                /*----- 禁用之前添加的房产评估 -----*/
                model('Assessestates')->save(['status'=>0],['assess_id' =>$assess_id]);
                /*----- 添加房产评估 -----*/
                $model->save([
                    'item_id' => $datas['item_id'],
                    'community_id' => $datas['community_id'],
                    'collection_id' => $datas['collection_id'],
                    'assess_id' => $assess_id,
                    'company_id' => $datas['company_id'],
                    'report_at' => $datas['report_at'],
                    'valued_at' => $datas['valued_at'],
                    'method' => $datas['method'],
                    'status' => 1,
                    'picture' => $datas['picture']
                ]);
                $estate_id = $model->getLastInsID();
                /*----- 添加房产评估--建筑评估 -----*/
                $building_data = [];
                $amount_nums = 0;
                foreach ($building_info as $k => $v) {
                    $real_num = model('Collectionbuildings')->where('id', $k)->value('real_num');
                    $building_data[] = [
                        'item_id' => $datas['item_id'],
                        'community_id' => $datas['community_id'],
                        'collection_id' => $datas['collection_id'],
                        'assess_id' => $assess_id,
                        'estate_id' => $estate_id,
                        'building_id' => $k,
                        'price' => $v,
                        'amount' => $real_num * $v
                    ];
                    $amount_nums .= $real_num * $v;
                }
                model('Assessestatebuildings')->saveAll($building_data);
                /*----- 添加房产评估--评估师 -----*/
                $valuer_ids = $datas['ids'];
                $valuer_data = [];
                foreach ($valuer_ids as $k => $v) {
                    $valuer_data[] = [
                        'item_id' => $datas['item_id'],
                        'collection_id' => $datas['collection_id'],
                        'assess_id' => $assess_id,
                        'estate_id' => $estate_id,
                        'company_id' => $datas['company_id'],
                        'valuer_id' => $v
                    ];
                }
                model('Assessestatevaluers')->saveAll($valuer_data);
                /*----- 修改房产评估总额 -----*/
                $model->save(['total' => $amount_nums], ['id' => $estate_id]);
                /*----- 修改房产评估总额 -----*/
                model('Assesss')->save(['estate' => $amount_nums], ['id' => $assess_id]);
                $assess_estate_valuer = true;
                Db::commit();
            } catch (\Exception $e) {
                $assess_estate_valuer = false;
                Db::rollback();
            }
            if ($assess_estate_valuer) {
                return $this->success('添加成功', '');
            } else {
                return $this->error('添加失败', '');
            }
        } else {
            /* ++++++++++ 项目列表 ++++++++++ */
            $items = model('Items')->field(['id', 'name', 'status'])->where('id', $item_id)->find();
            /* ++++++++++ 片区 ++++++++++ */
            $community_id = input('community_id');
            $collectioncommunitys = model('Collectioncommunitys')->field(['id', 'address', 'name'])->where('id',$community_id)->find();

            /* ++++++++++ 权属 ++++++++++ */
            $where['c.id']=$collection_id;
            $field=['c.id','c.item_id','c.community_id','c.building','c.unit','c.floor','c.number','c.has_assets','c.status','cc.address','cc.name as cc_name'];
            $collections=model('Collections')
                ->alias('c')
                ->field($field)
                ->join('collection_community cc','cc.id=c.community_id','left')
                ->where($where)
                ->find();
            return view($this->theme.'/assessestate/add',
                ['item_info' => $items,
                    'collectioncommunity_info' => $collectioncommunitys,
                    'collection_info'=>$collections
                ]);
        }

    }

    /* ========== 详情 ========== */
    public function detail()
    {
        $item_id = input('item_id');
        $id = input('id');
        if (!$id) {
            return $this->error('至少选中一项', '');
        }
        $assessestate_model = new Assessestates();
        $where = [];
        $field = ['ass.id','ass.created_at','ass.updated_at','ass.deleted_at', 'ass.assess_id', 'ass.collection_id', 'ass.company_id', 'i.name as item_name', 'cc.name as pq_name', 'cc.address as pq_address', 'c.building as c_building',
            'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number', 'c.id as c_id', 'cy.name as cy_name', 'ass.method', 'ass.valued_at', 'ass.status', 'ass.report_at', 'ass.picture'];

        $assessestate_info = $assessestate_model
            ->withTrashed()
            ->alias('ass')
            ->field($field)
            ->join('item i', 'i.id=ass.item_id', 'left')
            ->join('collection_community cc', 'cc.id=ass.community_id', 'left')
            ->join('collection c', 'c.id=ass.collection_id', 'left')
            ->join('assess ess', 'ess.id=ass.assess_id', 'left')
            ->join('item_company ic', 'ic.id=ass.company_id', 'left')
            ->join('company cy', 'cy.id=ic.company_id', 'left')
            ->where('ass.id',$id)
            ->find();

        /*----- 入户评估-评估建筑物查询 -----*/
        $building_price = model('Assessestatebuildings')
            ->field(['id,building_id,price,amount'])
            ->where('estate_id', $id)
            ->where('assess_id', $assessestate_info->assess_id)
            ->select();
        /*----- 评估建筑物查询 -----*/
        $where['collection_id'] = $assessestate_info->collection_id;
        $field = ['cb.id', 'cb.item_id', 'cb.community_id', 'cb.collection_id', 'cb.building', 'cb.unit', 'cb.floor', 'cb.number',
            'cb.real_num', 'cb.real_unit', 'cb.use_id', 'cb.struct_id', 'cb.status_id', 'cb.build_year', 'cb.remark', 'cb.deleted_at', 'i.name as i_name', 'i.is_top',
            'cc.address', 'cc.name as cc_name', 'c.building as c_building', 'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number',
            'bu.name as bu_name', 'bs.name as bs_name', 's.name as s_name'];

        $collectionbuildings = model('Collectionbuildings')
            ->alias('cb')
            ->field($field)
            ->join('item i', 'i.id=cb.item_id', 'left')
            ->join('collection_community cc', 'cc.id=cb.community_id', 'left')
            ->join('collection c', 'c.id=cb.collection_id', 'left')
            ->join('building_use bu', 'bu.id=cb.use_id', 'left')
            ->join('building_struct bs', 'bs.id=cb.struct_id', 'left')
            ->join('building_status s', 's.id=cb.status_id', 'left')
            ->where($where)
            ->where('status_id','not in','0,5')
            ->order(['cb.register' => 'desc', 'cb.use_id' => 'asc'])
            ->select();
        $deff = count($collectionbuildings)-count($building_price);
        if($deff){
            return $this->error('有新的建筑被添加，请销毁本条数据重新添加评估','');
        }
        /*----- 建筑物表格 -----*/
        $options = '';
        $optionss = '';
        if($collectionbuildings){
            foreach ($collectionbuildings as $k => $v) {
                $options .= '<tr class="h50">';
                $options .= '<td style="text-align: left;background: none;width: inherit !important;"><input type="hidden" name="ids[' . $building_price[$k]->id . ']" value="' . $v['id'] . '">' . $v['id'] . '</td>';
                $options .= '<td class="nowrap"  style="text-align: left;background: none;">' . $v['address'] . '</td>';
                $options .= '<td style="text-align: center;background: none;">' . $v['bu_name'] . '</td>';
                $options .= '<td style="text-align: center;background: none;">' . $v['bs_name'] . '</td>';
                $options .= '<td style="text-align: center;background: none;">' . $v['s_name'] . '</td>';
                $options .= '<td style="text-align: left;background: none;">' . $v['real_num'] . '</td>';
                $options .= '<td style="text-align: center;background: none;">' . $v['real_unit'] . '</td>';
                $options .= '<td style="text-align: left;background: none;"><input type="text" name="price[' . $building_price[$k]->id . ']" class="price" value="' . $building_price[$k]->price . '" data-real_num="' . $v['real_num'] . '" data-id="' . $v['id'] . '" onkeyup="price_num(this)" onchange="price_num(this)"></td>';
                $options .= '<td style="text-align: left;background: none;">' . $v['remark'] . '</td>';
                $options .= '<td style="text-align: left;background: none;"><input type="text" name="amount[' . $building_price[$k]->id . ']" id="total-' . $v['id'] . '"  value="' . $building_price[$k]->amount . '" readonly></td>';
                $options .= '</tr>';
                $optionss .= '<tr class="h50">'; 
                $optionss .= '<td  class="more"><i class="iconfont icon-gongyongshuangjiantouxia"></i></td>'; 
                $optionss .= '<td><input type="hidden" name="ids[' . $building_price[$k]->id . ']" value="' . $v['id'] . '">' . $v['id'] .'</td>';
                $optionss .= '<td  class="nowrap" >' . $v['address'] .'</td>'; 
                $optionss .= '<td>' . $v['bu_name'] .'</td>'; 
                $optionss .= '</tr>'; 
                $optionss .= '<tr class="hide_more hide">'; 
                $optionss .= '<td colspan="6" style="padding:0 !important;">'; 
                $optionss .= ' <div class="table_more w_100 backCo_f21">'; 
                $optionss .= '<div class="flex w_100">'; 
                $optionss .= '<div class="w_30 align_right">结构</div><div  class="align_left">'. $v['bs_name'] .'</div>'; 
                $optionss .= '</div>'; 
                $optionss .= '<div class="flex w_100">'; 
                $optionss .= '<div class="w_30 align_right">状况</div><div  class="align_left">' . $v['s_name'] . '</div>'; 
                $optionss .= '</div>';
                $optionss .=    '<div class="flex w_100">';
                $optionss .=     '<div class="w_30 align_right">数量</div><div  class="align_left">' . $v['real_num'] . '</div>';
                $optionss .=     '</div>';
                $optionss .=    '<div class="flex w_100">';
                $optionss .=     '<div class="w_30 align_right">计量单位</div><div  class="align_left">' . $v['real_unit'] . '</div>';
                $optionss .=     '</div>';
                $optionss .=    '<div class="flex w_100">';
                $optionss .=     '<div class="w_30 align_right">单价</div><div  class="align_left"><input type="text" name="price[' . $v['id'] . ']" class="price" value="' . $building_price[$k]->price . '" data-real_num="' . $v['real_num'] . '" data-id="' . $v['id'] . '" onkeyup="price_num(this)"  onchange="price_num(this)"></div>';
                $optionss .=     '             </div>';
                $optionss .=    '             <div class="flex w_100">';
                $optionss .=     '                 <div class="w_30 align_right">总价</div><div  class="align_left"><input type="text" name="amount[' . $v['id'] . ']" id="total-' . $v['id'] . '"  value="' . $building_price[$k]->amount . '" readonly></div>';
                $optionss .=     '             </div>';
                $optionss .=    '         </div>';
                $optionss .=    '     </td>';
                $optionss .=    ' </tr>';
            }
        }


        /*----- 评估师查询 -----*/
        $assessestatevaluer_ids = model('Assessestatevaluers')
            ->where('collection_id', $assessestate_info['collection_id'])
            ->where('assess_id', $assessestate_info['assess_id'])
            ->where('estate_id', $assessestate_info['id'])
            ->where('company_id', $assessestate_info['company_id'])
            ->column('valuer_id');
        $company_valuer_where['id'] = array('in', $assessestatevaluer_ids);
        $company_valuer_where['status'] = '1';
        $company_valuer_field = ['id', 'name', 'register_num', 'valid_at'];
        $company_valuer = model('Companyvaluers')
            ->field($company_valuer_field)
            ->where($company_valuer_where)
            ->select();
        $valuer_ids = implode(",", $assessestatevaluer_ids);
        return view($this->theme.'/assessestate/modify',
            [
                'infos' => $assessestate_info,
                'company_valuer_info' => $company_valuer,
                'valuer_ids' => $valuer_ids,
                'options' => $options,
                'optionss' => $optionss,
                'item_id'=>$item_id
            ]);
    }

    /* ========== 修改 ========== */
    public function edit()
    {
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
        /* ++++++++++ 入户评估状态 ++++++++++ */
        $assess_status=Itemstatuss::where(['keyname'=>'assess_id','keyvalue'=>input('assess_id')])->order('created_at desc')->value('status');
        if($assess_status == 8){
            $msg='入户评估数据已审核通过，禁止操作！';
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }

        $datas = input();
        $rule = [
            ['report_at', 'require', '报告时间不能为空'],
            ['valued_at', 'require', '价值时点不能为空'],
            ['method', 'require', '评估方法不能为空'],
            ['picture', 'require', '评估报告不能为空']
        ];
        $result = $this->validate($datas, $rule);
        if (true !== $result) {
            return $this->error($result);
        }

        $building_info = $datas['price'];
        $values=array_filter(array_values($datas['price']));
        if(empty($values)){
            return $this->error('请输入评估单价','');
        }
        $ids = $datas['ids'];
        Db::startTrans();
        try {
            $assessestate_info = model('Assessestates')->field(['item_id,assess_id,id,collection_id,company_id'])->where('id', $datas['id'])->find();
            /*----- 修改房产评估--建筑评估 -----*/
            $building_data = [];
            $amount_nums = 0;
            if(request()->isMobile()){
                $building_datass = [];
                foreach ($building_info as $k => $v) {
                    $real_num = model('Collectionbuildings')->where('id', $k)->value('real_num');
                    $building_data[] = [
                        'id' => array_search($k, $ids),
                        'price' => $v,
                        'amount' => $real_num * $v
                    ];
                    if(count($building_info)=='1') {
                        $building_datass[] = [
                            'id' => array_search($k, $ids),
                            'price' => $v,
                            'amount' => $real_num * $v
                        ];
                    }
                    $amount_nums += $real_num * $v;

                }
                if(count($building_info)=='1'){
                    model('Assessestatebuildings')->saveAll($building_datass);
                }else{
                    model('Assessestatebuildings')->isUpdate(true)->saveAll($building_data);
                }
            }else{
                foreach ($building_info as $k => $v) {
                    $real_num = model('Collectionbuildings')->where('id', $ids[$k])->value('real_num');
                    $building_data[] = [
                        'id' => $k,
                        'price' => $v,
                        'amount' => $real_num * $v
                    ];
                    $amount_nums += $real_num * $v;
                }

                if(count($building_info)=='1'){
                    model('Assessestatebuildings')->saveAll($building_data);
                }else{
                    model('Assessestatebuildings')->isUpdate(true)->saveAll($building_data);
                }
            }


            /*----- 房产评估信息修改 -----*/
            model('Assessestates')->save(
                ['report_at' => $datas['report_at'],
                    'valued_at' => $datas['valued_at'],
                    'method' => $datas['method'],
                    'picture' => $datas['picture'],
                    'total' => $amount_nums
                ],
                ['id' => $datas['id']]
            );
            /*----- 修改房产评估--评估师 -----*/
            $assessestatevaluer_ids = model('Assessestatevaluers')
                ->where('collection_id', $assessestate_info['collection_id'])
                ->where('assess_id', $assessestate_info['assess_id'])
                ->where('estate_id', $assessestate_info['id'])
                ->where('company_id', $assessestate_info['company_id'])
                ->column('id');
            $valuer_idss = implode(",", $assessestatevaluer_ids);
            model('Assessestatevaluers')->where('id', 'in', $valuer_idss)->delete(true);
            $valuer_ids = explode(",", $datas['valuer_id']);
            $valuer_data = [];
            foreach ($valuer_ids as $k => $v) {
                $valuer_data[] = [
                    'item_id' => $assessestate_info['item_id'],
                    'collection_id' => $assessestate_info['collection_id'],
                    'assess_id' => $assessestate_info['assess_id'],
                    'estate_id' => $assessestate_info['id'],
                    'company_id' => $assessestate_info['company_id'],
                    'valuer_id' => $v
                ];
            }
            model('Assessestatevaluers')->saveAll($valuer_data);
            /*----- 查询入户评估总表 -----*/
            $search_assess = model('Assesss')
                ->where('item_id', $assessestate_info['item_id'])
                ->where('collection_id', $assessestate_info['collection_id'])
                ->value('id');
            /*----- 修改房产评估总额 -----*/
            model('Assesss')->save(['estate' => $amount_nums], ['id' => $search_assess]);
            $res = true;
            Db::commit();
        } catch (\Exception $e) {
            dump($e);
            $res = false;
            Db::rollback();
        }
        if ($res) {
            return $this->success('修改成功', '');
        } else {
            return $this->error('修改失败');
        }
    }

    /* ========== 状态 ========== */
    public function status()
    {
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
        /* ++++++++++ 入户评估状态 ++++++++++ */
        $assess_status=Itemstatuss::where(['keyname'=>'assess_id','keyvalue'=>input('assess_id')])->order('created_at desc')->value('status');
        if($assess_status == 8){
            $msg='入户评估数据已审核通过，禁止操作！';
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }
        $inputs = input();
        $id = isset($inputs['id']) ? $inputs['id'] : '';

        if (empty($id)) {
            return $this->error('至少选择一项');
        }
        $assessestate_info =  model('Assessestates')->field(['item_id','community_id','collection_id','assess_id','status'])->where('id',  $id)->find();
        if(!$assessestate_info){
           return  $this->error('数据异常，请检查后重试');
        }
        if($assessestate_info->getData('status')==0){
            Db::startTrans();
            try{
                model('Assessestates')->save(['status'=>0],['item_id' => $assessestate_info->item_id, 'community_id' => $assessestate_info->community_id, 'collection_id' => $assessestate_info->collection_id]);
                model('Assessestates')->allowField(['status', 'updated_at'])->save(['status'=>1],['id'=>$id]);
                $assessestatebuilding_amount =  model('Assessestatebuildings')->field(['amount'])->where(['estate_id' =>$id])->select();
                $amounts = 0;
                foreach ($assessestatebuilding_amount as $k=>$v){
                  $amounts+=$v['amount'];
                }
                model('Assesss')->save(['estate' => $amounts], ['id' => $assessestate_info->assess_id]);
                $res = true;
                Db::commit();
            }catch (\Exception $e){
                $res = false;
                Db::rollback();
            }
            if($res){
                return $this->success('修改成功','');
            }else{
                return $this->error('修改失败');
            }
        }else{
            return $this->error('当前房产评估不能被禁用');
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
        /* ++++++++++ 入户评估状态 ++++++++++ */
        $assess_status=Itemstatuss::where(['keyname'=>'assess_id','keyvalue'=>input('assess_id')])->order('created_at desc')->value('status');
        if($assess_status == 8){
            $msg='入户评估数据已审核通过，禁止操作！';
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
            $rs = model('Assessestates')->destroy(['id'=>['in',$ids]]);
            model('Assessestatebuildings')->destroy(['estate_id'=>['in',$ids]]);
            model('Assessestatevaluers')->destroy(['estate_id'=>['in',$ids]]);
            if($rs){
                $res=true;
                Db::commit();
            }else{
                $res=false;
                Db::rollback();
            }
        }catch (\Exception $e){
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
        /* ++++++++++ 入户评估状态 ++++++++++ */
        $assess_status=Itemstatuss::where(['keyname'=>'assess_id','keyvalue'=>input('assess_id')])->order('created_at desc')->value('status');
        if($assess_status == 8){
            $msg='入户评估数据已审核通过，禁止操作！';
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
            $rs = db('assess_estate')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_estate_building')->whereIn('estate_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_estate_valuer')->whereIn('estate_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            if($rs){
                $res=true;
                Db::commit();
            }else{
                $res=false;
                Db::rollback();
            }
        }catch (\Exception $e){
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
        /* ++++++++++ 入户评估状态 ++++++++++ */
        $assess_status=Itemstatuss::where(['keyname'=>'assess_id','keyvalue'=>input('assess_id')])->order('created_at desc')->value('status');
        if($assess_status == 8){
            $msg='入户评估数据已审核通过，禁止操作！';
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
            model('Assessestatebuildings')->withTrashed()->whereIn('estate_id',$ids)->delete(true);
            model('Assessestatevaluers')->withTrashed()->whereIn('estate_id',$ids)->delete(true);
            $rs = model('Assessestates')->onlyTrashed()->whereIn('id',$ids)->delete(true);
            if($rs){
                $res=true;
                Db::commit();
            }else{
                $res=false;
                Db::rollback();
            }
        }catch (\Exception $e){
            $res=false;
            Db::rollback();
        }
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }

    /* ========== 高拍仪页面 ========== */
    public function gaopaiyi(){
        return view($this->theme.'/assessestate/gaopaiyi');
    }
}