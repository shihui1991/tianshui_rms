<?php
/* |------------------------------------------------------
 * | 常用工具
 * |------------------------------------------------------
 * | 默认上传
 * | kindeditor 上传
 * | kindeditor 文件管理
 * | 房源户型图
 * | 接口分组公共参数
 * | 生成GUID
 * */
namespace app\system\controller;


use app\system\model\Collectionbuildings;
use app\system\model\Collectionholders;
use app\system\model\Collections;
use app\system\model\Companys;
use app\system\model\Companyvaluers;
use app\system\model\Itemcompanys;

class Tools extends Auth
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

    /* ========== kindeditor 文件管理 ========== */
    public function filelist()
    {
        $path=$_GET['path'];
        $dir=input('dir')?trim(input('dir')):'';

        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $root_path='./uploads/';
        if(!file_exists($root_path)){
            exit(json_encode(['error'=>1,'message'=>'没有文件！','total_count'=>0,'file_list'=>[]]));
        }

        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $root_url = request()->domain();
        //图片扩展名
        $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp','ico');

        //文件目录
        $file_path=$root_path;
        if(file_exists($file_path.$dir)){
            $file_path .=$dir.'/';
        }
        $current_path=$path?$path:$file_path;
        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.') continue;
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }
        //排序形式，name or size or type
        global $order;
        $order= empty($_GET['order']) ? 'name' : strtolower($_GET['order']);


        usort($file_list, 'cmp_func');

        $result = array();
        //相对于根目录的上一级目录
        if(!$path || $path==$root_path || $file_path==$current_path){
            $moveup_dir_path=$root_path;
        }else{
            $moveup_dir_path=$file_path;
        }
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_path;
        //当前目录的URL
        $result['current_url'] = $root_url.ltrim($path,'.');
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;

        //输出JSON字符串
        exit(json_encode($result));
    }

    /* ========== 房源户型图 ========== */
    public function houselayoutpic(){
        $community_id=input('community_id');
        $layout_id=input('layout_id');
        if(!$community_id){
            return $this->error('请选择小区');
        }
        if(!$layout_id){
            return $this->error('请选择户型');
        }
        $houselayoutpics=model('Houselayoutpics')
            ->alias('p')
            ->field(['p.id','community_id','layout_id','remark','picture','p.status','p.deleted_at','l.name as l_name'])
            ->join('layout l','l.id=p.layout_id','left')
            ->where('community_id',$community_id)
            ->where('layout_id',$layout_id)
            ->where('p.status',1)
            ->select();

        $data=$houselayoutpics?$houselayoutpics:[];
        return $this->success('获取成功','',$data);
    }

    /* ========== 接口分组公共参数 ========== */
    public function params(){
        $parent_id=input('parentid');
        if(empty($parent_id)){
            return $this->error('请选择接口分组');
        }
        $parent_params=model('Apis')->where('id',$parent_id)->where('status',1)->value('params');
        $params=json_decode($parent_params,true);
        if(empty($params)){
            return $this->error('没有设置公共参数');
        }else{
            return $this->success('获取成功','',$params);
        }
    }

    /* ========== 生成GUID ========== */
    public function getguid(){
        return $this->success('获取成功','',create_guid());
    }

    /* ========== 查询房源 ========== */
    public function findhouse(){
        /* ********** 查询条件 ********** */
        $where=[];
        $field=['h.id','h.community_id','h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.has_lift','h.is_real',
            'h.is_buy','h.is_public','h.is_transit','h.deliver_at','h.status','c.address','c.name as c_name','l.name as l_name'];
        $inputs=input();

        /* ++++++++++ 小区 ++++++++++ */
        $community_ids=isset($inputs['community_ids'])?$inputs['community_ids']:[];
        if($community_ids){
            $where['community_id']=['in',$community_ids];
        }
        /* ++++++++++ 户型 ++++++++++ */
        $layout_ids=isset($inputs['layout_ids'])?$inputs['layout_ids']:[];
        if($layout_ids){
            $where['layout_id']=['in',$layout_ids];
        }
        /* ++++++++++ 面积 ++++++++++ */
        $area_start=input('area_start');
        if($area_start){
            $where['area']=['>=',$area_start];
        }
        $area_end=input('area_end');
        if($area_end){
            $where['area']=['<=',$area_end];
        }
        /* ++++++++++ 期房、现房 ++++++++++ */
        $is_real=input('is_real');
        if(is_numeric($is_real) && in_array($is_real,[0,1])){
            $where['is_real']=$is_real;
        }
        /* ++++++++++ 是否购置房 ++++++++++ */
        $is_buy=input('is_buy');
        if(is_numeric($is_buy) && in_array($is_buy,[0,1])){
            $where['is_buy']=$is_buy;
        }
        /* ++++++++++ 是否过渡房 ++++++++++ */
        $is_transit=input('is_transit');
        if(is_numeric($is_transit) && in_array($is_transit,[0,1])){
            $where['is_transit']=$is_transit;
        }
        /* ++++++++++ 是否共用 ++++++++++ */
        $is_public=input('is_public');
        if(is_numeric($is_public) && in_array($is_public,[0,1])){
            $where['is_public']=$is_public;
        }
        /* ++++++++++ 状态 ++++++++++ */
        $status=input('status');
        if(is_numeric($status) && in_array($status,[0,1,2,3])){
            $where['house.status']=$status;
        }
        /* ++++++++++ 查询 ++++++++++ */
        $houses=model('Houses')
            ->alias('h')
            ->field($field)
            ->join('house_community c','c.id=h.community_id','left')
            ->join('layout l','l.id=h.layout_id','left')
            ->where($where)
            ->select();

        if($houses){
            return $this->success('获取成功','',$houses);
        }else{
            return $this->error('没有数据','');
        }
    }


    /* ========== 评估公司 ========== */
    public function company(){
        $type=input('type');
        if(!is_numeric($type) || !in_array($type,[0,1])){
            return $this->error('错误操作','');
        }
        $companys=Companys::field(['id','name','type','status','sort'])->where('type',$type)->where('status',1)->order('sort asc')->select();
        if($companys){
            return $this->success('获取成功','',$companys);
        }else{
            return $this->error('没有数据','');
        }
    }

    /* ========== 摸底被征户 ========== */
    public function collection(){
        $item_id=input('item_id');
        if(!is_numeric($item_id) || $item_id<1){
            return $this->error('请先选择项目','');
        }
        $type=input('type');
        if(input('pay')){
            $where['collection.real_use']=['neq',3];
        }else{
            if(!is_numeric($type) || !in_array($type,[0,1])){
                return $this->error('请先选择评估公司类型','');
            }
        }

        if($type){
            $where['collection.has_assets']=$type;
        }
        $where['item_id']=$item_id;
        $where['collection.status']=1;

        $field=['c.id','c.item_id','c.community_id','c.building','c.unit','c.floor','c.number','c.has_assets','c.status','cc.address','cc.name as cc_name'];

        /* ++++++++++ 片区 ++++++++++ */
        $community_id=input('community_id');
        if(is_numeric($community_id)){
            $where['community_id']=$community_id;
        }
        /* ++++++++++ 几栋 ++++++++++ */
        $building=input('building');
        if(is_numeric($building)){
            $where['building']=$building;
        }
        /* ++++++++++ 几单元 ++++++++++ */
        $unit=input('unit');
        if(is_numeric($unit)){
            $where['unit']=$unit;
        }
        /* ++++++++++ 几楼 ++++++++++ */
        $floor=input('floor');
        if(is_numeric($floor)){
            $where['floor']=$floor;
        }
        /* ++++++++++ 几号 ++++++++++ */
        $number=input('number');
        if(is_numeric($number)){
            $where['number']=$number;
        }

        $collections=Collections::alias('c')
            ->field($field)
            ->join('collection_community cc','cc.id=c.community_id','left')
            ->where($where)
            ->select();

        if($collections){
            return $this->success('获取成功','',$collections);
        }else{
            return $this->error('没有数据','');
        }
    }

    /* ========== 查询摸底被征户(入户评估-房产评估-添加) ========== */
    public function collections(){
        $item_id=input('item_id');
        if(!is_numeric($item_id) || $item_id<1){
            return $this->error('请先选择项目','');
        }
        $where['item_id']=$item_id;
        $where['c.status']=1;
        $field=['c.id','c.item_id','c.community_id','c.building','c.unit','c.floor','c.number','c.has_assets','c.status','cc.address','cc.name as cc_name'];

        /* ++++++++++ 片区 ++++++++++ */
        $community_id=input('community_id');
        if(!is_numeric($community_id) || $community_id<1){
            return $this->error('请先选择片区','');
        }
        $where['community_id']=$community_id;
        $collections=Collections::alias('c')
            ->field($field)
            ->join('collection_community cc','cc.id=c.community_id','left')
            ->where($where)
            ->select();

        if($collections){
            return $this->success('获取成功','',$collections);
        }else{
            return $this->error('没有数据','');
        }
    }

    /* ========== 查询项目评估公司 ========== */
    public function item_company(){
        $item_id=input('item_id');
        $type=input('type');
        if(!is_numeric($item_id) || $item_id<1){
            return $this->error('请先选择项目','');
        }
        $where['i.item_id']=$item_id;
        $where['c.type']=$type;
        $field=['i.id','i.company_id','c.name as company_name'];

        $itemcompanys=Itemcompanys::alias('i')
            ->field($field)
            ->join('company c','c.id=i.company_id','left')
            ->where($where)
            ->select();

        if($itemcompanys){
            return $this->success('获取成功','',$itemcompanys);
        }else{
            return $this->error('没有数据','');
        }
    }

    /* ========== 查询项目评估公司-->评估师 ========== */
    public function item_company_valuer(){
        $field=['id','name','register_num','valid_at'];
        $ids = input('ids');
        if($ids){
            $where['id'] = array('in',$ids);
            $company_valuer=Companyvaluers::field($field)
                ->where($where)
                ->select();
        }else{
            $company_id=input('company_id');
            if(!is_numeric($company_id) || $company_id<1){
                return $this->error('请先选择评估公司','');
            }
            $where['company_id']=$company_id;
            $where['status']='1';

            $company_valuer=Companyvaluers::field($field)
                ->where($where)
                ->select();
        }


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
        $where['collection_id']=$collection_id;
        $field=['cb.id','cb.item_id','cb.community_id','cb.collection_id','cb.building','cb.unit','cb.floor','cb.number',
            'cb.real_num','cb.real_unit','cb.use_id','cb.struct_id','cb.status_id','cb.build_year','cb.remark','cb.deleted_at','i.name as i_name','i.is_top',
            'cc.address','cc.name as cc_name','c.building as c_building','c.unit as c_unit','c.floor as c_floor','c.number as c_number',
            'bu.name as bu_name','bs.name as bs_name','s.name as s_name'];

        $collectionbuildings=Collectionbuildings::alias('cb')
            ->field($field)
            ->join('item i','i.id=cb.item_id','left')
            ->join('collection_community cc','cc.id=cb.community_id','left')
            ->join('collection c','c.id=cb.collection_id','left')
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
            return $this->error('没有数据','');
        }
    }

    /* ========== 入户摸底-产权人或承租人 ========== */
    public function collection_holder(){
        $collection_id=input('collection_id');
        if(!is_numeric($collection_id) || $collection_id<1){
            return $this->error('请先选择权属','');
        }
        $collection=Collections::field(['id','type','status'])->where('id',$collection_id)->find();
        if(!$collection){
            return $this->error('选择权属不存在','');
        }
        if(!$collection->getData('status')){
            return $this->error('选择权属已禁用','');
        }

        $where['collection_id']=$collection_id;
        if($collection->getData('type')){
            $where['holder']=2;
        }else{
            $where['holder']=1;
        }
        $holders=Collectionholders::field(['id','name','address','phone'])->where($where)->select();
        if($holders){
            return $this->success('获取成功','',$holders);
        }else{
            return $this->error('没有数据','');
        }
    }

    /* ========== 查询成员 ========== */
    public function sear_collection_holder(){
        $collection_id = input('collection_id');
        if(!$collection_id){
            return $this->error('请选择权属','');
        }
        $holder_id = input('holder_id');
        if (!$holder_id){
            $type = model('Collections')->where('id',$collection_id)->value('type');
            if($type == 0){
                $where['holder'] = 1;
            }else{
                $where['holder'] = 2;
            }
            $where['collection_id'] = $collection_id;
            $holder_info = model('Collectionholders')
                ->field(['id','name'])
                ->where($where)
                ->select();
        }else{
            $type = model('Collections')->where('id',$collection_id)->value('type');
            if($type == 0){
                $where['holder'] = array('in','0,1');
            }else{
                $where['holder'] = array('in','0,1,2');
            }
            $where['collection_id'] = $collection_id;
            $holder_info = model('Collectionholders')
                ->field(['id','name'])
                ->where($where)
                ->select();
            foreach ($holder_info as $k=>$v){
              if ($v['id']==$holder_id){
                  unset($holder_info[$k]);
               }
            }
        }


        if($holder_info){
            return $this->success('获取成功','',$holder_info);
        }else{
            return $this->error('没有数据','');
        }
    }

    /* ========== 查询风险评估 ========== */
    public function sear_risk(){
        $item_id = input('item_id');
        $community_id = input('community_id');
        $collection_id = input('collection_id');
        if(!$item_id){
            $this->error('请选择项目','');
        }
        if(!$community_id){
            $this->error('请选择片区','');
        }
        if(!$collection_id){
            $this->error('请选择权属','');
        }
        $field = ['ass.*', 'i.name as item_name', 'cc.name as pq_name', 'c.building as c_building',
            'c.unit as c_unit', 'c.floor as c_floor', 'c.number as c_number', 'c.id as c_id','ch.name as holder_name','ch.id as holder_name_id','chr.name as recommemd_holder_name','chr.id as recommemd_holder_name_id'];
        $risk_list = model('Risks')
            ->alias('ass')
            ->field($field)
            ->join('item i', 'i.id=ass.item_id', 'left')
            ->join('collection_community cc', 'cc.id=ass.community_id', 'left')
            ->join('collection c', 'c.id=ass.collection_id', 'left')
            ->join('collection_holder ch', 'ch.id=ass.holder_id', 'left')
            ->join('collection_holder chr', 'chr.id=ass.recommemd_holder_id', 'left')
            ->where('ass.item_id',$item_id)
            ->where('ass.community_id',$community_id)
            ->where('ass.collection_id',$collection_id)
            ->select();
        if($risk_list){
            return $this->success('获取成功','',$risk_list);
        }else{
            return $this->error('没有数据','');
        }
    }

    /* ========== 查询项目话题 ========== */
    public function sear_item_topic(){
        $item_id = input('item_id');
        if(!$item_id){
            return $this->error('请选择项目','');
        }
       $item_topic_list = model('Itemtopics')
           ->alias('ips')
           ->field(['ips.*','t.id as topic_name_id','t.name as topic_name'])
           ->join('topic t','t.id = ips.topic_id','left')
           ->where('ips.item_id',$item_id)
           ->select();
        if($item_topic_list){
            return $this->success('获取成功','',$item_topic_list);
        }else{
            return $this->error('没有数据','');
        }
    }

    /* ========== 查询产权人(承租人)名称 ========== */
    public function sear_holder_name(){
        $item_id = input('item_id');
        $pay_id = input('pay_id');
        $biaoshi = input('bisoshi');
        if($biaoshi){
            if(!$pay_id){
                return $this->error('请选择兑付','');
            }
        }else{
            if(!$item_id){
                return $this->error('请选择项目','');
            }
        }

        if($item_id){
            $holder_name_list = model('Payholders')
                ->alias('p')
                ->field(['p.*','c.name as holder_name'])
                ->join('collection_holder c','c.id = p.collection_holder_id','left')
                ->where('p.item_id',$item_id)
                ->select();
        }else{
            $payholder_ids=model('Houseresettles')->where('pay_id',$pay_id)->column('collection_holder_id');
            $holder_name_list=model('Payholders')
                ->alias('ph')
                ->field(['ph.id','ph.item_id','ph.community_id','ph.collection_id','ph.pay_id','ph.collection_holder_id','ph.holder','ch.name','ch.id as ch_id'])
                ->join('collection_holder ch','ch.id=ph.collection_holder_id','left')
                ->where('ph.pay_id',$pay_id)
                ->where('ph.collection_holder_id','not in',$payholder_ids)
                ->order('ph.portion desc')
                ->select();
        }

        if($holder_name_list){
            return $this->success('获取成功','',$holder_name_list);
        }else{
            return $this->error('没有数据','');
        }
    }

    /* ========== 查询项目房源(冻结安置房) ========== */
    public function sear_item_house(){
        $id = input('id');
        if(!$id){
            return $this->error('至少选中一项');
        }
        /* ********** 查询条件 ********** */
        $field=['ih.*','i.name as i_name','i.status as i_status','i.is_top','h.community_id','c.address','c.name as c_name',
            'h.building','h.unit','h.floor','h.number','h.layout_id','h.area','h.status as house_status','h.is_real','h.is_buy',
            'h.is_transit','h.is_public','l.name as l_name'];


        $itemhouses=model('Itemhouses')
            ->alias('ih')
            ->field($field)
            ->join('item i','ih.item_id=i.id','left')
            ->join('house h','ih.house_id=h.id','left')
            ->join('house_community c','h.community_id=c.id','left')
            ->join('layout l','h.layout_id=l.id','left')
            ->where('ih.id',$id)
            ->find();
        if($itemhouses){
            return $this->success('获取成功','',$itemhouses);
        }else{
            return $this->error('没有数据','');
        }
    }

    /* ========== 查询兑付 ========== */
    public function sear_pay(){
        $id = input('id');
        if(!$id){
            return $this->error('至少选中一项');
        }
        /* ********** 查询条件 ********** */
        $field=['p.*','i.name as i_name','i.is_top','c.id as collection_id','c.community_id','c.building','c.unit','c.floor','c.number','c.type','c.real_use','cc.address','cc.name as cc_name','bu.name as bu_name'];

        $pays=model('Pays')
            ->alias('p')
            ->field($field)
            ->join('item i','i.id=p.item_id','left')
            ->join('collection c','c.id=p.collection_id','left')
            ->join('collection_community cc','cc.id=p.community_id','left')
            ->join('building_use bu','bu.id=c.real_use','left')
            ->where('p.id',$id)
            ->find();
        if($pays){
            return $this->success('获取成功','',$pays);
        }else{
            return $this->error('没有数据','');
        }
    }

    /* ========== 查询安置房源 ========== */
    public function sear_house(){
        $pay_id = input('pay_id');
        $pay_holder_id = input('pay_holder_id');
        if(!$pay_holder_id){
            return $this->error('请选择被征收人');
        }
        /* ++++++++++ 安置房选择 ++++++++++ */
        $where['pyh.pay_id']=$pay_id;
        $where['pyh.pay_holder_id']=$pay_holder_id;
        $where['h.status']=0;
        $house_list=model('Payholderhouses')
            ->alias('pyh')
            ->field(['pyh.collection_id','pyh.sort','pyh.house_id','h.community_id as house_community_id','h.id as h_id','h.building','h.unit',
                'h.floor','h.number','h.layout_id','h.area','h.status as house_status','hc.address','hc.name as hc_name','l.name as l_name'])
            ->join('house h','h.id=pyh.house_id','left')
            ->join('house_community hc','hc.id=h.community_id','left')
            ->join('layout l','l.id=h.layout_id','left')
            ->where($where)
            ->order('pyh.sort asc')
            ->select();
        if($house_list){
            return $this->success('获取成功','',$house_list);
        }else{
            return $this->error('没有数据','');
        }
    }

}