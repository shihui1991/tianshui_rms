<?php
/* |------------------------------------------------------
 * | 入户摸底状态 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;


class Collectionstatuss extends Model
{

    protected $table='collection_status';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function getStatusAttr($key=null){
        $array=[0=>'添加',1=>'修改',2=>'删除',3=>'恢复',8=>'通过',9=>'驳回'];
        if(is_numeric($key) && in_array($key,[0,1,2,3,8,9])){
            return $array[$key];
        }else{
            return $array;
        }
    }
}