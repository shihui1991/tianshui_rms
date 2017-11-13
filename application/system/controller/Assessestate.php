<?php
/* |------------------------------------------------------
 * | 房产评估
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
use app\system\model\Assessestates;
use think\Db;

class Assessestate extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
        /* ********** 查询条件 ********** */
        $datas=[];
        $where=[];
        $field=['ass.id','i.name as item_name','cc.name as pq_name','c.building as c_building',
            'c.unit as c_unit','c.floor as c_floor','c.number as c_number','c.id as c_id','cy.name as cy_name','ass.method','ass.valued_at','ass.status','ass.report_at','ass.deleted_at'];
        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        if(is_numeric($item_id)){
            $where['ass.item_id']=$item_id;
            $datas['item_id']=$item_id;
        }
        /* ++++++++++ 片区 ++++++++++ */
        $community_id=input('community_id');
        if(is_numeric($community_id)){
            $where['ass.community_id']=$community_id;
            $datas['community_id']=$community_id;
        }
        /* ++++++++++ 权属 ++++++++++ */
        $collection_id=input('collection_id');
        if(is_numeric($collection_id)){
            $where['ass.collection_id']=$collection_id;
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
        /* ++++++++++ 查询 ++++++++++ */
        $assessestate_model=new Assessestates();
        $deleted=input('deleted');
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $assessestate_model=$assessestate_model->onlyTrashed();
            }
        }else{
            $assessestate_model=$assessestate_model->withTrashed();
        }
        $assessestate_list=$assessestate_model
            ->alias('ass')
            ->field($field)
            ->join('item i','i.id=ass.item_id','left')
            ->join('collection_community cc','cc.id=ass.community_id','left')
            ->join('collection c','c.id=ass.collection_id','left')
            ->join('assess ess','ess.id=ass.assess_id','left')
            ->join('item_company ic','ic.id=ass.company_id','left')
            ->join('company cy','cy.id=ic.company_id','left')
            ->where($where)
            ->order(['i.is_top'=>'desc','ass.'.$ordername=>$orderby])
            ->paginate($display_num);
        $datas['assessestate_list']=$assessestate_list;
        $this->assign($datas);
        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        if(request()->isPost()){
            $model=new Assessestates();
            $datas = input();
            $rule = [
                ['item_id', 'require', '请选择项目'],
                ['community_id', 'require', '请选择片区'],
                ['collection_id', 'require', '请选择权属'],
                ['price', 'require', '建筑不能为空'],
                ['company_id', 'require', '请选择评估公司'],
                ['valuer_id', 'require', '请选择评估师'],
                ['report_at', 'require', '报告时间不能为空'],
                ['valued_at', 'require', '价值时点不能为空'],
                ['method', 'require', '评估方法不能为空'],
                ['picture', 'require', '评估报告不能为空']
            ];
            $result = $this->validate($datas, $rule);
            if(true !== $result){
                return $this->error($result);
            }
           $collections_count = model('Collections')->where('item_id',$datas['item_id'])->where('community_id',$datas['community_id'])->count();
            if($collections_count == 0){
                return $this->error('数据异常','');
            }
            $building_info = [];
           foreach($datas['price'] as $k=>$v){
               for($i=0;$i<count($datas['price']);$i++){
                   $building_info[$i][] = $k;
                   $building_info[$i][] = $v;
               }
           }

            Db::startTrans();
            try{
                /*----- 查询入户评估总表 -----*/
                $search_assess = model('Assesss')
                    ->where('item_id',$datas['item_id'])
                    ->where('collection_id',$datas['collection_id'])
                    ->value('id');
                if($search_assess== 0){
                   $assess_id =  model('Assesss')->save([
                        'item_id'=>$datas['item_id'],
                        'community_id'=>$datas['community_id'],
                        'collection_id'=>$datas['collection_id']
                        ]);
                }else{
                    $assess_id = $search_assess;
                }
                /*----- 添加房产评估 -----*/
                    $estate_id = $model->save([
                        'item_id'=>$datas['item_id'],
                        'community_id'=>$datas['community_id'],
                        'collection_id'=>$datas['collection_id'],
                        'assess_id'=>$assess_id,
                        'company_id'=>$datas['company_id'],
                        'report_at'=>$datas['report_at'],
                        'valued_at'=>$datas['valued_at'],
                        'method'=>$datas['method'],
                        'status'=>1,
                        'picture'=>$datas['picture'],
                    ]);
                /*----- 添加房产评估--建筑评估 -----*/
                    $building_data = [];
                    foreach ($building_info as $k=>$v){
                        $real_num = model('Collectionbuildings')->where('id',$v[0])->value('real_num');
                        $building_data[] = [
                            'item_id'=>$datas['item_id'],
                            'community_id'=>$datas['community_id'],
                            'collection_id'=>$datas['collection_id'],
                            'assess_id'=>$assess_id,
                            'estate_id'=>$estate_id,
                            'building_id'=>$v[0],
                            'price'=>$v[1],
                            'amount'=>$real_num*$v[1]
                        ];
                    }

                   model('Assessestatebuildings')->insertAll($building_data);
                    /*----- 添加房产评估--评估师 -----*/
                     $valuer_ids =  explode(",",$datas['valuer_id']);
                     $valuer_data = [];
                     foreach ($valuer_ids as $k=>$v){
                         $valuer_data[] = [
                             'item_id'=>$datas['item_id'],
                             'collection_id'=>$datas['collection_id'],
                             'assess_id'=>$assess_id,
                             'estate_id'=>$estate_id,
                             'company_id'=>$datas['company_id'],
                             'valuer_id'=>$v
                         ];
                     }
                model('Assessestatevaluers')->insertAll($valuer_data);
                $assess_estate_valuer = true;
                Db::commit();
            }catch(\Exception $e){
                $assess_estate_valuer = false;
                Db::rollback();
            }
            if($assess_estate_valuer){
                return $this->success('添加成功','');
            }else{
                return $this->error('添加失败','');
            }
        }else{
            /* ++++++++++ 项目列表 ++++++++++ */
            $items=model('Items')->field(['id','name','status'])->where('status',1)->order('is_top desc')->select();
            /* ++++++++++ 片区 ++++++++++ */
            $collectioncommunitys=model('Collectioncommunitys')->field(['id','address','name'])->select();

            return view('add',
                [   'items'=>$items,
                    'collectioncommunitys'=>$collectioncommunitys
             ]);
        }

    }

}