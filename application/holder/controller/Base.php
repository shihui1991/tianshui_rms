<?php
/* |------------------------------------------------------
 * | 基础控制器
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * */

namespace app\holder\controller;

use app\system\model\Collections;
use app\system\model\Itemprocesss;
use app\system\model\Items;
use app\system\model\Processurls;
use think\Controller;
use think\Session;

class Base extends Controller
{
    public $theme;
    public $url;
    public $process_status;
    public $items;

    /* ========== 初始化 ========== */
    public function _initialize()
    {
        /* ++++++++++ 未登录或操作超时 ++++++++++ */
        $userinfo=Session::get('holderinfo');
        if(!$userinfo || time()-$userinfo['time']>3600){
            $this->redirect('Index/index');
        }else{
            Session::set('holderinfo.time',time());
        }

        if(request()->isMobile()){
            $this->theme='mobile';
        }else{
            $this->theme='pc';
        }

        /* ++++++++++ 当前路由地址  ++++++++++ */
        $url=request()->path();
        $params=request()->route();
        if($params){
            foreach ($params as $key => $value){
                $url=str_replace('/'.$key.'/'.$value,'',$url);
            }
        }
        $url=$url=='/'?'/':'/'.$url;
        $this->url=$url;

        if(strtolower(request()->controller()) != 'home'){
            if(request()->isMobile()){
                $items=Items::whereIn('id',session('holderinfo.item_ids'))
                    ->whereIn('status',[1,2])
                    ->field(['id','name','area','is_top','status'])
                    ->order('is_top desc')
                    ->select();

                if(!count($items)){
                    return $this->error('没有数据！',url('Home/index'));
                }
                $this->items=$items;
            }



            if(input('item_id') && in_array(input('item_id'),session('holderinfo.item_ids'))){
                $process_id=Processurls::where('url',$url)->value('process_id');
                if($process_id){
                    $itemprocess=Itemprocesss::where(['item_id'=>input('item_id'),'process_id'=>$process_id])->find();
                    if($itemprocess){
                        $this->process_status=$itemprocess->getData('status');
                    }else{
                        $this->process_status=0;
                    }
                }
            }else{
                return $this->error('非法访问');
            }
        }
    }

}