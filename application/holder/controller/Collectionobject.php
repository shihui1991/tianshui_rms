<?php
/* |------------------------------------------------------
 * | 入户摸底 其他事项
 * |------------------------------------------------------
 * */
namespace app\holder\controller;

use app\system\model\Collectionobjects;

class Collectionobject extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();


    }

    /* ========== 列表 ========== */
    public function index()
    {
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问','');
        }
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问','');
        }

        $field=['co.*','o.name','o.infos'];
        $collectionobjects=Collectionobjects::alias('co')
            ->field($field)
            ->join('object o','o.id=co.object_id','left')
            ->where([
                'item_id'=>$item_id,
                'collection_id'=>$collection_id
            ])
            ->select();

        $datas=[
            'item_id'=>$item_id,
            'collection_id'=>$collection_id,
            'collectionobjects'=>$collectionobjects
        ];

        $this->assign($datas);

        return view($this->theme.'/collectionobject/index');
    }
}

