<?php
/* |------------------------------------------------------
 * | 房源价格 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Houseprices extends Model
{
    use SoftDelete;
    protected $table='house_price';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'start_at'=>'timestamp:Y/m/d',
        'end_at'=>'timestamp:Y/m/d',
    ];

    public function other_data($input){
        $data=[];

        return $data;
    }
}