<?php
/* |------------------------------------------------------
 * | 资产评估
 * |------------------------------------------------------
 * */

namespace app\holder\controller;

use app\system\model\Assessassetss;
use app\system\model\Assessassetsvaluers;
use app\system\model\Collections;
use app\system\model\Itemprocesss;
use app\system\model\Items;

class Assessassets extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
        $itemprocess_status=Itemprocesss::where(['item_id'=>$this->item_id,'process_id'=>5])->value('status');
        if($itemprocess_status != 2){
            return $this->error('数据采集中……');
        }
    }

    /* ========== 列表 ========== */
    public function index(){

        return '非法访问！';
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

        $assessassets=Assessassetss::alias('aa')
            ->field(['aa.*','com.name','a.estate','a.assets'])
            ->where([
                'aa.item_id'=>$item_id,
                'aa.collection_id'=>$collection_id,
                'aa.status'=>1
            ])
            ->join('company com','com.id=aa.company_id','left')
            ->join('assess a','a.id=aa.assess_id','left')
            ->find();

        if(!$assessassets){
            exit('没有评估报告！');
        }


        $assessassetsvaluers=Assessassetsvaluers::alias('aav')
            ->field(['aav.*','cv.name','cv.phone','cv.register_num','valid_at'])
            ->where(['aav.assets_id'=>$assessassets->id])
            ->join('company_valuer cv','cv.id=aav.valuer_id','left')
            ->select();

        $this->assign([
            'item_id'=>$item_id,
            'collection_id'=>$collection_id,
            'assessassets'=>$assessassets,
            'assessassetsvaluers'=>$assessassetsvaluers,
        ]);

        return view($this->theme.'/assessassets/detail');
    }
}