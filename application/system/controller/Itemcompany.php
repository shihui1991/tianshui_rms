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
use app\system\model\Itemstatuss;
use think\Db;
use think\Exception;

class Itemcompany extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
        $where=[];
        $datas=[];
        /* ********** 是否弹出层 ********** */
        $l=input('l');
        $item_id=input('item_id');
        if($l){
            if(!$item_id){
                return $this->error('错误操作','');
            }
            $with='company';
            $view='index';
            /* ++++++++++ 项目信息 ++++++++++ */
            $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
            $datas['item_info']=$item_info;
            $where['item_id']=$item_id;

        }else{
            if($item_id){
                $where['item_id']=$item_id;
                $datas['item_id']=$item_id;
            }
            $with='item,company';
            $view='all';
            /* ++++++++++ 项目列表 ++++++++++ */
            $items=Items::field(['id','name','status','is_top'])->order('is_top desc')->select();
            $datas['items']=$items;
        }


        /* ********** 查询条件 ********** */

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
            ->with($with)
            ->where($where)
            ->order([$ordername=>$orderby])
            ->paginate($display_num);

        $datas['itemcompanys']=$itemcompanys;

        /* ++++++++++ 评估公司 ++++++++++ */
        $companys=Companys::field(['id','name','status'])->where('status',1)->order('sort asc')->select();
        $datas['companys']=$companys;

        $this->assign($datas);

        return view($view);
    }

    /* ========== 添加 ========== */
    public function add(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        $item_info=Items::where('id',input('item_id'))->field(['id','name','status'])->find();
        if(!$item_info){
            return $this->error('选择项目不存在');
        }
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
                case 2:
                    $msg='项目已完成，禁止操作！';
                    break;
                case 3:
                    $msg='项目已取消，禁止操作！';
                    break;
                default:
                    $msg='项目未进行，禁止操作！';
            }
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }


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

            Db::startTrans();
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
            /* ++++++++++ 片区 ++++++++++ */
            $collectioncommunitys=Collectioncommunitys::field(['id','address','name'])->select();

            return view('add',[
                'model'=>$model,
                'item_info'=>$item_info,
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
            'item_id.require'=>'数据错误',
            'company_id.require'=>'数据错误',
            'company_id.unique'=>'评估公司已存在',
            'ids.require'=>'请选择被征户',
        ];

        $result=$this->validate(input(),$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $item_info=Items::where('id',input('item_id'))->field(['id','name','status'])->find();
        if(!$item_info){
            return $this->error('项目不存在');
        }
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
                case 2:
                    $msg='项目已完成，禁止操作！';
                    break;
                case 3:
                    $msg='项目已取消，禁止操作！';
                    break;
                default:
                    $msg='项目未进行，禁止操作！';
            }
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }



        $model=new Itemcompanys();
        $other_datas=$model->other_data(input());
        $datas=array_merge(input(),$other_datas);

        Db::startTrans();
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
    }


    /* ========== 删除 ========== */
    public function delete(){
        $item_info=Items::where('id',input('item_id'))->field(['id','name','status'])->find();
        if(!$item_info){
            return $this->error('项目不存在');
        }
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
                case 2:
                    $msg='项目已完成，禁止操作！';
                    break;
                case 3:
                    $msg='项目已取消，禁止操作！';
                    break;
                default:
                    $msg='项目未进行，禁止操作！';
            }
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }


        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $model=new Itemcompanys();
        try{
            $itemcompany_ids=Itemcompanys::whereIn('id',$ids)->column('id');
            if(!$itemcompany_ids){
                throw new Exception('数据不存在');
            }

            Itemcompanycollections::whereIn('item_company_id',$itemcompany_ids)->delete();
            $model->whereIn('id',$itemcompany_ids)->delete();
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