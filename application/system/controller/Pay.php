<?php
/* |------------------------------------------------------
 * | 兑付
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

use app\system\model\Collectioncommunitys;
use app\system\model\Collectionholders;
use app\system\model\Collectionobjects;
use app\system\model\Collections;
use app\system\model\Items;
use app\system\model\Payholders;
use app\system\model\Payobjects;
use app\system\model\Pays;
use think\Db;

class Pay extends Auth
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
        $datas=[];
        $where=[];
        $field=['p.*','i.name as i_name','i.is_top','c.community_id','c.building','c.unit','c.floor','c.number','c.type','c.real_use','cc.address','cc.name as cc_name','bu.name as bu_name'];

        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        if(is_numeric($item_id)){
            $where['pay.item_id']=$item_id;
            $datas['item_id']=$item_id;
        }
        /* ++++++++++ 片区 ++++++++++ */
        $community_id=input('community_id');
        if(is_numeric($community_id)){
            $where['pay.community_id']=$community_id;
            $datas['community_id']=$community_id;
        }
        /* ++++++++++ 权属 ++++++++++ */
        $collection_id=input('collection_id');
        if(is_numeric($collection_id)){
            $where['pay.collection_id']=$collection_id;
            $datas['collection_id']=$collection_id;
        }
        /* ++++++++++ 排序 ++++++++++ */
        $ordername=input('ordername');
        $ordername=$ordername?$ordername:'id';
        $datas['ordername']=$ordername;
        $orderby=input('orderby');
        $orderby=$orderby?$orderby:'asc';
        $datas['orderby']=$orderby;
        /* ++++++++++ 每页条数 ++++++++++ */
        $nums=[config('paginate.list_rows'),30,50,100,200,500];
        sort($nums);
        $datas['nums']=$nums;
        $display_num=input('display_num');
        $display_num=$display_num?$display_num:config('paginate.list_rows');
        $datas['display_num']=$display_num;
        /* ++++++++++ 是否删除 ++++++++++ */
        $deleted=input('deleted');
        $pay_model=new Pays();
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $pay_model=$pay_model->onlyTrashed();
            }
        }else{
            $pay_model=$pay_model->withTrashed();
        }
        $pays=$pay_model
            ->alias('p')
            ->field($field)
            ->join('item i','i.id=p.item_id','left')
            ->join('collection c','c.id=p.collection_id','left')
            ->join('collection_community cc','cc.id=p.community_id','left')
            ->join('building_use bu','bu.id=c.real_use','left')
            ->where($where)
            ->order(['item.is_top'=>'desc','pay.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['pays']=$pays;

        /* ++++++++++ 项目 ++++++++++ */
        $items=Items::field(['id','name','status','is_top'])->where(['status'=>1])->order('is_top desc')->select();
        $datas['items']=$items;
        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
        $datas['collectioncommunitys']=$collectioncommunitys;
        /* ++++++++++ 权属 ++++++++++ */
        $collections=Collections::field(['id','building','unit','floor','number','status'])->where('status',1)->select();
        $datas['collections']=$collections;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Pays();
        if(request()->isPost()){
            $rules=[
                'item_id'=>'require',
                'ids'=>'requireIf:select,0',
            ];
            $msg=[
                'item_id.require'=>'请选择项目',
                'ids.requireIf'=>'请选择被征户',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $inputs=input();
            $model->startTrans();
            try{
                /* ++++++++++ 查询选择的被征户 ++++++++++ */
                $where['collection.item_id']=input('item_id');
                $where['collection.status']=1;
                $where['collection.real_use']=['neq',3];
                if(input('community_id')){
                    $where['collection.community_id']=input('community_id');
                }
                if(input('select')==0){
                    $where['collection.id']=['in',$inputs['ids']];
                }
                Collections::alias('c')
                    ->field(['c.id','c.item_id','c.community_id','c.type','c.real_use','c.has_assets','c.compensate_way','c.compensate_price','c.status','a.id as assess_id','a.estate','a.assets','p.id as pay_id','p.subject_amount','p.object_amount'])
                    ->join('assess a','a.item_id=c.item_id and a.collection_id=c.id','left')
                    ->join('pay p','p.item_id=c.item_id and p.collection_id=c.id','left')
                    ->where($where)
                    ->chunk(100,function ($collections){
                        $pay_model=new Pays();
                        static $public=[];

                        foreach($collections as $collection){
                            unset($pay_data);
                            /* ********** 兑付基本数据 ********** */
                            $pay_data['item_id']=$collection->item_id;
                            $pay_data['community_id']=$collection->community_id;
                            $pay_data['collection_id']=$collection->id;
                            $pay_data['assess_id']=$collection->assess_id;
                            $pay_data['estate_amount']=$collection->estate;
                            $pay_data['assets_amount']=$collection->assets;

                            /* ********** 公共附属物摸底 ********** */
                            if(isset($public[$collection->item_id][$collection->community_id]['num'])){
                                $public_num=$public[$collection->item_id][$collection->community_id]['num'];
                                $public_sum=$public[$collection->item_id][$collection->community_id]['sum'];
                                $public_avg=$public[$collection->item_id][$collection->community_id]['avg'];
                            }else{
                                // 公共附属物平分户数
                                $public_num=Collections::where('item_id',$collection->item_id)
                                    ->where('community_id',$collection->community_id)
                                    ->where('real_use','neq',3)
                                    ->count();
                                // 公共附属物评估结果
                                $public_sum=Collections::alias('c')
                                    ->join('assess a','a.item_id=c.item_id and a.collection_id=c.id and c.community_id=a.community_id','left')
                                    ->where('collection.item_id',$collection->item_id)
                                    ->where('collection.community_id',$collection->community_id)
                                    ->where('collection.real_use',3)
                                    ->sum('a.estate+a.assets');
                                // 公共附属物平分总和
                                $public_avg=$public_sum/$public_num;
                                // 公共附属物数据暂存
                                $public[$collection->item_id][$collection->community_id]['num']=$public_num;
                                $public[$collection->item_id][$collection->community_id]['sum']=$public_sum;
                                $public[$collection->item_id][$collection->community_id]['avg']=$public_avg;
                            }
                            $pay_data['public_amount']=$public_sum;
                            $pay_data['public_num']=$public_num;
                            $pay_data['public_avg']=$public_avg;

                            /* ********** 兑付更新或添加 ********** */
                            if($collection->pay_id){
                                $pay_data['subject_amount']=$collection->subject_amount;
                                $pay_data['object_amount']=$collection->object_amount;
                                $pay_data['total']=($collection->estate+$collection->assets+$public_avg+$collection->subject_amount+$collection->object_amount);
                                $pay_data['compensate_way']=0;
                                $pay_data['transit_way']=0;
                                $pay_data['move_way']=0;
                                $pay_data['pay_way']=1;
                                $pay_data['picture']=[];
                                if($collection->getData('type')){
                                    $pay_data['pay_way']=0;
                                }

                                $pay_model->save($pay_data,['id'=>$collection->pay_id]);
                            }else{
                                // 产权人或承租人
                                $holders=Collectionholders::alias('ch')
                                    ->field(['ch.id','ch.item_id','ch.community_id','ch.collection_id','ch.holder','ch.portion','r.id as risk_id','r.compensate_way','r.transit_way','r.move_way'])
                                    ->join('risk r','r.item_id=ch.item_id and r.community_id=ch.community_id and r.collection_id=ch.collection_id and r.holder_id=ch.id','left')
                                    ->where('collection_holder.item_id',$collection->item_id)
                                    ->where('collection_holder.community_id',$collection->community_id)
                                    ->where('collection_holder.collection_id',$collection->id)
                                    ->where('collection_holder.holder','in',[1,2])
                                    ->order('portion desc')
                                    ->select();

                                // 兑付其他数据
                                $pay_data['subject_amount']=0;
                                $pay_data['object_amount']=0;
                                $pay_data['total']=($collection->estate+$collection->assets+$public_avg);
                                $pay_data['compensate_way']=$holders[0]->compensate_way;
                                $pay_data['transit_way']=$holders[0]->transit_way;
                                $pay_data['move_way']=$holders[0]->move_way;
                                $pay_data['picture']=[];
                                // 兑付添加
                                $pay_model->save($pay_data);

                                // 产权人或承租人
                                $holder_data=[];
                                $i=0;
                                foreach ($holders as $holder){
                                    $holder_data[$i]['item_id']=$collection->item_id;
                                    $holder_data[$i]['community_id']=$collection->community_id;
                                    $holder_data[$i]['collection_id']=$collection->id;
                                    $holder_data[$i]['assess_id']=$collection->assess_id;
                                    $holder_data[$i]['pay_id']=$pay_model->id;
                                    $holder_data[$i]['collection_holder_id']=$holder->id;
                                    $holder_data[$i]['holder']=$holder->getData('holder');
                                    $holder_data[$i]['portion']=$holder->portion;

                                    if($collection->getData('type')){
                                        if($holder->getData('holder')==1){
                                            $holder_data[$i]['estate_amount']=$holder->portion/100*$collection->estate;
                                            $holder_data[$i]['assets_amount']=0;
                                            $holder_data[$i]['public_amount']=$public_sum;
                                            $holder_data[$i]['public_num']=$public_num;
                                            $holder_data[$i]['public_avg']=0;
                                            $holder_data[$i]['subject_amount']=0;
                                            $holder_data[$i]['object_amount']=0;
                                            $holder_data[$i]['total_amount']=$holder->portion/100*$collection->estate;
                                        }else{
                                            $holder_data[$i]['estate_amount']=$holder->portion/100*$collection->estate;
                                            $holder_data[$i]['assets_amount']=$collection->assets;
                                            $holder_data[$i]['public_amount']=$public_sum;
                                            $holder_data[$i]['public_num']=$public_num;
                                            $holder_data[$i]['public_avg']=$public_avg;
                                            $holder_data[$i]['subject_amount']=0;
                                            $holder_data[$i]['object_amount']=0;
                                            $holder_data[$i]['total_amount']=($holder->portion/100*$collection->estate+$collection->assets+$public_avg);
                                        }
                                    }else{
                                        $holder_data[$i]['estate_amount']=$holder->portion/100*$collection->estate;
                                        $holder_data[$i]['assets_amount']=$holder->portion/100*$collection->assets;
                                        $holder_data[$i]['public_amount']=$public_sum;
                                        $holder_data[$i]['public_num']=$public_num;
                                        $holder_data[$i]['public_avg']=$holder->portion/100*$public_avg;
                                        $holder_data[$i]['subject_amount']=0;
                                        $holder_data[$i]['object_amount']=0;
                                        $holder_data[$i]['total_amount']=$holder->portion/100*($collection->estate+$collection->assets+$public_avg);
                                    }
                                    $i++;
                                }
                                $pay_holder_model=new Payholders();
                                $pay_holder_model->saveAll($holder_data);

                                // 补偿事项
                                $objects=Collectionobjects::field(['id','item_id','collection_id','object_id','number'])
                                    ->where('item_id',$collection->item_id)
                                    ->where('collection_id',$collection->id)
                                    ->select();

                                $object_data=[];
                                $i=0;
                                foreach($objects as $object){
                                    $object_data[$i]['item_id']=$collection->item_id;
                                    $object_data[$i]['community_id']=$collection->community_id;
                                    $object_data[$i]['collection_id']=$collection->id;
                                    $object_data[$i]['pay_id']=$pay_model->id;
                                    $object_data[$i]['collection_object_id']=$object->id;
                                    $object_data[$i]['price']=0;
                                    $object_data[$i]['number']=$object->number;
                                    $object_data[$i]['amount']=0;
                                    $i++;
                                }
                                $pay_object_model=new Payobjects();
                                $pay_object_model->saveAll($object_data);
                            }

                        }
                    },'collection.id');

                $res=true;
                $model->commit();
            }catch (\Exception $exception){
                $res=false;dump($exception);
                $model->rollback();
            }

            if($res){
                return $this->success('添加成功','');
            }else{
                return $this->error('添加失败');
            }
        }else{
            /* ++++++++++ 项目 ++++++++++ */
            $items=Items::field(['id','name','status','is_top'])->where(['status'=>1])->order('is_top desc')->select();
            /* ++++++++++ 片区 ++++++++++ */
            $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();

            return view('add',[
                'model'=>$model,
                'items'=>$items,
                'collectioncommunitys'=>$collectioncommunitys,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Pays::withTrashed()
            ->alias('p')
            ->field(['p.*','i.name as i_name','cc.address as cc_address','cc.name as cc_name','c.building','c.unit','c.floor','c.number','c.type','c.real_use','bu.name as bu_name'])
            ->join('item i','i.id=p.item_id','left')
            ->join('collection_community cc','cc.id=p.community_id','left')
            ->join('collection c','c.id=p.collection_id','left')
            ->join('building_use bu','bu.id=c.real_use','left')
            ->where('p.id',$id)
            ->find();
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Pays();

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

        $pay_model=new Pays();
        $other_datas=$pay_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $pay_model->isUpdate(true)->allowField(['compensate_way','transit_way','move_way','pay_way','picture'])->save($datas);
        if($pay_model !== false){
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
        $res=Pays::destroy($ids);
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

        $res=Db::table('pay')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Pays::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
