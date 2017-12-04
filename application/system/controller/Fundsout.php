<?php
/* |------------------------------------------------------
 * | 资金支出
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;
use app\system\model\Fundsouts;

class Fundsout extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
    }

    /* ========== 列表 ========== */
    public function index()
    {
        /* ********** 查询条件 ********** */
        $datas=[];
        $where=[];
        $field=['f.*','i.name as item_name','n.name as names','c.name as holder_names','p.collection_holder_id as collection_holder_ids'];
        /* ++++++++++ 项目 ++++++++++ */
        $item_id = input('item_id');
        if (is_numeric($item_id)) {
            $where['f.item_id'] = $item_id;
            $datas['item_id'] = $item_id;
        }
        /* ++++++++++ 资金款项 ++++++++++ */
        $name_id = input('name_id');
        if (is_numeric($name_id)) {
            $where['f.name_id'] = $name_id;
            $datas['name_id'] = $name_id;
        }
        /* ++++++++++ 凭证号 ++++++++++ */
        $voucher = input('voucher');
        if (is_numeric($voucher)) {
            $where['f.voucher'] = array('LIKE',"%$voucher%");
            $datas['voucher'] = $voucher;
        }
        /* ++++++++++ 支付时间 ++++++++++ */
        $outlay_at = input('outlay_at');
        if ($outlay_at) {
            $outlay_at_start = strtotime($outlay_at." 00:00:00");
            $outlay_at_end = strtotime($outlay_at." 23:59:59");
            $where['f.outlay_at'] = [['<=',$outlay_at_end],['>=',$outlay_at_start]];
            $datas['outlay_at'] = $outlay_at;
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
        /* ++++++++++ 是否删除 ++++++++++ */
        $deleted=input('deleted');
        $fundsout_model=new Fundsouts();
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $fundsout_model=$fundsout_model->onlyTrashed();
            }
        }else{
            $fundsout_model=$fundsout_model->withTrashed();
        }
        $fundsout_list=$fundsout_model
            ->alias('f')
            ->field($field)
            ->join('item i', 'i.id=f.item_id', 'left')
            ->join('funds_name n', 'n.id=f.name_id', 'left')
            ->join('pay_holder p', 'p.id=f.pay_holder_id', 'left')
            ->join('collection_holder c', 'c.id=p.collection_holder_id', 'left')
            ->where($where)
            ->order([$ordername=>$orderby])
            ->paginate($display_num);
        $datas['fundsout_list']=$fundsout_list;

        /* ++++++++++ 项目列表 ++++++++++ */
        $items = model('Items')->field(['id', 'name', 'status'])->where('status', 1)->order('is_top desc')->select();
        $datas['item_list'] = $items;
        /* ++++++++++ 资金款项列表 ++++++++++ */
        $fundsnames = model('Fundsnames')->field(['id', 'name'])->select();
        $datas['fundsnames'] = $fundsnames;
        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        if (request()->isPost()) {
            $datas = input();
            $rule = [
                ['item_id', 'require', '请选择项目'],
                ['name_id', 'require', '请选择资金款项'],
                ['voucher', 'require', '请输入凭证号'],
                ['outlay_at', 'require', '请选择支付时间'],
                ['payee', 'require', '请输入接收人'],
                ['amount', 'require', '请输入金额'],
                ['bank', 'require', '请输入接收银行'],
                ['account', 'require', '请输入接收账号']
            ];
            $result = $this->validate($datas, $rule);
            if (true !== $result) {
                return $this->error($result);
            }
            $pay_holder_ids = input('pay_holder_ids');
            if($pay_holder_ids){
                $pay_holder_ids = explode(',',$pay_holder_ids);
                $datas['pay_holder_id'] = $pay_holder_ids[0];
                $datas['pay_id'] = $pay_holder_ids[1];
            }
            unset($datas['pay_holder_ids']);
            $rs = model('Fundsouts')->save($datas);
            if ($rs) {
                return $this->success('添加成功', '');
            } else {
                return $this->error('添加失败', '');
            }
        } else {
            /* ++++++++++ 项目列表 ++++++++++ */
            $items = model('Items')->field(['id', 'name', 'status'])->where('status', 1)->order('is_top desc')->select();
            /* ++++++++++ 资金款项列表 ++++++++++ */
            $fundsnames = model('Fundsnames')->field(['id', 'name'])->select();

            return view('modify',[
                'items' => $items,
                'fundsnames' => $fundsnames
                ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id = input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $field=['f.*','i.name as item_name','n.name as names','c.name as holder_names','p.collection_holder_id as collection_holder_ids'];
        $fundsout_info=model('Fundsouts')
            ->alias('f')
            ->field($field)
            ->join('item i', 'i.id=f.item_id', 'left')
            ->join('funds_name n', 'n.id=f.name_id', 'left')
            ->join('pay_holder p', 'p.id=f.pay_holder_id', 'left')
            ->join('collection_holder c', 'c.id=p.collection_holder_id', 'left')
            ->where('f.id',$id)
            ->find();
        return view('modify',[
            'infos'=>$fundsout_info
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $datas = input();
        $rule = [
            ['voucher', 'require', '请输入凭证号'],
            ['outlay_at', 'require', '请选择支付时间'],
            ['payee', 'require', '请输入接收人'],
            ['amount', 'require', '请输入金额'],
            ['bank', 'require', '请输入接收银行'],
            ['account', 'require', '请输入接收账号']
        ];
        $result = $this->validate($datas, $rule);
        if (true !== $result) {
            return $this->error($result);
        }
        $rs = model('Fundsouts')->save([
                'voucher'=>$datas['voucher'],
                'outlay_at'=>$datas['outlay_at'],
                'payee'=>$datas['payee'],
                'amount'=>$datas['amount'],
                'bank'=>$datas['bank'],
                'account'=>$datas['account']
            ],['id'=>input('id')]);
        if ($rs) {
            return $this->success('修改成功', '');
        } else {
            return $this->error('修改失败', '');
        }
    }

    /* ========== 删除 ========== */
    public function delete(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res = model('Fundsouts')->destroy($ids);
        if($res){
            return $this->success('删除成功','');
        }else{
            return $this->error('删除失败');
        }
    }

    /* ========== 恢复 ========== */
    public function restore(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res = db('funds_out')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
        if($res){
            return $this->success('恢复成功','');
        }else{
            return $this->error('恢复失败');
        }
    }

    /* ========== 销毁 ========== */
    public function destroy(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res = model('Fundsouts')->onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }

}