<?php
/* |------------------------------------------------------
 * | 房源物业管理费 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Housemanagefees extends Model
{

    protected $table='house_manage_fee';
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

    public function house(){
        return $this->belongsTo('Houses','house_id','id')->field(['id','community_id','building','unit','floor','number']);
    }
}