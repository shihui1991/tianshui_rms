<?php
/* |------------------------------------------------------
 * | 项目重要时间 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;


class Itemtimes extends Model
{

    protected $table='item_time';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';

    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function setCollectionStartAttr($value){
        return $value?strtotime($value):null;
    }

    public function setCollectionEndAttr($value){
        return $value?strtotime($value):null;
    }

    public function setVoteStartAttr($value){
        return $value?strtotime($value):null;
    }

    public function setVoteEndAttr($value){
        return $value?strtotime($value):null;
    }

    public function setAssessStartAttr($value){
        return $value?strtotime($value):null;
    }

    public function setAssessEndAttr($value){
        return $value?strtotime($value):null;
    }

    public function setRiskStartAttr($value){
        return $value?strtotime($value):null;
    }

    public function setRiskEndAttr($value){
        return $value?strtotime($value):null;
    }

    public function setSignStartAttr($value){
        return $value?strtotime($value):null;
    }

    public function setSignEndAttr($value){
        return $value?strtotime($value):null;
    }


    public function item(){
        return $this->belongsTo('Items','item_id','id')->field(['id','name','status']);
    }

}