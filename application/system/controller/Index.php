<?php
/* |------------------------------------------------------
 * | 后台入口
 * |------------------------------------------------------
 * | 登录页
 * | 登录
 * | 退出
 * */

namespace app\system\controller;

use think\Controller;
use think\Session;
use app\system\model\Users;

class Index extends Controller
{
    /* ========== 登录页 ========== */
    public function index()
    {
        return view();
    }

    /* ========== 登录 ========== */
    public function login(){
        $rules=[
            'username'=>'require|alphaDash|length:4,20',
            'password'=>'require|length:4,20',
        ];
        $msg=[
            'username.require'=>'用户名不能为空',
            'username.alphaDash'=>'用户名只能为字母和数字，下划线（_）及破折号（-）',
            'username.length'=>'用户名长度为4-20位',
            'password.require'=>'密码不能为空',
            'password.length'=>'密码长度为4-20位',
        ];

        $result=$this->validate(input(),$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $username=input('username');
        $password=input('password');

        $user=Users::field(['u.id','username','password','secret_key','role_id','u.status','is_admin','menu_ids','r.status as r_status'])
            ->alias('u')
            ->join('role r','r.id = u.role_id','left')
            ->where(['username'=>$username])
            ->find();
        if(!$user){
            return $this->error('用户不存在');
        }
        if(md5($password) != $user->password){
            return $this->error('密码错误');
        }
        if(!$user->getData('status')){
            return $this->error('用户已禁用');
        }
        if(!$user->r_status){
            return $this->error('没有权限');
        }
        $userinfo=[
            'user_id'=>$user->id,
            'role_id'=>$user->role_id,
            'username'=>$user->username,
            'secret_key'=>$user->secret_key,
            'is_admin'=>$user->is_admin,
            'menu_ids'=>$user->menu_ids,
            'time'=>time(),
        ];

        Session::set('userinfo',$userinfo);

        $user_model=new Users();
        $data=$user_model->login_data();
        $user_model->where('id',$user->id)->update($data);

        return $this->success('登录成功',url('Home/index'));
    }

    /* ========== 退出 ========== */
    public function logout(){
        Session::clear();
        $this->redirect('index');
    }
}
