<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------


// 应用公共文件
/** 生成树形结构
 * @param array $list       原始数据
 * @param string $str       树形结构样式 如："<option value='\$id' \$selected>\$space \$name</option>"
 * @param int $parent_id    一级项目ID
 * @param int $level        初始层级
 * @param array $icon       树形图标
 * @param string $nbsp      图标空格
 * @return string           树形结构字符串
 */
function get_tree($list=[], $str="<option value='\$id' \$selected>\$space \$name</option>", $parent_id=0, $level=1, $tree_icon=['&nbsp;┃','&nbsp;┣','&nbsp;┗'], $tree_nbsp='&nbsp;'){
    $result='';
    if(empty($list)){
        return $result;
    }
    $array=get_childs($list,$parent_id);
    $childs=$array['childs'];
    $new_list=$array['new_list'];
    $num=count($childs);
    if(empty($childs)){
        return $result;
    }
    $i=1;
    foreach ($childs as $child){
        $child=$child->toArray();
        $space='';
        for($j=1;$j<$level;$j++){
            if($j==1){
                $space .=$tree_nbsp;
            }else{
                $space .=$tree_icon[0].$tree_nbsp;
            }
        }
        if($level!=1){
            if($i==$num){
                $space.=$tree_icon[2];
            }else{
                $space.=$tree_icon[1];
            }
        }
        @extract($child);
        eval("\$nstr = \"$str\";");
        $result .=$nstr;
        $result .=get_tree($new_list,$str,$child['id'],$level+1,$tree_icon,$tree_nbsp);
        $i++;
    }
    return $result;
}


/** 获取集合中的子元素
 * @param array $list       数据集合
 * @param int $parent_id    上级ID
 * @return array            子元素集合
 */
function get_childs($list, $parent_id){
    $array=[];
    foreach ($list as $key=>$value){
        if($value->parent_id==$parent_id){
            $array[]=$value;
            unset($list[$key]);
        }
    }
    return ['childs'=>$array,'new_list'=>$list];
}


/** 批量 更新或插入数据的sql
 * @param string $table         数据表名
 * @param array $insert_columns 数据字段
 * @param array $values         原始数据
 * @param array|string $update_columns 更新字段
 * @return bool|array          返回false(条件不符)，返回array(sql语句)
 */
function batch_update_or_insert_sql($table='', $insert_columns=[], $values=[], $update_columns=[]){
    if(empty($table) || empty($insert_columns) || empty($values) || empty($update_columns)){
        return false;
    }
    // 数据字段必须包含更新字段
    if(is_string($update_columns)){
        if(!in_array($update_columns,$insert_columns)){
            return false;
        }
    }else{
        $common_columns= array_intersect($insert_columns,$update_columns);
        sort($common_columns);
        sort($update_columns);
        if($common_columns != $update_columns){
            return false;
        }
    }

    //数据字段
    $sql_insert_columns=[];
    foreach ($insert_columns as $insert_column){
        $sql_insert_columns[]='`'.$insert_column.'`';
    }
    $sql_insert_columns=implode(',',$sql_insert_columns);
    //数据分页
    $num=100;
    $page_values=[];
    foreach ($values as $k=>$value){
        $p=ceil(($k+1)/$num);
        $temp_values=[];
        foreach ($insert_columns as $insert_column){
            $temp=(string)$value[$insert_column] or '';
            $temp_values[]="'".$temp."'";
        }
        $temp_values=implode(',',$temp_values);
        $page_values[$p][]='('.$temp_values.')';
    }
    //更新字段
    if(is_string($update_columns)){
        $sql_update_columns= ' `'.$update_columns.'` = values(`'.$update_columns.'`) ';
    }else{
        $sql_update_columns=[];
        foreach ($update_columns as $update_column){
            $sql_update_columns[]= ' `'.$update_column.'` = values(`'.$update_column.'`) ';
        }
        $sql_update_columns=implode(',',$sql_update_columns);
    }
    // 生成sql
    $sqls=[];
    foreach($page_values as $p=>$value){
        $sql_values=implode(',',$value);
        $sqls[]='insert into `'.$table.'` ('.$sql_insert_columns.') values '.$sql_values.' on duplicate key update '.$sql_update_columns;
    }

    return $sqls;
}

/** 批量更新数据 sql
 * @param string $table         数据表名
 * @param array $insert_columns 数据字段
 * @param array $values         原始数据
 * @param array|string $update_columns  更新字段
 * @param array|string $where_columns   条件字段
 * @return bool|string          返回false(条件不符)，返回string(sql语句)
 */
