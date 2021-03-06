<?php
/* |------------------------------------------------------
 * | 房源户型图 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Houselayoutpics extends Model
{
    use SoftDelete;
    protected $table='house_layout_pic';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function setRemarkAttr($value)
    {
        return trim($value);
    }

    public function getStatusAttr($key=null){
        $array=[1=>'启用',0=>'禁用'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function other_data($input){
        $data=[];
        if(!isset($input['picture'])){
            $data['picture']='';
        }
        return $data;
    }
}