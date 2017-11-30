<?php
/* |------------------------------------------------------
 * | 兑付协议
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * */
namespace app\system\controller;

use app\system\model\Items;
use app\system\model\Pacts;
use app\system\model\Payholders;
use app\system\model\Pays;
use think\Db;

class Pact extends Auth
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
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
                case 2:
                    $msg='项目已完成，禁止操作！';
                    break;
                case 3:
                    $msg='项目已取消，禁止操作！';
                    break;
                default:
                    $msg='项目未进行，禁止操作！';
            }
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }
        $datas['item_info']=$item_info;


        $pay_id=input('pay_id');
        if(!$pay_id){
            return $this->error('错误操作');
        }
        $datas['pay_id']=$pay_id;
        $where['pact.pay_id']=$pay_id;
        $field=['pact.id','pact.item_id','pact.community_id','pact.collection_id','pact.pay_id','pact.pay_holder_id','pact.name','pact.status',
            'ph.collection_holder_id','ph.holder','ch.name as ch_name','ch.address','ch.phone'];
        $pacts=Pacts::field($field)
            ->join('pay_holder ph','ph.id=pact.pay_holder_id','left')
            ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
            ->where($where)
            ->select();

        $datas['pacts']=$pacts;
        $this->assign($datas);
        
        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
                case 2:
                    $msg='项目已完成，禁止操作！';
                    break;
                case 3:
                    $msg='项目已取消，禁止操作！';
                    break;
                default:
                    $msg='项目未进行，禁止操作！';
            }
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }


        $pay_id=input('pay_id');
        if(!$pay_id){
            return $this->error('错误操作');
        }
        $model=new Pacts();
        if(request()->isPost()){
            $rules=[
                'pay_holder_id'=>'require',
                'name'=>'require|unique:pact,pay_id='.$pay_id.'&pay_holder_id='.input('pay_holder_id').'&name='.input('name'),
                'content'=>'require'
            ];
            $msg=[
                'pay_holder_id.require'=>'请选择签约人',
                'name.require'=>'名称不能为空',
                'name.unique'=>'名称已存在',
                'content.require'=>'内容不能为空',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);

            /* ********** 获取兑付基本数据 ********** */
            $pay_info=Pays::withTrashed()
                ->alias('p')
                ->field(['p.id','p.item_id','p.community_id','p.collection_id'])
                ->where('p.id',$pay_id)
                ->find();

            $datas['item_id']=$pay_info->item_id;
            $datas['community_id']=$pay_info->community_id;
            $datas['collection_id']=$pay_info->collection_id;

            $model->save($datas);
            if($model !== false){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            $payholders=Payholders::alias('ph')
                ->field(['ph.id','ph.pay_id','ph.collection_holder_id','ch.name as ch_name','ch.address'])
                ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
                ->where('ph.pay_id',$pay_id)
                ->select();

            return view('add',[
                'model'=>$model,
                'pay_id'=>$pay_id,
                'item_info'=>$item_info,
                'payholders'=>$payholders,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $field=['pact.*','ph.collection_holder_id','ch.name as ch_name','ch.address'];
        $infos=Pacts::field($field)
            ->join('pay_holder ph','ph.id=pact.pay_holder_id','left')
            ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
            ->where('pact.id',$id)
            ->find();

        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Pacts();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('错误操作','');
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_info=Items::field(['id','name','status'])->where('id',$item_id)->find();
        if($item_info->getData('status') !=1){
            switch ($item_info->getData('status')){
                case 2:
                    $msg='项目已完成，禁止操作！';
                    break;
                case 3:
                    $msg='项目已取消，禁止操作！';
                    break;
                default:
                    $msg='项目未进行，禁止操作！';
            }
            if(request()->isAjax()){
                return $this->error($msg,'');
            }else{
                return $msg;
            }
        }


        $id=input('id');
        if(!$id){
            return $this->error('错误操作');
        }
        $datas=input();
        $rules=[
            'name'=>'require|unique:pact,pay_id='.input('pay_id').'&pay_holder_id='.input('pay_holder_id').'&name='.input('name'),
            'content'=>'require'
        ];
        $msg=[
            'name.require'=>'名称不能为空',
            'name.unique'=>'名称已存在',
            'content.require'=>'内容不能为空',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $pact_model=new Pacts();
        $other_datas=$pact_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $pact_model->isUpdate(true)->save($datas);
        if($pact_model !== false){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }
}
