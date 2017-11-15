<?php
/* |------------------------------------------------------
 * | 兑付 重要补偿科目 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Paysubjects extends Model
{
    use SoftDelete;
    protected $table='pay_subject';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function getNumFromAttr($key=null){
        $array=[0=>'合法建筑面积',1=>'合法建筑总价',2=>'自定义'];
        if(is_numeric($key) && in_array($key,[0,1,2])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function other_data($input){
        $data=[];
        $data['amount']=$input['price']*$input['number']*$input['times'];
        return $data;
    }
}