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
            if(is_array($ids)){
                foreach ($ids as $k=>$v){
                    model('Assessestates')->destroy(['assess_id'=>$v]);
                    model('Assessestatebuildings')->destroy(['assess_id'=>$v]);
                    model('Assessestatevaluers')->destroy(['assess_id'=>$v]);
                    model('Assessassetss')->destroy(['assess_id'=>$v]);
                    model('Assessassetsvaluers')->destroy(['assess_id'=>$v]);
                }
            }else{
                model('Assessestates')->destroy(['assess_id'=>$ids]);
                model('Assessestatebuildings')->destroy(['assess_id'=>$ids]);
                model('Assessestatevaluers')->destroy(['assess_id'=>$ids]);
                model('Assessassetss')->destroy(['assess_id'=>$ids]);
                model('Assessassetsvaluers')->destroy(['assess_id'=>$ids]);
            }
            if($rs){
                $res=true;
            }else{
                $res=false;
            }
            Db::commit();
        }catch (\Exception $e){
            $res=false;dump($e);
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
            if(is_array($ids)){
                foreach ($ids as $k=>$v){
                    db('assess_estate')->where('assess_id',$v)->update(['deleted_at'=>null,'updated_at'=>time()]);
                    db('assess_estate_building')->where('assess_id',$v)->update(['deleted_at'=>null,'updated_at'=>time()]);
                    db('assess_estate_valuer')->where('assess_id',$v)->update(['deleted_at'=>null,'updated_at'=>time()]);
                    db('assess_assets')->where('assess_id',$v)->update(['deleted_at'=>null,'updated_at'=>time()]);
                    db('assess_assets_valuer')->where('assess_id',$v)->update(['deleted_at'=>null,'updated_at'=>time()]);
                }
            }else{
                db('assess_estate')->where('assess_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
                db('assess_estate_building')->where('assess_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
                db('assess_estate_valuer')->where('assess_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
                db('assess_assets')->where('assess_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
                db('assess_assets_valuer')->where('assess_id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
            }
            if($rs){
                $res=true;
            }else{
                $res=false;
            }
            Db::commit();
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

            if(is_array($ids)){
                foreach ($ids as $k=>$v){
                    model('Assessestates')->onlyTrashed()->where('assess_id',$v)->delete(true);
                    model('Assessestatebuildings')->onlyTrashed()->where('assess_id',$v)->delete(true);
                    model('Assessestatevaluers')->onlyTrashed()->where('assess_id',$v)->delete(true);
                    model('Assessassetss')->onlyTrashed()->where('assess_id',$v)->delete(true);
                    model('Assessassetsvaluers')->onlyTrashed()->where('assess_id',$v)->delete(true);
                }
            }else{
                model('Assessestates')->onlyTrashed()->where('assess_id',$ids)->delete(true);
                model('Assessestatebuildings')->onlyTrashed()->where('assess_id',$ids)->delete(true);
                model('Assessestatevaluers')->onlyTrashed()->where('assess_id',$ids)->delete(true);
                model('Assessassetss')->onlyTrashed()->where('assess_id',$ids)->delete(true);
                model('Assessassetsvaluers')->onlyTrashed()->where('assess_id',$ids)->delete(true);
            }
           $rs = model('Assesss')->onlyTrashed()->whereIn('id',$ids)->delete(true);
            if($rs){
                $res=true;
            }else{
                $res=false;
            }
            Db::commit();
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