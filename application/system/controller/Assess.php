<?php
/* |------------------------------------------------------
 * | 入户评估
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;
use app\system\model\Assesss;
use think\Db;

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
        $field = ['ass.id', 'i.name as item_name', 'cc.name as pq_name','c.building as c_building',
            'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number','c.id as c_id','ass.estate','ass.assets','ass.deleted_at'];
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

        /* ++++++++++ 项目列表 ++++++++++ */
        $items = model('Items')->field(['id', 'name', 'status'])->where('status', 1)->order('is_top desc')->select();
        $datas['item_list'] = $items;
        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys = model('Collectioncommunitys')->field(['id', 'address', 'name'])->select();
        $datas['collectioncommunity_list'] = $collectioncommunitys;
        /* ++++++++++ 权属 ++++++++++ */
        $collections = model('Collections')->field(['id', 'building', 'unit','floor','number'])->select();
        $datas['collections_list'] = $collections;
        $this->assign($datas);
        return view();
    }

    /* ========== 删除 ========== */
    public function delete(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        Db::startTrans();
        try{
            $rs = model('Assesss')->destroy($ids);
                model('Assessestates')->destroy(['estate_id'=>['in',$ids]]);
                model('Assessestatebuildings')->destroy(['estate_id'=>['in',$ids]]);
                model('Assessestatevaluers')->destroy(['estate_id'=>['in',$ids]]);
                model('Assessassetss')->destroy(['estate_id'=>['in',$ids]]);
                model('Assessassetsvaluers')->destroy(['estate_id'=>['in',$ids]]);
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
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        Db::startTrans();
        try{
           $rs = db('assess')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_estate')->whereIn('assess_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_estate_building')->whereIn('assess_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_estate_valuer')->whereIn('assess_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_assets')->whereIn('assess_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            db('assess_assets_valuer')->whereIn('assess_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        Db::startTrans();
        try{
            model('Assessestates')->withTrashed()->whereIn('assess_id',$ids)->delete(true);
            model('Assessestatebuildings')->withTrashed()->whereIn('assess_id',$ids)->delete(true);
            model('Assessestatevaluers')->withTrashed()->whereIn('assess_id',$ids)->delete(true);
            model('Assessassetss')->withTrashed()->whereIn('assess_id',$ids)->delete(true);
            model('Assessassetsvaluers')->withTrashed()->whereIn('assess_id',$ids)->delete(true);
           $rs = model('Assesss')->onlyTrashed()->whereIn('id',$ids)->delete(true);
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
}