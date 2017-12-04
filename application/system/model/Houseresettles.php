<?php
/* |------------------------------------------------------
 * | 房源安置 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Houseresettles extends Model
{
    use SoftDelete;
    protected $table='house_resettle';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function setStartAtAttr($value){
        return $value?strtotime($value):null;
    }

    public function setEndAtAttr($value){
        return $value?strtotime($value):null;
    }

    public function getStartAtAttr($value){
        return $value?date('Y-m-d',$value):null;
    }

    public function getEndAtAttr($value){
        return $value?date('Y-m-d',$value):null;
    }
}