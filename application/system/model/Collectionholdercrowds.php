<?php
/* |------------------------------------------------------
 * | 入户摸底 特殊人群 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Collectionholdercrowds extends Model
{
    use SoftDelete;
    protected $table='collection_holder_crowd';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'picture'=>'array'
    ];

    public function other_data($input){
        $data=[];
        if(!isset($input['picture'])){
            $data['picture']=[];
        }

        return $data;
    }
}