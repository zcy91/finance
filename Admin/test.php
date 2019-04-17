<?php
header("Content-type: text/html; charset=utf-8");
$a1 = [array("id"=>10,"name"=>"车贷a1","pid"=>0),array("id"=>2,"name"=>"车贷a2","pid"=>0)];  //1级
$a2 = [array("id"=>3,"name"=>"车贷b1","pid"=>10),array("id"=>5,"name"=>"车贷b2","pid"=>1)];  //2级
$a3 = [array("id"=>4,"name"=>"车贷c1","pid"=>3),array("id"=>6,"name"=>"车贷c2","pid"=>3)];   //3级
$a4 = [array("id"=>11,"name"=>"车贷c1","pid"=>6),array("id"=>12,"name"=>"车贷c2","pid"=>4)];   //4级
$a5 = [array("id"=>13,"name"=>"车贷c1","pid"=>11),array("id"=>14,"name"=>"车贷c2","pid"=>12)];   //5级

$arr = [];$arr0 = [];$arr1 = [];$arr2 = [];$arr3 = [];
$index = 0;

foreach($a5 as $v5){
    $arr[$v5['pid']][] = $v5;
    $index++;
}

foreach($a4 as $v4){
    if(isset($arr[$v4['id']])){
        $v4['child'] = $arr[$v4['id']];
    }
    $arr0[$v4['pid']][] = $v4;
    $index++;
}


foreach($a3 as $val){
    if(isset($arr0[$val['id']])){
        $val['child'] = $arr0[$val['id']];
    }
   $arr1[$val['pid']][] = $val;
   $index++;
}

foreach($a2 as $v2){
    if(isset($arr1[$v2['id']])){
        $v2['child'] = $arr1[$v2['id']];
    }
    $arr2[$v2['pid']][] = $v2;
    $index++;
}

foreach($a1 as $v3){
    if(isset($arr2[$v3['id']])){
        $v3['child'] = $arr2[$v3['id']];
    }
    $arr3[] = $v3;
    $index++;
}

echo $index++;


echo "<pre>";
print_r($arr3);
echo "</pre>";
exit;
 $w = date("w")+6;
 $begin_date = date("Y")."-01-01 00:00:00"; 
                $end_date = date("Y-m-d H:i:s");//默认为今年
                
                echo $begin_date."<br />".$end_date;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

