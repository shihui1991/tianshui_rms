<?php
/* |------------------------------------------------------
 * | 项目控制流程 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Itemprocesss extends Model
{
    use SoftDelete;
    protected $table='item_process';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function setSortAttr($value){
        return is_null($value)?0:(int)$value;
    }

    public function getStatusAttr($key=null){
        $array=[0=>'未开始',1=>'进行中',2=>'已完成'];
        if(is_numeric($key) && in_array($key,[0,1,2])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function other_data($input){
        $data=[];

        return $data;
    }
}