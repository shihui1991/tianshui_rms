<?php
/* |------------------------------------------------------
 * | 项目风险评估话题 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Itemtopics extends Model
{
    use SoftDelete;
    protected $table='item_topic';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
}