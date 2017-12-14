<?php
/* |------------------------------------------------------
 * | 新闻公告
 * |------------------------------------------------------
 * | 初始化操作
 * | 列表
 * | 添加
 * | 详情
 * | 修改
 * | 排序
 * | 状态
 * | 置顶
 * | 删除
 * | 恢复
 * | 销毁
 * */
namespace app\system\controller;

use app\system\model\Items;
use app\system\model\Newscates;
use app\system\model\Newss;
use think\Db;

class News extends Auth
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
        $field=['n.id','cate_id','n.name','item_id','release_at','keywords','url','url_name','n.sort','n.is_top','n.status','n.deleted_at','c.name as c_name','i.name as i_name'];
        /* ++++++++++ 分类 ++++++++++ */
        $cate_id=input('cate_id');
        if(is_numeric($cate_id)){
            $where['cate_id']=$cate_id;
            $datas['cate_id']=$cate_id;
        }
        /* ++++++++++ 名称 ++++++++++ */
        $name=trim(input('name'));
        if($name){
            $where['n.name']=['like','%'.$name.'%'];
            $datas['name']=$name;
        }
        /* ++++++++++ 项目 ++++++++++ */
        $item_id=input('item_id');
        if(is_numeric($item_id)){
            $where['item_id']=$item_id;
            $datas['item_id']=$item_id;
        }
        /* ++++++++++ 关键词 ++++++++++ */
        $keywords=trim(input('keywords'));
        if($keywords){
            $where['keywords']=['like','%'.$keywords.'%'];
            $datas['keywords']=$keywords;
        }
        /* ++++++++++ 链接 ++++++++++ */
        $url=trim(input('url'));
        if($url){
            $where['url']=['like','%'.$url.'%'];
            $datas['url']=$url;
        }
        /* ++++++++++ 置顶 ++++++++++ */
        $is_top=input('is_top');
        if(is_numeric($is_top) && in_array($is_top,[0,1])){
            $where['n.is_top']=$is_top;
            $datas['is_top']=$is_top;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1])){
            $where['n.status']=$status;
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
        /* ++++++++++ 查询 ++++++++++ */
        $news_model=new Newss();
        $datas['model']=$news_model;
        $deleted=input('deleted');
        if(is_numeric($deleted) && in_array($deleted,[0,1])){
            $datas['deleted']=$deleted;
            if($deleted==1){
                $news_model=$news_model->onlyTrashed();
            }
        }else{
            $news_model=$news_model->withTrashed();
        }
        $newss=$news_model
            ->alias('n')
            ->field($field)
            ->join('news_cate c','c.id=n.cate_id','left')
            ->join('item i','i.id=n.item_id','left')
            ->where($where)
            ->order(['news.is_top'=>'desc','news.'.$ordername=>$orderby])
            ->paginate($display_num);

        $datas['newss']=$newss;

        /* ++++++++++ 新闻分类列表 ++++++++++ */
        $newscates=Newscates::field(['id','name','status'])->where('status',1)->select();
        $datas['newscates']=$newscates;
        /* ++++++++++ 项目列表 ++++++++++ */
        $items=Items::field(['id','name','is_top','status'])->order('is_top desc')->select();
        $datas['items']=$items;

        $this->assign($datas);

        return view();
    }

    /* ========== 添加 ========== */
    public function add(){
        $model=new Newss();
        if(request()->isPost()){
            $rules=[
                'cate_id'=>'require',
                'name'=>'require|unique:news',
                'release_at'=>'require',
            ];
            $msg=[
                'cate_id.require'=>'请选择分类',
                'name.require'=>'名称不能为空',
                'name.unique'=>'名称已存在',
                'release_at.require'=>'请选择发布时间',
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
            /* ++++++++++ 新闻分类列表 ++++++++++ */
            $newscates=Newscates::field(['id','name','status'])->where('status',1)->select();
            /* ++++++++++ 项目列表 ++++++++++ */
            $items=Items::field(['id','name','is_top','status'])->where('status',1)->order('is_top desc')->select();

            return view('modify',[
                'model'=>$model,
                'newscates'=>$newscates,
                'items'=>$items,
            ]);
        }
    }

    /* ========== 详情 ========== */
    public function detail(){
        $id=input('id');
        if(!$id){
            return $this->error('至少选择一项');
        }
        $infos=Newss::withTrashed()->find($id);
        if(!$infos){
            return $this->error('选择项目不存在');
        }

        $model=new Newss();
        /* ++++++++++ 新闻分类列表 ++++++++++ */
        $newscates=Newscates::field(['id','name','status'])->where('status',1)->select();
        /* ++++++++++ 项目列表 ++++++++++ */
        $items=Items::field(['id','name','is_top','status'])->where('status',1)->order('is_top desc')->select();

        return view('modify',[
            'model'=>$model,
            'infos'=>$infos,
            'newscates'=>$newscates,
            'items'=>$items,
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
            'cate_id'=>'require',
            'name'=>'require|unique:news,name,'.$id.',id',
            'release_at'=>'require',
        ];
        $msg=[
            'cate_id.require'=>'请选择分类',
            'name.require'=>'名称不能为空',
            'name.unique'=>'名称已存在',
            'release_at.require'=>'请选择发布时间',
        ];

        $result=$this->validate($datas,$rules,$msg);
        if(true !== $result){
            return $this->error($result);
        }

        $news_model=new Newss();
        $other_datas=$news_model->other_data(input());
        $datas=array_merge(input(),$other_datas);
        $news_model->isUpdate(true)->save($datas);
        if($news_model !== false){
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
        $model=new Newss();
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
        $sqls=batch_update_sql('news',['id','sort','updated_at'],$datas,['sort','updated_at'],'id');
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

    /* ========== 置顶 ========== */
    public function istop(){
        $inputs=input();
        $ids=isset($inputs['ids'])?$inputs['ids']:'';
        $top=input('top');

        if(empty($ids)){
            return $this->error('至少选择一项');
        }
        if(!in_array($top,[0,1])){
            return $this->error('错误操作');
        }
        $model=new Newss();
        $res=$model->allowField(['is_top','updated_at'])->save(['is_top'=>$top],['id'=>['in',$ids]]);
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
        $res=Newss::destroy(['id'=>['in',$ids]]);
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

        $res=Db::table('news')->whereIn('id',$ids)->update(['deleted_at'=>null,'updated_at'=>time()]);
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
        $res=Newss::onlyTrashed()->whereIn('id',$ids)->delete(true);
        if($res){
            return $this->success('销毁成功','');
        }else{
            return $this->error('销毁失败，请先删除！');
        }
    }
}
