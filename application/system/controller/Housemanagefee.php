<?php
/* |------------------------------------------------------
 * | 房源物业管理费
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表 index
 * | 物业费计算 add
 * | excel导出 statis
 * */

namespace app\system\controller;


use app\system\model\Housecommunitys;
use app\system\model\Housemanagefees;
use app\system\model\Houses;
use app\system\model\Items;
use app\system\model\Layouts;
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
        $sum=Housemanagefees::field(['SUM(`public_price`) as public','SUM(`manage_fee`) as manage','(SUM(`public_price`+`manage_fee`)) as total'])->where($where)->find();
        $datas['sum']=$sum;

        $this->assign($datas);

        return view($this->theme.'/housemanagefee/index');
    }


    /* ========== 物业费计算 ========== */
    public function add(){
        if(request()->isPost()){
            $year=input('year');
            if($year>date('Y')){
                return $this->error('请选择正确的计算年份');
            }
            if(!$year){
                $year=date('Y');
            }
            if($year==date('Y')){
                $month=date('m');
            }else{
                $month=12;
            }
            $GLOBALS['year']=$year;
            $GLOBALS['month']=$month;

            $inputs=input();
            $house_ids=isset($inputs['ids'])?$inputs['ids']:'';
            if($house_ids){
                $h_where['id']=['in',$house_ids];
            }
            $h_where['deliver_at']=['elt',strtotime($year.'-'.$month.' +1 month -1 day 23:59:59')];
            $h_where['is_buy']=1;
            $h_where['status']=['neq',3];
            Db::startTrans();
            try{
                $houses=Houses::field(['id','area','manage_price','public_price','status','deliver_at'])
                    ->with(['resettle'=>function($query){
                        $query
                            ->where('(`start_at` < '.strtotime($GLOBALS['year'].'-'.$GLOBALS['month'].' +1 month -1 day 23:59:59').') AND (`end_at` IS NULL OR `end_at` > '.strtotime($GLOBALS['year'].'-01-01 00:00:00').')')

                            ->order('start_at asc');
                    }
                    ,'transit'=>function($query){
                            $query
                                ->where('(`start_at` < '.strtotime($GLOBALS['year'].'-'.$GLOBALS['month'].' +1 month -1 day 23:59:59').') AND (`end_at` IS NULL OR `end_at` > '.strtotime($GLOBALS['year'].'-01-01 00:00:00').')')

                                ->order('start_at asc');
                        }
                        ,'managefee'=>function($query){
                            $query->where('date_at','between',[$GLOBALS['year'].'-01',date('Y-m',strtotime($GLOBALS['year'].'-'.$GLOBALS['month']))])->order('date_at asc');
                        }])
                    ->where($h_where)
                    ->select();

                if(!$houses){
                    throw new \Exception('没有符合计算条件的房源');
                }

                $fee_data=[];
                $del_ids=[];
                $j=0;
                foreach($houses as $house){
                    for($i=1;$i<=$month;$i++){
                        $date_at=date('Y-m',strtotime($year.'-'.$i));

                        $nofee=false;
                        /* ++++++++++ 安置记录 ++++++++++ */
                        if($house->resettle){
                            foreach ($house->resettle as $resettle){
                                if(date('Y-m',$resettle->getData('start_at')) <= $date_at){
                                    if($resettle->getData('end_at')){
                                        if(date('Y-m',$resettle->getData('end_at')) >= $date_at){
                                            $nofee=true;
                                            break;
                                        }
                                    }else{
                                        $nofee=true;
                                        break;
                                    }
                                }
                            }
                        }
                        /* ++++++++++ 临时安置记录 ++++++++++ */
                        if(!$nofee){
                            if($house->transit){
                                foreach ($house->transit as $transit){
                                    if(date('Y-m',$transit->getData('start_at')) <= $date_at){
                                        if($transit->getData('end_at')){
                                            if(date('Y-m',$transit->getData('end_at')) >= $date_at){
                                                $nofee=true;
                                                break;
                                            }
                                        }else{
                                            $nofee=true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        unset($fee_model);
                        /* ++++++++++ 获取已计算费用的模型 ++++++++++ */
                        if($house->managefee){
                            foreach ($house->managefee as $managefee){
                                if($managefee->date_at == $date_at){
                                    $fee_model=$managefee;
                                    break;
                                }
                            }
                        }

                        if($nofee){
                            if(isset($fee_model)){
                                $del_ids[]=$fee_model->id;
                            }
                        }else{

                            if(date('Y-m',$house->getData('deliver_at'))>$date_at){
                                if(isset($fee_model)){
                                    $del_ids[]=$fee_model->id;
                                }
                                continue;
                            }

                            /* ++++++++++ 整理数据 ++++++++++ */
                            $fee_data[$j]=[
                                'house_id'=>$house->id
                                ,'area'=>$house->area
                                ,'manage_price'=>$house->manage_price
                                ,'public_price'=>$house->public_price
                                ,'manage_fee'=>($house->manage_price*$house->area)
                                ,'date_at'=>$date_at
                                ,'created_at'=>time()
                                ,'updated_at'=>time()
                            ];
                            $fee_data[$j]['id']=isset($fee_model)?$fee_model->id:null;
                            $j++;
                        }
                    }
                }

                /* ++++++++++ 删除没有费用的数据 ++++++++++ */
                if($del_ids){
                    $model=new Housemanagefees();
                    $model->whereIn('id',$del_ids)->delete();
                }

                $sqls=batch_update_or_insert_sql('house_manage_fee', ['id','house_id','area','manage_price','public_price','manage_fee','date_at','created_at','updated_at'], $fee_data, ['area','manage_price','public_price','manage_fee','updated_at']);
                if(!$sqls){
                    throw new \Exception('没有产生物业管理费');
                }
                foreach ($sqls as $sql){
                    db()->execute($sql);
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
        }else{
            /* ++++++++++ 小区列表 ++++++++++ */
            $communitys=Housecommunitys::field(['id','name','status'])->where('status',1)->select();
            /* ++++++++++ 户型列表 ++++++++++ */
            $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();
            /* ++++++++++ 房源模型 ++++++++++ */
            $model=new Houses();

            $this->assign([
                'communitys'=>$communitys,
                'layouts'=>$layouts,
                'model'=>$model,
            ]);

            return view($this->theme.'/housemanagefee/add');
        }

    }

    /* ========== excel导出 ========== */
    public function statis(){
        $where = [];
        $years = input('year');
        if($years){
            $where['date_at']=['like',$years.'-%'];
        }
       $housefee =  model('Housemanagefees')
           ->where($where)
           ->with('house,house.community')
           ->order('house_id asc,date_at asc')
           ->select();
        $new_data = [];
      foreach ($housefee as $k=>$v){
          $building = $v->house->building?$v->house->building.'栋':'';
          $unit = $v->house->unit?$v->house->unit.'单元':'';
          $floor = $v->house->floor?$v->house->floor.'楼':'';
          $number = $v->house->number?$v->house->number.'号':'';
          $new_data[$k][] = $k+1;
          $new_data[$k][] = $v->house->community->name.'('.$v->house->community->address.')';
          $new_data[$k][] = $building.$unit.$floor.$number;
          $new_data[$k][] = $v->area;
          $new_data[$k][] = $v->date_at;
          $new_data[$k][] = $v->manage_price;
          $new_data[$k][] = $v->manage_fee;
          $new_data[$k][] = $v->public_price;
      }
        $new_title[0][0] = '序号';
        $new_title[0][1] = '小区名称(地点)';
        $new_title[0][2] = '房号';
        $new_title[0][3] = '面积(㎡)';
        $new_title[0][4] = '空置期(月)';
        $new_title[0][5] = '物业服务费单价（元/㎡/月）';
        $new_title[0][6] = '物业服务费（元/月）';
        $new_title[0][7] = '公摊费（元/月）';
       $new_data_array = array_merge($new_title,$new_data);
        if($housefee){
            create_housemanagefee_xls($new_data_array,'房源管理费'.date('Ymd'));
        }else{
            return $this->error('暂无数据');
        }

    }
}