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

    public function item(){
        return $this->belongsTo('Items','item_id','id','item')->field('id,name,is_top,status,deleted_at');
    }

    public function community(){
        return $this->belongsTo('Collectioncommunitys','community_id','id','community')->field('id,address,name');
    }

    public function collection(){
        return $this->belongsTo('Collections','collection_id','id','collection')->field('id,building,unit,floor,number,type');
    }

    public function holder(){
        return $this->belongsTo('Collectionholders','holder_id','id','holder')->field('id,name,address,phone,holder,portion');
    }

    public function crowd(){
        return $this->belongsTo('Crowds','crowd_id','id','holder')->field('id,name,parent_id');
    }

    public function crowdgroup(){
        return $this->belongsTo('Crowds','crowd_parent_id','id','holder')->field('id,name');
    }
}