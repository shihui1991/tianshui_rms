<?php
/* |------------------------------------------------------
 * | 评估
 * |------------------------------------------------------
 * */

namespace app\holder\controller;


use app\system\model\Assessassetss;
use app\system\model\Assessassetsvaluers;
use app\system\model\Assessestatebuildings;
use app\system\model\Assessestates;
use app\system\model\Assessestatevaluers;
use app\system\model\Assesss;
use app\system\model\Collections;
use app\system\model\Itemprocesss;

class Assess extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
        if(request()->isMobile()){
            $itemprocess_status=Itemprocesss::where(['item_id'=>$this->item_id,'process_id'=>5])->value('status');
            if($itemprocess_status != 2){
                return $this->error('数据采集中……');
            }

            $assess=Assesss::where(['item_id'=>$this->item_id,'collection_id'=>$this->collection_id])->find();

            $hasassets=Collections::where(['id'=>$this->collection_id])->value('has_assets');

            $view='index';
            if($hasassets){
                $assessassets=Assessassetss::alias('aa')
                    ->field(['aa.*','com.name','a.estate','a.assets'])
                    ->where([
                        'aa.item_id'=>$this->item_id,
                        'aa.collection_id'=>$this->collection_id,
                        'aa.status'=>1
                    ])
                    ->join('company com','com.id=aa.company_id','left')
                    ->join('assess a','a.id=aa.assess_id','left')
                    ->find();

                $assessassetsvaluers=Assessassetsvaluers::alias('aav')
                    ->field(['aav.*','cv.name','cv.phone','cv.register_num','valid_at'])
                    ->where(['aav.assets_id'=>$assessassets->id])
                    ->join('company_valuer cv','cv.id=aav.valuer_id','left')
                    ->select();

                $view='hasassets';
            }


            $assessestate=Assessestates::alias('ae')
                ->field(['ae.*','com.name','a.estate','a.assets'])
                ->where([
                    'ae.item_id'=>$this->item_id,
                    'ae.collection_id'=>$this->collection_id,
                    'ae.status'=>1
                ])
                ->join('company com','com.id=ae.company_id','left')
                ->join('assess a','a.id=ae.assess_id','left')
                ->find();

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
                'assess'=>$assess,
                'hasassets'=>$hasassets,
                'assessassets'=>$assessassets,
                'assessassetsvaluers'=>$assessassetsvaluers,
                'assessestate'=>$assessestate,
                'assessestatebuildings'=>$assessestatebuildings,
                'assessestatevaluers'=>$assessestatevaluers,
                'url'=>url('Collection/detail')
            ]);

            return view('mobile/assess/'.$view);

        }else{
            return '非法访问！';
        }
    }
}