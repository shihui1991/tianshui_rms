<?php
/* |------------------------------------------------------
 * | 项目动态 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;


class Itemstatuss extends Model
{

    protected $table='item_status';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function getStatusAttr($key=null){
        $array=[0=>'添加',1=>'修改',2=>'删除',3=>'恢复',4=>'销毁',8=>'审核通过',9=>'审核驳回'];
        if(is_numeric($key) && in_array($key,[0,1,2,3,4,8,9])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function user(){
        return $this->belongsTo('Users','user_id','id')->field(['id','name','username']);
    }

    public function role(){
        return $this->belongsTo('Roles','role_id','id')->field(['id','name','parent_id']);
    }
}