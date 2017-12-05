<?php
/* |------------------------------------------------------
 * | 登录
 * |------------------------------------------------------
 * | 登录入口 index
 * | 登录 login
 * | 退出 logout
 * */

namespace app\company\controller;

use app\system\model\Companys;
use app\system\model\Items;
use think\Controller;

class Index extends Controller
{
    public function index(){
        return view();
    }


    /* ========== 登录 ========== */
    public function login()
    {
        $rules = [
            'username' => 'require|alphaDash|length:4,20',
            'password' => 'require|length:4,20',
        ];
        $msg = [
            'username.require' => '用户名不能为空',
            'username.alphaDash' => '用户名只能为字母和数字，下划线（_）及破折号（-）',
            'username.length' => '用户名长度为4-20位',
            'password.require' => '密码不能为空',
            'password.length' => '密码长度为4-20位',
        ];

        $result = $this->validate(input(), $rules, $msg);
        if (true !== $result) {
            return $this->error($result);
        }

        $username = input('username');
        $password = input('password');

        $company=Companys::field(['id','type','name','username','password','secret_key','status'])
            ->where('username',$username)
            ->find();
        if(!$company){
            return $this->error('非法访问');
        }
        if(!$company->getData('status')){
            return $this->error('非法访问');
        }
        if($company->password != $password){
            return $this->error('密码错误');
        }

        $item_ids=$company->itemcompany()->column('item_id');
        if($item_ids){
            $item_ids=Items::whereIn('id',$item_ids)->where('status',1)->column('id');
        }

        session('company',[
            'company_id'=>$company->id,
            'type'=>$company->getData('type'),
            'name'=>$company->name,
            'secret_key'=>$company->secret_key,
            'item_ids'=>$item_ids,
            'time'=>time(),
        ]);

        return $this->success('登录成功',url('Home/index'),$company->secret_key);
    }


    /* ========== 退出 ========== */
    public function logout(){
        session(null);
        $this->redirect('index');
    }
}