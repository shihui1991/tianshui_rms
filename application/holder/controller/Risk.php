<?php
/* |------------------------------------------------------
 * | 风险评估调查
 * |------------------------------------------------------
 * */
namespace app\holder\controller;


use app\system\model\Collectionholders;
use app\system\model\Collections;
use app\system\model\Itemtopics;
use app\system\model\Risks;
use app\system\model\Risktopics;
use think\Db;

class Risk extends Base
{
    /* ========== 初始化 ========== */
    public function _initialize()
    {
        parent::_initialize();


    }

    /* ========== 列表 ========== */
    public function index()
    {
        return '非法访问';
    }


    /* ========== 添加 ========== */
    public function add(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问','');
        }
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问','');
        }
        $holders=session('holderinfo.collection_holders');
        $holder_id=$holders[$collection_id];

        if(request()->isPost()){
            Db::startTrans();
            try{
                $inputs=input();
                $topics=$inputs['topics'];

                $community_id=Collections::where(['item_id'=>$item_id,'id'=>$collection_id])->value('community_id');

                $datas=$inputs;
                $datas['holder_id']=$holder_id;
                $datas['community_id']=$community_id;
                $model=new Risks();
                $model->save($datas);

                $risktopic_data=[];
                foreach($topics as $topic_id=>$answer){
                    $risktopic_data[]=[
                        'item_id'=>$item_id,
                        'community_id'=>$community_id,
                        'collection_id'=>$collection_id,
                        'holder_id'=>$holder_id,
                        'risk_id'=>$model->id,
                        'topic_id'=>$topic_id,
                        'answer'=>$answer,
                    ];
                }
                $risktopic_model=new Risktopics();
                $risktopic_model->saveAll($risktopic_data);

                $res=true;
                Db::commit();
            }catch (\Exception $exception){
                $res=false;
                Db::rollback();
            }

            if($res){
                return $this->success('保存成功','');
            }else{
                return $this->error('保存失败');
            }
        }else{
            $itemtopics=Itemtopics::with(['topic'])->where(['item_id'=>$item_id])->select();

            $holders=Collectionholders::field(['id','name','address','phone'])->where(['item_id'=>$item_id,'collection_id'=>$collection_id,'id'=>['neq',$holder_id]])->select();

            $model=new Risks();

            $this->assign([
                'item_id'=>$item_id,
                'collection_id'=>$collection_id,
                'itemtopics'=>$itemtopics,
                'holders'=>$holders,
                'model'=>$model,
            ]);

            return view();
        }
    }


    /* ========== 详情 ========== */
    public function detail(){
        $item_id=input('item_id');
        if(!$item_id){
            return $this->error('非法访问','');
        }
        $collection_id=input('collection_id');
        if(!$collection_id){
            return $this->error('非法访问','');
        }

        $holders=session('holderinfo.collection_holders');
        $holder_id=$holders[$collection_id];

        $infos=Risks::with(['risktopic'=>['topic'],'recommendholder'])
            ->where([
                'item_id'=>$item_id,
                'collection_id'=>$collection_id,
                'holder_id'=>$holder_id,
            ])
            ->find();

        if(!$infos){
            return $this->redirect('add',['item_id'=>$item_id, 'collection_id'=>$collection_id]);
        }

        $this->assign(['infos'=>$infos]);

        return view();
    }
}

