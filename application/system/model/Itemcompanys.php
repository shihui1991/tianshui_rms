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

    public function other_data($input){
        $data=[];

        return $data;
    }

    public function item(){
        return $this->belongsTo('Items','item_id','id','item')->field('id,name,status');
    }

    public function company(){
        return $this->belongsTo('Companys','company_id','id','company')->field('id,name,type,status');
    }

    public function companycollectin(){
        return $this->hasMany('Itemcompanycollections','item_company_id','id');
    }
}