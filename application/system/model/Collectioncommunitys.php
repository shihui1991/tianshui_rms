<?php
/* |------------------------------------------------------
 * | 征地片区 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Collectioncommunitys extends Model
{
    use SoftDelete;
    protected $table='collection_community';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function setAddressAttr($value)
    {
        return trim($value);
    }

    public function setNameAttr($value)
    {
        return trim($value);
    }

    public function other_data($input){
        $data=[];

        return $data;
    }
}