<?php
/* |------------------------------------------------------
 * | 征地片区
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
        $field=['id','address','name','infos','deleted_at'];
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
        $deleted=input('deleted');
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $collectioncommunity_model=$collectioncommunity_model->onlyTrashed();
            }
        }else{
            $collectioncommunity_model=$collectioncommunity_model->withTrashed();
        }
        $collectioncommunitys=$collectioncommunity_model->where($where)->field($field)->order([$ordername=>$orderby])->paginate($display_num);

        $datas['collectioncommunitys']=$collectioncommunitys;

        $this->assign($datas);

        return view($this->theme.'/collectioncommunity/index');
    }

    /* ========== 添加 ========== */
    public function add(){
        $model=new Collectioncommunitys();
        if(request()->isPost()){
            $rules=[
                'address'=>'require',
                'name'=>'require|unique:collection_community',
            ];
            $msg=[
                'address.require'=>'地址不能为空',
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
            return view($this->theme.'/collectioncommunity/modify',[
                'model'=>$model,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Collectioncommunitys::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Collectioncommunitys();

        return view($this->theme.'/collectioncommunity/modify',[
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
            'address'=>'require',
            'name'=>'require|unique:collection_community,name,'.$id.',id',
        ];
        $msg=[
            'address.require'=>'地址不能为空',
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

    /* ========== 删除 ========== */
    public function delete(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }

        /*----- 当删除条数为1条时 -----*/
        if(count($ids)==1){
            if(is_array($ids)){
                $collectioncommunitys_ids = $ids[0];
            }else{
                $collectioncommunitys_ids = $ids;
            }
            $assesss_count = model('Assesss')->withTrashed()->where('community_id',$collectioncommunitys_ids)->count();
            $collection_building_count = model('Collectionbuildings')->withTrashed()->where('community_id',$collectioncommunitys_ids)->count();
            $collection_holder_count = model('Collectionholders')->withTrashed()->where('community_id',$collectioncommunitys_ids)->count();
            $collection_holder_crowd_count = model('Collectionholdercrowds')->withTrashed()->where('community_id',$collectioncommunitys_ids)->count();
            $collection_holder_house_count = model('Collectionholderhouses')->withTrashed()->where('community_id',$collectioncommunitys_ids)->count();
            if($assesss_count||$collection_building_count||$collection_holder_count||$collection_holder_crowd_count||$collection_holder_house_count){
                return $this->error('当前征片地区正在被使用，删除失败');
            }
            $rs =  model('Collectioncommunitys')->destroy(['id'=>$collectioncommunitys_ids]);
            if($rs){
                return $this->success('删除成功','');
            }else{
                return $this->error('删除失败');
            }
        }else{
            /*----- 当删除条数为多条时 -----*/
            $num = 0;
            $del_num = 0;
            $del_ids = [];
            foreach ($ids as $k=>$v){
                $assesss_count = model('Assesss')->withTrashed()->where('community_id',$v)->count();
                $collection_building_count = model('Collectionbuildings')->withTrashed()->where('community_id',$v)->count();
                $collection_holder_count = model('Collectionholders')->withTrashed()->where('community_id',$v)->count();
                $collection_holder_crowd_count = model('Collectionholdercrowds')->withTrashed()->where('community_id',$v)->count();
                $collection_holder_house_count = model('Collectionholderhouses')->withTrashed()->where('community_id',$v)->count();
                if(!$assesss_count&&!$collection_building_count&&!$collection_holder_count&&!$collection_holder_crowd_count&&!$collection_holder_house_count){
                    $del_ids[] = $v;
                    $del_num += 1;
                }else{
                    $num += 1;
                }
            }
            if($del_ids){
                model('Collectioncommunitys')->destroy(['id'=>['in',$del_ids]]);
            }
            if($num==count($ids)){
                return $this->error('选中征片地区正在被使用，删除失败');
            }
            if($del_num==count($ids)){
                return $this->success('删除成功','');
            }else{
                return $this->success('删除成功'.$del_num.'条,其他征片地区正在被使用','');
            }
        }
    }

    /* ========== 恢复 ========== */
    public function restore(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }

        $res=Db::table('collection_community')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
        if($res){
            return $this->success('恢复成功','');
        }else{
            return $this->error('恢复失败');
        }
    }

    /* ========== 销毁 ========== */
    public function destroy(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res=Collectioncommunitys::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
