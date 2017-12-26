<?php
/* |------------------------------------------------------
 * | 入户摸底 家庭情况 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Collectionholders extends Model
{
    use SoftDelete;
    protected $table='collection_holder';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function setPortionAttr($value){
        return $value?(float)$value:0;
    }

    public function getHolderAttr($key=null){
        $array=[0=>'无',1=>'产权人',2=>'承租人'];
        if(is_numeric($key) && in_array($key,[0,1,2])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getGenderAttr($key=null){
        $array=[0=>'无',1=>'男',2=>'女'];
        if(is_numeric($key) && in_array($key,[0,1,2])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getMarriedAttr($key=null){
        $array=[0=>'无',1=>'未婚',2=>'已婚'];
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


    public function item(){
        return $this->belongsTo('Items','item_id','id','item')->field('id,name,is_top,status,deleted_at');
    }

    public function community(){
        return $this->belongsTo('Collectioncommunitys','community_id','id','community')->field('id,address,name');
    }

    public function collection(){
        return $this->belongsTo('Collections','collection_id','id','collection')->field('id,building,unit,floor,number,type');
    }

    public function holdercrowd(){
        return $this->hasMany('Collectionholdercrowds','holder_id','id');
    }
}