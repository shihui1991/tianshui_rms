<?php
/* |------------------------------------------------------
 * | 项目评估公司
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 删除
 * */

namespace app\system\controller;

use app\system\model\Companys;
use app\system\model\Itemcompanys;
use app\system\model\Items;

class Itemcompany extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index(){
        /* ********** 查询条件 ********** */
        $datas=[];
        $where=[];
        $field=['ic.id','ic.item_id','ic.company_id','i.name as i_name','c.type','c.name as c_name'];

        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        if(is_numeric($item_id)){
            $where['item_id']=$item_id;
            $datas['item_id']=$item_id;
        }
        /* ++++++++++ 评估公司 ++++++++++ */
        $company_id=input('company_id');
        if(is_numeric($company_id)){
            $where['company_id']=$company_id;
            $datas['company_id']=$company_id;
        }
        /* ++++++++++ 评估公司类型 ++++++++++ */
        $type=input('type');
        if(is_numeric($type) && in_array($type,[0,1])){
            $where['company.type']=$type;
            $datas['type']=$type;
        }
        /* ++++++++++ 排序 ++++++++++ */
        $ordername=input('ordername');
        $ordername=$ordername?$ordername:'id';
        $datas['ordername']=$ordername;
        $orderby=input('orderby');
        $orderby=$orderby?$orderby:'asc';
        $datas['orderby']=$orderby;
        /* ++++++++++ 每页条数 ++++++++++ */
        $nums=[config('paginate.list_rows'),30,50,100,200,500];
        sort($nums);
        $datas['nums']=$nums;
        $display_num=input('display_num');
        $display_num=$display_num?$display_num:config('paginate.list_rows');
        $datas['display_num']=$display_num;

        /* ++++++++++ 查询 ++++++++++ */
        $itemcompany_model=new Itemcompanys();
        $datas['model']=$itemcompany_model;
        $itemcompanys=$itemcompany_model
            ->alias('ic')
            ->field($field)
            ->join('item i','ic.item_id=i.id','left')
            ->join('company c','ic.company_id=c.id','left')
            ->where($where)
            ->order(['item.is_top'=>'desc','item_company.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['itemcompanys']=$itemcompanys;

        /* ++++++++++ 项目列表 ++++++++++ */
        $items=Items::field(['id','name','status'])->where('status',1)->order('is_top desc')->select();
        $datas['items']=$items;
        /* ++++++++++ 评估公司 ++++++++++ */
        $companys=Companys::field(['id','name','status'])->where('status',1)->order('sort asc')->select();
        $datas['companys']=$companys;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add(){

    }
}