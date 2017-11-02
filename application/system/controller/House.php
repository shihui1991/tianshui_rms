<?php
/* |------------------------------------------------------
 * | 安置房源
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
use app\system\model\Houses;
use app\system\model\Layouts;
use think\Db;

class House extends Auth
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
        $field=['h.*','c.address','c.name as c_name','l.name as l_name'];
        $inputs=input();
        /* ++++++++++ 地址 ++++++++++ */
        $community_ids_a=isset($inputs['community_ids_a'])?$inputs['community_ids_a']:[];
        $datas['community_ids_a']=$community_ids_a;
        /* ++++++++++ 小区 ++++++++++ */
        $community_ids_c=isset($inputs['community_ids_c'])?$inputs['community_ids_c']:[];
        $datas['community_ids_c']=$community_ids_c;
        $community_ids=array_filter(array_merge($community_ids_a,$community_ids_c));
        if($community_ids){
            $where['community_id']=['in',$community_ids];
            $datas['community_ids']=$community_ids;
        }
        /* ++++++++++ 几栋 ++++++++++ */
        $building=input('building');
        if(is_numeric($building)){
            $where['building']=$building;
            $datas['building']=$building;
        }
        /* ++++++++++ 几单元 ++++++++++ */
        $unit=input('unit');
        if(is_numeric($unit)){
            $where['unit']=$unit;
            $datas['unit']=$unit;
        }
        /* ++++++++++ 几楼 ++++++++++ */
        $floor=input('floor');
        if(is_numeric($floor)){
            $where['floor']=$floor;
            $datas['floor']=$floor;
        }
        /* ++++++++++ 几号 ++++++++++ */
        $number=input('number');
        if(is_numeric($number)){
            $where['number']=$number;
            $datas['number']=$number;
        }
        /* ++++++++++ 户型 ++++++++++ */
        $layout_id=input('layout_id');
        if($layout_id){
            $where['layout_id']=$layout_id;
            $datas['layout_id']=$layout_id;
        }
        /* ++++++++++ 面积 ++++++++++ */
        $area_start=input('area_start');
        if($area_start){
            $where['area']=['>=',$area_start];
            $datas['area_start']=$area_start;
        }
        $area_end=input('area_end');
        if($area_end){
            $where['area']=['<=',$area_end];
            $datas['area_end']=$area_end;
        }
        /* ++++++++++ 是否有电梯 ++++++++++ */
        $has_lift=input('has_lift');
        if(is_numeric($has_lift) && in_array($has_lift,[0,1])){
            $where['has_lift']=$has_lift;
            $datas['has_lift']=$has_lift;
        }
        /* ++++++++++ 期房、现房 ++++++++++ */
        $is_real=input('is_real');
        if(is_numeric($is_real) && in_array($is_real,[0,1])){
            $where['is_real']=$is_real;
            $datas['is_real']=$is_real;
        }
        /* ++++++++++ 是否购置房 ++++++++++ */
        $is_buy=input('is_buy');
        if(is_numeric($is_buy) && in_array($is_buy,[0,1])){
            $where['is_buy']=$is_buy;
            $datas['is_buy']=$is_buy;
        }
        /* ++++++++++ 是否过渡房 ++++++++++ */
        $is_transit=input('is_transit');
        if(is_numeric($is_transit) && in_array($is_transit,[0,1])){
            $where['is_transit']=$is_transit;
            $datas['is_transit']=$is_transit;
        }
        /* ++++++++++ 是否共用 ++++++++++ */
        $is_public=input('is_public');
        if(is_numeric($is_public) && in_array($is_public,[0,1])){
            $where['is_public']=$is_public;
            $datas['is_public']=$is_public;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1,2,3])){
            $where['house.status']=$status;
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
        $house_model=new Houses();
        $datas['model']=$house_model;
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $house_model=$house_model->onlyTrashed();
            }
        }else{
            $house_model=$house_model->withTrashed();
        }
        /* ++++++++++ 查询 ++++++++++ */
        $houses=$house_model
            ->alias('h')
            ->field($field)
            ->join('house_community c','c.id=h.community_id','left')
            ->join('layout l','l.id=h.layout_id','left')
            ->where($where)
            ->order(['house.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['houses']=$houses;
        
        /* ++++++++++ 小区列表 ++++++++++ */
        $communitys=Housecommunitys::field(['id','address','name','status'])->where('status',1)->select();
        $datas['communitys']=$communitys;
        /* ++++++++++ 户型列表 ++++++++++ */
        $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();
        $datas['layouts']=$layouts;
        
        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Houses();
        if(request()->isPost()){
            $rules=[
                'community_id'=>'require',
                'layout_id'=>'require',
                'layout_pic_id'=>'require',
                'area'=>'min:1',
                'deliver_at'=>'require',
            ];
            $msg=[
                'community_id.require'=>'请选择小区',
                'layout_id.unique'=>'请选择户型',
                'layout_pic_id.unique'=>'请选择户型图',
                'area.min'=>'请输入面积',
                'deliver_at.require'=>'请输入交付时间',
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
            return view('modify',[
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
        $infos=Houses::withTrashed()
            ->alias('h')
            ->field(['h.*','p.picture as l_pic'])
            ->join('house_layout_pic p','p.id=h.layout_pic_id','left')
            ->where('h.id',$id)
            ->find();
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Houses();

        /* ++++++++++ 小区列表 ++++++++++ */
        $communitys=Housecommunitys::field(['id','name','status'])->where('status',1)->select();
        /* ++++++++++ 户型列表 ++++++++++ */
        $layouts=Layouts::field(['id','name','status'])->where('status',1)->select();
        return view('modify',[
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
            'layout_pic_id'=>'require',
            'area'=>'min:1',
            'deliver_at'=>'require',
        ];
        $msg=[
            'community_id.require'=>'请选择小区',
            'layout_id.unique'=>'请选择户型',
            'layout_pic_id.unique'=>'请选择户型图',
            'area.min'=>'请输入面积',
            'deliver_at.require'=>'请输入交付时间',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $house_model=new Houses();
        $other_datas=$house_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $house_model->isUpdate(true)->save($datas);
        if($house_model !== false){
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
        if(!in_array($status,[0,1,2,3])){
            return $this->error('错误操作');
        }
        $model=new Houses();
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
        $res=Houses::destroy($ids);
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

        $res=Db::table('house')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Houses::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
