<?php
/* |------------------------------------------------------
 * | 冻结安置房 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;

class Itemhouses extends Model
{
    protected $table='item_house';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function getIsRealAttr($key=null){
        $array=[0=>'期房',1=>'现房'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getIsBuyAttr($key=null){
        $array=[0=>'非购置房',1=>'购置房'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getIsTransitAttr($key=null){
        $array=[0=>'非过渡',1=>'可过渡'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getIsPublicAttr($key=null){
        $array=[0=>'专用',1=>'共用'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getHouseStatusAttr($key=null){
        $array=[0=>'闲置',1=>'临时安置',2=>'安置',3=>'失效'];
        if(is_numeric($key) && in_array($key,[0,1,2,3])){
            return $array[$key];
        }else{
            return $array;
        }
    }
}