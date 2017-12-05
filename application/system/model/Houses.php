<?php
/* |------------------------------------------------------
 * | 安置房源 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Houses extends Model
{
    use SoftDelete;
    protected $table='house';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'picture'=>'array',
        'deliver_at'=>'timestamp:Y-m-d',
    ];

    public function getHasLiftAttr($key=null){
        $array=[0=>'无电梯',1=>'有电梯'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

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

    public function getStatusAttr($key=null){
        $array=[0=>'闲置',1=>'临时安置',2=>'安置',3=>'失效'];
        if(is_numeric($key) && in_array($key,[0,1,2,3])){
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


    public function community(){
        return $this->belongsTo('Housecommunitys','community_id','id')->field(['id','address','name']);
    }

    public function resettle(){
        return $this->hasMany('Houseresettles','house_id','id');
    }

    public function transit(){
        return $this->hasMany('Housetransits','house_id','id');
    }

    public function managefee(){
        return $this->hasMany('Housemanagefees','house_id','id');
    }
}