function batch_update_sql($table='', $insert_columns=[], $values=[], $update_columns=[], $where_columns='id'){
    if(empty($table) || empty($insert_columns) || empty($values) || empty($update_columns) || empty($where_columns)){
        return false;
    }
    // 数据字段必须包含更新字段
    if(is_string($update_columns)){
        if(!in_array($update_columns,$insert_columns)){
            return false;
        }
    }else{
        $common_columns= array_intersect($insert_columns,$update_columns);
        sort($common_columns);
        sort($update_columns);
        if($common_columns != $update_columns){
            return false;
        }
    }
    // 数据字段必须包含条件字段
    if(is_string($where_columns)){
        if(!in_array($where_columns,$insert_columns)){
            return false;
        }
    }else{
        $common_columns= array_intersect($insert_columns,$where_columns);
        sort($common_columns);
        sort($where_columns);
        if($common_columns != $where_columns){
            return false;
        }
    }

    /* ++++++++++ 创建虚拟表 ++++++++++ */
    //创建虚拟表 表名
    $temp_table='`'.$table.'_temp`';
    //创建虚拟表 sql
    $sqls[]='create temporary table '.$temp_table.' like `'.$table.'`';

    /* ++++++++++ 添加数据 ++++++++++ */
    //数据字段
    $sql_insert_columns=[];
    foreach ($insert_columns as $insert_column){
        $sql_insert_columns[]='`'.$insert_column.'`';
    }
    $sql_insert_columns=implode(',',$sql_insert_columns);
    //数据分页
    $num=100;
    $page_values=[];
    foreach ($values as $k=>$value){
        $p=ceil(($k+1)/$num);
        $temp_values=[];
        foreach ($insert_columns as $insert_column){
            $temp=(string)$value[$insert_column] or '';
            $temp_values[]="'".$temp."'";
        }
        $temp_values=implode(',',$temp_values);
        $page_values[$p][]='('.$temp_values.')';
    }

    //插入数据 sql
    foreach($page_values as $p=>$value){
        $sql_values=implode(',',$value);
        $sqls[]='insert into '.$temp_table.' ('.$sql_insert_columns.') values '.$sql_values;
    }


    /* ++++++++++ 批量更新 ++++++++++ */
    //更新字段
    if(is_string($update_columns)){
        $sql_update_columns= '`'.$table.'`.`'.$update_columns.'`='.$temp_table.'.`'.$update_columns.'`';
    }else{
        $sql_update_columns=[];
        foreach ($update_columns as $update_column){
            $sql_update_columns[]= '`'.$table.'`.`'.$update_column.'`='.$temp_table.'.`'.$update_column.'`';
        }
        $sql_update_columns=implode(',',$sql_update_columns);
    }
    //条件字段
    if(is_string($where_columns)){
        $sql_where_columns= '`'.$table.'`.`'.$where_columns.'`='.$temp_table.'.`'.$where_columns.'`';
    }else{
        $sql_where_columns=[];
        foreach ($where_columns as $where_column){
            $sql_where_columns[]= '`'.$table.'`.`'.$where_column.'`='.$temp_table.'.`'.$where_column.'`';
        }
        $sql_where_columns=implode(' and ',$sql_where_columns);
    }
    //更新数据 sql
    $sqls[]='update `'.$table.'`,'.$temp_table.' set '.$sql_update_columns.' where '.$sql_where_columns;

    return $sqls;
}


/** 生成导航菜单树
 * @param array $menus      菜单数据
 * @param int $current_id   当前菜单ID
 * @param int $level        菜单层级
 * @param int $parent_id    上级菜单ID
 * @return string           导航菜单树 ul>li
 */
function get_nav_li_list($menus, $level=1, $parent_id=0){
    if($level>2){
        return '';
    }
    $str='';
    $ul_class='';
    $list=get_childs($menus,$parent_id);
    $childs=$list['childs'];
    $new_list=$list['new_list'];
    if(count($childs)){
        foreach($childs as $child){
            $child=$child->toArray();
            if($level==1){
                $onclick='onclick="leftNavToggle(this)"';
                $div='<div class="link">'.$child['icon'].$child['name'].'<i></i></div>';
                /* ul标签class */
                $ul_class='leftNav_1 ov f12';
            }else{
                $onclick='onclick="leftSubNavManage(this,event)"';
                $div='<div class="link">'.$child['icon'].$child['name'].'</div>';
                $ul_class='leftNav_2 bg_f';
            }

            $data_src='data-src="'.$child['url'].'" data-tit="'.$child['name'].'"';

            $str_childs='';
            if(count($new_list)){
                $str_childs=get_nav_li_list($new_list,$level+1,$child['id']);
            }
            $str .= '<li '.$onclick.$data_src.'>'.$div.$str_childs.'</li>';
        }
        /* ul标签class */
        $str ='<ul class="'.$ul_class.'">'.$str.'</ul>';
    }
    return $str;
}


