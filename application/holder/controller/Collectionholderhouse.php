<?php
/* |------------------------------------------------------
 * | 安置房选择
 * |------------------------------------------------------
 * */
namespace app\holder\controller;


use app\system\model\Collectionholderhouses;
use app\system\model\Collections;
use app\system\model\Housecommunitys;
use app\system\model\Houses;
use app\system\model\Itemhouses;
use app\system\model\Items;
use app\system\model\Layouts;
use think\Db;

class Collectionholderhouse extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
        $this->assign(['url'=>url('Collectionholderhouse/index')]);
    }

    /* ========== 列表 ========== */
    public function index()
    {
        if(!$this->item_id){
            return $this->error('非法访问','');
        }
        if(!$this->collection_id){
            return $this->error('非法访问','');
        }

        $item_time=Items::where('id',$this->item_id)->value('created_at');
        $where['chh.item_id']=$this->item_id;
        $where['chh.collection_id']=$this->collection_id;

        /* ********** 查询条件 ********** */

        $field=['chh.*','ch.name','ch.address','ch.phone','ch.holder',
            'h.community_id as h_community_id','h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.total_floor','h.has_lift','h.status as house_status','h.picture','hlp.picture as l_pic',
            'hc.address as hc_address','hc.name as hc_name','l.name as l_name','hp.market_price','hp.price'];

        /* ++++++++++ 查询 ++++++++++ */
        $collectionholderhouses=Collectionholderhouses::alias('chh')
            ->field($field)
            ->join('collection_holder ch','ch.id=chh.collection_holder_id','left')
            ->join('house h','h.id=chh.house_id','left')
            ->join('house_community hc','hc.id=h.community_id','left')
            ->join('layout l','l.id=h.layout_id','left')
            ->join('house_layout_pic hlp','hlp.id=h.layout_pic_id','left')
            ->join('house_price hp','hp.house_id=chh.house_id and start_at<='.$item_time.' and end_at>='.$item_time,'left')
            ->where($where)
            ->order('chh.sort asc')
            ->select();


        $datas=[
            'item_id'=>$this->item_id,
            'collection_id'=>$this->collection_id,
            'collectionholderhouses'=>$collectionholderhouses
        ];

        $this->assign($datas);

        return view($this->theme.'/collectionholderhouse/index');
    }

    /* ========== 添加 ========== */
    public function add(){
        if(!$this->item_id){
            return $this->error('非法访问','');
        }
        if(!$this->collection_id){
            return $this->error('非法访问','');
        }

        if(request()->isPost()){
            $inputs=input();
            $ids=@$inputs['ids'] or [];
            if(!$ids){
                return $this->error('请选择安置房','');
            }
            $community_id=Collections::where(['item_id'=>$this->item_id,'id'=>$this->collection_id])->value('community_id');

            $holders=session('holderinfo.collection_holders');
            $holder_id=$holders[$this->collection_id];

            $cur_house_ids=Collectionholderhouses::where([
                'item_id'=>$this->item_id,
                'collection_id'=>$this->collection_id
            ])
                ->column('house_id','id');

            $chh_data=[];
            foreach($ids as $house_id){
                $chh_data[]=[
                    'id'=>(($cur_house_ids && in_array($house_id,$cur_house_ids))?array_search($house_id,$cur_house_ids):null),
                    'item_id'=>$this->item_id,
                    'community_id'=>$community_id,
                    'collection_id'=>$this->collection_id,
                    'collection_holder_id'=>$holder_id,
                    'house_id'=>$house_id,
                    'sort'=>0,
                    'created_at'=>time(),
                    'updated_at'=>time()
                ];
            }

            Db::startTrans();
            try{

                $sqls=batch_update_or_insert_sql('collection_holder_house',['id','item_id','community_id','collection_id','collection_holder_id','house_id','sort','created_at','updated_at'],$chh_data,['updated_at']);
                if(!$sqls){
                    throw new \Exception('数据错误',404404);
                }
                foreach($sqls as $sql){
                    db()->execute($sql);
                }

                $res=true;
                Db::commit();
            }catch (\Exception $exception){
                $res=false;
                Db::rollback();
            }

            if($res){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }

        }else{
            $itemhouses=Itemhouses::where('item_id',$this->item_id)->column('house_id');
            $item_time=Items::where('id',$this->item_id)->value('created_at');

            /* ********** 查询条件 ********** */
            $datas=[
                'item_id'=>$this->item_id,
                'collection_id'=>$this->collection_id,
            ];
            $where['h.id']=['in',$itemhouses];
            $where['h.status']=0;

            /* ++++++++++ 小区 ++++++++++ */
            $community_id=input('community_id');

            if($community_id){
                $where['h.community_id']=$community_id;
                $datas['community_id']=$community_id;
            }
            /* ++++++++++ 户型 ++++++++++ */
            $layout_id=input('layout_id');
            if($layout_id){
                $where['h.layout_id']=$layout_id;
                $datas['layout_id']=$layout_id;
            }
            /* ++++++++++ 面积 ++++++++++ */
            $area_start=input('area_start');
            if($area_start){
                $where['h.area']=['>=',$area_start];
                $datas['area_start']=$area_start;
            }
            $area_end=input('area_end');
            if($area_end){
                $where['h.area']=['<=',$area_end];
                $datas['area_end']=$area_end;
            }
            /* ++++++++++ 是否有电梯 ++++++++++ */
            $has_lift=input('has_lift');
            if(is_numeric($has_lift) && in_array($has_lift,[0,1])){
                $where['h.has_lift']=$has_lift;
                $datas['has_lift']=$has_lift;
            }
            /* ++++++++++ 期房、现房 ++++++++++ */
            $is_real=input('is_real');
            if(is_numeric($is_real) && in_array($is_real,[0,1])){
                $where['h.is_real']=$is_real;
                $datas['is_real']=$is_real;
            }

            /* ++++++++++ 排序 ++++++++++ */
            $ordername=input('ordername');
            $ordername=$ordername?$ordername:'id';
            $datas['ordername']=$ordername;
            $orderby=input('orderby');
            $orderby=$orderby?$orderby:'asc';
            $datas['orderby']=$orderby;

            $house_model=new Houses();
            $datas['model']=$house_model;
            /* ++++++++++ 查询 ++++++++++ */
            $field=['h.*','c.address','c.name as c_name','l.name as l_name','hlp.picture as l_pic','hp.market_price','hp.price'];
            $houses=$house_model
                ->alias('h')
                ->field($field)
                ->join('house_community c','c.id=h.community_id','left')
                ->join('layout l','l.id=h.layout_id','left')
                ->join('house_layout_pic hlp','hlp.id=h.layout_pic_id','left')
                ->join('house_price hp','hp.house_id=h.id and start_at<='.$item_time.' and end_at>='.$item_time,'left')
                ->where($where)
                ->order(['h.'.$ordername=>$orderby])
                ->paginate(1);

            $datas['houses']=$houses;

            /* ++++++++++ 小区列表 ++++++++++ */
            $communitys=Housecommunitys::field(['id','address','name','status'])->where('status',1)->select();
            $datas['communitys']=$communitys;
            /* ++++++++++ 户型列表 ++++++++++ */
            $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();
            $datas['layouts']=$layouts;

            $this->assign($datas);

            return view($this->theme.'/collectionholderhouse/add');
        }
    }


    /* ========== 排序 ========== */
    public function sort(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        $sorts=isset($inputs['sorts'])?$inputs['sorts']:[];
        if(empty($ids)||empty($sorts)){
            return $this->error('至少选择一项');
        }
        $datas=[];
        $i=0;
        $time=time();
        foreach ($ids as $id){
            $datas[$i]['id']=$id;
            $datas[$i]['sort']=(int)$sorts[$id];
            $datas[$i]['updated_at']=$time;
            $i++;
        }

        Db::startTrans();
        try{
            $sqls=batch_update_sql('collection_holder_house',['id','sort','updated_at'],$datas,['sort','updated_at'],'id');
            if(!$sqls){
                throw new \Exception('数据错误',404404);
            }
            if($sqls){
                foreach ($sqls as $sql){
                    db()->execute($sql);
                }
            }

            $res=true;
            Db::commit();
        }catch (\Exception $exception){
            $res=false;
            Db::rollback();
        }

        if($res){
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
        $res=Collectionholderhouses::whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }
}

