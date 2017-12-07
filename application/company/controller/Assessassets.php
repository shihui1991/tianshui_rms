<?php
/* |------------------------------------------------------
 * | 资产评估
 * |------------------------------------------------------
 * */

namespace app\company\controller;


use app\system\model\Assessassetss;

class Assessassets extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问');
        }

        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问');
        }

        $datas['item_id']=$item_id;
        $datas['collection_id']=$collection_id;
        $datas['company_id']=session('company.company_id');

        $where['item_id']=$item_id;
        $where['collection_id']=$collection_id;
        $where['company_id']=session('company.company_id');

        $assetss=Assessassetss::with('company')->where($where)->select();

        $datas['assetss']=$assetss;

        $this->assign($datas);

        return view();
    }


    /* ========== 添加 ========== */
    public function add(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问');
        }

        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问');
        }
        if(request()->isPost()){

        }else{

            $datas['item_id']=$item_id;
            $datas['collection_id']=$collection_id;
            $datas['company_id']=session('company.company_id');
            $this->assign($datas);

            return view();
        }
    }
}