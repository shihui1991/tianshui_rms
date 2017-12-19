<?php
/* |------------------------------------------------------
 * | 基础控制器
 * |------------------------------------------------------
 * | 初始化操作
 * */

namespace app\system\controller;

use app\system\model\Itemprocesss;
use app\system\model\Menus;
use app\system\model\Processurls;
use think\Controller;
use think\Session;

class Base extends Controller
{
    public $theme;
    public $url;
    public $menu_id;

    /* ========== 初始化 ========== */
    public function _initialize()
    {
        /* ++++++++++ 未登录或操作超时 ++++++++++ */
        $userinfo=Session::get('userinfo');
        if(!$userinfo || time()-$userinfo['time']>7200){
            $this->redirect('Index/index');
        }else{
            Session::set('userinfo.time',time());
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
        /* ++++++++++ 当前菜单  ++++++++++ */
        $cur_menu=Menus::field(['id','parent_id','name','icon','url'])->where(['status'=>1,'url'=>$url])->find();
        $this->menu_id=$cur_menu?$cur_menu->id:0;


        /* ++++++++++ 流程控制 ++++++++++ */
        if(request()->isPost() && strtolower(request()->controller()) != 'tools'){
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


        if(request()->isMobile()){
            $this->theme='mobile';

            if((request()->isGet() || (input('issearch') && request()->isPost())) && strtolower(request()->controller()) != 'tools' && !request()->isAjax()){
                /* ++++++++++ 所有父级菜单 ++++++++++ */
                $parents=[];
                if($cur_menu){
                    $parents=$this->get_parents_menus($cur_menu);
                }
                if($parents){
                    $parents_menus_ids=$parents['parents_menus_ids'];
                    $parents_menus=$parents['parents_menus'];
                    ksort($parents_menus);
                }else{
                    $parents_menus_ids=[];
                    $parents_menus=[];
                }

                /* ++++++++++ 导航菜单  ++++++++++ */
                $userinfo=Session::get('userinfo');
                $menus_ids=json_decode($userinfo['menu_ids'],true);
                $menus=[];
                $field=['id','parent_id','name','icon','url','display','status','sort'];
                if($userinfo['is_admin']){
                    $menus=Menus::field($field)->where('display',1)->order(['sort'=>'asc','id'=>'asc'])->select();
                }elseif($menus_ids){
                    $menus=Menus::field($field)->where('display',1)->whereIn('id',$menus_ids)->order(['sort'=>'asc','id'=>'asc'])->select();
                }
                $nav_menus='';
                if($menus){
                    $nav_menus=get_na_li_list_mobile($menus,1,0,$this->menu_id,$parents_menus_ids);
                }

                $this->assign([
                    'cur_menu'=>$cur_menu,
                    'parents_menus'=>$parents_menus,
                    'nav_menus'=>$nav_menus,
                ]);
            }
        }else{
            $this->theme='pc';
        }

    }

    /* ========== 获取所有父级菜单 ========== */
    public function get_parents_menus($menu)
    {
        static $parents_menus=[];
        static $parents_menus_ids=[];
        if($menu->parent_id){
            $parent_menu=Menus::where('id',$menu->parent_id)->where('status',1)->find();
            if($parent_menu){
                $parents_menus[$parent_menu->level-1]=$parent_menu;
                $parents_menus_ids[$parent_menu->level-1]=$parent_menu->id;
                if($parent_menu->parent_id){
                    $this->get_parents_menus($parent_menu);
                }
            }
        }
        if($parents_menus){
            return ['parents_menus'=>$parents_menus,'parents_menus_ids'=>$parents_menus_ids];
        }else{
            return false;
        }
    }

}