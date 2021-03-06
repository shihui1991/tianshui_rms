<?php
/* |------------------------------------------------------
 * | 权限与角色 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Roles extends Model
{
    use SoftDelete;
    protected $table='role';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [
        'menu_ids'      =>  'array',
    ];

    public function setNameAttr($value)
    {
        return trim($value);
    }

    public function getIsAdminAttr($key=null){
        $array=[0=>'受约束角色',1=>'超级管理员'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function getStatusAttr($key=null){
        $array=[1=>'启用',0=>'禁用'];
        if(is_numeric($key) && in_array($key,[0,1])){
            return $array[$key];
        }else{
            return $array;
        }
    }

    public function other_data($input){
        $data=[];
        $parent_role=null;
        if($input['parent_id']){
            $parent_role=$this->field(['id','parent_id','level','is_admin','menu_ids'])->find($input['parent_id']);
            $data['level']=$parent_role->getAttr('level')+1;
        }else{
            $data['level']=1;
        }

        /* ++++++++++ 类型 ++++++++++ */
        if(session('userinfo.is_admin') && $input['is_admin']){
            $data['is_admin']=1;
        }else{
            $data['is_admin']=0;
        }

        /* ++++++++++ 权限菜单 ++++++++++ */
        if(session('userinfo.is_admin')){
            if($data['is_admin']){
                $data['menus_ids']=[];
            }else{
                $data['menus_ids']=isset($input['menuids'])?$input['menuids']:[];
            }
        }else{
            $data['menus_ids']=isset($input['menuids'])?array_intersect(session('userinfo.menu_ids'),$input['menuids']) : [];
        }

        return $data;
    }
}