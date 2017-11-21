<?php
/* |------------------------------------------------------
 * | 项目风险评估话题
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
use app\system\model\Itemtopics;

class Itemtopic extends Auth
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
        /* ++++++++++ 项目 ++++++++++ */
        $item_id = input('item_id');
        if (is_numeric($item_id)) {
            $where['t.item_id'] = $item_id;
            $datas['item_id'] = $item_id;
        }
        /* ++++++++++ 话题 ++++++++++ */
        $topic_id = input('topic_id');
        if (is_numeric($topic_id)) {
            $where['t.topic_id'] = $topic_id;
            $datas['topic_id'] = $topic_id;
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
        $itemtopic_model = new Itemtopics();
        $deleted = input('deleted');
        if (is_numeric($deleted) && in_array($deleted, [0, 1])) {
            $datas['deleted'] = $deleted;
            if ($deleted == 1) {
                $itemtopic_model = $itemtopic_model->onlyTrashed();
            }
        } else {
            $itemtopic_model = $itemtopic_model->withTrashed();
        }
        $itemtopic_list = $itemtopic_model
            ->alias('t')
            ->field(['t.*','i.name as item_name','p.name as topic_name'])
            ->join('item i', 'i.id=t.item_id', 'left')
            ->join('topic p','p.id=t.topic_id','left')
            ->where($where)
            ->order([$ordername => $orderby])
            ->paginate($display_num);
        $datas['itemtopic_list'] = $itemtopic_list;

        /* ++++++++++ 项目列表 ++++++++++ */
        $items = model('Items')->field(['id', 'name', 'status'])->where('status', 1)->order('is_top desc')->select();
        $datas['item_list'] = $items;
        /* ++++++++++ 话题列表 ++++++++++ */
        $topics = model('Topics')->field(['id', 'name'])->select();
        $datas['topic_list'] = $topics;
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
                ['topic_id', 'require', '请选择风险评估话题']
            ];
            $result = $this->validate($datas, $rule);
            if (true !== $result) {
                return $this->error($result);
            }
            $item_topic_count = model('Itemtopics')
                ->where('item_id',$datas['item_id'])
                ->where('topic_id',$datas['topic_id'])
                ->count();
            if($item_topic_count){
                return $this->error('数据重复，请确认后再添加','');
            }
            $rs = model('Itemtopics')->save($datas);
            if ($rs) {
                return $this->success('添加成功', '');
            } else {
                return $this->error('添加失败', '');
            }
        } else {
            /* ++++++++++ 项目列表 ++++++++++ */
            $items = model('Items')->field(['id', 'name', 'status'])->where('status', 1)->order('is_top desc')->select();

            /* ++++++++++ 话题列表 ++++++++++ */
            $topic = model('Topics')->field(['id', 'name'])->select();

            return view('modify',['items'=>$items,'topic'=>$topic]);
        }
    }

    /* ========== 详情 ========== */
    public function detail()
    {
        $id = input('id');
        if (!$id) {
            return $this->error('至少选中一项', '');
        }
        $itemtopic_info = model('Itemtopics')
            ->withTrashed()
            ->where('id',$id)
            ->find();
        /* ++++++++++ 项目列表 ++++++++++ */
        $items = model('Items')->field(['id', 'name', 'status'])->where('status', 1)->order('is_top desc')->select();
        /* ++++++++++ 话题列表 ++++++++++ */
        $topic = model('Topics')->field(['id', 'name'])->select();

        return view('modify', ['infos' => $itemtopic_info,'items'=>$items,'topic'=>$topic]);
    }

    /* ========== 修改 ========== */
    public function edit()
    {
        $datas = input();
        $rule = [
            ['item_id', 'require', '请选择项目'],
            ['topic_id', 'require', '请选择风险评估话题']
        ];
        $result = $this->validate($datas, $rule);
        if (true !== $result) {
            return $this->error($result);
        }
        $res = model('Itemtopics')->save($datas,['id'=>input('id')]);
        if ($res) {
            return $this->success('修改成功', '');
        } else {
            return $this->error('修改失败');
        }
    }

    /* ========== 删除 ========== */
    public function delete(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res = model('Itemtopics')->destroy($ids);
        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }

    /* ========== 恢复 ========== */
    public function restore(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res = db('item_topic')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
        if($res){
            return $this->success('恢复成功','');
        }else{
            return $this->error('恢复失败');
        }
    }

    /* ========== 销毁 ========== */
    public function destroy(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res = model('Itemtopics')->onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}