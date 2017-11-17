<?php
/* |------------------------------------------------------
 * | 调查话题 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Topics extends Model
{
    use SoftDelete;
    protected $table='topic';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
}