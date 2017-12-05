<?php
/**
 * Created by PhpStorm.
 * User: bulian
 * Date: 2017/12/5
 * Time: 16:04
 */

namespace app\company\controller;


class Home extends Base
{
    public function _initialize(){
        parent::_initialize();
    }

    public function index(){
        return view();
    }

    public function console(){
        return view();
    }
}