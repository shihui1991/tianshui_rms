<?php
/* |------------------------------------------------------
 * | 流程控制-流程地址 模型
 * |------------------------------------------------------
 * */
namespace app\system\model;

use think\Model;
use traits\model\SoftDelete;

class Processurls extends Model
{
    use SoftDelete;
    protected $table='process_url';
    protected $pk='id';
    protected $createTime='created_at';
    protected $updateTime='updated_at';
    protected $deleteTime='deleted_at';
    protected $autoWriteTimestamp = true;
    protected $field=true;
    protected $type = [

    ];

}