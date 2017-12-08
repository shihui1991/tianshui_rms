<?php
/* |------------------------------------------------------
 * | 入户摸底 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Collections extends Model
{
    use SoftDelete;
    protected $table='collection';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'picture'=>'array',
    ];

    public function getTypeAttr($key=null){
        $array=[0=>'私产',1=>'公产'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getLandPropAttr($key=null){
        $array=[0=>'国有',1=>'集体'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getLandSourceAttr($key=null){
        $array=[0=>'出让',1=>'划拨'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getLandStatusAttr($key=null){
        $array=[0=>'转让',1=>'租赁',2=>'抵押',3=>'查封'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getHasAssetsAttr($key=null){
        $array=[0=>'无',1=>'有'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getIsAgreeAttr($key=null){
        $array=[1=>'同意',0=>'反对'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getCompensateWayAttr($key=null){
        $array=[0=>'货币补偿',1=>'产权调换'];
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


    public function item(){
        return $this->belongsTo('Items','item_id','id','item')->field('id,name,is_top,status,deleted_at');
    }

    public function community(){
        return $this->belongsTo('Collectioncommunitys','community_id','id','community')->field('id,address,name');
    }

    public function realuse(){
        return $this->belongsTo('Buildinguses','real_use','id','building_use')->field('id,name');
    }

    public function assess(){
        return $this->hasOne('Assesss','collection_id','id');
    }

    public function assets(){
        return $this->hasMany('Assessassetss','collection_id','id');
    }

    public function estate(){
        return $this->hasMany('Assessestates','collection_id','id');
    }

    public function collectionbuilding(){
        return $this->hasMany('Collectionbuildings','collection_id','id');
    }
}