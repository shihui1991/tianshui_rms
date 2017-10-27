<?php
/* |------------------------------------------------------
 * | 系统用户 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Users extends Model
{
    use SoftDelete;
    protected $table='user';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;

    public function setNameAttr($value)
    {
        return trim($value);
    }

    public function setPasswordAttr($value)
    {
        return md5($value);
    }

    public function getLoginAtAttr($value){
        return date('Y-m-d H:i:s',$value);
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
        if($input['username']){
            $data['secret_key']=md5($input['username'].time());
        }
        return $data;
    }

    public function login_data(){
        $data['login_at']=time();
        $data['login_ip']=request()->ip();
        return $data;
    }
}