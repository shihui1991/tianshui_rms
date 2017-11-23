<?php
/* |------------------------------------------------------
 * | 资金收入 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Fundsins extends Model
{
    use SoftDelete;
    protected $table='funds_in';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'entry_at'=>'timestamp:Y-m-d'
    ];
}