<?php
/* |------------------------------------------------------
 * | 兑付汇总
 * |------------------------------------------------------
 * */
namespace app\holder\controller;

use app\system\model\Items;
use app\system\model\Pays;

class Pay extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();


    }

    /* ========== 列表 ========== */
    public function index()
    {
        return '非法访问';
    }


    /* ========== 详情 ========== */
    public function detail(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问','');
        }
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问','');
        }

        $infos=Pays::alias('p')
            ->field(['p.*','c.building','c.unit','c.floor','c.number','c.type','c.real_use','bu.name as bu_name'])
            ->join('collection c','c.id=p.collection_id','left')
            ->join('building_use bu','bu.id=c.real_use','left')
            ->where([
                'p.item_id'=>$item_id,
                'p.collection_id'=>$collection_id
            ])
            ->find();
        if(!$infos){
            exit('没有兑付数据！');
        }

//        /* ++++++++++ 项目 ++++++++++ */
//        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
//        if($item_info->getData('status')==1){
//            $view='edit';
//        }else{
//            $view='detail';
//        }

//        $model=new Pays();

        $this->assign([
           'item_id'=>$item_id,
           'collection_id'=>$collection_id,
           'infos'=>$infos,
        ]);

        return view();
    }


    /* ========== 修改 ========== */
//    public function edit(){
//        $id=input('id');
//        if(!$id){
//            return $this->error('非法访问','');
//        }
//        $item_id=input('item_id');
//        if(!$item_id){
//            return $this->error('非法访问','');
//        }
//        /* ++++++++++ 项目 ++++++++++ */
//        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
//        if($item_info->getData('status') !=1){
//            switch ($item_info->getData('status')){
//                case 2:
//                    $msg='项目已完成，禁止操作！';
//                    break;
//                case 3:
//                    $msg='项目已取消，禁止操作！';
//                    break;
//                default:
//                    $msg='项目未进行，禁止操作！';
//            }
//            if(request()->isAjax()){
//                return $this->error($msg,'');
//            }else{
//                return $msg;
//            }
//        }
//
//        $pay_model=new Pays();
//        $pay_model->isUpdate(true)->allowField(['compensate_way','transit_way','move_way','pay_way'])->save(input(),['id'=>$id]);
//        if($pay_model !== false){
//            return $this->success('修改成功','');
//        }else{
//            return $this->error('修改失败');
//        }
//    }
}

