<?php
/* |------------------------------------------------------
 * | 流程控制 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Processs extends Model
{
    use SoftDelete;
    protected $table='process';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function setNameAttr($value)
    {
        return trim($value);
    }

    public function setControllerAttr($value)
    {
        return trim($value);
    }

    public function setActionAttr($value)
    {
        return trim($value);
    }


    public function other_data($input){
        $data=[];

        return $data;
    }
}