<?php
/* |------------------------------------------------------
 * | 接口文档
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 列表全部
 * | 添加
 * | 详情
 * | 修改
 * | 状态
 * | 排序
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;

use app\system\model\Apis;
use think\Db;

class Api extends Auth
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();

    }

    /* ========== 列表 ========== */
    public function index()
    {
        $apis=Apis::field(['id','parent_id','name','url','type','infos','status','sort'])->order('sort asc')->select();
        $table_apis='';
        if($apis){
            $array=[];
            foreach ($apis as $api){
                $api->add_btn=$api->parent_id?'':"<button type='button' class='btn' onclick='layerIfWindow(&apos;添加接口&apos;,&apos;".url('add',['id'=>$api->id])."&apos;,&apos;800&apos;,&apos;600&apos;)' >添加子接口</button>";
                $api->detail_url=url('detail',['id'=>$api->id]);
                $api->delete_url=url('delete',['ids'=>$api->id]);
                $array[]=$api;
            }
            $str = "
                    <tr data-tt-id='\$id' data-tt-parent-id='\$parent_id' >
                        <td>
                            <input class='va_m' type='checkbox' name='ids[]' value='\$id' onclick='checkBoxOp(this)' id='check-\$id'/>
                        </td>
                        <td><input style='width: 50px;' type='text' name='sorts[\$id]' value='\$sort' id='input-\$id' data-id='\$id'></td>
                        <td>\$id</td>
                        <td>\$space \$name</td>
                        <td>\$url</td>
                        <td>\$type</td>
                        <td>\$infos</td>
                        <td>\$status</td>
                        <td>
                            \$add_btn
                            <button type='button' class='btn' onclick='layerIfWindow(&apos;接口信息&apos;,	&apos;\$detail_url&apos;,&apos;800&apos;,&apos;600&apos;)' >详细信息</button>
                            <button type='button' data-action='\$delete_url' class='btn js-ajax-form-btn'>删除</button>
                        </td>
                    </tr>
                    ";
            $table_apis=get_tree($array,$str,0,1,['&nbsp;&nbsp;┃&nbsp;','&nbsp;&nbsp;┣┅','&nbsp;&nbsp;┗┅'],'&nbsp;&nbsp;');
        }
        return view('index',['table_apis'=>$table_apis]);
    }

    /* ========== 列表全部 ========== */
    public function all(){
        /* ********** 查询条件 ********** */
        $datas=[];
        $where=[];
        $field=['id','parent_id','name','url','type','infos','status','sort','deleted_at'];
        /* ++++++++++ 名称 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['name']=['like','%'.$name.'%'];
            $datas['name']=$name;
        }
        /* ++++++++++ 地址 ++++++++++ */
        $url=trim(input('url'));
        if($url){
            $where['url']=['like','%'.$url.'%'];
            $datas['url']=$url;
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
        $api_model=new Apis();
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $api_model=$api_model->onlyTrashed();
            }
        }else{
            $api_model=$api_model->withTrashed();
        }
        $apis=$api_model->where($where)->field($field)->order([$ordername=>$orderby])->paginate($display_num);

        $datas['apis']=$apis;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add($id=0){
        $model=new Apis();
        if(request()->isPost()){
            $rules=[
                'name'=>'require|unique:api',
                'url'=>'require|unique:api',
            ];
            $msg=[
                'name.require'=>'名称不能为空',
                'url.require'=>'地址不能为空',
                'name.unique'=>'名称已存在',
                'url.unique'=>'地址已存在',
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
            $apis=Apis::field(['id','parent_id','name','status','sort'])->where('status',1)->where('parent_id',0)->order('sort asc')->select();
            $options_apis='';
            if($apis){
                $array=[];
                foreach ($apis as $api){
                    $api->selected=$api->id==$id?'selected':'';
                    $array[]=$api;
                }
                $options_apis=get_tree($array);
            }

            return view('modify',[
                'model'=>$model,
                'options_apis'=>$options_apis
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail($id=null){
        if(!$id){
            return $this->error('至少选择一项');
        }

        $model=new Apis();
        $infos=Apis::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $apis=Apis::field(['id','parent_id','name','status','sort'])->where('status',1)->where('parent_id',0)->order('sort asc')->select();
        $options_apis='';
        if($apis){
            $array=[];
            foreach ($apis as $api){
                $api->selected=$api->id==$infos->parent_id?'selected':'';
                $array[]=$api;
            }
            $options_apis=get_tree($array);
        }
        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'options_apis'=>$options_apis,
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
            'name'=>'require|unique:api,name,'.$id.',id',
            'url'=>'require|unique:api,url,'.$id.',id',
        ];
        $msg=[
            'parent_id.notIn'=>'上级接口不能为本身',
            'name.require'=>'名称不能为空',
            'url.require'=>'地址不能为空',
            'name.unique'=>'名称已存在',
            'url.unique'=>'地址已存在',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $api_model=new Apis();
        $other_datas=$api_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $api_model->isUpdate(true)->save($datas);
        if($api_model !== false){
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
        $model=new Apis();
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
        $sqls=batch_update_sql('api',['id','sort','updated_at'],$datas,['sort','updated_at'],'id');
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
        if(is_array($ids)){
            $fail_ids=[];
            $del_ids[]='0';
            foreach ($ids as $id){
                $count=Apis::field('id')->where('parent_id',$id)->count();
                if($count){
                    $fail_ids[]=$id;
                }else{
                    $del_ids[]=$id;
                }
            }
            $res=Apis::destroy(['id'=>['in',$del_ids]]);
            $fail_num=count($fail_ids);
            if($res){
                if($fail_num){
                    return $this->success('部分删除成功！部分存在子接口，请先删除其全部子接口后重试！','');
                }else{
                    return $this->success('删除成功','');
                }
            }else{
                return $this->error('删除失败');
            }
        }else{
            $count=Apis::field('id')->where('parent_id',$ids)->count();
            if($count){
                return $this->error('其下存在子接口，请先删除全部子接口后重试！');
            }
            $res=Apis::destroy(['id'=>['in',$ids]]);
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

        $res=Db::table('api')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Apis::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
