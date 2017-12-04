<?php
/* |------------------------------------------------------
 * | 房源临时安置 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Housetransits extends Model
{
    use SoftDelete;
    protected $table='house_transit';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'start_at'=>'timestamp:Y-m-d',
        'exp_end'=>'timestamp:Y-m-d',
        'end_at'=>'timestamp:Y-m-d'
    ];

    public function setStartAtAttr($value){
        return $value?strtotime($value):null;
    }

    public function setExpEndAtAttr($value){
        return $value?strtotime($value):null;
    }

    public function setEndAtAttr($value){
        return $value?strtotime($value):null;
    }

    public function getStartAtAttr($value){
        return $value?date('Y-m-d',$value):null;
    }

    public function getExpEndAtAttr($value){
        return $value?date('Y-m-d',$value):null;
    }

    public function getEndAtAttr($value){
        return $value?date('Y-m-d',$value):null;
    }
}