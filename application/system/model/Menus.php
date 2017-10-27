<?php
/* |------------------------------------------------------
 * | 功能与菜单 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Menus extends Model
{
    use SoftDelete;
    protected $table='menu';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;

    public function setNameAttr($value)
    {
        return trim($value);
    }

    public function setUrlAttr($value)
    {
        return trim($value);
    }

    public function setSortAttr($value)
    {
         return is_null($value)?0:(integer)$value;
    }

    public function getDisplayAttr($key=null){
        $array=[0=>'隐藏',1=>'显示'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
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
        if($input['parent_id']){
            $parent_menu=$this->field(['id','parent_id','level'])->find($input['parent_id']);
            $data['level']=$parent_menu->getAttr('level')+1;
        }else{
            $data['level']=1;
        }
        return $data;
    }
}