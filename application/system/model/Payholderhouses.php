<?php
/* |------------------------------------------------------
 * | 兑付 安置房 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;

class Payholderhouses extends Model
{

    protected $table='pay_holder_house';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';

    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function getHolderAttr($key=null){
        $array=[0=>'无',1=>'产权人',2=>'承租人'];
        if(is_numeric($key) && in_array($key,[0,1,2])){
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

    public function payholderhouseups(){
        return $this->hasMany('Payholderhouseups','pay_holder_house_id','id');
    }

    public function other_data($input){
        $data=[];

        return $data;
    }
}