<?php
/* |------------------------------------------------------
 * | 系统用户
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 修改密码
 * | 状态
 * | 删除
 * | 恢复
 * | 销毁
 * | 个人中心
 * */
namespace app\system\controller;

use app\system\model\Roles;
use app\system\model\Users;
use think\Db;
use think\Session;

class User extends Auth
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
        /* ++++++++++ 角色 ++++++++++ */
        $role_id=input('role_id');
        if(is_numeric($role_id)){
            $where['role_id']=$role_id;
        }
        $roles=Roles::field(['id','parent_id','name','status'])->where('status',1)->select();
        $options_roles='';
        if($roles){
            $array=[];
            foreach ($roles as $role){
                $role->selected=$role->id==$role_id?'selected':'';
                $array[]=$role;
            }
            $options_roles=get_tree($array);
        }
        $datas['roles']=$options_roles;
        /* ++++++++++ 姓名 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['user.name']=['like','%'.$name.'%'];
            $datas['name']=$name;
        }
        /* ++++++++++ 用户名 ++++++++++ */
        $username=trim(input('username'));
        if($username){
            $where['user.username']=['like','%'.$username.'%'];
            $datas['username']=$username;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1])){
            $where['user.status']=$status;
            $datas['status']=$status;
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
        $user_model=new Users();
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $user_model=$user_model->onlyTrashed();
            }
        }else{
            $user_model=$user_model->withTrashed();
        }

        $field=['u.id','role_id','u.name','phone','email','username','secret_key','u.status','u.deleted_at','r.name as role_name'];
        $users=$user_model
            ->alias('u')
            ->field($field)
            ->join('role r','u.role_id = r.id','left')
            ->where($where)
            ->order([$ordername=>$orderby])
            ->paginate($display_num);

        $datas['users']=$users;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Users();
        if(request()->isPost()){
            $rules=[
                'role_id'=>'require',
                'username'=>'require|alphaDash|length:4,20|unique:user',
                'password'=>'require|length:4,20|confirm',
                'password_confirm'=>'require|length:4,20',
                'name'=>'length:2,20',
                'email'=>'email',
                'phone'=>['regex'=>'/^1[3-9]\d{9}/'],
            ];
            $msg=[
                'role_id.require'=>'选择用户角色',
                'username.require'=>'用户名不能为空',
                'username.alphaDash'=>'用户名只能为字母和数字，下划线（_）及破折号（-）',
                'username.length'=>'用户名长度为4-20位',
                'username.unique'=>'用户名已存在',
                'password.require'=>'密码不能为空',
                'password.length'=>'密码长度为4-20位',
                'password.confirm'=>'重复密码不一致',
                'password_confirm.require'=>'重复密码不能为空',
                'password_confirm.length'=>'重复密码长度为4-20位',
                'name.length'=>'姓名格式错误',
                'email.email'=>'邮箱格式错误',
                'phone.regex'=>'手机格式错误',
            ];

            $result=$this->validate(input(),$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $other_datas=$model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $model->save($datas);
            if($model !== false){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            $roles=Roles::field(['id','parent_id','name','status'])->where('status',1)->select();
            $options_roles='';
            if($roles){
                $options_roles=get_tree($roles,"<option value='\$id'>\$space \$name</option>");
            }
            return view('modify',[
                'model'=>$model,
                'options_roles'=>$options_roles,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $model=new Users();
        $infos=Users::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $roles=Roles::field(['id','parent_id','name','status'])->where('status',1)->select();
        $options_roles='';
        if($roles){
            $array=[];
            foreach ($roles as $role){
                $role->selected=$role->id==$infos->role_id?'selected':'';
                $array[]=$role;
            }
            $options_roles=get_tree($array);
        }
        return view('modify',[
            'infos'=>$infos,
            'model'=>$model,
            'options_roles'=>$options_roles,
        ]);
    }

    /* ========== 修改 ========== */
    public function edit(){
        $id=input('id');
        if(!$id){
            return $this->error('错误操作');
        }
        $datas=input();
        $rules=[
            'role_id'=>'require',
            'username'=>'require|alphaDash|length:4,20|unique:user,username,'.$id.',id',
            'name'=>'length:2,20',
            'email'=>'email',
            'phone'=>['regex'=>'/^1[3-9]\d{9}/'],
        ];
        $msg=[
            'role_id.require'=>'选择用户角色',
            'username.require'=>'用户名不能为空',
            'username.alphaDash'=>'用户名只能为字母和数字，下划线（_）及破折号（-）',
            'username.length'=>'用户名长度为4-20位',
            'username.unique'=>'用户名已存在',
            'name.length'=>'姓名格式错误',
            'email.email'=>'邮箱格式错误',
            'phone.regex'=>'手机格式错误',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $user_model=new Users();
        $other_datas=$user_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $user_model->isUpdate(true)->save($datas);
        if($user_model !== false){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

    /* ========== 修改密码 ========== */
    public function password(){
        $id=input('id');
        if(!$id){
            return $this->error('错误操作');
        }
        if(request()->isPost()){
            $datas=input();
            $rules=[
                'password_current'=>'require|length:4,20',
                'password'=>'require|length:4,20|confirm',
                'password_confirm'=>'require|length:4,20',
            ];
            $msg=[
                'password_current.require'=>'当前密码不能为空',
                'password_current.length'=>'密码长度为4-20位',
                'password.require'=>'密码不能为空',
                'password.length'=>'密码长度为4-20位',
                'password.confirm'=>'重复密码不一致',
                'password_confirm.require'=>'重复密码不能为空',
                'password_confirm.length'=>'重复密码长度为4-20位',
            ];

            $result=$this->validate($datas,$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $user_model=Users::field(['id','password'])->find($id);
            if(md5($datas['password_current']) != $user_model->password){
                return $this->error('当前密码输入错误');
            }

            $user_model->isUpdate(true)->allowField(['password','updated_at'])->save($datas);
            if($user_model !== false){
                return $this->success('修改成功','');
            }else{
                return $this->error('修改失败');
            }
        }else{
            $infos=Users::field(['id'])->find($id);
            if(!$infos){
                return $this->error('错误操作');
            }

            return view('',[
                'infos'=>$infos,
            ]);
        }
    }

    /* ========== 状态 ========== */
    public function status(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        $status=$inputs['status'];

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        if(!in_array($status,[0,1])){
            return $this->error('错误操作');
        }
        $res=model('Users')->save(['status'=>$status],['id'=>['in',$ids]]);
        if($res){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

    /* ========== 删除 ========== */
    public function delete(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        $res=Users::destroy($ids);
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

        $res=Db::table('user')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Users::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }

    /* ========== 个人中心 ========== */
    public function info(){
        if(request()->isPost()){
            $datas=input();
            $rules=[
                'username'=>'require|alphaDash|length:4,20|unique:user,username,'.$datas['id'].',id',
                'name'=>'length:2,20',
                'email'=>'email',
                'phone'=>['regex'=>'/^1[3-9]\d{9}/'],
            ];
            $msg=[
                'username.require'=>'用户名不能为空',
                'username.alphaDash'=>'用户名只能为字母和数字，下划线（_）及破折号（-）',
                'username.length'=>'用户名长度为4-20位',
                'username.unique'=>'用户名已存在',
                'name.length'=>'姓名格式错误',
                'email.email'=>'邮箱格式错误',
                'phone.regex'=>'手机格式错误',
            ];

            $result=$this->validate($datas,$rules,$msg);
            if(true !== $result){
                return $this->error($result);
            }

            $user_model=new Users();
            $other_datas=$user_model->other_data(input());
            $datas=array_merge(input(),$other_datas);
            $user_model->isUpdate(true)->save($datas);
            if($user_model !== false){
                return $this->success('修改成功','');
            }else{
                return $this->error('修改失败');
            }
        }else{
            $infos=Users::field(['u.*','is_admin','r.name as role_name'])
                ->alias('u')
                ->join('role r','r.id = u.role_id','left')
                ->where(['u.id'=>Session::get('userinfo.user_id')])
                ->find();

            return view('',[
                'infos'=>$infos,
            ]);
        }
    }
}
