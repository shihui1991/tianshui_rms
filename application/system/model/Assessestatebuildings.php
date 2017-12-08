<?php
/* |------------------------------------------------------
 * | 房产评估--建筑评估 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Assessestatebuildings extends Model
{
    use SoftDelete;
    protected $table='assess_estate_building';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;

    public function item(){
        return $this->belongsTo('Items','item_id','id');
    }

    public function community(){
        return $this->belongsTo('Collectioncommunitys','community_id','id');
    }

    public function collection(){
        return $this->belongsTo('Collections','collection_id','id');
    }

    public function collectionbuilding(){
        return $this->belongsTo('Collectionbuildings','building_id','id');
    }


}