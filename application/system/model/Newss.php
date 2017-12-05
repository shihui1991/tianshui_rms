<?php
/* |------------------------------------------------------
 * | 新闻公告 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Newss extends Model
{
    use SoftDelete;
    protected $table='news';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'picture'=>'array',
        'release_at'=>'timestamp:Y-m-d',
    ];

    public function setNameAttr($value)
    {
        return trim($value);
    }

    public function setSortAttr($value)
    {
        return $value?(int)$value:0;
    }

    public function getIsTopAttr($key=null){
        $array=[0=>'非置顶',1=>'置顶'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getStatusAttr($key=null){
        $array=[1=>'启用',0=>'禁用'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function other_data($input){
        $data=[];
        if(!isset($input['title_page'])){
            $data['title_page']='';
        }
        if(!isset($input['picture'])){
            $data['picture']=[];
        }
        return $data;
    }

    public function item(){
        return $this->belongsTo('Items','item_id','id');
    }

    public function cate(){
        return $this->belongsTo('Newscates','cate_id','id');
    }
}