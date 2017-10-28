<?php
/* |------------------------------------------------------
 * | 组织与部门
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 列表全部
 * | 添加
 * | 详情
 * | 修改
 * | 状态
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;

use app\system\model\Users;
use app\system\model\Depts;
use think\Db;

class Dept extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();

    }

    /* ========== 列表 ========== */
    public function index()
    {
        $depts=Depts::field(['d.id','d.parent_id','d.name','d.status','u.name as user_name'])
            ->alias('d')
            ->join('user u','u.id=d.user_id','left')
            ->select();
        $table_depts='';
        if($depts){
            $array=[];
            foreach ($depts as $dept){
                $dept->add_url=url('add',['id'=>$dept->id]);
                $dept->detail_url=url('detail',['id'=>$dept->id]);
                $dept->delete_url=url('delete',['ids'=>$dept->id]);
                $array[]=$dept;
            }
            $str = "
                    <tr data-tt-id='\$id' data-tt-parent-id='\$parent_id' >
                        <td>
                            <input class='va_m' type='checkbox' name='ids[]' value='\$id' onclick='checkBoxOp(this)' id='check-\$id'/>
                        </td>
                        <td>\$id</td>
                        <td>\$space \$name</td>
                        <td>\$user_name</td>
                        <td>\$status</td>
                        <td>
                            <button type='button' class='btn' onclick='layerIfWindow(&apos;添加部门&apos;,&apos;\$add_url&apos;,&apos;600&apos;,&apos;310&apos;)' >添加下级</button>
                            <button type='button' class='btn' onclick='layerIfWindow(&apos;部门信息&apos;,	&apos;\$detail_url&apos;,&apos;600&apos;,&apos;380&apos;)' >详细信息</button>
                            <button type='button' data-action='\$delete_url' class='btn js-ajax-form-btn'>删除</button>
                        </td>
                    </tr>
                    ";
            $table_depts=get_tree($array,$str,0,1,['&nbsp;&nbsp;┃&nbsp;','&nbsp;&nbsp;┣┅','&nbsp;&nbsp;┗┅'],'&nbsp;&nbsp;');
        }
        return view('index',['table_depts'=>$table_depts]);
    }

    /* ========== 列表全部 ========== */
    public function all(){
        /* ********** 查询条件 ********** */
        $datas=[];
        $where=[];
        $field=['d.id','d.parent_id','d.name','d.status','d.deleted_at','u.name as user_name'];
        /* ++++++++++ 部门名称 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['d.name']=['like','%'.$name.'%'];
            $datas['name']=$name;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1])){
            $where['d.status']=$status;
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
        $dept_model=new Depts();
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $dept_model=$dept_model->onlyTrashed();
            }
        }else{
            $dept_model=$dept_model->withTrashed();
        }
        $depts=$dept_model
            ->alias('d')
            ->field($field)
            ->where($where)
            ->join('user u','u.id=d.user_id','left')
            ->order(['d.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['depts']=$depts;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Depts();
        if(request()->isPost()){
            $rules=[
                'name'=>'require|unique:dept',
            ];
            $msg=[
                'name.require'=>'名称不能为空',
                'name.unique'=>'名称已存在',
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
            /* ++++++++++ 部门列表 ++++++++++ */
            $depts=Depts::field(['id','parent_id','name','status'])->where('status',1)->select();
            $options_depts='';
            if($depts){
                $array=[];
                foreach ($depts as $dept){
                    $dept->selected=$dept->id==$id?'selected':'';
                    $array[]=$dept;
                }
                $options_depts=get_tree($array);
            }

            /* ++++++++++ 用户列表 ++++++++++ */
            $users=Users::field(['id','name','status'])->where('status',1)->select();

            return view('modify',[
                'model'=>$model,
                'options_depts'=>$options_depts,
                'users'=>$users,
                
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Depts::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Depts();
        /* ++++++++++ 部门列表 ++++++++++ */
        $depts=Depts::field(['id','parent_id','name','status'])->where('status',1)->select();
        $options_depts='';
        if($depts){
            $array=[];
            foreach ($depts as $dept){
                $dept->selected=$dept->id==$infos->parent_id?'selected':'';
                $array[]=$dept;
            }
            $options_depts=get_tree($array);
        }

        /* ++++++++++ 用户列表 ++++++++++ */
        $users=Users::field(['id','name','status'])->where('status',1)->select();
        
        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'options_depts'=>$options_depts,
            'users'=>$users,
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
            'parent_id'=>'notIn:'.$id,
            'name'=>'require|unique:dept,name,'.$id.',id',
        ];
        $msg=[
            'parent_id.notIn'=>'上级部门不能为本身',
            'name.require'=>'名称不能为空',
            'name.unique'=>'名称已存在',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $dept_model=new Depts();
        $other_datas=$dept_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $dept_model->isUpdate(true)->save($datas);
        if($dept_model !== false){
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
        $model=new Depts();
        $res=$model->save(['status'=>$status],['id'=>['in',$ids]]);
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
        if(is_array($ids)){
            $fail_ids=[];
            $del_ids[]='0';
            foreach ($ids as $id){
                $count=Depts::field('id')->where('parent_id',$id)->count();
                if($count){
                    $fail_ids[]=$id;
                }else{
                    $del_ids[]=$id;
                }
            }
            $res=Depts::destroy($del_ids);
            $fail_num=count($fail_ids);
            if($res){
                if($fail_num){
                    return $this->success('部分删除成功！部分存在下级部门，请先删除其全部下级部门后重试！','');
                }else{
                    return $this->success('删除成功','');
                }
            }else{
                return $this->error('删除失败');
            }
        }else{
            $count=Depts::field('id')->where('parent_id',$ids)->count();
            if($count){
                return $this->error('其下存在下级部门，请先删除全部下级部门后重试！');
            }
            $res=Depts::destroy($ids);
            if($res){
                return $this->success('删除成功','');
            }else{
                return $this->error('删除失败');
            }
        }
    }

    /* ========== 恢复 ========== */
    public function restore(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';

        if(empty($ids)){
            return $this->error('至少选择一项');
        }

        $res=Db::table('dept')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Depts::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
