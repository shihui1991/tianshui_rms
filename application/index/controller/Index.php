<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        return '<a href="/system">跳转到后台</a><br>演示账号：demo<br>密码：123456';
    }
}
