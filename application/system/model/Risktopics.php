<?php
/* |------------------------------------------------------
 * | 风险评估话题结果 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Risktopics extends Model
{
    use SoftDelete;
    protected $table='risk_topic';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;


    public function topic(){
        return $this->belongsTo('Topics','topic_id','id');
    }
}