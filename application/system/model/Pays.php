<?php
/* |------------------------------------------------------
 * | 兑付 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Pays extends Model
{
    use SoftDelete;
    protected $table='pay';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'picture'=>'array',
    ];

    public function getCompensateWayAttr($key=null){
        $array=[0=>'货币补偿',1=>'产权调换'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getTransitWayAttr($key=null){
        $array=[0=>'货币过渡',1=>'周转房过渡'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getMoveWayAttr($key=null){
        $array=[0=>'自行搬迁',1=>'政府负责'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getPayWayAttr($key=null){
        $array=[0=>'分权兑付',1=>'合并兑付'];
        if(is_numeric($key) && in_array($key,[0,1])){
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
        if(!isset($input['picture'])){
            $data['picture']=[];
        }
        return $data;
    }
}