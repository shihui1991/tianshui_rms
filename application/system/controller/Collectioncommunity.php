<?php
/* |------------------------------------------------------
 * | 征地片区
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * */
namespace app\system\controller;

use app\system\model\Collectioncommunitys;
use think\Db;

class Collectioncommunity extends Auth
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
        $field=['id','address','name','infos'];
        /* ++++++++++ 地址 ++++++++++ */
        $address=trim(input('address'));
        if($address){
            $where['address']=['like','%'.$address.'%'];
            $datas['address']=$address;
        }
        /* ++++++++++ 名称 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['name']=['like','%'.$name.'%'];
            $datas['name']=$name;
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
        $collectioncommunity_model=new Collectioncommunitys();

        $collectioncommunitys=$collectioncommunity_model->where($where)->field($field)->order([$ordername=>$orderby])->paginate($display_num);

        $datas['collectioncommunitys']=$collectioncommunitys;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Collectioncommunitys();
        if(request()->isPost()){
            $rules=[
                'name'=>'require|unique:collection_community',
            ];
            $msg=[
                'name.require'=>'名称不能为空',
                'name.unique'=>'名称已存在',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $model->save($datas);
            if($model !== false){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            return view('modify',[
                'model'=>$model,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collectioncommunitys::find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collectioncommunitys();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $id=input('id');
        if(!$id){
            return $this->error('错误操作');
        }
        $datas=input();
        $rules=[
            'name'=>'require|unique:collection_community,name,'.$id.',id',
        ];
        $msg=[
            'name.require'=>'名称不能为空',
            'name.unique'=>'名称已存在',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $collectioncommunity_model=new Collectioncommunitys();
        $other_datas=$collectioncommunity_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $collectioncommunity_model->isUpdate(true)->save($datas);
        if($collectioncommunity_model !== false){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }
}
