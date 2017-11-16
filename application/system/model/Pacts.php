<?php
/* |------------------------------------------------------
 * | 兑付协议 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;

class Pacts extends Model
{
    protected $table='pact';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';

    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'picture'=>'array'
    ];

    public function setNameAttr($value)
    {
        return trim($value);
    }

    public function getStatusAttr($key=null){
        $array=[1=>'有效',0=>'失效'];
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
}