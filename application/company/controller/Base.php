<?php
/* |------------------------------------------------------
 * | 基础控制器
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * */

namespace app\company\controller;

use app\system\model\Itemprocesss;
use app\system\model\Processurls;
use think\Controller;
use think\Session;

class Base extends Controller
{
    public $theme;
    public $url;
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        /* ++++++++++ 未登录或操作超时 ++++++++++ */
        $company=Session::get('company');
        if(!$company || time()-$company['time']>7200){
            $this->redirect('Index/index');
        }else{
            Session::set('company.time',time());
        }


        $url=request()->path();
        $params=request()->route();
        if($params){
            foreach ($params as $key => $value){
                $url=str_replace('/'.$key.'/'.$value,'',$url);
            }
        }
        $url=$url=='/'?'/':'/'.$url;
        /* ++++++++++ 流程控制 ++++++++++ */
        if(request()->isPost()){
            $process_id=Processurls::where('url',$url)->value('process_id');
            if($process_id){
                $itemprocess=Itemprocesss::where(['item_id'=>input('item_id'),'process_id'=>$process_id])->find();
                if($itemprocess){
                    if($itemprocess->getData('status') == 0){
                        if(request()->isAjax()){
                            return $this->error('当前项目，此流程尚未启动！');
                        }else{
                            exit('当前项目，此流程尚未启动！');
                        }
                    }elseif ($itemprocess->getData('status') == 2){
                        if(request()->isAjax()){
                            return $this->error('当前项目，此流程已完成，禁止操作！');
                        }else{
                            exit('当前项目，此流程已完成，禁止操作！');
                        }
                    }

                }else{
                    if(request()->isAjax()){
                        return $this->error('当前项目，无此流程，禁止操作！');
                    }else{
                        exit('当前项目，无此流程，禁止操作！');
                    }
                }
            }
        }

    }

}