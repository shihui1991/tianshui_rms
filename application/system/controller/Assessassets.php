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
        $field = ['ass.id', 'i.name as item_name', 'cc.name as pq_name', 'c.building as c_building',
            'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number', 'c.id as c_id', 'cy.name as cy_name', 'ass.method', 'ass.valued_at', 'ass.status', 'ass.report_at', 'ass.deleted_at'];
        /* ++++++++++ 项目 ++++++++++ */
        $item_id = input('item_id');
        if (is_numeric($item_id)) {
            $where['ass.item_id'] = $item_id;
            $datas['item_id'] = $item_id;
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
            ->join('company cy', 'cy.id=ic.company_id', 'left')
            ->where($where)
            ->order(['i.is_top' => 'desc', 'ass.' . $ordername => $orderby])
            ->paginate($display_num);
        $datas['assessassets_list'] = $assessassets_list;
        $this->assign($datas);
        return view();
    }

    /* ========== 添加 ========== */
    public function add()
    {
        if (request()->isPost()) {
            $model = new Assessassetss();
            $datas = input();
            $rule = [
                ['item_id', 'require', '请选择项目'],
                ['community_id', 'require', '请选择片区'],
                ['collection_id', 'require', '请选择权属'],
                ['company_id', 'require', '请选择评估公司'],
                ['valuer_id', 'require', '请选择评估师'],
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
            $collections_count = model('Collections')->where('item_id', $datas['item_id'])->where('community_id', $datas['community_id'])->count();
            if ($collections_count == 0) {
                return $this->error('数据异常', '');
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
                $valuer_ids = explode(",", $datas['valuer_id']);
                $valuer_data = [];
                foreach ($valuer_ids as $k => $v) {
                    $valuer_data[] = [
                        'item_id' => $datas['item_id'],
                        'collection_id' => $datas['collection_id'],
                        'assess_id' => $assess_id,
                        'estate_id' => $assets_id,
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
            $items = model('Items')->field(['id', 'name', 'status'])->where('status', 1)->order('is_top desc')->select();
            /* ++++++++++ 片区 ++++++++++ */
            $collectioncommunitys = model('Collectioncommunitys')->field(['id', 'address', 'name'])->select();

            return view('add',
                ['items' => $items,
                    'collectioncommunitys' => $collectioncommunitys
                ]);
        }

    }

    /* ========== 详情 ========== */
    public function detail()
    {
        $id = input('id');
        if (!$id) {
            return $this->error('至少选中一项', '');
        }
        $assessassets_model = new Assessassetss();
        $where = [];
        $field = ['ass.id', 'ass.assess_id','ass.total', 'ass.collection_id', 'ass.company_id', 'i.name as item_name', 'cc.name as pq_name', 'c.building as c_building',
            'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number', 'c.id as c_id', 'cy.name as cy_name', 'ass.method', 'ass.valued_at', 'ass.status', 'ass.report_at', 'ass.picture'];

        $where['ass.id'] = $id;
        $assessassets_info = $assessassets_model
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
            ->where('estate_id', $id)
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
        return view('modify',
            [
                'infos' => $assessassets_info,
                'company_valuer_info' => $company_valuer,
                'valuer_ids' => $valuer_ids
            ]);
    }

    /* ========== 修改 ========== */
    public function edit()
    {
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
                ->where('estate_id', $assessassets_info['id'])
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
                    'estate_id' => $assessassets_info['id'],
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
        $inputs = input();
        $ids = isset($inputs['ids']) ? $inputs['ids'] : '';
        $status = input('status');

        if (empty($ids)) {
            return $this->error('至少选择一项');
        }
        if (!in_array($status, [0, 1])) {
            return $this->error('错误操作');
        }
        Db::startTrans();
        try {
            model('Assessassetss')->allowField(['status', 'updated_at'])->save(['status' => $status], ['id' => ['in', $ids]]);
            $rs = model('Assessassetss')->where('id', 'in', $ids)->select();
            $where = [];
            $new_array = [];
            foreach ($rs as $k => $v) {
                $where[] = ['item_id' => $v->item_id, 'community_id' => $v->community_id, 'collection_id' => $v->collection_id];
                $new_array[] = $v->item_id."-".$v->community_id."-".$v->collection_id;
            }
            $new_array = array_keys(array_unique($new_array));

            $new_where = [];
            for($i=0;$i<count($new_array);$i++){
                $new_where[] = $where[$new_array[$i]];
                $new_where[$i]['updated_at'] = time();
            }
            $res = false;
            $sqls =  batch_update_sql('assess',['updated_at','item_id','community_id','collection_id'],$new_where,'updated_at',['item_id','community_id','collection_id']);

            if($sqls){
                foreach ($sqls as $sql){
                    $res=db()->execute($sql);
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            $res = false;dump($e);
            Db::rollback();
        }
        if($res){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }
}