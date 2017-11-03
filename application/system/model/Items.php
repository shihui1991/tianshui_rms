<?php
/* |------------------------------------------------------
 * | 项目管理 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;

class Items extends Model
{
    protected $table='item';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'picture'=>'array',
    ];

    public function setNameAttr($value)
    {
        return trim($value);
    }

    public function setRecordNumAttr($value)
    {
        return trim($value);
    }

    public function getIsTopAttr($key=null){
        $array=[0=>'非置顶',1=>'置顶'];
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
        if(!isset($input['picture'])){
            $data['picture']=[];
        }
        return $data;
    }
}