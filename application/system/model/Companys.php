<?php
/* |------------------------------------------------------
 * | 评估公司 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Companys extends Model
{
    use SoftDelete;
    protected $table='company';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function setNameAttr($value)
    {
        return trim($value);
    }

    public function setUsernameAttr($value)
    {
        return trim($value);
    }

    public function getTypeAttr($key=null){
        $array=[0=>'房产评估机构',1=>'资产评估机构'];
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

        return $data;
    }
}