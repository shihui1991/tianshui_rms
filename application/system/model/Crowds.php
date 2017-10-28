<?php
/* |------------------------------------------------------
 * | 特殊人群分类 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Crowds extends Model
{
    use SoftDelete;
    protected $table='crowd';
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