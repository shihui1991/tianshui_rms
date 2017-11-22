<?php
/* |------------------------------------------------------
 * | 兑付 安置房 上浮 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;

class Payholderhouseups extends Model
{

    protected $table='pay_holder_house_up';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';

    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];


    public function other_data($input){
        $data=[];

        return $data;
    }
}