<?php
/* |------------------------------------------------------
 * | 安置房选择
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

use app\system\model\Collectionholderhouses;
use app\system\model\Collectionholders;
use app\system\model\Itemhouses;
use think\Db;

class Collectionholderhouse extends Auth
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
       
        $field=['chh.*','ch.name','ch.address','ch.phone','ch.holder',
            'h.community_id as h_community_id','h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.total_floor','h.has_lift','h.status as house_status',
            'hc.address as hc_address','hc.name as hc_name','l.name as l_name'];

        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作');
        }
        /* ++++++++++ 片区 ++++++++++ */
        $community_id=input('community_id');
        if(!$community_id){
            return $this->error('错误操作');
        }
        /* ++++++++++ 权属 ++++++++++ */
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('错误操作');
        }

        $datas=[
            'item_id'=>$item_id,
            'community_id'=>$community_id,
            'collection_id'=>$collection_id,
        ];
        $where=[
            'chh.item_id'=>$item_id,
            'chh.community_id'=>$community_id,
            'chh.collection_id'=>$collection_id,
        ];

        /* ++++++++++ 查询 ++++++++++ */
        $collectionholderhouse_model=new Collectionholderhouses();
        $deleted=input('deleted');
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $collectionholderhouse_model=$collectionholderhouse_model->onlyTrashed();
            }
        }else{
            $collectionholderhouse_model=$collectionholderhouse_model->withTrashed();
        }
        $collectionholderhouses=$collectionholderhouse_model
            ->alias('chh')
            ->field($field)
            ->join('collection_holder ch','ch.id=chh.collection_holder_id','left')
            ->join('house h','h.id=chh.house_id','left')
            ->join('house_community hc','hc.id=h.community_id','left')
            ->join('layout l','l.id=h.layout_id','left')
            ->where($where)
            ->order('chh.sort asc')
            ->select();

        $datas['collectionholderhouses']=$collectionholderhouses;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        $model=new Collectionholderhouses();
        if(request()->isPost()){
            $rules=[
                'collection_id'=>'require',
                'collection_holder_id'=>'require',
                'house_id'=>'require|unique:collection_holder_house,collection_id='.input('collection_id').'&house_id='.input('house_id'),
            ];
            $msg=[
                'collection_id.require'=>'错误操作，缺少权属参数',
                'collection_holder_id.require'=>'请选择被征收人',
                'house_id.require'=>'请选择安置房',
                'house_id.unique'=>'安置房已选择',
            ];
            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }
            $holder_info=Collectionholders::field(['id','item_id','community_id','collection_id'])->find(input('collection_holder_id'));
            if(!$holder_info){
                return $this->error('错误操作，成员不存在！');
            }
            if( input('collection_id') != $holder_info->collection_id){
                return $this->error('错误操作，数据不一致！');
            }

            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $datas['item_id']=$holder_info->item_id;
            $datas['community_id']=$holder_info->community_id;

            $model->save($datas);
            if($model !== false){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            $item_id=input('item_id');
            if(!$item_id){
                return $this->error('错误操作');
            }
            $collection_id=input('collection_id');
            if(!$collection_id){
                return $this->error('错误操作');
            }
            /* ++++++++++ 成员 ++++++++++ */
            $collectionholders=Collectionholders::alias('ch')
                ->field(['ch.id','ch.item_id','ch.community_id','ch.collection_id','ch.name','ch.holder','c.type'])
                ->join('collection c','c.id=ch.collection_id','left')
                ->where('ch.collection_id',$collection_id)
                ->where('ch.holder','in','1,2')
                ->select();

            /* ++++++++++ 安置房 ++++++++++ */
            $houses=Itemhouses::alias('ih')
                ->field(['ih.item_id','ih.house_id','h.community_id','h.building','h.unit','h.floor','h.number','h.layout_id',
                    'h.area','h.total_floor','h.has_lift','h.status','hc.address','hc.name','l.name as l_name'])
                ->join('house h','h.id=ih.house_id','left')
                ->join('house_community hc','hc.id=h.community_id','left')
                ->join('layout l','l.id=h.layout_id','left')
                ->where('ih.item_id',$item_id)
                ->where('h.status',0)
                ->select();

            return view('add',[
                'model'=>$model,
                'collection_id'=>$collection_id,
                'collectionholders'=>$collectionholders,
                'houses'=>$houses,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collectionholderhouses::withTrashed()
            ->alias('chh')
            ->field(['chh.*','ch.name','h.community_id as h_community_id','h.building','h.unit','h.floor','h.number','h.layout_id',
                'h.area','h.total_floor','h.has_lift','h.status','l.name as l_name','hc.address','hc.name as hc_name','ch.name as ch_name','ch.holder'])
            ->join('collection_holder ch','ch.id=chh.collection_holder_id','left')
            ->join('house h','h.id=chh.house_id','left')
            ->join('house_community hc','hc.id=h.community_id','left')
            ->join('layout l','l.id=h.layout_id','left')
            ->where('chh.id',$id)
            ->find();
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collectionholderhouses();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $id=input('id');
        if(!$id){
            return $this->error('错误操作');
        }

        $collectionholderhouse_model=new Collectionholderhouses();
        $other_datas=$collectionholderhouse_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $collectionholderhouse_model->isUpdate(true)->allowField(['sort','updated_at'])->save($datas);
        if($collectionholderhouse_model !== false){
            return $this->success('修改成功','');
        }else{
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
        $res=Collectionholderhouses::destroy($ids);
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

        $res=Db::table('collection_holder_house')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Collectionholderhouses::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
