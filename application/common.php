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
 * @return bool|string          返回false(条件不符)，返回string(sql语句)
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
    //数据
    $sql_values=[];
    foreach ($values as $value){
        $temp_values=[];
        foreach ($insert_columns as $insert_column){
            $temp=(string)$value[$insert_column] or '';
            $temp_values[]="'".$temp."'";
        }
        $temp_values=implode(',',$temp_values);
        $sql_values[]='('.$temp_values.')';
    }
    $sql_values=implode(',',$sql_values);
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
    $sql='insert into `'.$table.'` ('.$sql_insert_columns.') values '.$sql_values.' on duplicate key update '.$sql_update_columns;
    return $sql;
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
    $sql_create_temp_table='create temporary table '.$temp_table.' like `'.$table.'`';
    /* ++++++++++ 添加数据 ++++++++++ */
    //数据字段
    $sql_insert_columns=[];
    foreach ($insert_columns as $insert_column){
        $sql_insert_columns[]='`'.$insert_column.'`';
    }
    $sql_insert_columns=implode(',',$sql_insert_columns);
    //数据
    $sql_values=[];
    foreach ($values as $value){
        $temp_values=[];
        foreach ($insert_columns as $insert_column){
            $temp=(string)$value[$insert_column] or '';
            $temp_values[]="'".$temp."'";
        }
        $temp_values=implode(',',$temp_values);
        $sql_values[]='('.$temp_values.')';
    }
    $sql_values=implode(',',$sql_values);
    //插入数据 sql
    $sql_insert_values='insert into '.$temp_table.' ('.$sql_insert_columns.') values '.$sql_values;
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
    $sql_update_values='update `'.$table.'`,'.$temp_table.' set '.$sql_update_columns.' where '.$sql_where_columns;
    $sql= [$sql_create_temp_table,$sql_insert_values,$sql_update_values];
    return $sql;
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
