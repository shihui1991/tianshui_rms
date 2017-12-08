<?php
/* |------------------------------------------------------
 * | 房产评估 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Assessestates extends Model
{
    use SoftDelete;
    protected $table='assess_estate';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'picture'=>'array',
        'report_at'=>'timestamp:Y-m-d',
        'valued_at'=>'timestamp:Y-m-d'
    ];
    public function getStatusAttr($key=null){
        $array=[0=>'禁用',1=>'启用'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function other_data($input){
        $datas=[];
        if(!isset($input['picture'])){
            $datas['picture']=[];
        }
        return $datas;
    }

    public function item(){
        return $this->belongsTo('Items','item_id','id');
    }

    public function community(){
        return $this->belongsTo('Collectioncommunitys','community_id','id');
    }

    public function collection(){
        return $this->belongsTo('Collections','collection_id','id');
    }

    public function assess(){
        return $this->belongsTo('Assesss','assess_id','id');
    }

    public function company(){
        return $this->belongsTo('Companys','company_id','id')->field(['id','type','name','short_name']);
    }
}