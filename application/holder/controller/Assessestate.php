<?php
/* |------------------------------------------------------
 * | 房产评估
 * |------------------------------------------------------
 * */

namespace app\holder\controller;


use app\system\model\Assessestatebuildings;
use app\system\model\Assessestates;
use app\system\model\Assessestatevaluers;
use app\system\model\Collections;
use app\system\model\Items;

class Assessestate extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
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

        $assessestate=Assessestates::alias('ae')
            ->field(['ae.*','com.name','a.estate','a.assets'])
            ->where([
                'ae.item_id'=>$item_id,
                'ae.collection_id'=>$collection_id,
                'ae.status'=>1
            ])
            ->join('company com','com.id=ae.company_id','left')
            ->join('assess a','a.id=ae.assess_id','left')
            ->find();

        if(!$assessestate){
            exit('没有评估报告！');
        }

        $field=['aeb.*','cb.building','cb.unit','cb.floor','cb.number','cb.register','cb.real_num','cb.real_unit','cb.use_id','cb.struct_id','cb.status_id','cb.build_year','cb.remark'
            ,'bu.name as bu_name','bs.name as bs_name','bss.name as bss_name',];

        $assessestatebuildings=Assessestatebuildings::alias('aeb')
            ->field($field)
            ->where(['aeb.estate_id'=>$assessestate->id])
            ->join('collection_building cb','cb.id=aeb.building_id','left')
            ->join('building_use bu','bu.id=cb.use_id','left')
            ->join('building_struct bs','bs.id=cb.struct_id','left')
            ->join('building_status bss','bss.id=cb.status_id','left')
            ->select();

        $assessestatevaluers=Assessestatevaluers::alias('aev')
            ->field(['aev.*','cv.name','cv.phone','cv.register_num','valid_at'])
            ->where(['aev.estate_id'=>$assessestate->id])
            ->join('company_valuer cv','cv.id=aev.valuer_id','left')
            ->select();

        $this->assign([
            'item_id'=>$item_id,
            'collection_id'=>$collection_id,
            'assessestate'=>$assessestate,
            'assessestatebuildings'=>$assessestatebuildings,
            'assessestatevaluers'=>$assessestatevaluers,
        ]);

        return view($this->theme.'/assessestate/detail');
    }
}