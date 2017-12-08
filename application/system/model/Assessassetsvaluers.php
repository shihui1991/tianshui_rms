<?php
/* |------------------------------------------------------
 * | 资产评估--评估师 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;
use think\Model;
use traits\model\SoftDelete;

class Assessassetsvaluers extends Model
{
    use SoftDelete;
    protected $table='assess_assets_valuer';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;


    public function valuer(){
        return $this->belongsTo('Companyvaluers','valuer_id','id');
    }
}