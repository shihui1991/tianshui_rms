<?php
/* |------------------------------------------------------
 * | 房源物业管理费
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表 index
 * | 物业费计算 add
 * */

namespace app\system\controller;


use app\system\model\Housecommunitys;
use app\system\model\Housemanagefees;
use app\system\model\Houses;
use think\Db;

class Housemanagefee extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
    }


    /* ========== 列表 ========== */
    public function index(){
        /* ********** 查询条件 ********** */
        $where=[];
        /* ++++++++++ 小区 ++++++++++ */
        $address=trim(input('address'));
        $name=trim(input('name'));
        if($address){
            $c_where['address']=['like','%'.$address.'%'];
            $datas['address']=$address;
        }
        if($name){
            $c_where['name']=['like','%'.$name.'%'];
            $datas['name']=$name;
        }
        if(isset($c_where)){
            $c_ids=Housecommunitys::where($c_where)->column('id');
        }
        /* ++++++++++ 房源 ++++++++++ */
        if(isset($c_ids)){
            $c_ids=$c_ids?$c_ids:0;
            $h_where['community_id']=['in',$c_ids];
        }
        $building=input('building');
        if($building){
            $h_where['building']=$building;
            $datas['building']=$building;
        }
        $unit=input('unit');
        if($unit){
            $h_where['unit']=$unit;
            $datas['unit']=$unit;
        }
        $floor=input('floor');
        if($floor){
            $h_where['floor']=$floor;
            $datas['floor']=$floor;
        }
        $number=input('number');
        if($number){
            $h_where['number']=$number;
            $datas['number']=$number;
        }
        $h_where['deliver_at']=['gt',0];
        $h_where['is_buy']=1;
        $h_ids=Houses::where($h_where)->column('id');
        $h_ids=$h_ids?$h_ids:0;
        $where['house_id']=['in',$h_ids];
        /* ++++++++++ 时间 ++++++++++ */
        $year=input('year');
        if($year){
            $where['date_at']=['like',$year.'-%'];
            $datas['year']=$year;
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

        $housefees=Housemanagefees::where($where)->with('house,house.community')->order([$ordername=>$orderby])->paginate($display_num);
        $datas['housefees']=$housefees;
        $sum=Housemanagefees::field(['SUM(`public_price`) as public','SUM(`manage_fee`) as manage','(SUM(`public_price`)+SUM(`manage_fee`)) as total'])->where($where)->find();
        $datas['sum']=$sum;

        $this->assign($datas);

        return view();
    }


    /* ========== 物业费计算 ========== */
    public function add(){
        $inputs=input();
        $house_ids=isset($inputs['ids'])?$inputs['ids']:'';
        if($house_ids){
            $h_where['id']=['in',$house_ids];
        }
        $h_where['deliver_at']=['gt',0];
        $h_where['is_buy']=1;
        Db::startTrans();
        try{
            $houses=Houses::where($h_where)->field(['id','area','manage_price','public_price','status'])->select();
            if(!$houses){
                throw new \Exception('没有符合计算条件的房源');
            }


            $res=true;
            $msg='计算完成';
            Db::commit();
        }catch (\Exception $exception){
            $res=false;
            $msg=$exception->getMessage();
            Db::rollback();
        }

        if($res){
            return $this->success($msg,'');
        }else{
            return $this->error($msg);
        }
    }

}