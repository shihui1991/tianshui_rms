<?php
/* |------------------------------------------------------
 * | 调查话题
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
use app\system\model\Topics;

class Topic extends Auth
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
        /* ++++++++++ 话题名称 ++++++++++ */
        $name = input('name');
        if($name){
           $where['name'] = array('like',"%$name%");
            $datas['name'] = $name;
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
        $topic_model = new Topics();
        $deleted = input('deleted');
        if (is_numeric($deleted) && in_array($deleted, [0, 1])) {
            $datas['deleted'] = $deleted;
            if ($deleted == 1) {
                $topic_model = $topic_model->onlyTrashed();
            }
        } else {
            $topic_model = $topic_model->withTrashed();
        }
        $topic_list = $topic_model
            ->where($where)
            ->order([$ordername => $orderby])
            ->paginate($display_num);
        $datas['topic_list'] = $topic_list;
        $this->assign($datas);
        return view($this->theme.'/topic/index');
    }

    /* ========== 添加 ========== */
    public function add()
    {
        if (request()->isPost()) {
            $datas = input();
            $rule = [
                ['name', 'require', '请输入话题内容']
            ];
            $result = $this->validate($datas, $rule);
            if (true !== $result) {
                return $this->error($result);
            }
            $rs = model('Topics')->save(['name'=>input('name')]);
            if ($rs) {
                return $this->success('添加成功', '');
            } else {
                return $this->error('添加失败', '');
            }
        } else {
            return view($this->theme.'/topic/modify');
        }
    }

    /* ========== 详情 ========== */
    public function detail()
    {
        $id = input('id');
        if (!$id) {
            return $this->error('至少选中一项', '');
        }
        $assessassets_info = model('Topics')
            ->withTrashed()
            ->where('id',$id)
            ->find();
        return view($this->theme.'/topic/modify', ['infos' => $assessassets_info]);
    }

    /* ========== 修改 ========== */
    public function edit()
    {
        $datas = input();
        $rule = [
            ['name', 'require', '请输入话题内容']
        ];
        $result = $this->validate($datas, $rule);
        if (true !== $result) {
            return $this->error($result);
        }
        $res = model('Topics')->save(['name'=>input('name')],['id'=>input('id')]);
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
        $res = model('Topics')->destroy(['id'=>['in',$ids]]);
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
        $res = db('topic')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res = model('Topics')->onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}