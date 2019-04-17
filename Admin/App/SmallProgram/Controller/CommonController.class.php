<?php
namespace SmallProgram\Controller;
use Think\Controller;
class CommonController extends Controller {
    
    private $AllowAcessUrl = [
        "test.zhenfang123.com",
        "admin.yhjr.com",
        "211.155.230.114:4869"
    ];  //允许访问的域名列表
    
   
    protected function _initialize(){
       
    }
}