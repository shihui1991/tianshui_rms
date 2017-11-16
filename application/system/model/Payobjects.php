<?php
/* |------------------------------------------------------
 * | 兑付 其他补偿事项 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Payobjects extends Model
{
    use SoftDelete;
    protected $table='pay_object';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];


    public function other_data($input){
        $data=[];

        return $data;
    }
}