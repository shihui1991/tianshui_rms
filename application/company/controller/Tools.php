<?php
/* |------------------------------------------------------
 * | 常用工具
 * |------------------------------------------------------
 * | 默认上传
 * | kindeditor 上传
 * */
namespace app\company\controller;

use app\system\model\Collectionbuildings;
use app\system\model\Companyvaluers;

class Tools extends Base
{
    /* ========== 默认上传 ========== */
    public function upl(){
        $files=request()->file();
        $key=array_keys($files);
        $file = $files[$key[0]];
        if($file){
            $info = $file->move( './uploads/default');
            if($info){
                $file_name=str_replace('\\','/',$info->getSaveName());
                $datas = '/uploads/default/'.$file_name;
                return $this->success('','',$datas);
            }else{
                return $this->error($file->getError(),'');
            }
        }
        return $this->error('请选择上传文件！');
    }

    /* ========== kindeditor 上传 ========== */
    public function uploads(){
        $files=request()->file();
        $key=array_keys($files);
        $file = $files[$key[0]];
        $dir=input('dir')?trim(input('dir')):'kindeditor';
        if($file){
            $info = $file->move('./uploads/'.$dir);
            if($info){
                $file_name=str_replace('\\','/',$info->getSaveName());
                $url = '/uploads/'.$dir.'/'.$file_name;
                $data['error']=0;
                $data['url']=$url;
            }else{
                $data['error']=1;
                $data['message']='上传失败！';
            }
        }else{
            $data['error']=1;
            $data['message']='请选择上传文件！';
        }
        exit(json_encode($data));
    }


    /* ========== 查询项目评估公司-->评估师 ========== */
    public function item_company_valuer(){
        $field=['id','name','register_num','valid_at'];

        $company_id=input('company_id');
        if(!is_numeric($company_id) || $company_id<1){
            return $this->error('请先选择评估公司','');
        }
        $where['company_id']=$company_id;
        $where['status']='1';

        $company_valuer=Companyvaluers::field($field)
            ->where($where)
            ->select();


        if($company_valuer){
            return $this->success('获取成功','',$company_valuer);
        }else{
            return $this->error('没有数据','');
        }
    }


    /* ========== 评估建筑物 ========== */
    public function estate_building(){
        $collection_id=input('collection_id');
        if(!is_numeric($collection_id) || $collection_id<1){
            return $this->error('请先选择权属','');
        }

        $count=Collectionbuildings::where('collection_id',$collection_id)->where('status_id',0)->count();
        if($count){
            return $this->error('房屋合法性认定未完成，暂时不能评估！','');
        }

        $where['collection_id']=$collection_id;
        $field=['cb.id','cb.item_id','cb.community_id','cb.collection_id','cb.building','cb.unit','cb.floor','cb.number',
            'cb.real_num','cb.real_unit','cb.use_id','cb.struct_id','cb.status_id','cb.build_year','cb.remark','cb.deleted_at',
            'bu.name as bu_name','bs.name as bs_name','s.name as s_name'];

        $collectionbuildings=Collectionbuildings::alias('cb')
            ->field($field)
            ->join('building_use bu','bu.id=cb.use_id','left')
            ->join('building_struct bs','bs.id=cb.struct_id','left')
            ->join('building_status s','s.id=cb.status_id','left')
            ->where($where)
            ->where('status_id', 'not in', '0,5')
            ->order(['cb.register' => 'desc', 'cb.use_id' => 'asc'])
            ->select();

        if($collectionbuildings){
            return $this->success('获取成功','',$collectionbuildings);
        }else{
            return $this->error('没有房屋数据','');
        }
    }
}