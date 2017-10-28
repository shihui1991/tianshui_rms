<?php
/* |------------------------------------------------------
 * | 特殊人群分类
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

use app\system\model\Crowds;
use think\Db;

class Crowd extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();

    }

    /* ========== 列表 ========== */
    public function index()
    {
        $crowds=Crowds::field(['id','parent_id','name','status'])->select();
        $table_crowds='';
        if($crowds){
            $array=[];
            foreach ($crowds as $crowd){
                $crowd->add_btn=$crowd->parent_id?'':"<button type='button' class='btn' onclick='layerIfWindow(&apos;添加分类&apos;,&apos;".url('add',['id'=>$crowd->id])."&apos;,&apos;600&apos;,&apos;260&apos;)' >添加子分类</button>";
                $crowd->detail_url=url('detail',['id'=>$crowd->id]);
                $crowd->delete_url=url('delete',['ids'=>$crowd->id]);
                $array[]=$crowd;
            }
            $str = "
                    <tr data-tt-id='\$id' data-tt-parent-id='\$parent_id' >
                        <td>
                            <input class='va_m' type='checkbox' name='ids[]' value='\$id' onclick='checkBoxOp(this)' id='check-\$id'/>
                        </td>
                        <td>\$id</td>
                        <td>\$space \$name</td>
                        <td>\$status</td>
                        <td>
                            \$add_btn
                            <button type='button' class='btn' onclick='layerIfWindow(&apos;特殊人群分类信息&apos;,	&apos;\$detail_url&apos;,&apos;600&apos;,&apos;320&apos;)' >详细信息</button>
                            <button type='button' data-action='\$delete_url' class='btn js-ajax-form-btn'>删除</button>
                        </td>
                    </tr>
                    ";
            $table_crowds=get_tree($array,$str,0,1,['&nbsp;&nbsp;┃&nbsp;','&nbsp;&nbsp;┣┅','&nbsp;&nbsp;┗┅'],'&nbsp;&nbsp;');
        }
        return view('index',['table_crowds'=>$table_crowds]);
    }

    /* ========== 列表全部 ========== */
    public function all(){
        /* ********** 查询条件 ********** */
        $datas=[];
        $where=[];
        $field=['id','parent_id','name','infos','status','deleted_at'];
        /* ++++++++++ 名称 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['name']=['like','%'.$name.'%'];
            $datas['name']=$name;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1])){
            $where['status']=$status;
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
        $crowd_model=new Crowds();
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $crowd_model=$crowd_model->onlyTrashed();
            }
        }else{
            $crowd_model=$crowd_model->withTrashed();
        }
        $crowds=$crowd_model->where($where)->field($field)->order([$ordername=>$orderby])->paginate($display_num);

        $datas['crowds']=$crowds;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Crowds();
        if(request()->isPost()){
            $rules=[
                'name'=>'require|unique:crowd',
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
            $crowds=Crowds::field(['id','parent_id','name','status'])->where('status',1)->where('parent_id',0)->select();
            $options_crowds='';
            if($crowds){
                $array=[];
                foreach ($crowds as $crowd){
                    $crowd->selected=$crowd->id==$id?'selected':'';
                    $array[]=$crowd;
                }
                $options_crowds=get_tree($array);
            }

            return view('modify',[
                'model'=>$model,
                'options_crowds'=>$options_crowds
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }

        $model=new Crowds();
        $infos=Crowds::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $crowds=Crowds::field(['id','parent_id','name','status'])->where('status',1)->where('parent_id',0)->select();
        $options_crowds='';
        if($crowds){
            $array=[];
            foreach ($crowds as $crowd){
                $crowd->selected=$crowd->id==$infos->parent_id?'selected':'';
                $array[]=$crowd;
            }
            $options_crowds=get_tree($array);
        }
        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'options_crowds'=>$options_crowds,
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
            'name'=>'require|unique:crowd,name,'.$id.',id',
        ];
        $msg=[
            'parent_id.notIn'=>'上级分类不能为本身',
            'name.require'=>'名称不能为空',
            'name.unique'=>'名称已存在',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $crowd_model=new Crowds();
        $other_datas=$crowd_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $crowd_model->isUpdate(true)->save($datas);
        if($crowd_model !== false){
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
        $model=new Crowds();
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
                $count=Crowds::field('id')->where('parent_id',$id)->count();
                if($count){
                    $fail_ids[]=$id;
                }else{
                    $del_ids[]=$id;
                }
            }
            $res=Crowds::destroy($del_ids);
            $fail_num=count($fail_ids);
            if($res){
                if($fail_num){
                    return $this->success('部分删除成功！部分存在子分类，请先删除其全部子分类后重试！','');
                }else{
                    return $this->success('删除成功','');
                }
            }else{
                return $this->error('删除失败');
            }
        }else{
            $count=Crowds::field('id')->where('parent_id',$ids)->count();
            if($count){
                return $this->error('其下存在子分类，请先删除全部子分类后重试！');
            }
            $res=Crowds::destroy($ids);
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

        $res=Db::table('crowd')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Crowds::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
