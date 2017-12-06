<?php
/* |------------------------------------------------------
 * | 被征收户
 * |------------------------------------------------------
 * */

namespace app\company\controller;


use app\system\model\Collectioncommunitys;
use app\system\model\Collections;

class Collection extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
        $item_id=input('item');
        if(!$item_id){
            return $this->error('非法访问');
        }
        if(!in_array($item_id,session('company.item_ids'))){
            return $this->error('非法访问');
        }
        $where['item_id']=$item_id;
        $datas['item_id']=$item_id;
        $where['status']=1;
        /* ++++++++++ 片区 ++++++++++ */
        $community_id=input('community_id');
        if(is_numeric($community_id)){
            $where['community_id']=$community_id;
            $datas['community_id']=$community_id;
        }
        /* ++++++++++ 几栋 ++++++++++ */
        $building=input('building');
        if(is_numeric($building)){
            $where['building']=$building;
            $datas['building']=$building;
        }
        /* ++++++++++ 几单元 ++++++++++ */
        $unit=input('unit');
        if(is_numeric($unit)){
            $where['unit']=$unit;
            $datas['unit']=$unit;
        }
        /* ++++++++++ 几楼 ++++++++++ */
        $floor=input('floor');
        if(is_numeric($floor)){
            $where['floor']=$floor;
            $datas['floor']=$floor;
        }
        /* ++++++++++ 几号 ++++++++++ */
        $number=input('number');
        if(is_numeric($number)){
            $where['number']=$number;
            $datas['number']=$number;
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

        if(session('company.type')){
            $with_count='assets';
        }else{
            $with_count='estate';
        }

        $collections=Collections::field(['id','item_id','community_id','building','unit','floor','number'])
            ->withCount($with_count)
            ->with(['community','assess'])
            ->where($where)
            ->order([$ordername=>$orderby])
            ->paginate($display_num);

        $datas['collections']=$collections;

        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();
        $datas['collectioncommunitys']=$collectioncommunitys;

        $this->assign($datas);

        return view();
    }

}