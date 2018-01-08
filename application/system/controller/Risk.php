<?php
/* |------------------------------------------------------
 * | 风险评估
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
use app\system\model\Risks;
use think\Db;
use think\Exception;

class Risk extends Auth
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
            'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number', 'c.id as c_id','ch.name as holder_name','chr.name as recommemd_holder_name'];
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
        $risk_model = new Risks();
        $deleted = input('deleted');
        if (is_numeric($deleted) && in_array($deleted, [0, 1])) {
            $datas['deleted'] = $deleted;
            if ($deleted == 1) {
                $risk_model = $risk_model->onlyTrashed();
            }
        } else {
            $risk_model = $risk_model->withTrashed();
        }
        $risk_list = $risk_model
            ->alias('ass')
            ->field($field)
            ->join('item i', 'i.id=ass.item_id', 'left')
            ->join('collection_community cc', 'cc.id=ass.community_id', 'left')
            ->join('collection c', 'c.id=ass.collection_id', 'left')
            ->join('collection_holder ch', 'ch.id=ass.holder_id', 'left')
            ->join('collection_holder chr', 'chr.id=ass.recommemd_holder_id', 'left')
            ->where($where)
            ->order(['i.is_top' => 'desc', 'ass.' . $ordername => $orderby])
            ->paginate($display_num);
        $datas['risk_list'] = $risk_list;

        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys = model('Collectioncommunitys')->field(['id', 'address', 'name'])->select();
        $datas['collectioncommunity_list'] = $collectioncommunitys;
        /* ++++++++++ 权属 ++++++++++ */
        $collections = model('Collections')->field(['id', 'building', 'unit','floor','number'])->select();
        $datas['collections_list'] = $collections;
        $this->assign($datas);
        return view($this->theme.'/risk/'.$view);
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
                ['community_id', 'require', '请选择片区'],
                ['collection_id', 'require', '请选择权属'],
                ['holder_id', 'require', '请选择成员'],
                ['deputy', 'require', '请选择群众代表意见'],
                ['recommemd_holder_id', 'require', '请选择推荐代表成员'],
                ['is_agree', 'require', '请选择方案意见'],
                ['compensate_way', 'require', '请选择补偿方式'],
                ['transit_way', 'require', '请选择过渡方式'],
                ['move_way', 'require', '请选择搬迁方式'],
                ['topic_id', 'require', '项目话题不存在']
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
            $sear_rickid = model('Risks')
                ->where('item_id',$datas['item_id'])
                ->where('community_id',$datas['community_id'])
                ->where('collection_id',$datas['collection_id'])
                ->where('holder_id',$datas['holder_id'])
                ->count();
            if($sear_rickid){
                return $this->error('数据重复,只能参加一次评估','');
            }
            Db::startTrans();
            try{
                $rs = model('Risks')->save($datas);
                $risk_id = model('Risks')->getLastInsID();
                $topic_count = model('Itemtopics')->where('item_id',$datas['item_id'])->count();
                if($topic_count){
                    $risktopic_datas = [];
                    for ($i=0;$i<$topic_count;$i++){
                        $risktopic_datas[$i]['item_id'] = $datas['item_id'];
                        $risktopic_datas[$i]['community_id'] = $datas['community_id'];
                        $risktopic_datas[$i]['collection_id'] = $datas['collection_id'];
                        $risktopic_datas[$i]['holder_id'] = $datas['holder_id'];
                        $risktopic_datas[$i]['risk_id'] = $risk_id;
                        $risktopic_datas[$i]['topic_id'] = $datas['topic_id'][$i];
                        $risktopic_datas[$i]['answer'] = $datas['answer'][$i];
                    }
                    model('Risktopics')->saveAll($risktopic_datas);
                }

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
            if ($res) {
                return $this->success('添加成功', '');
            } else {
                return $this->error('添加失败', '');
            }
        } else {
            /* ++++++++++ 项目列表 ++++++++++ */
            $items = model('Items')->field(['id', 'name', 'status'])->where('id', $item_id)->find();
            /* ++++++++++ 片区 ++++++++++ */
            $collectioncommunitys = model('Collectioncommunitys')->field(['id', 'address', 'name'])->select();
            return view($this->theme.'/risk/add',[
                'item_info' => $items,
                'collectioncommunitys' => $collectioncommunitys]);
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $item_id = input('item_id');
        $id = input('id');
        if(!$id){
            return $this->error('至少选中一项','');
        }
        $where['ass.id'] = $id;
        $field = ['ass.*', 'i.name as item_name', 'cc.name as pq_name', 'c.building as c_building',
            'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number', 'c.id as c_id','ch.name as holder_name','chr.name as recommemd_holder_name'];

        $risk_info = model('Risks')
            ->withTrashed()
            ->alias('ass')
            ->field($field)
            ->join('item i', 'i.id=ass.item_id', 'left')
            ->join('collection_community cc', 'cc.id=ass.community_id', 'left')
            ->join('collection c', 'c.id=ass.collection_id', 'left')
            ->join('collection_holder ch', 'ch.id=ass.holder_id', 'left')
            ->join('collection_holder chr', 'chr.id=ass.recommemd_holder_id', 'left')
            ->where($where)
            ->find();
        $risktopic_infos = model('Risktopics')
            ->alias('rt')
            ->field(['rt.id','rt.topic_id','rt.answer','t.name as topic_name'])
            ->join('topic t','t.id = rt.topic_id','left')
            ->where('rt.risk_id',$id)
            ->select();
        $topic_ids = [];
        foreach ($risktopic_infos as $k=>$v){
            $topic_ids[] = $v->topic_id;
        }
        $item_topic = model('Itemtopics')
            ->alias('rt')
            ->field(['rt.topic_id','t.name as topic_name'])
            ->join('topic t','t.id = rt.topic_id','left')
            ->where('rt.item_id',$item_id)
            ->where('rt.topic_id','not in',$topic_ids)
            ->select();
        $risktopic_count = count($risktopic_infos);
        return view($this->theme.'/risk/modify',[
            'infos'=>$risk_info,
            'item_id'=>$item_id,
            'risktopic_infos'=>$risktopic_infos,
            'item_topic'=>$item_topic,
            'risktopic_count'=>$risktopic_count
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
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

        $datas = input();
        $rule = [
            ['deputy', 'require', '请选择群众代表意见'],
            ['is_agree', 'require', '请选择方案意见'],
            ['compensate_way', 'require', '请选择补偿方式'],
            ['transit_way', 'require', '请选择过渡方式'],
            ['move_way', 'require', '请选择搬迁方式']
        ];
        $result = $this->validate($datas, $rule);
        if (true !== $result) {
            return $this->error($result);
        }
        Db::startTrans();
        try{
            if(isset($datas['new_topic_id'])){
                $risk_data =  model('Risks')
                    ->field(['item_id','community_id','collection_id','holder_id'])
                    ->where('id',$datas['id'])
                    ->find();
                $new_topic_data = [];
                  foreach ($datas['new_topic_id'] as $k=>$v){
                      $new_topic_data[$k]['item_id'] = $risk_data->item_id;
                      $new_topic_data[$k]['community_id'] = $risk_data->community_id;
                      $new_topic_data[$k]['collection_id'] = $risk_data->collection_id;
                      $new_topic_data[$k]['holder_id'] = $risk_data->holder_id;
                      $new_topic_data[$k]['risk_id'] = $datas['id'];
                      $new_topic_data[$k]['topic_id'] = $v;
                      $new_topic_data[$k]['answer'] = $datas['new_answer'][$k];
                  }
                $risktopic_rs = model('Risktopics')->saveAll($new_topic_data);
                if(!$risktopic_rs){
                    throw new \think\Exception('数据异常，请刷新重试', 100006);
                }
            }
            $rs =  model('Risks')->save([
                'deputy'=>$datas['deputy'],
                'is_agree'=>$datas['is_agree'],
                'compensate_way'=>$datas['compensate_way'],
                'compensate_price'=>$datas['compensate_price'],
                'transit_way'=>$datas['transit_way'],
                'move_way'=>$datas['move_way'],
                'opinion'=>$datas['opinion']
            ],['id'=>$datas['id']]);
            foreach ($datas['risktopic_id'] as $k=>$v){
                model('Risktopics')->isUpdate(true)->save(['answer'=>$datas['answer'][$k]],['id'=>$v]);
            }
            if(!$rs){
                throw new \think\Exception('数据异常，请刷新重试', 100006);
            }else{
                $msg = '修改成功';
                $res = true;
                Db::commit();
            }
        }catch (\Exception $e){
            if($e->getCode()==100006){
                $msg = $e->getMessage();
            }else{
                $msg = '网络异常,请稍后重试';
            }
            $res = false;
            Db::rollback();
        }
        if($res){
            return $this->success($msg,'');
        }else{
            return $this->error($msg,'');
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
        Db::startTrans();
        try{
            $rs = model('Risks')->destroy(['id'=>['in',$ids]]);
            model('Risktopics')->destroy(['risk_id'=>['in',$ids]]);
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

        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        Db::startTrans();
        try{
            $rs = db('risk')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('risk_topic')->whereIn('risk_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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

        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        Db::startTrans();
        try{
            $rs = model('Risks')->onlyTrashed()->whereIn('id',$ids)->delete(true);
            model('Risktopics')->withTrashed()->whereIn('risk_id',$ids)->delete(true);

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
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}