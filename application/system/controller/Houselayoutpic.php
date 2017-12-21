<?php
/* |------------------------------------------------------
 * | 房源户型图
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 状态
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;

use app\system\model\Housecommunitys;
use app\system\model\Houselayoutpics;
use app\system\model\Layouts;
use think\Db;

class Houselayoutpic extends Auth
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
        $field=['p.id','community_id','layout_id','remark','p.status','p.deleted_at','c.name as c_name','l.name as l_name'];

        /* ++++++++++ 小区 ++++++++++ */
        $community_id=input('community_id');
        if($community_id){
            $where['community_id']=$community_id;
            $datas['community_id']=$community_id;
        }
        /* ++++++++++ 户型 ++++++++++ */
        $layout_id=input('layout_id');
        if($layout_id){
            $where['layout_id']=$layout_id;
            $datas['layout_id']=$layout_id;
        }
        /* ++++++++++ 标记 ++++++++++ */
        $remark=trim(input('remark'));
        if($remark){
            $where['remark']=['like','%'.$remark.'%'];
            $datas['remark']=$remark;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1])){
            $where['house_layout_pic.status']=$status;
            $datas['status']=$status;
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
        /* ++++++++++ 是否删除 ++++++++++ */
        $deleted=input('deleted');
        $houselayoutpic_model=new Houselayoutpics();
        $datas['model']=$houselayoutpic_model;

        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $houselayoutpic_model=$houselayoutpic_model->onlyTrashed();
            }
        }else{
            $houselayoutpic_model=$houselayoutpic_model->withTrashed();
        }
        $houselayoutpics=$houselayoutpic_model
            ->alias('p')
            ->join('house_community c','c.id=p.community_id','left')
            ->join('layout l','l.id=p.layout_id','left')
            ->where($where)
            ->field($field)
            ->order(['house_layout_pic.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['houselayoutpics']=$houselayoutpics;

        /* ++++++++++ 小区列表 ++++++++++ */
        $communitys=Housecommunitys::field(['id','name','status'])->where('status',1)->select();
        $datas['communitys']=$communitys;
        /* ++++++++++ 户型列表 ++++++++++ */
        $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();
        $datas['layouts']=$layouts;

        $this->assign($datas);

        return view($this->theme.'/houselayoutpic/index');
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Houselayoutpics();
        if(request()->isPost()){
            $rules=[
                'community_id'=>'require',
                'layout_id'=>'require',
                'remark'=>'require|unique:house_layout_pic,community_id='.input('community_id').'&layout_id='.input('layout_id').'&remark='.input('remark'),
                'picture'=>'require',
            ];
            $msg=[
                'community_id.require'=>'请选择小区',
                'layout_id.require'=>'请选择户型',
                'remark.require'=>'标记不能为空',
                'remark.unique'=>'标记已存在',
                'picture.require'=>'请上传户型图',
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
            /* ++++++++++ 小区列表 ++++++++++ */
            $communitys=Housecommunitys::field(['id','name','status'])->where('status',1)->select();
            /* ++++++++++ 户型列表 ++++++++++ */
            $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();

            return view($this->theme.'/houselayoutpic/modify',[
                'model'=>$model,
                'communitys'=>$communitys,
                'layouts'=>$layouts,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Houselayoutpics::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Houselayoutpics();

        /* ++++++++++ 小区列表 ++++++++++ */
        $communitys=Housecommunitys::field(['id','name','status'])->where('status',1)->select();
        /* ++++++++++ 户型列表 ++++++++++ */
        $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();

        return view($this->theme.'/houselayoutpic/modify',[
            'model'=>$model,
            'infos'=>$infos,
            'communitys'=>$communitys,
            'layouts'=>$layouts,
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
            'community_id'=>'require',
            'layout_id'=>'require',
            'remark'=>'require|unique:house_layout_pic,community_id='.input('community_id').'&layout_id='.input('layout_id').'&remark='.input('remark'),
            'picture'=>'require',
        ];
        $msg=[
            'community_id.require'=>'请选择小区',
            'layout_id.require'=>'请选择户型',
            'remark.require'=>'标记不能为空',
            'remark.unique'=>'标记已存在',
            'picture.require'=>'请上传户型图',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $houselayoutpic_model=new Houselayoutpics();
        $other_datas=$houselayoutpic_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $houselayoutpic_model->isUpdate(true)->save($datas);
        if($houselayoutpic_model !== false){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

    /* ========== 状态 ========== */
    public function status(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        $status=input('status');

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        if(!in_array($status,[0,1])){
            return $this->error('错误操作');
        }
        $model=new Houselayoutpics();
        $res=$model->save(['status'=>$status],['id'=>['in',$ids]]);
        if($res){
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
        $res=Houselayoutpics::destroy(['id'=>['in',$ids]]);
        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }

    /* ========== 恢复 ========== */
    public function restore(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }

        $res=Db::table('house_layout_pic')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Houselayoutpics::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
