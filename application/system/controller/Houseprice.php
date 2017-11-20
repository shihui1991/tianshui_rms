<?php
/* |------------------------------------------------------
 * | 房源价格
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

use app\system\model\Houseprices;
use think\Db;

class Houseprice extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();

    }

    /* ========== 列表 ========== */
    public function index()
    {
        $house_id=input('house_id');
        if(!$house_id){
            return $this->error('错误操作');
        }
        $datas['house_id']=$house_id;
        $where['house_id']=$house_id;
        $houseprices=Houseprices::withTrashed()->where($where)->order('start_at asc')->select();
        $datas['houseprices']=$houseprices;
        
        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        $house_id=input('house_id');
        if(!$house_id){
            return $this->error('错误操作');
        }
        $model=new Houseprices();
        if(request()->isPost()){
            $rules=[
                'start_at'=>'require|unique:house_price,house_id='.$house_id.'&start_at='.strtotime(input('start_at')),
                'end_at'=>'require|after:'.input('start_at'),
                'market_price'=>'require|min:0',
                'price'=>'require|min:0|elt:'.input('market_price'),
            ];
            $msg=[
                'start_at.require'=>'请选择生效时间',
                'start_at.unique'=>'评估时点已存在',
                'end_at.require'=>'请选择有效期限',
                'end_at.after'=>'有效期限应在生效时间之后',
                'market_price.require'=>'市场评估价不能为空',
                'market_price.min'=>'市场评估价不少于0',
                'price.require'=>'优惠安置价不能为空',
                'price.min'=>'优惠安置价不少于0',
                'price.elt'=>'优惠安置价不高于市场评估价',
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
                'house_id'=>$house_id,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Houseprices::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Houseprices();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $house_id=input('house_id');
        if(!$house_id){
            return $this->error('错误操作');
        }
        $id=input('id');
        if(!$id){
            return $this->error('错误操作');
        }
        $datas=input();
        $rules=[
            'start_at'=>'require|unique:house_price,house_id='.$house_id.'&start_at='.strtotime(input('start_at')),
            'end_at'=>'require|after:'.input('start_at'),
            'market_price'=>'require|min:0',
            'price'=>'require|min:0|elt:'.input('market_price'),
        ];
        $msg=[
            'start_at.require'=>'请选择生效时间',
            'start_at.unique'=>'评估时点已存在',
            'end_at.require'=>'请选择有效期限',
            'end_at.after'=>'有效期限应在生效时间之后',
            'market_price.require'=>'市场评估价不能为空',
            'market_price.min'=>'市场评估价不少于0',
            'price.require'=>'优惠安置价不能为空',
            'price.min'=>'优惠安置价不少于0',
            'price.elt'=>'优惠安置价不高于市场评估价',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $houseprice_model=new Houseprices();
        $other_datas=$houseprice_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $houseprice_model->isUpdate(true)->save($datas);
        if($houseprice_model !== false){
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
        $res=Houseprices::destroy($ids);
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

        $res=Db::table('house_price')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Houseprices::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
