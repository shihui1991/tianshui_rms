<?php
/* |------------------------------------------------------
 * | 安置房选择 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Collectionholderhouses extends Model
{
    use SoftDelete;
    protected $table='collection_holder_house';
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

    public function getHasLiftAttr($key=null){
        $array=[0=>'无电梯',1=>'有电梯'];
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

    public function getHolderAttr($key=null){
        $array=[0=>'无',1=>'产权人',2=>'承租人'];
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