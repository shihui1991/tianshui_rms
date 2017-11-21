<?php
/* |------------------------------------------------------
 * | 风险评估话题结果
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


use app\system\model\Risktopics;

class Risktopic extends Auth
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
        $field = ['ass.*', 'i.name as item_name', 'cc.name as pq_name', 'c.building as c_building',
            'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number', 'c.id as c_id','ch.name as holder_name','tc.name as topic_name'];
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
        $risktopic_model = new Risktopics();
        $deleted = input('deleted');
        if (is_numeric($deleted) && in_array($deleted, [0, 1])) {
            $datas['deleted'] = $deleted;
            if ($deleted == 1) {
                $risktopic_model = $risktopic_model->onlyTrashed();
            }
        } else {
            $risktopic_model = $risktopic_model->withTrashed();
        }
        $risktopic_list = $risktopic_model
            ->alias('ass')
            ->field($field)
            ->join('item i', 'i.id=ass.item_id', 'left')
            ->join('collection_community cc', 'cc.id=ass.community_id', 'left')
            ->join('collection c', 'c.id=ass.collection_id', 'left')
            ->join('collection_holder ch', 'ch.id=ass.holder_id', 'left')
            ->join('topic tc', 'tc.id=ass.topic_id', 'left')
            ->where($where)
            ->order(['i.is_top' => 'desc', 'ass.' . $ordername => $orderby])
            ->paginate($display_num);
        $datas['risktopic_list'] = $risktopic_list;
        $this->assign($datas);
        return view();
    }

    /* ========== 添加 ========== */
    public function add()
    {
        if (request()->isPost()) {
            $datas = input();
            $rule = [
                ['item_id', 'require', '请选择项目'],
                ['community_id', 'require', '请选择片区'],
                ['collection_id', 'require', '请选择权属'],
                ['holder_id', 'require', '请选择成员']
            ];
            $result = $this->validate($datas, $rule);
            if (true !== $result) {
                return $this->error($result);
            }
            $rs = model('Risktopics')->save($datas);
            if ($rs) {
                return $this->success('添加成功', '');
            } else {
                return $this->error('添加失败', '');
            }
        } else {
            /* ++++++++++ 项目列表 ++++++++++ */
            $items = model('Items')->field(['id', 'name', 'status'])->where('status', 1)->order('is_top desc')->select();
            /* ++++++++++ 片区 ++++++++++ */
            $collectioncommunitys = model('Collectioncommunitys')->field(['id', 'address', 'name'])->select();
            return view('add',[
                'items' => $items,
                'collectioncommunitys' => $collectioncommunitys]);
        }
    }
}