/** 生成导航菜单树 手机版
 * @param array $menus      菜单数据
 * @param int $current_id   当前菜单ID
 * @param int $level        菜单层级
 * @param int $parent_id    上级菜单ID
 * @return string           导航菜单树 ul>li
 */
function get_na_li_list_mobile($menus, $level=1, $parent_id=0,$current_id,$parent_ids){
    if($level>2){
        return '';
    }
    $str='';
    $ul_class='';
    $list=get_childs($menus,$parent_id);
    $childs=$list['childs'];
    $new_list=$list['new_list'];
    if(count($childs)){
        foreach($childs as $child){
            $open='';
            $open_li='';
            $child=$child->toArray();
            if($level==1){
                if(in_array($child['id'],$parent_ids)){
                    $open=' open ';
                }
                $content='<div class="link '.$open.'">'.$child['icon'].' '.$child['name'].'<i class="iconfont icon-arrow"></i></div>';
            }else{
                if($child['id']==$current_id || in_array($child['id'],$parent_ids)){
                    $open_li=' open_li ';
                }
                $content='<a href="'.$child['url'].'">'.$child['icon'].' '.$child['name'].'</a>';
            }

            $str .= '<li class="'.$open_li.'">'.$content;

            $str_childs=get_na_li_list_mobile($new_list,$level+1,$child['id'],$current_id,$parent_ids);
            $str .= $str_childs.'</li>';
        }
        if($level==2){
            $ul_class=' two ';
            if(in_array($parent_id,$parent_ids)){
                $ul_class .=' open ';
            }else{
                $ul_class .=' hide ';
            }
        }

        /* ul标签class */
        $str ='<ul class="'.$ul_class.'">'.$str.'</ul>';
    }
    return $str;
}



/** cURL函数简单封装
 * @param $url
 * @param null $data
 * @return mixed
 */
function https_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}


/** 排序
 * @param $a
 * @param $b
 * @return int
 */
function cmp_func($a, $b) {
    global $order;
    if ($a['is_dir'] && !$b['is_dir']) {
        return -1;
    } else if (!$a['is_dir'] && $b['is_dir']) {
        return 1;
    } else {
        if ($order == 'size') {
            if ($a['filesize'] > $b['filesize']) {
                return 1;
            } else if ($a['filesize'] < $b['filesize']) {
                return -1;
            } else {
                return 0;
            }
        } else if ($order == 'type') {
            return strcmp($a['filetype'], $b['filetype']);
        } else {
            return strcmp($a['filename'], $b['filename']);
        }
    }
}


/** 生成GUID
 * @return string
 */
function create_guid(){
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45);// "-"
    $guid = substr($charid, 6, 2).substr($charid, 4, 2).
        substr($charid, 2, 2).substr($charid, 0, 2).$hyphen
        .substr($charid, 10, 2).substr($charid, 8, 2).$hyphen
        .substr($charid,14, 2).substr($charid,12, 2).$hyphen
        .substr($charid,16, 4).$hyphen.substr($charid,20,12);
    return $guid;
}

/**
 * 数组转xls格式的excel文件
 * @param  array  $data      需要生成excel文件的数组
 * @param  string $filename  生成的excel文件名
 *      示例数据：
$data = array(
array(NULL, 2010, 2011, 2012),
array('Q1',   12,   15,   21),
array('Q2',   56,   73,   86),
array('Q3',   52,   61,   69),
array('Q4',   30,   32,    0),
);
 */
