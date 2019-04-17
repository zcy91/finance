<?php
function process_class($cate1 = array() , $cate2 = array() , $cate3 = array() , $cate4 = array() , $cate5 = array()){
    $arr1 = [];
    $arr2 = [];
    $arr3 = [];
    $arr4 = [];
    $arr5 = [];
    if(!empty($cate5)){
        foreach($cate5 as $v5){
            $arr5[$v5['pid']][] = $v5;
        }
    }
    if(!empty($cate4)){
        foreach($cate4 as $v4){
            if(isset($arr5[$v4['id']])){
                $v4['children'] = $arr5[$v4['id']];
            }
            $arr4[$v4['pid']][] = $v4;
        }
    }
    if(!empty($cate3)){
        foreach($cate3 as $v3){
            if(isset($arr4[$v3['id']])){
                $v3['children'] = $arr4[$v3['id']];
            }
            $arr3[$v3['pid']][] = $v3;
        }
    }
    if(!empty($cate2)){
        foreach($cate2 as $v2){
            if(isset($arr3[$v2['id']])){
                $v2['children'] = $arr3[$v2['id']];
            }
            $arr2[$v2['pid']][] = $v2;
        }
    }
    foreach($cate1 as $v1){
        if(isset($arr2[$v1['id']])){
            $v1['children'] = $arr2[$v1['id']];
        }
        $arr1[] = $v1;
    }
    
    return $arr1;
}


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

