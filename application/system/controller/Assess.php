<?php
/* |------------------------------------------------------
 * | 入户评估
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;
use app\system\model\Assesss;
use app\system\model\Items;
use app\system\model\Itemstatuss;
use think\Db;
use think\Exception;

class Assess extends Auth
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
        $field = ['ass.*', 'i.name as item_name', 'cc.name as pq_name','c.building as c_building',
            'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number','c.id as c_id'];
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
        $assess_model = new Assesss();
        $deleted = input('deleted');
        if (is_numeric($deleted) && in_array($deleted, [0, 1])) {
            $datas['deleted'] = $deleted;
            if ($deleted == 1) {
                $assess_model = $assess_model->onlyTrashed();
            }
        } else {
            $assess_model = $assess_model->withTrashed();
        }
        $assess_list = $assess_model
            ->alias('ass')
            ->field($field)
            ->join('item i', 'i.id=ass.item_id', 'left')
            ->join('collection_community cc', 'cc.id=ass.community_id', 'left')
            ->join('collection c', 'c.id=ass.collection_id', 'left')
            ->where($where)
            ->order(['i.is_top' => 'desc', 'ass.' . $ordername => $orderby])
            ->paginate($display_num);
        $datas['assess_list'] = $assess_list;

        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys = model('Collectioncommunitys')->field(['id', 'address', 'name'])->select();
        $datas['collectioncommunity_list'] = $collectioncommunitys;
        /* ++++++++++ 权属 ++++++++++ */
        $collections = model('Collections')->field(['id', 'building', 'unit','floor','number'])->select();
        $datas['collections_list'] = $collections;
        $this->assign($datas);
        return view($this->theme.'/assess/'.$view);
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
                ['collection_id', 'require', '请选择权属']
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
            $assess_count = model('Assesss')
                ->where('item_id', $datas['item_id'])
                ->where('community_id', $datas['community_id'])
                ->where('collection_id', $datas['collection_id'])
                ->count();
            if ($assess_count) {
                return $this->error('数据重复,请不要重复添加', '');
            }

            Db::startTrans();
            try{
                $rs = model('Assesss')->save([
                    'item_id'=>$datas['item_id'],
                    'community_id'=>$datas['community_id'],
                    'collection_id'=>$datas['collection_id']
                ]);
                $status_data=[
                    'keyname'=>'assess_id',
                    'keyvalue'=>model('Assesss')->id,
                    'user_id'=>session('userinfo.user_id'),
                    'role_id'=>session('userinfo.role_id'),
                    'role_parent_id'=>session('userinfo.role_parent_id'),
                    'status'=>0
                ];

                $status_model=new Itemstatuss();
                $status_model->save($status_data);
                if(!$rs){
                    $res = false;
                    Db::rollback();
                }else{
                    $res = true;
                    Db::commit();
                }
            }catch(\Exception $e){
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

            return view($this->theme.'/assess/add',
                ['item_info' => $items,
                    'collectioncommunitys' => $collectioncommunitys
                ]);
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
        if(is_array($ids)){
            $ids=implode(',',$ids);
        }

        Db::startTrans();
        try{
            $lists=db()->query('SELECT * FROM (SELECT * FROM `item_status` WHERE `keyname`=\'assess_id\' AND `keyvalue` IN ('.$ids.') ORDER BY `created_at` DESC) cs GROUP BY `keyvalue`');
            $status_data=[];
            foreach ($lists as $list){
                if($list['status']==8){
                    throw new Exception('勾选存在审核通过项，删除失败！');
                    break;
                }
                $status_data[]=[
                    'keyname'=>'assess_id',
                    'keyvalue'=>$list['keyvalue'],
                    'user_id'=>session('userinfo.user_id'),
                    'role_id'=>session('userinfo.role_id'),
                    'role_parent_id'=>session('userinfo.role_parent_id'),
                    'status'=>2
                ];
            }
            $status_model=new Itemstatuss();
            $status_model->saveAll($status_data);

            $rs = model('Assesss')->destroy(['id'=>['in',$ids]]);
                model('Assessestates')->destroy(['assess_id'=>['in',$ids]]);
                model('Assessestatebuildings')->destroy(['assess_id'=>['in',$ids]]);
                model('Assessestatevaluers')->destroy(['assess_id'=>['in',$ids]]);
                model('Assessassetss')->destroy(['assess_id'=>['in',$ids]]);
                model('Assessassetsvaluers')->destroy(['assess_id'=>['in',$ids]]);
            if($rs){
                $res=true;
                $msg='删除成功';
                Db::commit();
            }else{
                $res=false;
                $msg='删除失败';
                Db::rollback();
            }
        }catch (\Exception $e){
            $res=false;
            $msg=$e->getMessage();
            Db::rollback();
        }
        if($res){
            return $this->success($msg,'');
        }else{
            return $this->error($msg);
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
        if(is_string($ids)){
            $ids=[$ids];
        }
        Db::startTrans();
        try{
            $del_ids = Assesss::onlyTrashed()->whereIn('id',$ids)->column('id');
            if(!$del_ids){
                throw new Exception('请选择已删除数据！');
            }
            $status_data=[];
            foreach ($del_ids as $id){
                $status_data[]=[
                    'keyname'=>'assess_id',
                    'keyvalue'=>$id,
                    'user_id'=>session('userinfo.user_id'),
                    'role_id'=>session('userinfo.role_id'),
                    'role_parent_id'=>session('userinfo.role_parent_id'),
                    'status'=>3
                ];
            }
            $status_model=new Itemstatuss();
            $status_model->saveAll($status_data);
            $rs =  Assesss::onlyTrashed()->whereIn('id',$del_ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_estate')->whereIn('assess_id',$del_ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_estate_building')->whereIn('assess_id',$del_ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_estate_valuer')->whereIn('assess_id',$del_ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_assets')->whereIn('assess_id',$del_ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_assets_valuer')->whereIn('assess_id',$del_ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            if($rs){
                $res=true;
                $msg = '恢复成功';
                Db::commit();
            }else{
                $res=false;
                $msg = '请选择已删除数据';
                Db::rollback();
            }
        }catch (\Exception $e){
            $res=false;
            $msg=$e->getMessage();
            Db::rollback();
        }
        if($res){
            return $this->success($msg,'');
        }else{
            return $this->error($msg);
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
            $ids=Assesss::onlyTrashed()->whereIn('id',$ids)->column('id');
            if(!$ids){
                throw new Exception('只能销毁已删除的数据！');
            }
            $status_data=[];
            foreach ($ids as $id){
                $status_data[]=[
                    'keyname'=>'assess_id',
                    'keyvalue'=>$id,
                    'user_id'=>session('userinfo.user_id'),
                    'role_id'=>session('userinfo.role_id'),
                    'role_parent_id'=>session('userinfo.role_parent_id'),
                    'status'=>4
                ];
            }
            $status_model=new Itemstatuss();
            $status_model->saveAll($status_data);

            model('Assessestates')->withTrashed()->whereIn('assess_id',$ids)->delete(true);
            model('Assessestatebuildings')->withTrashed()->whereIn('assess_id',$ids)->delete(true);
            model('Assessestatevaluers')->withTrashed()->whereIn('assess_id',$ids)->delete(true);
            model('Assessassetss')->withTrashed()->whereIn('assess_id',$ids)->delete(true);
            model('Assessassetsvaluers')->withTrashed()->whereIn('assess_id',$ids)->delete(true);
           $rs = model('Assesss')->onlyTrashed()->whereIn('id',$ids)->delete(true);
            if($rs){
                $res=true;
                $msg='销毁成功';
                Db::commit();
            }else{
                $res=false;
                $msg='销毁失败';
                Db::rollback();
            }
        }catch (\Exception $e){
            $res=false;
            $msg=$e->getMessage();
            Db::rollback();
        }
        if($res){
            return $this->success($msg,'');
        }else{
            return $this->error($msg);
        }
    }
    /* ========== 状态 ========== */
    public function status(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }

        $datas['id']=$id;
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        $datas['item_info']=$item_info;

        $model=new Itemstatuss();
        $datas['model']=$model;

        $statuss=$model->with('user,role')->where(['keyname'=>'assess_id','keyvalue'=>$id])->order('created_at asc')->paginate();
        $datas['statuss']=$statuss;

        $this->assign($datas);

        return view($this->theme.'/assess/status');
    }
    /* ========== 审核 ========== */
    public function check(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if($item_info->getData('status') ==2){
            $msg='项目已完成，禁止操作！';
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }
        $id=input('id');
        if(!$id){
            return $this->error('错误操作','');
        }
        $check=input('check');
        if(!in_array($check,[8,9])){
            return $this->error('错误操作','');
        }
        Db::startTrans();
        try{
            $last_status=Itemstatuss::where(['keyname'=>'assess_id','keyvalue'=>$id])->order('created_at desc')->find();
            if(!$last_status){
                throw new Exception('数据异常！');
            }
            if($last_status->role_parent_id!=session('userinfo.role_id') && $last_status->role_parent_id!=session('userinfo.role_parent_id')){
                throw new Exception('审核流程已超出权限！');
            }
            $status_model=new Itemstatuss();
            $status_data=[
                'keyname'=>'assess_id',
                'keyvalue'=>$id,
                'user_id'=>session('userinfo.user_id'),
                'role_id'=>session('userinfo.role_id'),
                'role_parent_id'=>session('userinfo.role_parent_id'),
                'status'=>$check
            ];
            $status_model->save($status_data);

            $res=true;
            $msg='操作成功';
            Db::commit();
        }catch (\Exception $exception){
            $msg=$exception->getMessage();
            $res=false;
            Db::rollback();
        }

        if($res){
            return $this->success($msg,'');
        }else{
            return $this->error($msg,'');
        }
    }

}