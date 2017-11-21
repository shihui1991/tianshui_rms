<?php
/* |------------------------------------------------------
 * | 风险评估 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Risks extends Model
{
    use SoftDelete;
    protected $table='risk';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    public function getDeputyAttr($key=null){
        $array=[0=>'拒绝',1=>'同意'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }
    public function getIsAgreeAttr($key=null){
        $array=[0=>'反对',1=>'同意'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }
    public function getCompensateWayAttr($key=null){
        $array=[0=>'货币补偿',1=>'产权调换'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }
    public function getTransitWayAttr($key=null){
        $array=[0=>'货币过渡',1=>'周转房临时安置'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }
    public function getMoveWayAttr($key=null){
        $array=[0=>'自行搬迁',1=>'政府负责'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }
}