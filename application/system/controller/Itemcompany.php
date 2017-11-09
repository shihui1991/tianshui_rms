<?php
/* |------------------------------------------------------
 * | 项目评估公司
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 删除
 * */

namespace app\system\controller;

use app\system\model\Collectioncommunitys;
use app\system\model\Companys;
use app\system\model\Itemcompanycollections;
use app\system\model\Itemcompanys;
use app\system\model\Items;

class Itemcompany extends Auth
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
        $field=['ic.id','ic.item_id','ic.company_id','i.name as i_name','c.type','c.name as c_name'];

        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        if(is_numeric($item_id)){
            $where['item_id']=$item_id;
            $datas['item_id']=$item_id;
        }
        /* ++++++++++ 评估公司 ++++++++++ */
        $company_id=input('company_id');
        if(is_numeric($company_id)){
            $where['company_id']=$company_id;
            $datas['company_id']=$company_id;
        }
        /* ++++++++++ 评估公司类型 ++++++++++ */
        $type=input('type');
        if(is_numeric($type) && in_array($type,[0,1])){
            $where['company.type']=$type;
            $datas['type']=$type;
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
        $itemcompany_model=new Itemcompanys();
        $datas['model']=$itemcompany_model;
        $itemcompanys=$itemcompany_model
            ->alias('ic')
            ->field($field)
            ->join('item i','ic.item_id=i.id','left')
            ->join('company c','ic.company_id=c.id','left')
            ->where($where)
            ->order(['item.is_top'=>'desc','item_company.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['itemcompanys']=$itemcompanys;

        /* ++++++++++ 项目列表 ++++++++++ */
        $items=Items::field(['id','name','status'])->where('status',1)->order('is_top desc')->select();
        $datas['items']=$items;
        /* ++++++++++ 评估公司 ++++++++++ */
        $companys=Companys::field(['id','name','status'])->where('status',1)->order('sort asc')->select();
        $datas['companys']=$companys;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        $model=new Itemcompanys();
        if(request()->isPost()){
            $rules=[
                'item_id'=>'require',
                'company_id'=>'require|unique:item_company,item_id='.input('item_id').'&company_id='.input('company_id'),
                'ids'=>'require',
            ];
            $msg=[
                'item_id.require'=>'请选择项目',
                'company_id.require'=>'请选择评估公司',
                'company_id.unique'=>'评估公司已存在',
                'ids.require'=>'请选择被征户',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);

            $model->startTrans();
            try{
                /* ++++++++++ 添加评估公司 ++++++++++ */
                $model->save($datas);
                /* ++++++++++ 添加评估公司-被征户 ++++++++++ */
                $icc_data=[];
                $i=0;
                $input=input();
                foreach ($input['ids'] as $collection_id){
                    if($collection_id){
                        $icc_data[$i]['item_company_id']=$model->id;
                        $icc_data[$i]['collection_id']=$collection_id;
                        $i++;
                    }
                }

                $icc_model=new Itemcompanycollections();
                $icc_model->saveAll($icc_data);
                $res=true;
                $model->commit();
            }catch (\Exception $exception){
                $res=false;
                $model->rollback();
            }

            if($res){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            /* ++++++++++ 项目列表 ++++++++++ */
            $items=Items::field(['id','name','status'])->where('status',1)->order('is_top desc')->select();
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
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $model=new Itemcompanys();

        $infos=Itemcompanys::alias('ic')
            ->field(['ic.*','i.name as i_name','c.name as c_name','c.type'])
            ->join('item i','i.id=ic.item_id','left')
            ->join('company c','c.id=ic.company_id','left')
            ->where('ic.id',$id)
            ->find();
        if(!$infos){
            return $this->error('选择项目不存在');
        }
        $collection_ids=Itemcompanycollections::where('item_company_id',$id)->column('collection_id');
        $collection_ids=$collection_ids?json_encode($collection_ids):json_encode([]);

        /* ++++++++++ 片区 ++++++++++ */
        $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'collection_ids'=>$collection_ids,
            'collectioncommunitys'=>$collectioncommunitys,
            ]);
    }


    /* ========== 修改 ========== */
    public function edit(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $rules=[
            'item_id'=>'require',
            'company_id'=>'require|unique:item_company,item_id='.input('item_id').'&company_id='.input('company_id'),
            'ids'=>'require',
        ];
        $msg=[
            'item_id.require'=>'请选择项目',
            'company_id.require'=>'请选择评估公司',
            'company_id.unique'=>'评估公司已存在',
            'ids.require'=>'请选择被征户',
        ];

        $result=$this->validate(input(),$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }
        $model=new Itemcompanys();
        $other_datas=$model->other_data(input());
        $datas=array_merge(input(),$other_datas);

        $model->startTrans();

        try{
            $model->isUpdate(true)->allowField(['infos','updated_at'])->save($datas);

            /* ++++++++++ 评估公司-被征户 ++++++++++ */
            $icc_data=[];
            $i=0;
            $input=input();
            foreach ($input['ids'] as $collection_id){
                if($collection_id){
                    $icc_data[$i]['item_company_id']=$model->id;
                    $icc_data[$i]['collection_id']=$collection_id;
                    $i++;
                }
            }

            $icc_model=new Itemcompanycollections();
            /* ++++++++++ 清空评估公司-被征户 ++++++++++ */
            $icc_model->where('item_company_id',$id)->delete();
            /* ++++++++++ 添加评估公司-被征户 ++++++++++ */
            $icc_model->saveAll($icc_data);
            $res=true;
            $model->commit();
        }catch (\Exception $exception){
            $res=false;
            $model->rollback();
        }

        if($res){
            return $this->success('保存成功','');
        }else{
            return $this->error('保存失败');
        }
    }


    /* ========== 删除 ========== */
    public function delete(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $model=new Itemcompanys();
        try{
            Itemcompanycollections::whereIn('item_company_id',$ids)->delete();
            $model->whereIn('id',$ids)->delete();
            $res=true;
            $model->commit();
        }catch (\Exception $exception){
            $res=false;
            $model->rollback();
        }

        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }
}