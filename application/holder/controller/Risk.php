<?php
/* |------------------------------------------------------
 * | 风险评估调查
 * |------------------------------------------------------
 * */
namespace app\holder\controller;


use app\system\model\Collectionholders;
use app\system\model\Collections;
use app\system\model\Itemprocesss;
use app\system\model\Itemtopics;
use app\system\model\Risks;
use app\system\model\Risktopics;
use think\Db;

class Risk extends Base
{
    public $itemprocess_status;
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();
        $this->itemprocess_status=Itemprocesss::where(['item_id'=>$this->item_id,'process_id'=>7])->value('status');
        if(!$this->itemprocess_status){
            return $this->error('调查还未开始……');
        }
        if(request()->isMobile()){
            $this->assign([
                'url'=>url('Itemcompanyvote/index'),
            ]);
        }
    }

    /* ========== 列表 ========== */
    public function index()
    {
        return '非法访问';
    }


    /* ========== 添加 ========== */
    public function add(){
        if(!$this->item_id){
            return $this->error('非法访问','');
        }
        if(!$this->collection_id){
            return $this->error('非法访问','');
        }
        $holders=session('holderinfo.collection_holders');
        $holder_id=$holders[$this->collection_id];

        if(request()->isPost()){
            Db::startTrans();
            try{
                $inputs=input();
                $topics=isset($inputs['topics'])?$inputs['topics']:[];

                $community_id=Collections::where(['item_id'=>$this->item_id,'id'=>$this->collection_id])->value('community_id');

                $datas=$inputs;
                $datas['item_id']=$this->item_id;
                $datas['collection_id']=$this->collection_id;
                $datas['holder_id']=$holder_id;
                $datas['community_id']=$community_id;
                $model=new Risks();
                $model->save($datas);

                if($topics){
                    $risktopic_data=[];
                    foreach($topics as $topic_id=>$answer){
                        $risktopic_data[]=[
                            'item_id'=>$this->item_id,
                            'community_id'=>$community_id,
                            'collection_id'=>$this->collection_id,
                            'holder_id'=>$holder_id,
                            'risk_id'=>$model->id,
                            'topic_id'=>$topic_id,
                            'answer'=>$answer,
                        ];
                    }
                    $risktopic_model=new Risktopics();
                    $risktopic_model->saveAll($risktopic_data);
                }

                $res=true;
                Db::commit();
            }catch (\Exception $exception){
                $res=false;dump($exception);
                Db::rollback();
            }

            if($res){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            $itemtopics=Itemtopics::with(['topic'])->where(['item_id'=>$this->item_id])->select();

            $holders=Collectionholders::field(['id','name','address','phone'])->where(['item_id'=>$this->item_id,'collection_id'=>$this->collection_id,'id'=>['neq',$holder_id]])->select();

            $model=new Risks();

            $this->assign([
                'item_id'=>$this->item_id,
                'collection_id'=>$this->collection_id,
                'itemtopics'=>$itemtopics,
                'holders'=>$holders,
                'model'=>$model,
            ]);

            return view($this->theme.'/risk/add');
        }
    }


    /* ========== 详情 ========== */
    public function detail(){
        if(!$this->item_id){
            return $this->error('非法访问','');
        }
        if(!$this->collection_id){
            return $this->error('非法访问','');
        }

        $holders=session('holderinfo.collection_holders');
        $holder_id=$holders[$this->collection_id];

        $infos=Risks::with(['risktopic'=>['topic'],'recommendholder'])
            ->where([
                'item_id'=>$this->item_id,
                'collection_id'=>$this->collection_id,
                'holder_id'=>$holder_id,
            ])
            ->find();

        if(!$infos){
            return $this->redirect('add',['item_id'=>$this->item_id, 'collection_id'=>$this->collection_id]);
        }

        $this->assign(['infos'=>$infos]);

        return view($this->theme.'/risk/detail');
    }
}

