<?php
/* |------------------------------------------------------
 * | 房产评估--评估师 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Assessestatevaluers extends Model
{
    use SoftDelete;
    protected $table='assess_estate_valuer';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
}