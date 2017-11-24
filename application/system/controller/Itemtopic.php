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
        $item_id = input('item_id');
        $datas['item_id'] = $item_id;

        $where['item_id'] = $item_id;
        if($item_id){
            $item_name = model('Items')->where('id',$item_id)->value('name');
        }else{
            $item_name = '';
        }
        $datas['item_name'] = $item_name;
        /* ++++++++++ 查询 ++++++++++ */
        $itemtopic_model = new Itemtopics();
        $itemtopic_list = $itemtopic_model
            ->alias('t')
            ->field(['t.*','i.name as item_name','p.name as topic_name'])
            ->join('item i', 'i.id=t.item_id', 'left')
            ->join('topic p','p.id=t.topic_id','left')
            ->where($where)
            ->order('created_at desc')
            ->paginate(config('paginate.list_rows'));
        $datas['itemtopic_list'] = $itemtopic_list;

        $this->assign($datas);
        return view();
    }

    /* ========== 添加 ========== */
    public function add()
    {
        $item_id = input('item_id');
        $this->assign('item_id',$item_id);
        if (request()->isPost()) {
            $datas = input();
            $rule = [
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
            $rs = model('Itemtopics')->save(['item_id'=>$datas['item_id'],'topic_id'=>$datas['topic_id']]);
            if ($rs) {
                return $this->success('添加成功', '');
            } else {
                return $this->error('添加失败', '');
            }
        } else {
            $item_name = model('Items')->where('id',$item_id)->value('name');
            /* ++++++++++ 话题列表 ++++++++++ */
            $topic = model('Topics')->field(['id', 'name'])->select();

            return view('modify',['item_name'=>$item_name,'topic'=>$topic]);
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
            ->alias('t')
            ->field(['t.*','i.name as item_name'])
            ->join('item i', 'i.id=t.item_id', 'left')
            ->where('t.id',$id)
            ->find();
       /* ++++++++++ 话题列表 ++++++++++ */
        $topic = model('Topics')->field(['id', 'name'])->select();

        return view('modify', ['infos' => $itemtopic_info,'topic'=>$topic]);
    }

    /* ========== 修改 ========== */
    public function edit()
    {
        $datas = input();
        $rule = [
            ['topic_id', 'require', '请选择风险评估话题']
        ];
        $result = $this->validate($datas, $rule);
        if (true !== $result) {
            return $this->error($result);
        }
        $res = model('Itemtopics')->save(['topic_id'=>$datas['topic_id']],['id'=>input('id')]);
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