<?php
/* |------------------------------------------------------
 * | 安置房价上浮 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;

class Itemhouseups extends Model
{

    protected $table='item_house_up';
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