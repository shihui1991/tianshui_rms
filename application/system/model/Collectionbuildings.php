<?php
/* |------------------------------------------------------
 * | 入户摸底 建筑 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Collectionbuildings extends Model
{
    use SoftDelete;
    protected $table='collection_building';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'picture'=>'array',
    ];

    public function getStatusAttr($key=null){
        $array=[0=>'自用',1=>'租赁'];
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