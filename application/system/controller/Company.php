<?php
/* |------------------------------------------------------
 * | 评估公司
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 排序
 * | 状态
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;

use app\system\model\Companys;
use think\Db;

class Company extends Auth
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
        $field=['id','type','name','short_name','contact_man','contact_phone','username','sort','status','deleted_at'];
        /* ++++++++++ 类型 ++++++++++ */
        $type=input('type');
        if(is_numeric($type) && in_array($type,[0,1])){
            $where['type']=$type;
            $datas['type']=$type;
        }
        /* ++++++++++ 名称 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['name']=['like','%'.$name.'%'];
            $datas['name']=$name;
        }
        /* ++++++++++ 登录名 ++++++++++ */
        $username=trim(input('username'));
        if($username){
            $where['username']=['like','%'.$username.'%'];
            $datas['username']=$username;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1])){
            $where['status']=$status;
            $datas['status']=$status;
        }
        /* ++++++++++ 排序 ++++++++++ */
        $ordername=input('ordername');
        $ordername=$ordername?$ordername:'sort';
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
        $company_model=new Companys();
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $company_model=$company_model->onlyTrashed();
            }
        }else{
            $company_model=$company_model->withTrashed();
        }
        $companys=$company_model->where($where)->field($field)->order([$ordername=>$orderby])->paginate($display_num);

        $datas['companys']=$companys;

        $this->assign($datas);

        return view($this->theme.'/company/index');
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Companys();
        if(request()->isPost()){
            $rules=[
                'name'=>'require|unique:company',
                'username'=>'require|alphaDash|length:4,20|unique:company',
                'password'=>'require|length:4,20',
                'secret_key'=>['regex'=>'/^[0-9a-zA-Z\-]{36}$/','unique'=>'company'],
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
                'secret_key.regex'=>'密钥为36位GUID',
                'secret_key.unique'=>'密钥已存在',
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
            return view($this->theme.'/company/modify',[
                'model'=>$model,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Companys::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Companys();

        return view($this->theme.'/company/modify',[
            'model'=>$model,
            'infos'=>$infos,
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
            'name'=>'require|unique:company,name,'.$id.',id',
            'username'=>'require|alphaDash|length:4,20|unique:company,username,'.$id.',id',
            'password'=>'require|length:4,20',
            'secret_key'=>['regex'=>'/^[0-9a-zA-Z\-]{36}$/','unique'=>['company','secret_key',$id,'id']],
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
            'secret_key.regex'=>'密钥为36位GUID',
            'secret_key.unique'=>'密钥已存在',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $company_model=new Companys();
        $other_datas=$company_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $company_model->isUpdate(true)->save($datas);
        if($company_model !== false){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

    /* ========== 状态 ========== */
    public function status(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        $status=input('status');

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        if(!in_array($status,[0,1])){
            return $this->error('错误操作');
        }
        $model=new Companys();
        $res=$model->save(['status'=>$status],['id'=>['in',$ids]]);
        if($res){
            return $this->success('修改成功','');
        }else{
            return $this->error('修改失败');
        }
    }

    /* ========== 排序 ========== */
    public function sort(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        $sorts=isset($inputs['sorts'])?$inputs['sorts']:[];
        if(empty($ids)||empty($sorts)){
            return $this->error('至少选择一项');
        }
        $datas=[];
        $i=0;
        $time=time();
        foreach ($ids as $id){
            $datas[$i]['id']=$id;
            $datas[$i]['sort']=(int)$sorts[$id];
            $datas[$i]['updated_at']=$time;
            $i++;
        }
        $sqls=batch_update_sql('company',['id','sort','updated_at'],$datas,['sort','updated_at'],'id');
        $res=false;
        if($sqls){
            foreach ($sqls as $sql){
                $res=db()->execute($sql);
            }
        }
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
        $res=Companys::destroy(['id'=>['in',$ids]]);
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

        $res=Db::table('company')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Companys::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
