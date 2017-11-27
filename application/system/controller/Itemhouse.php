<?php
/* |------------------------------------------------------
 * | 冻结安置房
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 删除
 * */
namespace app\system\controller;

use app\system\model\Housecommunitys;
use app\system\model\Itemhouses;
use app\system\model\Layouts;
use app\system\model\Items;
use think\Db;

class Itemhouse extends Auth
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
        $field=['ih.*','i.name as i_name','i.status as i_status','i.is_top','h.community_id','c.address','c.name as c_name',
            'h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.status as house_status','h.is_real','h.is_buy',
            'h.is_transit','h.is_public','l.name as l_name'];

        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        if(is_numeric($item_id)){
            $where['item_id']=$item_id;
            $datas['item_id']=$item_id;
        }
        $inputs=input();
        /* ++++++++++ 地址 ++++++++++ */
        $community_ids_a=isset($inputs['community_ids_a'])?$inputs['community_ids_a']:[];
        $datas['community_ids_a']=$community_ids_a;
        /* ++++++++++ 小区 ++++++++++ */
        $community_ids_c=isset($inputs['community_ids_c'])?$inputs['community_ids_c']:[];
        $datas['community_ids_c']=$community_ids_c;
        $community_ids=array_filter(array_merge($community_ids_a,$community_ids_c));
        if($community_ids){
            $where['house.community_id']=['in',$community_ids];
            $datas['community_ids']=$community_ids;
        }
        /* ++++++++++ 几栋 ++++++++++ */
        $building=input('building');
        if(is_numeric($building)){
            $where['house.building']=$building;
            $datas['building']=$building;
        }
        /* ++++++++++ 几单元 ++++++++++ */
        $unit=input('unit');
        if(is_numeric($unit)){
            $where['house.unit']=$unit;
            $datas['unit']=$unit;
        }
        /* ++++++++++ 几楼 ++++++++++ */
        $floor=input('floor');
        if(is_numeric($floor)){
            $where['house.floor']=$floor;
            $datas['floor']=$floor;
        }
        /* ++++++++++ 几号 ++++++++++ */
        $number=input('number');
        if(is_numeric($number)){
            $where['house.number']=$number;
            $datas['number']=$number;
        }
        /* ++++++++++ 户型 ++++++++++ */
        $layout_id=input('layout_id');
        if($layout_id){
            $where['house.layout_id']=$layout_id;
            $datas['layout_id']=$layout_id;
        }
        /* ++++++++++ 面积 ++++++++++ */
        $area_start=input('area_start');
        if($area_start){
            $where['house.area']=['>=',$area_start];
            $datas['area_start']=$area_start;
        }
        $area_end=input('area_end');
        if($area_end){
            $where['house.area']=['<=',$area_end];
            $datas['area_end']=$area_end;
        }
        /* ++++++++++ 期房、现房 ++++++++++ */
        $is_real=input('is_real');
        if(is_numeric($is_real) && in_array($is_real,[0,1])){
            $where['house.is_real']=$is_real;
            $datas['is_real']=$is_real;
        }
        /* ++++++++++ 是否购置房 ++++++++++ */
        $is_buy=input('is_buy');
        if(is_numeric($is_buy) && in_array($is_buy,[0,1])){
            $where['house.is_buy']=$is_buy;
            $datas['is_buy']=$is_buy;
        }
        /* ++++++++++ 是否过渡房 ++++++++++ */
        $is_transit=input('is_transit');
        if(is_numeric($is_transit) && in_array($is_transit,[0,1])){
            $where['house.is_transit']=$is_transit;
            $datas['is_transit']=$is_transit;
        }
        /* ++++++++++ 是否共用 ++++++++++ */
        $is_public=input('is_public');
        if(is_numeric($is_public) && in_array($is_public,[0,1])){
            $where['house.is_public']=$is_public;
            $datas['is_public']=$is_public;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1,2,3])){
            $where['house.status']=$status;
            $datas['status']=$status;
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
        /* ++++++++++ 查询 ++++++++++ */

        $itemhouse_model=new Itemhouses();
        $datas['model']=$itemhouse_model;
        $itemhouses=$itemhouse_model
            ->alias('ih')
            ->field($field)
            ->join('item i','ih.item_id=i.id','left')
            ->join('house h','ih.house_id=h.id','left')
            ->join('house_community c','h.community_id=c.id','left')
            ->join('layout l','h.layout_id=l.id','left')
            ->where($where)
            ->order(['item.is_top'=>'desc','item_house.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['itemhouses']=$itemhouses;
        /* ++++++++++ 项目列表 ++++++++++ */
        $items=Items::field(['id','name','status'])->order('is_top desc')->select();
        $datas['items']=$items;
        /* ++++++++++ 小区列表 ++++++++++ */
        $communitys=Housecommunitys::field(['id','address','name','status'])->where('status',1)->select();
        $datas['communitys']=$communitys;
        /* ++++++++++ 户型列表 ++++++++++ */
        $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();
        $datas['layouts']=$layouts;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        $model=new Itemhouses();
        if(request()->isPost()){
            $inputs=input();
            $rules=[
                'item_id'=>'require',
                'ids'=>'require',
            ];
            $msg=[
                'item_id.require'=>'请选择项目',
                'ids.require'=>'请选择安置房源',
            ];

            $result=$this->validate($inputs,$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $model->startTrans();
            try{
                $item_id=$inputs['item_id'];
                $ids=$inputs['ids'];
                $datas=[];
                $update_ids=[];
                $i=0;
                $itemhouses=Itemhouses::where('item_id',$item_id)->where('house_id','in',$ids)->column('house_id','id');
                foreach ($ids as $house_id){
                    $id=array_search($house_id,$itemhouses);
                    if($id){
                        $update_ids[]=$id;
                    }else{
                        $datas[$i]=[
                            'item_id'=>$item_id,
                            'house_id'=>$house_id,
                        ];
                        $i++;
                    }
                }
                if($datas){
                    $model->saveAll($datas);
                }
                if($update_ids){
                    Itemhouses::where('id','in',$update_ids)->update(['updated_at'=>time()]);
                }

                $model->commit();
                $res=true;
            }catch (\Exception $exception){
                $model->rollback();
                $res=false;
            }

            if($res){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            /* ++++++++++ 项目列表 ++++++++++ */
            $items=Items::field(['id','name','status'])->where('status',1)->order('is_top desc')->select();
            /* ++++++++++ 小区列表 ++++++++++ */
            $communitys=Housecommunitys::field(['id','name','status'])->where('status',1)->select();
            /* ++++++++++ 户型列表 ++++++++++ */
            $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();
            return view('modify',[
                'model'=>$model,
                'items'=>$items,
                'communitys'=>$communitys,
                'layouts'=>$layouts,
            ]);
        }
    }


    /* ========== 删除 ========== */
    public function delete(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res=Itemhouses::destroy($ids);
        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }
}
