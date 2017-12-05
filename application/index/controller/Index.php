<?php
namespace app\index\controller;

use app\system\model\Newss;
use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $newss=Newss::with('item')->where('status',1)->order(['is_top'=>'desc','release_at'=>'desc'])->select();

        $this->assign([
            'newss'=>$newss
        ]);
        return view();
    }
}
