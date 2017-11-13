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
}