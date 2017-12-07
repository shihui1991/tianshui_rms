<?php
/* |------------------------------------------------------
 * | 评估公司资料
 * |------------------------------------------------------
 * */

namespace app\company\controller;


use app\system\model\Companys;

class Company extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize(){
        parent::_initialize();
    }

    /* ========== 资料 ========== */
    public function index(){
        if(request()->isPost()){
            $datas=input();
            $rules=[
                'name'=>'require|unique:company,name,'.session('company.company_id').',id',
                'username'=>'require|alphaDash|length:4,20|unique:company,username,'.session('company.company_id').',id',
                'password'=>'require|length:4,20',
            ];
            $msg=[
                'name.require'=>'名称不能为空',
                'name.unique'=>'名称已存在',
                'username.require'=>'登录名不能为空',
                'username.alphaDash'=>'登录名只能为字母和数字，下划线（_）及破折号（-）',
                'username.length'=>'登录名长度为4-20位',
                'username.unique'=>'登录名已存在',
                'password.require'=>'密码不能为空',
                'password.length'=>'密码长度为4-20位',
            ];

            $result=$this->validate($datas,$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $company_model=new Companys();
            $other_datas=$company_model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $company_model->except(['id','type','secret_key','sort','status','picture'])->save($datas,['id'=>session('company.company_id')]);
            if($company_model !== false){
                return $this->success('修改成功','');
            }else{
                return $this->error('修改失败');
            }
        }else{
            $infos=Companys::get(session('company.company_id'));

            $this->assign([
                'infos'=>$infos,
            ]);

            return view();
        }
    }

}