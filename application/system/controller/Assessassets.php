<?php
/* |------------------------------------------------------
 * | 资产评估
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 状态
 * */
namespace app\system\controller;
use app\system\model\Assessassetss;
use app\system\model\Items;
use app\system\model\Itemstatuss;
use think\Db;

class Assessassets extends Auth
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
        $assessassets_model = new Assessassetss();
        $deleted = input('deleted');
        if (is_numeric($deleted) && in_array($deleted, [0, 1])) {
            $datas['deleted'] = $deleted;
            if ($deleted == 1) {
                $assessassets_model = $assessassets_model->onlyTrashed();
            }
        } else {
            $assessassets_model = $assessassets_model->withTrashed();
        }
        $assessassets_list = $assessassets_model
            ->alias('ass')
            ->field($field)
            ->join('item i', 'i.id=ass.item_id', 'left')
            ->join('collection_community cc', 'cc.id=ass.community_id', 'left')
            ->join('collection c', 'c.id=ass.collection_id', 'left')
            ->join('assess ess', 'ess.id=ass.assess_id', 'left')
            ->join('item_company ic', 'ic.id=ass.company_id', 'left')
            ->join('company cy', 'cy.id=ass.company_id', 'left')
            ->where($where)
            ->order(['i.is_top' => 'desc', 'ass.' . $ordername => $orderby])
            ->paginate($display_num);
        $datas['assessassets_list'] = $assessassets_list;

        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys = model('Collectioncommunitys')->field(['id', 'address', 'name'])->select();
        $datas['collectioncommunity_list'] = $collectioncommunitys;
        /* ++++++++++ 权属 ++++++++++ */
        $collections = model('Collections')->field(['id', 'building', 'unit','floor','number'])->select();
        $datas['collections_list'] = $collections;
        /* ++++++++++ 评估公司 ++++++++++ */
        $companys = model('Companys')->field(['id','name'])->where('status',1)->where('type',1)->select();
        $datas['company_list'] = $companys;
        $this->assign($datas);
        return view($this->theme.'/assessassets/'.$view);
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
        if (request()->isPost()) {
            $model = new Assessassetss();
            $datas = input();
            $rule = [
                ['item_id', 'require', '请选择项目'],
                ['community_id', 'require', '请选择片区'],
                ['collection_id', 'require', '请选择权属'],
                ['company_id', 'require', '请选择评估公司'],
                ['ids', 'require', '请选择评估师'],
                ['method', 'require', '评估方法不能为空'],
                ['total', 'require', '资产总额不能为空'],
                ['report_at', 'require', '报告时间不能为空'],
                ['valued_at', 'require', '价值时点不能为空'],
                ['picture', 'require', '评估报告不能为空']
            ];
            $result = $this->validate($datas, $rule);
            if (true !== $result) {
                return $this->error($result);
            }

            $collection_info=model('Collections')->field(['id','item_id','community_id'])->find(input('collection_id'));
            if(!$collection_info){
                return $this->error('选择权属不存在！');
            }
            if(input('item_id') != $collection_info->item_id || input('community_id') != $collection_info->community_id){
                return $this->error('选择权属与项目片区不一致');
            }
            $assessassets_count = model('Assessassetss')
                ->where('item_id', $datas['item_id'])
                ->where('community_id', $datas['community_id'])
                ->where('collection_id', $datas['collection_id'])
                ->where('company_id', $datas['company_id'])
                ->count();
            if ($assessassets_count) {
                return $this->error('数据重复,请不要重复添加', '');
            }

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
                model('Assessassetss')->save(['status'=>0],['assess_id' =>$assess_id]);
                /*----- 添加资产评估 -----*/
                $model->save([
                    'item_id' => $datas['item_id'],
                    'community_id' => $datas['community_id'],
                    'collection_id' => $datas['collection_id'],
                    'assess_id' => $assess_id,
                    'company_id' => $datas['company_id'],
                    'report_at' => $datas['report_at'],
                    'valued_at' => $datas['valued_at'],
                    'method' => $datas['method'],
                    'total' => $datas['total'],
                    'status' => 1,
                    'picture' => $datas['picture']
                ]);
                $assets_id = $model->getLastInsID();
                /*----- 添加资产评估--评估师 -----*/
                $valuer_ids = $datas['ids'];
                $valuer_data = [];
                foreach ($valuer_ids as $k => $v) {
                    $valuer_data[] = [
                        'item_id' => $datas['item_id'],
                        'collection_id' => $datas['collection_id'],
                        'assess_id' => $assess_id,
                        'assets_id' => $assets_id,
                        'company_id' => $datas['company_id'],
                        'valuer_id' => $v
                    ];
                }
                model('Assessassetsvaluers')->saveAll($valuer_data);
                /*----- 修改资产评估总额 -----*/
                model('Assesss')->save(['assets' => $datas['total']], ['id' => $assess_id]);
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
            $collection_id=input('collection_id');
            $where['c.id']=$collection_id;
            $field=['c.id','c.item_id','c.community_id','c.building','c.unit','c.floor','c.number','c.has_assets','c.status','cc.address','cc.name as cc_name'];
            $collections=model('Collections')
                ->alias('c')
                ->field($field)
                ->join('collection_community cc','cc.id=c.community_id','left')
                ->where($where)
                ->find();
            return view($this->theme.'/assessassets/add',
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
        $assessassets_model = new Assessassetss();
        $where = [];
        $field = ['ass.id','ass.created_at','ass.updated_at','ass.deleted_at', 'ass.assess_id','ass.total', 'ass.collection_id', 'ass.company_id', 'i.name as item_name', 'cc.name as pq_name', 'c.building as c_building',
            'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number', 'c.id as c_id', 'cy.name as cy_name', 'ass.method', 'ass.valued_at', 'ass.status', 'ass.report_at', 'ass.picture'];

        $where['ass.id'] = $id;
        $assessassets_info = $assessassets_model
            ->withTrashed()
            ->alias('ass')
            ->field($field)
            ->join('item i', 'i.id=ass.item_id', 'left')
            ->join('collection_community cc', 'cc.id=ass.community_id', 'left')
            ->join('collection c', 'c.id=ass.collection_id', 'left')
            ->join('assess ess', 'ess.id=ass.assess_id', 'left')
            ->join('item_company ic', 'ic.id=ass.company_id', 'left')
            ->join('company cy', 'cy.id=ic.company_id', 'left')
            ->where($where)
            ->find();

        /*----- 评估师查询 -----*/
        $assessassetsvaluer_ids = model('Assessassetsvaluers')
            ->where('collection_id', $assessassets_info['collection_id'])
            ->where('assess_id', $assessassets_info['assess_id'])
            ->where('assets_id', $id)
            ->where('company_id', $assessassets_info['company_id'])
            ->column('valuer_id');
        $company_valuer_where['id'] = array('in', $assessassetsvaluer_ids);
        $company_valuer_where['status'] = '1';
        $company_valuer_field = ['id', 'name', 'register_num', 'valid_at'];
        $company_valuer = model('Companyvaluers')
            ->field($company_valuer_field)
            ->where($company_valuer_where)
            ->select();
        $valuer_ids = implode(",", $assessassetsvaluer_ids);
        return view($this->theme.'/assessassets/modify',
            [
                'infos' => $assessassets_info,
                'company_valuer_info' => $company_valuer,
                'valuer_ids' => $valuer_ids,
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
            ['total', 'require', '资产总额不能为空'],
            ['picture', 'require', '评估报告不能为空']
        ];
        $result = $this->validate($datas, $rule);
        if (true !== $result) {
            return $this->error($result);
        }
        Db::startTrans();
        try {
            $assessassets_info = model('Assessassetss')->field(['item_id,assess_id,id,collection_id,company_id'])->where('id', $datas['id'])->find();
            /*----- 资产评估信息修改 -----*/
            model('Assessassetss')->save(
                ['report_at' => $datas['report_at'],
                    'valued_at' => $datas['valued_at'],
                    'method' => $datas['method'],
                    'picture' => $datas['picture'],
                    'total' => $datas['total']
                ],
                ['id' => $datas['id']]
            );
            /*----- 修改资产评估--评估师 -----*/
            $assessassetsvaluer_ids = model('Assessassetsvaluers')
                ->where('collection_id', $assessassets_info['collection_id'])
                ->where('assess_id', $assessassets_info['assess_id'])
                ->where('assets_id', $assessassets_info['id'])
                ->where('company_id', $assessassets_info['company_id'])
                ->column('id');
            $valuer_idss = implode(",", $assessassetsvaluer_ids);
            model('Assessassetsvaluers')->where('id', 'in', $valuer_idss)->delete(true);
            $valuer_ids = explode(",", $datas['valuer_id']);
            $valuer_data = [];
            foreach ($valuer_ids as $k => $v) {
                $valuer_data[] = [
                    'item_id' => $assessassets_info['item_id'],
                    'collection_id' => $assessassets_info['collection_id'],
                    'assess_id' => $assessassets_info['assess_id'],
                    'assets_id' => $assessassets_info['id'],
                    'company_id' => $assessassets_info['company_id'],
                    'valuer_id' => $v
                ];
            }
            model('Assessassetsvaluers')->saveAll($valuer_data);
            /*----- 查询入户评估总表 -----*/
            $search_assess = model('Assesss')
                ->where('item_id', $assessassets_info['item_id'])
                ->where('collection_id', $assessassets_info['collection_id'])
                ->value('id');
            /*----- 修改资产评估总额 -----*/
            model('Assesss')->save(['assets' => $datas['total']], ['id' => $search_assess]);
            $res = true;
            Db::commit();
        } catch (\Exception $e) {
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
        $assessassets_info =  model('Assessassetss')
            ->field(['item_id','community_id','collection_id','assess_id','status','total'])
            ->where('id',  $id)
            ->find();
        if(!$assessassets_info){
            return  $this->error('数据异常，请检查后重试');
        }
        if($assessassets_info->getData('status')==0){
            Db::startTrans();
            try{
                model('Assessassetss')->save(['status'=>0],['item_id' => $assessassets_info->item_id, 'community_id' => $assessassets_info->community_id, 'collection_id' => $assessassets_info->collection_id]);
                model('Assessassetss')->allowField(['status', 'updated_at'])->save(['status'=>1],['id'=>$id]);

                model('Assesss')->save(['assets' => $assessassets_info->total], ['id' => $assessassets_info->assess_id]);
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
            return $this->error('当前资产评估不能被禁用');
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
            $rs = model('Assessassetss')->destroy(['id'=>['in',$ids]]);
            model('Assessassetsvaluers')->destroy(['assets_id'=>['in',$ids]]);
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
            $rs = db('assess_assets')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_assets_valuer')->whereIn('assets_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
            model('Assessassetsvaluers')->withTrashed()->whereIn('assets_id',$ids)->delete(true);
            $rs = model('Assessassetss')->onlyTrashed()->whereIn('id',$ids)->delete(true);
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
        return view($this->theme.'/assessassets/gaopaiyi');
    }
}