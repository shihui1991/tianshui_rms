<?php
/* |------------------------------------------------------
 * | 资金支出 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Fundsouts extends Model
{
    use SoftDelete;
    protected $table='funds_out';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'outlay_at'=>'timestamp:Y-m-d'
    ];
}