function create_xls($cd1,$cd2,$cd3,$cd4,$ColumnDimension1,$ColumnDimension2,$ColumnDimension3,$data,$filename='simple.xls'){

    ini_set('max_execution_time', '0');
    vendor("PHPExcels.PHPExcel");
    $filename=str_replace('.xls', '', $filename).'.xls';
    $filename = iconv("utf-8", "gb2312", $filename);
    $phpexcel = new \PHPExcel();
    $phpexcel->getProperties()
        ->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");
    // 合并单元格
    $phpexcel->getActiveSheet()->mergeCells('A1:A2');
    $phpexcel->getActiveSheet()->mergeCells('B1:B2');
    $phpexcel->getActiveSheet()->mergeCells($cd1.'1:'.$cd1.'2');
    $phpexcel->getActiveSheet()->mergeCells($cd2.'1:'.$cd2.'2');
    $phpexcel->getActiveSheet()->mergeCells($cd3.'1:'.$cd3.'2');
    $phpexcel->getActiveSheet()->mergeCells($cd4.'1:'.$cd4.'2');
    $phpexcel->getActiveSheet()->mergeCells('C1:'.$ColumnDimension1.'1');
    $phpexcel->getActiveSheet()->mergeCells($ColumnDimension2.'1:'.$ColumnDimension3.'1');
    // 设置个表格宽度
    $phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
    // 标题位置调整
    $phpexcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->getActiveSheet()->getStyle('B1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('B1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->getActiveSheet()->getStyle($cd1.'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle($cd1.'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->getActiveSheet()->getStyle($cd2.'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle($cd2.'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->getActiveSheet()->getStyle($cd3.'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle($cd3.'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->getActiveSheet()->getStyle($cd4.'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle($cd4.'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $phpexcel->setActiveSheetIndex(0)->getStyle('C1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle($ColumnDimension2.'1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // 水平居中（位置很重要，建议在最初始位置）
    $phpexcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('J')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('K')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('L')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('M')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//    设置单元格的值
//    $phpexcel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
//    $phpexcel->getActiveSheet()->getStyle('J')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

    $phpexcel->getActiveSheet()->fromArray($data);
    $phpexcel->getActiveSheet()->setTitle('Sheet1');
    $phpexcel->setActiveSheetIndex(0);
    ob_end_clean();
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=$filename");
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
    $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
    $objwriter->save('php://output');
    exit;
}
function create_houseresettle_xls($data,$filename='simple.xls'){

    ini_set('max_execution_time', '0');
    vendor("PHPExcels.PHPExcel");
    $filename=str_replace('.xls', '', $filename).'.xls';
    $filename = iconv("utf-8", "gb2312", $filename);
    $phpexcel = new \PHPExcel();
    $phpexcel->getProperties()
        ->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");

    // 设置个表格宽度
    $phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
  // 水平居中（位置很重要，建议在最初始位置）
    $phpexcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('J')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('K')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('L')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('M')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//    设置单元格的值
//    $phpexcel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
//    $phpexcel->getActiveSheet()->getStyle('J')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

    $phpexcel->getActiveSheet()->fromArray($data);
    $phpexcel->getActiveSheet()->setTitle('Sheet1');
    $phpexcel->setActiveSheetIndex(0);
    ob_end_clean();
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=$filename");
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
    $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
    $objwriter->save('php://output');
    exit;
}
function create_housetransit_xls($data,$filename='simple.xls'){

    ini_set('max_execution_time', '0');
    vendor("PHPExcels.PHPExcel");
    $filename=str_replace('.xls', '', $filename).'.xls';
    $filename = iconv("utf-8", "gb2312", $filename);
    $phpexcel = new \PHPExcel();
    $phpexcel->getProperties()
        ->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");

    // 设置个表格宽度
    $phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
    // 水平居中（位置很重要，建议在最初始位置）
    $phpexcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('J')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('K')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('L')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('M')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//    设置单元格的值
//    $phpexcel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
//    $phpexcel->getActiveSheet()->getStyle('J')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

    $phpexcel->getActiveSheet()->fromArray($data);
    $phpexcel->getActiveSheet()->setTitle('Sheet1');
    $phpexcel->setActiveSheetIndex(0);
    ob_end_clean();
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=$filename");
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
    $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
    $objwriter->save('php://output');
    exit;
}
function create_housemanagefee_xls($data,$filename='simple.xls'){

    ini_set('max_execution_time', '0');
    vendor("PHPExcels.PHPExcel");
    $filename=str_replace('.xls', '', $filename).'.xls';
    $filename = iconv("utf-8", "gb2312", $filename);
    $phpexcel = new \PHPExcel();
    $phpexcel->getProperties()
        ->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");

    // 设置个表格宽度
    $phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
    $phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(18);
    $phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
    $phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(18);

    // 水平居中（位置很重要，建议在最初始位置）
    $phpexcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    //    设置单元格的值
    $phpexcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
    $phpexcel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
    $phpexcel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
    $phpexcel->getActiveSheet()->getStyle('H')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);

    $phpexcel->getActiveSheet()->fromArray($data);
    $phpexcel->getActiveSheet()->setTitle('Sheet1');
    $phpexcel->setActiveSheetIndex(0);
    ob_end_clean();
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=$filename");
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
    $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
    $objwriter->save('php://output');
    exit;
}
function create_house_xls($data,$filename='simple.xls'){

    ini_set('max_execution_time', '0');
    vendor("PHPExcels.PHPExcel");
    $filename=str_replace('.xls', '', $filename).'.xls';
    $filename = iconv("utf-8", "gb2312", $filename);
    $phpexcel = new \PHPExcel();
    $phpexcel->getProperties()
        ->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");

    // 设置个表格宽度
    $phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(16);
    $phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
    $phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
    $phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(16);
    $phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
    $phpexcel->getActiveSheet()->getColumnDimension('J')->setWidth(16);

    // 水平居中（位置很重要，建议在最初始位置）
    $phpexcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('J')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//    设置单元格的值
    $phpexcel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
    $phpexcel->getActiveSheet()->fromArray($data);
    $phpexcel->getActiveSheet()->setTitle('Sheet1');
    $phpexcel->setActiveSheetIndex(0);
    ob_end_clean();
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=$filename");
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
    $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
    $objwriter->save('php://output');
    exit;
}
function create_houses_xls($data,$filename='simple.xls'){

    ini_set('max_execution_time', '0');
    vendor("PHPExcels.PHPExcel");
    $filename=str_replace('.xls', '', $filename).'.xls';
    $filename = iconv("utf-8", "gb2312", $filename);
    $phpexcel = new \PHPExcel();
    $phpexcel->getProperties()
        ->setCreator("Maarten Balliauw")
        ->setLastModifiedBy("Maarten Balliauw")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");

    // 设置个表格宽度
    $phpexcel->getActiveSheet()->getColumnDimension('A')->setWidth(16);
    $phpexcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('C')->setWidth(8);
    $phpexcel->getActiveSheet()->getColumnDimension('D')->setWidth(8);
    $phpexcel->getActiveSheet()->getColumnDimension('E')->setWidth(8);
    $phpexcel->getActiveSheet()->getColumnDimension('F')->setWidth(8);
    $phpexcel->getActiveSheet()->getColumnDimension('G')->setWidth(18);
    $phpexcel->getActiveSheet()->getColumnDimension('H')->setWidth(16);
    $phpexcel->getActiveSheet()->getColumnDimension('I')->setWidth(16);
    $phpexcel->getActiveSheet()->getColumnDimension('J')->setWidth(16);
    $phpexcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('L')->setWidth(24);
    $phpexcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
    $phpexcel->getActiveSheet()->getColumnDimension('N')->setWidth(46);
    $phpexcel->getActiveSheet()->getColumnDimension('O')->setWidth(40);
    $phpexcel->getActiveSheet()->getColumnDimension('P')->setWidth(40);
    $phpexcel->getActiveSheet()->getColumnDimension('Q')->setWidth(40);

    // 水平居中（位置很重要，建议在最初始位置）
    $phpexcel->setActiveSheetIndex(0)->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('G')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('H')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('I')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('J')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('K')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('L')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('M')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('N')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('O')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('P')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $phpexcel->setActiveSheetIndex(0)->getStyle('Q')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    //    设置单元格的值
    $phpexcel->getActiveSheet()->getStyle('A')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('C')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('F')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('G')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('H')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('I')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('J')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('K')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('L')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('M')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('N')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('O')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('P')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    $phpexcel->getActiveSheet()->getStyle('Q')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

    $phpexcel->getActiveSheet()->fromArray($data);
    $phpexcel->getActiveSheet()->setTitle('Sheet1');
    $phpexcel->setActiveSheetIndex(0);
    ob_end_clean();
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment;filename=$filename");
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header ('Pragma: public'); // HTTP/1.0
    $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
    $objwriter->save('php://output');
    exit;
}
/**
 * 导入excel文件
 * @param  string $file excel文件路径
 * @return array        excel文件内容数组
 * data_count     总条数
 * success_count  成功条数
 * error_count    失败条数
 * add_count      可添加条数
 * add_datas      可添加数组
 */
function import_excel($file){
    // 判断文件是什么格式
    $type = pathinfo($file);
    $type = strtolower($type["extension"]);
    $type=$type==='csv' ? $type : 'Excel5';
    ini_set('max_execution_time', '0');
    vendor("PHPExcels.PHPExcel");
    vendor("PHPExcels.PHPExcel.IOFactory");
    // 判断使用哪种格式
    $objReader = PHPExcel_IOFactory::createReader($type);
    $objPHPExcel = $objReader->load($file,$encode='utf-8');
    $sheet = $objPHPExcel->getSheet(0);
    // 取得总行数
    $highestRow = $sheet->getHighestRow();
    // 取得总列数
    $highestColumn = $sheet->getHighestColumn();
    //循环读取excel文件,读取一条,插入一条
    $data=array();
    /* 获取表头数组*/
        $title = [
          'name'=>'小区',
          'address'=>'小区地址',
          'building'=>'栋',
          'unit'=>'单元',
          'floor'=>'楼',
          'number'=>'号',
          'deliver_at'=>'交付时间',
          'layout_name'=>'户型',
          'remark'=>'户型标识',
          'area'=>'面积(㎡)',
          'has_lift'=>'有无电梯',
          'is_real'=>'是否现房',
          'is_buy'=>'是否购置房',
          'is_transit'=>'是否可过渡',
          'is_public'=>'是否专用',
          'manage_price'=>'物业管理费单价(元/平米/月)',
          'public_price'=>'公摊费单价(元/月)',
        ];


    /*数据拼装*/
    $keys_array = [];
    //从第二行开始读取数据
    for($j=2;$j<=$highestRow;$j++){
        //从A列读取数据
        for($k='A';$k<=$highestColumn;$k++){
            // 读取单元格
            $vals =$objPHPExcel->getActiveSheet()->getCell($k.'1')->getValue();
            $keys=array_search($vals,$title);
            $cell = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
            // 转字符型
            if($cell instanceof PHPExcel_RichText){
                $cell = $cell->__toString();
            }
            $data[$j][$keys]=$cell;
        }

        // 数据验证及查询
        /*小区ID*/
        if(isset($data[$j]['name'])&&isset($data[$j]['address'])){
            $community_id = db('house_community')
                ->where('name',$data[$j]['name'])
                ->where('address',$data[$j]['address'])
                ->value('id');
            if(isset($community_id)){
                $data[$j]['community_id']=$community_id;
            }else{
                $keys_array[] = $j;
                continue;
            }
        }
        /*户型ID 户型图ID*/
        if(isset($data[$j]['layout_name'])&&isset($data[$j]['remark'])){
            $layout_id = db('layout')
                ->where('name',$data[$j]['layout_name'])
                ->value('id');
            $layout_pic_id =db('house_layout_pic')
                ->where('layout_id',$layout_id)
                ->where('remark',$data[$j]['remark'])
                ->value('id');
            if(isset($layout_id)&&isset($layout_pic_id)){
                $data[$j]['layout_id']=$layout_id;
                $data[$j]['layout_pic_id']=$layout_pic_id;
            }else{
                $keys_array[] = $j;
                continue;
            }
        }
        /*交付时间*/
        if(isset($data[$j]['deliver_at'])){
            $deliver_at =  strtotime($data[$j]['deliver_at']);
            if($deliver_at==false){
                $keys_array[] = $j;
                continue;
            }
        }
        /*栋*/
        if(isset($data[$j]['building'])&&!is_numeric($data[$j]['building'])){
                $keys_array[] = $j;
                continue;
        }
        /*单元*/
        if(isset($data[$j]['unit'])&&!is_numeric($data[$j]['unit'])){
            $keys_array[] = $j;
            continue;
        }
        /*楼*/
        if(isset($data[$j]['floor'])&&!is_numeric($data[$j]['floor'])){
            $keys_array[] = $j;
            continue;
        }
        /*号*/
        if(isset($data[$j]['number'])&&!is_numeric($data[$j]['number'])){
            $keys_array[] = $j;
            continue;
        }
        /*面积*/
        if(isset($data[$j]['area'])&&!is_numeric($data[$j]['area'])){
            $keys_array[] = $j;
            continue;
        }

        /*有无电梯*/
        if(isset($data[$j]['has_lift'])&&!in_array($data[$j]['is_real'],['0','1'])){
            $keys_array[] = $j;
            continue;
        }
        /*是否现房*/
        if(isset($data[$j]['is_real'])&&!in_array($data[$j]['is_real'],['0','1'])){
            $keys_array[] = $j;
            continue;
        }
        /*是否购置房*/
        if(isset($data[$j]['is_buy'])&&!in_array($data[$j]['is_buy'],['0','1'])){
            $keys_array[] = $j;
            continue;
        }
        /*是否可过渡*/
        if(isset($data[$j]['is_transit'])&&!in_array($data[$j]['is_transit'],['0','1'])){
            $keys_array[] = $j;
            continue;
        }
        /*是否项目专用*/
        if(isset($data[$j]['is_public'])&&!in_array($data[$j]['is_public'],['0','1'])){
            $keys_array[] = $j;
            continue;
        }
        /*物业管理费单价*/
        if(isset($data[$j]['manage_price'])&&!is_numeric($data[$j]['manage_price'])){
            $keys_array[] = $j;
            continue;
        }
        /*公摊费单价*/
        if(isset($data[$j]['public_price'])&&!is_numeric($data[$j]['public_price'])){
            $keys_array[] = $j;
            continue;
        }
        if(empty($data[$j]['deliver_at'])){
            $keys_array[] = $j;
            continue;
        }
        $data[$j]['picture']=[];
        $data[$j]['total_floor']=0;
        unset($data[$j]['name']);
        unset($data[$j]['address']);
        unset($data[$j]['layout_name']);
        unset($data[$j]['remark']);
    }
    /*
     * data_count     总条数
     * success_count  成功条数
     * error_count    失败条数
     * add_count      可添加条数
     * add_datas      可添加数组*/
    $data_count = count($data);
    $error_count = count($keys_array);
    $success_count = $data_count-$error_count;
    // 去除不符合格式的数据
    if($error_count>0){
        foreach ($keys_array as $k=>$v){
            unset($data[$v]['community_id']);
            unset($data[$v]['layout_id']);
            unset($data[$v]['layout_pic_id']);
            unset($data[$v]);
        }
    }
    /*符合格式的数据去重复*/
    $data_unique = [];
    foreach ($data as $k=>$v){
        $data_unique[$k] =  $v['community_id'].'|'.$v['building'].'|'.$v['unit'].'|'.$v['floor'].'|'.$v['number'];
    }
    /*合格数据键名*/
    $add_keys = array_keys(array_unique($data_unique));
    $add_count = count($add_keys);
    $add_datas = [];
    foreach ($add_keys as $k=>$v){
        $add_datas[] = $data[$v];
    };
    $new_data = [
        'data_count'=>$data_count,
        'success_count'=>$success_count,
        'error_count'=>$error_count,
        'add_count'=>$add_count,
        'add_datas'=>$add_datas
    ];
    return $new_data;
}
/*导出失败数据*/
function create_error_excel($file){
    // 判断文件是什么格式
    $type = pathinfo($file);
    $type = strtolower($type["extension"]);
    $type=$type==='csv' ? $type : 'Excel5';
    ini_set('max_execution_time', '0');
    vendor("PHPExcels.PHPExcel");
    vendor("PHPExcels.PHPExcel.IOFactory");
    // 判断使用哪种格式
    $objReader = PHPExcel_IOFactory::createReader($type);
    $objPHPExcel = $objReader->load($file,$encode='utf-8');
    $sheet = $objPHPExcel->getSheet(0);
    // 取得总行数
    $highestRow = $sheet->getHighestRow();
    // 取得总列数
    $highestColumn = $sheet->getHighestColumn();
    //循环读取excel文件,读取一条,插入一条
    $data=array();
    /* 获取表头数组*/
    $title = [
        'name'=>'小区',
        'address'=>'小区地址',
        'building'=>'栋',
        'unit'=>'单元',
        'floor'=>'楼',
        'number'=>'号',
        'deliver_at'=>'交付时间',
        'layout_name'=>'户型',
        'remark'=>'户型标识',
        'area'=>'面积(㎡)',
        'has_lift'=>'有无电梯',
        'is_real'=>'是否现房',
        'is_buy'=>'是否购置房',
        'is_transit'=>'是否可过渡',
        'is_public'=>'是否专用',
        'manage_price'=>'物业管理费单价(元/平米/月)',
        'public_price'=>'公摊费单价(元/月)',
    ];


    /*数据拼装*/
    $keys_array = [];
    //从第二行开始读取数据
    for($j=2;$j<=$highestRow;$j++){
        //从A列读取数据
        for($k='A';$k<=$highestColumn;$k++){
            // 读取单元格
            $vals =$objPHPExcel->getActiveSheet()->getCell($k.'1')->getValue();
            $keys=array_search($vals,$title);
            $cell = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
            // 转字符型
            if($cell instanceof PHPExcel_RichText){
                $cell = $cell->__toString();
            }
            $data[$j][$keys]=$cell;
        }

        // 数据验证及查询
        /*小区ID*/
        if(isset($data[$j]['name'])&&isset($data[$j]['address'])){
            $community_id = db('house_community')
                ->where('name',$data[$j]['name'])
                ->where('address',$data[$j]['address'])
                ->value('id');
            if(isset($community_id)){
                $data[$j]['community_id']=$community_id;
            }else{
                $keys_array[] = $j;
                continue;
            }
        }
        /*户型ID 户型图ID*/
        if(isset($data[$j]['layout_name'])&&isset($data[$j]['remark'])){
            $layout_id = db('layout')
                ->where('name',$data[$j]['layout_name'])
                ->value('id');
            $layout_pic_id =db('house_layout_pic')
                ->where('layout_id',$layout_id)
                ->where('remark',$data[$j]['remark'])
                ->value('id');
            if(isset($layout_id)&&isset($layout_pic_id)){
                $data[$j]['layout_id']=$layout_id;
                $data[$j]['layout_pic_id']=$layout_pic_id;
            }else{
                $keys_array[] = $j;
                continue;
            }
        }
        /*交付时间*/
        if(isset($data[$j]['deliver_at'])){
            $deliver_at =  strtotime($data[$j]['deliver_at']);
            if($deliver_at==false){
                $keys_array[] = $j;
                continue;
            }
        }

        /*栋*/
        if(isset($data[$j]['building'])&&!is_numeric($data[$j]['building'])){
            $keys_array[] = $j;
            continue;
        }
        /*单元*/
        if(isset($data[$j]['unit'])&&!is_numeric($data[$j]['unit'])){
            $keys_array[] = $j;
            continue;
        }
        /*楼*/
        if(isset($data[$j]['floor'])&&!is_numeric($data[$j]['floor'])){
            $keys_array[] = $j;
            continue;
        }
        /*号*/
        if(isset($data[$j]['number'])&&!is_numeric($data[$j]['number'])){
            $keys_array[] = $j;
            continue;
        }
        /*面积*/
        if(isset($data[$j]['area'])&&!is_numeric($data[$j]['area'])){
            $keys_array[] = $j;
            continue;
        }

        /*有无电梯*/
        if(isset($data[$j]['has_lift'])&&!in_array($data[$j]['is_real'],['0','1'])){
            $keys_array[] = $j;
            continue;
        }
        /*是否现房*/
        if(isset($data[$j]['is_real'])&&!in_array($data[$j]['is_real'],['0','1'])){
            $keys_array[] = $j;
            continue;
        }
        /*是否购置房*/
        if(isset($data[$j]['is_buy'])&&!in_array($data[$j]['is_buy'],['0','1'])){
            $keys_array[] = $j;
            continue;
        }
        /*是否可过渡*/
        if(isset($data[$j]['is_transit'])&&!in_array($data[$j]['is_transit'],['0','1'])){
            $keys_array[] = $j;
            continue;
        }
        /*是否项目专用*/
        if(isset($data[$j]['is_public'])&&!in_array($data[$j]['is_public'],['0','1'])){
            $keys_array[] = $j;
            continue;
        }
        /*物业管理费单价*/
        if(isset($data[$j]['manage_price'])&&!is_numeric($data[$j]['manage_price'])){
            $keys_array[] = $j;
            continue;
        }
        /*公摊费单价*/
        if(isset($data[$j]['public_price'])&&!is_numeric($data[$j]['public_price'])){
            $keys_array[] = $j;
            continue;
        }
        unset($data[$j]['name']);
        unset($data[$j]['address']);
        unset($data[$j]['layout_name']);
        unset($data[$j]['remark']);
    }
    /*
     * error_data     失败数组
  */
    $error_data = [];
    // 获取错误数组
    foreach ($keys_array as $k=>$v){
        unset($data[$v]['community_id']);
        unset($data[$v]['layout_id']);
        unset($data[$v]['layout_pic_id']);
        $error_data[] = $data[$v];
    }

    return $error_data;
}
