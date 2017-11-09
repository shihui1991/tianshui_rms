<?php
/* |------------------------------------------------------
 * | 项目评估公司-被征户 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;


class Itemcompanycollections extends Model
{

    protected $table='item_company_collection';
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