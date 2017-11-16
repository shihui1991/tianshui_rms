<?php
/* |------------------------------------------------------
 * | 兑付 产权人 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Payholders extends Model
{
    use SoftDelete;
    protected $table='pay_holder';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

    public function getHolderAttr($key=null){
        $array=[0=>'无',1=>'产权人',2=>'承租人'];
        if(is_numeric($key) && in_array($key,[0,1,2])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getTypeAttr($key=null){
        $array=[0=>'私产',1=>'公产'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function other_data($input){
        $data=[];

        return $data;
    }
}