<?php
/* |------------------------------------------------------
 * | 被征户登录入口
 * |------------------------------------------------------
 * | 登录页
 * | 登录
 * | 退出
 * */

namespace app\holder\controller;

use app\system\model\Collectionholders;
use think\Controller;
use think\Session;

class Index extends Controller
{
    /* ========== 登录页 ========== */
    public function index()
    {
        return view('login/index');
    }

    /* ========== 登录 ========== */
    public function login(){
        $rules=[
            'name'=>'require',
            'cardnum'=>'require',
            'phone'=>'require',
        ];
        $msg=[
            'name.require'=>'姓名不能为空',
            'cardnum.require'=>'身份证号不能为空',
            'phone.require'=>'电话号码不能为空',
        ];

        $result=$this->validate(input(),$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        /* ++++++++++ 短信验证码 ++++++++++ */

        /* ++++++++++ 产权人或承租人 ++++++++++ */
        $holders=Collectionholders::where([
            'name'=>input('name'),
            'phone'=>input('phone'),
            'cardnum'=>input('cardnum')
        ])
            ->select();
        if(!$holders){
            return $this->error('输入信息有误','');
        }

        $item_ids=[];
        $collection_holders=[];
        foreach ($holders as $holder){
            if($holder->getData('holder')){
                $item_ids[]=$holder->item_id;
                $collection_holders[$holder->collection_id]=$holder->id;
            }
        }
        if(!$item_ids){
            return $this->error('非法访问','');
        }


        Session::set('holderinfo',[
            'name'=>input('name'),
            'phone'=>input('phone'),
            'cardnum'=>input('cardnum'),
            'item_ids'=>$item_ids,
            'collection_holders'=>$collection_holders,
            'time'=>time()
        ]);


        return $this->success('登录成功',url('Home/index'));
    }

    /* ========== 退出 ========== */
    public function logout(){
        session('holderinfo',null);
        $this->redirect('index');
    }
}
