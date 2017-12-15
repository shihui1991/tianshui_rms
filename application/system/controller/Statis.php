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
        /* ********** 查询执行 ********** */
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

        /* ++++++++++ 【查询项目下拉框】 ++++++++++ */
        $items=Items::field(['id','name','status','is_top'])->order('is_top desc')->select();
        $datas['item_list']=$items;
        /* ++++++++++ 【查询年份下拉框】 ++++++++++ */
        $years_1=model('Fundsins')
            ->withTrashed()
            ->field(['entry_at'])
            ->select();
        $years_2 = model('Fundsouts')
            ->withTrashed()
            ->alias('fi')
            ->field(['fi.outlay_at','fn.name as fundsname'])
            ->join('funds_name fn', 'fn.id=fi.name_id', 'left')
            ->select();
        /* $years支出与收入所有年份--$years_in收入所有年份 */
        $years = [];
        $years_in = [];
        foreach ($years_1 as $k=>$v){
            $years[] = substr($v->entry_at,0,4);
            $years_in[] = substr($v->entry_at,0,4);
        }

        $years_in = array_unique($years_in);
        asort($years_in);
        $datas['years_in'] = array_values($years_in);

        /* $fundsname_array所有款项名称 */
        $fundsname_array = [];
        foreach ($years_2 as $k=>$v){
            $years[] = substr($v->outlay_at,0,4);
            $fundsname_array[] = $v->fundsname;
        }
        $years = array_unique($years);
        $datas['years_info'] = $years;

        $fundsname_array = array_values(array_unique($fundsname_array));
        asort($fundsname_array);
        $datas['fundsname_array'] = $fundsname_array;

        /* ++++++++++ 【列表数据】[查询支出与收入] ++++++++++ */
        $fundsin_array = model('Fundsins')
            ->withTrashed()
            ->alias('fi')
            ->field(['fi.item_id','i.name as item_name','sum(amount) as amounts_in','FROM_UNIXTIME(fi.entry_at,\'%Y\') as group_time'])
            ->join('funds_name fn', 'fn.id=fi.name_id', 'left')
            ->join('item i', 'i.id=fi.item_id', 'left')
            ->group('item_id,group_time')
            ->select();
        $fundsout_array=model('Fundsouts')
            ->withTrashed()
            ->alias('fi')
            ->field(['fi.item_id','i.name as item_name','fn.name as fundsname','sum(amount) as amounts_out'])
            ->join('funds_name fn', 'fn.id=fi.name_id', 'left')
            ->join('item i', 'i.id=fi.item_id', 'left')
            ->group('item_id,fundsname')
            ->select();
        /* ++++++++++ 拼装数据(收入部分) ++++++++++ */
        /* 所有年份 */
        $new_years = [];
        foreach ($datas['years_in'] as $k=>$v){
            $new_years[$v] = $v;
        }
        /* 项目->收入所有年份(年份收入)
        *$new_array_in 年份收入分组
        *$item_id_arr 项目=>项目ID*/
       $new_array_in = [];
       $item_id_arr = [];
       foreach ($fundsin_array as $k=>$v){
           $new_array_in[$v['item_name']][$v['group_time']] = $v['amounts_in'];
           $item_id_arr[$v['item_name']] = $v['item_id'];
       }
        $fundsin_year_val = [];
        foreach ($new_array_in as $k=>$v){
               $new_filed = array_diff_key($new_years,$v);
               $diff_filed = array_keys($new_filed);
              for ($i = 0;$i<count($diff_filed);$i++){
                    $v[$diff_filed[$i]] = 0;
              }
              ksort($v);
             $v['sums'] = array_sum(array_values($v));
             $fundsin_year_val[$k][] = $k;
             $fundsin_year_val[$k][] = $item_id_arr[$k];
             $fundsin_year_val[$k][] = array_values($v);
        }
        /* ++++++++++ 拼装数据(支出部分) ++++++++++ */
        /* 所有款项 */
        $new_fundsname = [];
        foreach ($fundsname_array as $k=>$v){
            $new_fundsname[$v] = $v;
        }
        /* 项目->支出所有款项(款项支出)
        *$item_id_arrout 项目=>项目ID*/
        $new_array_out = [];
        $item_id_arrout = [];
        foreach ($fundsout_array as $k=>$v){
            $new_array_out[$v['item_name']][$v['fundsname']] = $v['amounts_out'];
            $item_id_arrout[$v['item_name']] = $v['item_id'];
        }

        $fundsout_fundsname_val = [];
        foreach ($new_array_out as $k=>$v){
            $new_filed_out = array_diff_key($new_fundsname,$v);

            $diff_filed_val = array_keys($new_filed_out);

            for ($i = 0;$i<count($diff_filed_val);$i++){
                $v[$diff_filed_val[$i]] = 0;
            }
            ksort($v);
            $v['sums'] = array_sum(array_values($v));
            $fundsout_fundsname_val[$k][] = array_values($v);
        }
        /* ++++++++++ 拼装数据(项目支出收入) ++++++++++ */
        $new_array = [];
        if(isset($fundsin_year_val)&&isset($fundsout_fundsname_val)){
            foreach ($fundsin_year_val as $k=>$v){
                foreach ($fundsout_fundsname_val as $key=>$val){
                    if($k==$key){
                        $new_array[$k] = $v;
                        foreach ($val as $keys=>$vals){
                            $new_array[$k][] = $vals;
                        }
                    }
                }
            }
        }
        if(isset($fundsin_year_val)&&empty($fundsout_fundsname_val)){
            foreach ($fundsin_year_val as $k=>$v) {
                $new_array[$k] = $v;
                $new_array[$k][] = ['0'];
            }
        }
        if(empty($fundsin_year_val)&&isset($fundsout_fundsname_val)){
            foreach ($fundsout_fundsname_val as $k=>$v) {
                $new_array[$k][0] = $k;
                $new_array[$k][1] = $item_id_arrout[$k];
                $new_array[$k][2] = ['0'];
                foreach ($v as $keys=>$vals){
                    $new_array[$k][3] = $vals;
                }
            }
        }
        /*计算项目结余*/
       $statis_list = [];
      foreach ($new_array as $k=>$v){
          $a = array_sum($v[2])/2;
          $b = array_sum($v[3])/2;
          $v['diffs'] = $a-$b;
          $statis_list[] = array_values($v);
      }
     $datas['statis_list'] = $statis_list;

        $this->assign($datas);
        return view();
    }

    /* ========== 导出勾选项目统计数据 ========== */
    public function excel_statis(){
        $item_id = input('ids');
        if(!$item_id){
            return $this->error('请勾选项目');
        }
        /* ++++++++++ 【查询年份下拉框】 ++++++++++ */
        $years_1=model('Fundsins')
            ->withTrashed()
            ->field(['entry_at'])
            ->select();
        $years_2 = model('Fundsouts')
            ->withTrashed()
            ->alias('fi')
            ->field(['fi.outlay_at','fn.name as fundsname'])
            ->join('funds_name fn', 'fn.id=fi.name_id', 'left')
            ->select();
        /* $years_in收入所有年份 */
        $years_in = [];
        foreach ($years_1 as $k=>$v){
            $years_in[] = substr($v->entry_at,0,4);
        }

        $years_in = array_unique($years_in);
        asort($years_in);
        $datas['years_in'] = array_values($years_in);

        /* $fundsname_array所有款项名称 */
        $fundsname_array = [];
        foreach ($years_2 as $k=>$v){
            $fundsname_array[] = $v->fundsname;
        }
        $fundsname_array = array_values(array_unique($fundsname_array));
        asort($fundsname_array);

        /* ++++++++++ 【列表数据】[查询支出与收入] ++++++++++ */
        $fundsin_array = model('Fundsins')
            ->withTrashed()
            ->alias('fi')
            ->field(['i.name as item_name','sum(amount) as amounts_in','FROM_UNIXTIME(fi.entry_at,\'%Y\') as group_time'])
            ->join('funds_name fn', 'fn.id=fi.name_id', 'left')
            ->join('item i', 'i.id=fi.item_id', 'left')
            ->group('item_id,group_time')
            ->where('item_id','in',$item_id)
            ->select();
        $fundsout_array=model('Fundsouts')
            ->withTrashed()
            ->alias('fi')
            ->field(['i.name as item_name','fn.name as fundsname','sum(amount) as amounts_out'])
            ->join('funds_name fn', 'fn.id=fi.name_id', 'left')
            ->join('item i', 'i.id=fi.item_id', 'left')
            ->group('item_id,fundsname')
            ->where('item_id','in',$item_id)
            ->select();
        /* ++++++++++ 拼装数据(收入部分) ++++++++++ */
        /* 所有年份 */
        $new_years = [];
        foreach ($datas['years_in'] as $k=>$v){
            $new_years[$v] = $v;
        }
        /* 项目->收入所有年份(年份收入) */
        $new_array_in = [];
        foreach ($fundsin_array as $k=>$v){
            $new_array_in[$v['item_name']][$v['group_time']] = $v['amounts_in'];
        }

        $fundsin_year_val = [];
        foreach ($new_array_in as $k=>$v){
            $new_filed = array_diff_key($new_years,$v);
            $diff_filed = array_keys($new_filed);
            for ($i = 0;$i<count($diff_filed);$i++){
                $v[$diff_filed[$i]] = 0;
            }
            ksort($v);
            $v['sums'] = array_sum(array_values($v));
            $fundsin_year_val[$k][] = $k;
            $fundsin_year_val[$k][] = array_values($v);
        }
        /* ++++++++++ 拼装数据(支出部分) ++++++++++ */
        /* 所有款项 */
        $new_fundsname = [];
        foreach ($fundsname_array as $k=>$v){
            $new_fundsname[$v] = $v;
        }
        /* 项目->支出所有款项(款项支出) */
        $new_array_out = [];
        foreach ($fundsout_array as $k=>$v){
            $new_array_out[$v['item_name']][$v['fundsname']] = $v['amounts_out'];
        }

        $fundsout_fundsname_val = [];
        foreach ($new_array_out as $k=>$v){
            $new_filed_out = array_diff_key($new_fundsname,$v);

            $diff_filed_val = array_keys($new_filed_out);

            for ($i = 0;$i<count($diff_filed_val);$i++){
                $v[$diff_filed_val[$i]] = 0;
            }
            ksort($v);
            $v['sums'] = array_sum(array_values($v));
            $fundsout_fundsname_val[$k][] = array_values($v);
        }

        /* ++++++++++ 拼装数据(项目支出收入) ++++++++++ */
        $new_array = [];
        if(isset($fundsin_year_val)&&isset($fundsout_fundsname_val)){
            foreach ($fundsin_year_val as $k=>$v){
                foreach ($fundsout_fundsname_val as $key=>$val){
                    if($k==$key){
                        $new_array[$k] = $v;
                        foreach ($val as $keys=>$vals){
                            $new_array[$k][] = $vals;
                        }
                    }
                }
            }
        }
        if(isset($fundsin_year_val)&&empty($fundsout_fundsname_val)){
            foreach ($fundsin_year_val as $k=>$v) {
                $new_array[$k] = $v;
                $new_array[$k][] = ['0'];
            }
        }
        if(empty($fundsin_year_val)&&isset($fundsout_fundsname_val)){
            foreach ($fundsout_fundsname_val as $k=>$v) {
                $new_array[$k][0] = $k;
                $new_array[$k][1] = ['0'];
                foreach ($v as $keys=>$vals){
                    $new_array[$k][2] = $vals;
                }
            }
        }
        $statis_list = [];
        $ks = 0;
        foreach ($new_array as $k=>$v){
            $ks++;
            $a = array_sum($v[1])/2;
            $b = array_sum($v[2])/2;
            $v['diffs'] = $a-$b;

            $statis_list[$k][] = $ks;
            $statis_list[$k][] = $v[0];

            foreach ($v[1] as $key=>$val){
                if($val==0){
                    $val = '0';
                }
                $statis_list[$k][] = $val;
            }
            foreach ($v[2] as $keys=>$vals){
                if($vals==0){
                    $vals = '0';
                }
                $statis_list[$k][] = $vals;
            }
            $statis_list[$k][] = $v['diffs'];
        }

        $new_title_a = [];
        $new_title_b = [];
        $new_title_a[0] = '序号';
        $new_title_b[0] = '序号';
        $new_title_a[1] = '项目名称';
        $new_title_b[1] = '项目名称';
        if($datas['years_in']){
            foreach ($datas['years_in'] as $k=>$v){
                $new_title_a[2] = '收入情况';
                if($k!=0) {
                    $new_title_a[2 + $k] = '';
                }
                $new_title_b[2+$k] = $v.'年';
                $new_title_a[2+$k+1] = '收入合计';
                $new_title_b[2+$k+1] = '收入合计';
            }
        }else{
            $new_title_a[2] = '收入合计';
            $new_title_b[2] = '收入合计';
        }
        $fundin_cout = count($new_title_a);

        if($fundsname_array){
            foreach(array_values($fundsname_array) as $k=>$v ){
                $new_title_a[$fundin_cout] = '支出情况';
                if($k!=0){
                    $new_title_a[$fundin_cout+$k] = '';
                }
                $new_title_b[$fundin_cout+$k] = $v;
                $new_title_a[$fundin_cout+$k+1] = '支出合计';
                $new_title_b[$fundin_cout+$k+1] = '支出合计';
                $new_title_a[$fundin_cout+$k+2] = '结余';
                $new_title_b[$fundin_cout+$k+2] = '结余';
                $new_title_a[$fundin_cout+$k+3] = '备注';
                $new_title_b[$fundin_cout+$k+3] = '备注';
            }   
        }else{
            $new_title_a[$fundin_cout] = '支出合计';
            $new_title_b[$fundin_cout] = '支出合计';
            $new_title_a[$fundin_cout+1] = '结余';
            $new_title_b[$fundin_cout+1] = '结余';
            $new_title_a[$fundin_cout+2] = '备注';
            $new_title_b[$fundin_cout+2] = '备注';
        }
       

        $datas_title = [$new_title_a,$new_title_b];
        $datas_excel = array_merge($datas_title,array_values($statis_list));
        $ColumnDimension_array = range('A','Z');
        if($datas['years_in']){
            if($fundsname_array){
                $ColumnDimension1 = $ColumnDimension_array[2+count($datas['years_in'])-1];
                $ColumnDimension2 = $ColumnDimension_array[2+count($datas['years_in'])+1];
                $ColumnDimension3 = $ColumnDimension_array[2+count($datas['years_in'])+count($fundsname_array)];
            }else{
                $ColumnDimension1 = $ColumnDimension_array[2+count($datas['years_in'])-1];
                $ColumnDimension2 = $ColumnDimension_array[2+count($datas['years_in'])+1];
                $ColumnDimension3 = $ColumnDimension_array[2+count($datas['years_in'])+1];
            }

        }else{
            $ColumnDimension1 = $ColumnDimension_array[2+count($datas['years_in'])];
            $ColumnDimension2 = $ColumnDimension_array[2+count($datas['years_in'])+2];
            $ColumnDimension3 = $ColumnDimension_array[3+count($datas['years_in'])+count($fundsname_array)];
        }

        $cd1 = $ColumnDimension_array[2+count($datas['years_in'])];
        $cd2 = $ColumnDimension_array[2+count($datas['years_in'])+count($fundsname_array)+1];
        $cd3 = $ColumnDimension_array[2+count($datas['years_in'])+count($fundsname_array)+2];
        $cd4 = $ColumnDimension_array[2+count($datas['years_in'])+count($fundsname_array)+3];

        if ($fundsin_array||$fundsout_array){
            create_xls($cd1,$cd2,$cd3,$cd4,$ColumnDimension1,$ColumnDimension2,$ColumnDimension3,$datas_excel,'项目资金明细'.date('Ymd'));
        }else{
            return $this->error('暂无数据');
        }
    }

}