<?php
/* |------------------------------------------------------
 * | 控制台
 * |------------------------------------------------------
 * */

namespace app\holder\controller;


use app\system\model\Items;
use app\system\model\Newss;

class Home extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
    }

    /* ========== 框架 ========== */
    public function index(){
        if(request()->isMobile()){
            $newss=Newss::with('item')->where('status',1)->order(['is_top'=>'desc','release_at'=>'desc'])->select();

            $this->assign([
                'newss'=>$newss
            ]);
        }
        return view($this->theme.'/home/index');
    }

    /* ========== 控制台 ========== */
    public function console(){
        $items=Items::whereIn('id',session('holderinfo.item_ids'))
            ->whereIn('status',[1,2])
            ->field(['id','name','area','is_top','status'])
            ->order('is_top desc')
            ->select();

        $datas['items']=$items;

        $this->assign($datas);

        return view($this->theme.'/home/console');
    }

    /* ========== 新闻详情 ========== */
    public function newsdetail(){
        $id=input('id');
        if(!$id){
            return $this->error('非法访问');
        }

        $infos=Newss::where(['id'=>$id,'status'=>1])->find();
        if(!$infos){
            return $this->error('非法访问');
        }

        $this->assign([
            'infos'=>$infos,
            'url'=>url('Home/index')
        ]);

        return view($this->theme.'/home/newsdetail');
    }

    /* ========== 更换摸底 ========== */
    public function changecollection(){
        if(request()->isMobile()){
            $item_id=input('item_id') or session('collectioninfo.item_id');
            $collection_id=input('collection_id') or session('collectioninfo.collection_id');

            session('collectioninfo',[
                'item_id'=>$item_id,
                'collection_id'=>$collection_id,
            ]);

            return $this->success('选择成功','');
        }else{
            return $this->error('非法访问');
        }
    }
}