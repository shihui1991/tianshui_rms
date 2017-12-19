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
use app\system\model\Items;
use app\system\model\Itemtopics;
use think\Db;

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
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目信息 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        $datas['item_info']=$item_info;
        $where['item_id']=$item_id;


        /* ++++++++++ 查询 ++++++++++ */
        $itemtopic_model = new Itemtopics();
        $itemtopic_list = $itemtopic_model
            ->alias('t')
            ->field(['t.*','i.name as item_name','p.name as topic_name'])
            ->join('item i', 'i.id=t.item_id', 'left')
            ->join('topic p','p.id=t.topic_id','left')
            ->where($where)
            ->paginate();
        $datas['itemtopic_list'] = $itemtopic_list;

        /* ++++++++++ 话题列表 ++++++++++ */
        $topics = model('Topics')->field(['id', 'name'])->select();
        $datas['topic_list'] = $topics;
        $this->assign($datas);
        return view($this->theme.'/itemtopic/index');
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
            $rs = model('Itemtopics')->save($datas);
            if ($rs) {
                return $this->success('添加成功', '');
            } else {
                return $this->error('添加失败', '');
            }
        } else {

            /* ++++++++++ 话题列表 ++++++++++ */
            $topic = model('Topics')->field(['id', 'name'])->select();

            return view($this->theme.'/itemtopic/modify',['item_info'=>$item_info,'topic'=>$topic]);
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


        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res = model('Itemtopics')->whereIn('id',$ids)->delete();
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}