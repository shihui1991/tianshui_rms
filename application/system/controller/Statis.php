<?php
/* |------------------------------------------------------
 * | 统计与图表
 * |------------------------------------------------------
 * | 项目首页
 * */
namespace app\system\controller;
use app\system\model\Items;

class Statis extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
    }

    /* ========== 项目首页 ========== */
    public function index(){
        /* ********** 查询条件 ********** */
        $datas=[];
        /* ++++++++++ 获取(项目和年份) ++++++++++ */
        $item_id=input('item_id');
        $years = input('years');
        if(is_numeric($item_id)){
            $datas['item_id']=$item_id;
        }
        if($years){
            $datas['years_val']=$years;
        }

        if(!$item_id&&!$years){
            /* ++++++++++ 资金收入 ++++++++++ */
            $fundsin_info=model('Fundsins')
                ->withTrashed()
                ->field(['sum(amount) as amounts','FROM_UNIXTIME(entry_at,\'%Y\') as group_time'])
                ->group('FROM_UNIXTIME(entry_at,\'%Y\')')
                ->select();
            /* ++++++++++ 资金支出 ++++++++++ */
            $fundsout_info=model('Fundsouts')
                ->withTrashed()
                ->field(['sum(amount) as amounts','FROM_UNIXTIME(outlay_at,\'%Y\') as group_time'])
                ->group('FROM_UNIXTIME(outlay_at,\'%Y\')')
                ->select();
            $datas['fundsin_info'] =json_encode($fundsin_info);
            $datas['fundsout_info'] =json_encode($fundsout_info);
            $datas['types'] = 1;
        }
        if($years&&$item_id){
            $years_in['FROM_UNIXTIME(fi.entry_at)'] = array('like',"$years%");
            $years_out['FROM_UNIXTIME(fi.outlay_at)'] = array('like',"$years%");
            /* ++++++++++ 资金收入 ++++++++++ */
            $fundsin_info=model('Fundsins')
                ->withTrashed()
                ->alias('fi')
                ->field(['fn.name as fundsname','sum(amount) as amounts','FROM_UNIXTIME(fi.entry_at,\'%Y-%m\') as group_time'])
                ->join('funds_name fn', 'fn.id=fi.name_id', 'left')
                ->where('fi.item_id',$item_id)
                ->where($years_in)
                ->group('group_time,fundsname')
                ->select();
            $fundsin_vals = [];
            $fundsin_titles = [];
            $group_time = [];
            foreach ($fundsin_info as $k=>$v){
                $fundsin_vals[$v['fundsname']][] = $v['amounts'];
                $fundsin_titles[$v['fundsname']][] = $v['fundsname'];
                $group_time[] = $v['group_time'];
            }
            $fundsin_cont = '';
            $in_title = [];
            foreach ($fundsin_vals as $k=>$v){
                $fundsin_cont .= "{name:'".$fundsin_titles[$k][0]."',type:'line',data:".json_encode($v)."},";
                $in_title[] = $fundsin_titles[$k][0];
            }
            $fundsin_cont = substr($fundsin_cont,0,strlen($fundsin_cont)-1);
            $datas['fundsin_cont'] = $fundsin_cont;
            $datas['fundsin_times'] =json_encode(array_values(array_unique($group_time)));
            $datas['fundsin_titles'] =json_encode($in_title);
            /* ++++++++++ 资金支出 ++++++++++ */
            $fundsout_info=model('Fundsouts')
                ->withTrashed()
                ->alias('fi')
                ->field(['fn.name as fundsname','sum(amount) as amounts','FROM_UNIXTIME(fi.outlay_at,\'%Y-%m\') as group_time'])
                ->join('funds_name fn', 'fn.id=fi.name_id', 'left')
                ->where('fi.item_id',$item_id)
                ->where($years_out)
                ->group('group_time,fundsname')
                ->select();

            $fundsout_vals = [];
            $fundsout_titles = [];
            $group_time = [];
            foreach ($fundsout_info as $k=>$v){
                $fundsout_vals[$v['fundsname']][] = $v['amounts'];
                $fundsout_titles[$v['fundsname']][] = $v['fundsname'];
                $group_time[] = $v['group_time'];
            }
            $fundsout_cont = '';
            $out_title = [];
            foreach ($fundsout_vals as $k=>$v){
                $fundsout_cont .= "{name:'".$fundsout_titles[$k][0]."',type:'line',data:".json_encode($v)."},";
                $out_title[] = $fundsout_titles[$k][0];
            }
            $fundsout_cont = substr($fundsout_cont,0,strlen($fundsout_cont)-1);
            $datas['fundsout_cont'] = $fundsout_cont;
            $datas['fundsout_times'] =json_encode(array_values(array_unique($group_time)));
            $datas['fundsout_titles'] =json_encode($out_title);
            $item_name = model('Items')->where('id',$item_id)->value('name');
            $datas['item_name'] = $item_name;
            $datas['types'] = 2;
        }
        if($item_id&&!$years){
            /* ++++++++++ 资金收入 ++++++++++ */
            $fundsin_info=model('Fundsins')
                ->withTrashed()
                ->alias('fi')
                ->field(['fn.name as fundsname','sum(amount) as amounts'])
                ->join('funds_name fn', 'fn.id=fi.name_id', 'left')
                ->where('fi.item_id',$item_id)
                ->group('fundsname')
                ->select();
            $fundsin_vals = '';
            $fundsin_titles = [];
            foreach ($fundsin_info as $k=>$v){
                $fundsin_vals.= '{value:'.$v['amounts'].",name:'".$v['fundsname']."'},";
                $fundsin_titles[] = $v['fundsname'];
            }
            $fundsin_vals = substr($fundsin_vals,0,strlen($fundsin_vals)-1);
            $datas['fundsin_vals'] = $fundsin_vals;
            $datas['fundsin_titles'] = json_encode($fundsin_titles);
            /* ++++++++++ 资金支出 ++++++++++ */
            $fundsout_info=model('Fundsouts')
                ->withTrashed()
                ->alias('fi')
                ->field(['fn.name as fundsname','sum(amount) as amounts'])
                ->join('funds_name fn', 'fn.id=fi.name_id', 'left')
                ->where('fi.item_id',$item_id)
                ->group('fundsname')
                ->select();
            $fundsout_vals = '';
            $fundsout_titles = [];
            foreach ($fundsout_info as $k=>$v){
                $fundsout_vals.= '{value:'.$v['amounts'].",name:'".$v['fundsname']."'},";
                $fundsout_titles[] = $v['fundsname'];
            }
            $fundsout_vals = substr($fundsout_vals,0,strlen($fundsout_vals)-1);
            $datas['fundsout_vals'] = $fundsout_vals;
            $datas['fundsout_titles'] = json_encode($fundsout_titles);
            $item_name = model('Items')->where('id',$item_id)->value('name');
            $datas['item_name'] = $item_name;
            $datas['types'] = 3;
        }
        if(!$item_id&&$years){
            $years_in['FROM_UNIXTIME(entry_at)'] = array('like',"$years%");
            $years_out['FROM_UNIXTIME(outlay_at)'] = array('like',"$years%");
            /* ++++++++++ 资金收入 ++++++++++ */
            $fundsin_info=model('Fundsins')
                ->withTrashed()
                ->field(['sum(amount) as amounts','FROM_UNIXTIME(entry_at,\'%Y-%m\') as group_time'])
                ->where($years_in)
                ->group('group_time')
                ->select();
            /* ++++++++++ 资金支出 ++++++++++ */
            $fundsout_info=model('Fundsouts')
                ->withTrashed()
                ->field(['sum(amount) as amounts','FROM_UNIXTIME(outlay_at,\'%Y-%m\') as group_time'])
                ->where($years_out)
                ->group('group_time')
                ->select();
            $datas['fundsin_info'] =json_encode($fundsin_info);
            $datas['fundsout_info'] =json_encode($fundsout_info);
            $datas['types'] = 4;
        }

        /* ++++++++++ 【项目列表】 ++++++++++ */
        $items=Items::field(['id','name','status','is_top'])->order('is_top desc')->select();
        $datas['item_list']=$items;
        /* ++++++++++ 【查询年份】 ++++++++++ */
        $years_1=model('Fundsins')
            ->withTrashed()
            ->field(['entry_at'])
            ->select();
        $years_2 = model('Fundsouts')
            ->withTrashed()
            ->field(['outlay_at'])
            ->select();
        $years = [];
        foreach ($years_1 as $k=>$v){
            $years[] = substr($v->entry_at,0,4);
        }
        foreach ($years_2 as $k=>$v){
            $years[] = substr($v->outlay_at,0,4);
        }
        $years = array_unique($years);
        $datas['years_info'] = $years;


        $this->assign($datas);
        return view();
    }
}