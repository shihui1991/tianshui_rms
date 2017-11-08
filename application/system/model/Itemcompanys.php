<?php
/* |------------------------------------------------------
 * | 项目评估公司 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;


class Itemcompanys extends Model
{

    protected $table='item_company';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';

    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function getTypeAttr($key=null){
        $array=[0=>'房产评估机构',1=>'资产评估机构'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }
}