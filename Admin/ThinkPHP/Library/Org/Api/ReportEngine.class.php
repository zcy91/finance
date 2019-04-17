<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Org\Api;

/**
 * Description of ReportEngine
 *
 * @author sicnco
 */
class ReportEngine {
    //put your code here
    public $report_header;
    public $report_footer;
    public $page_header;
    public $page_footer;
    public $group_header;
    public $group_footer;
    public $detail;
    
    public function __construct($template_filename)
    {
        
    }
    
    /**
     * 当数据量很大时，分批次读取（使用分页功能）
     */
    public function fetch_next_data()
    {
        
    }
    
    public function read_template_size()
    {
        
    }
    
    public function check_page_overflow()
    {
        //页边距
        //
    }
    
    public function init_data()
    {
        //遍历图片文字边框，不接受除此之外内容
        //$objPHPExcel = $objReader->load("goods_list.xls");  //载入文件
//        foreach ($objPHPExcel->getSheet(0)->getDrawingCollection() as $k => $drawing) {
//                $codata = $drawing->getCoordinates(); //得到单元数据 比如G2单元
//                $filename = $drawing->getIndexedFilename();  //文件名
//                ob_start();
//                call_user_func(
//                    $drawing->getRenderingFunction(),
//                    $drawing->getImageResource()
//                );
//                $imageContents = ob_get_contents();
//                file_put_contents('pic/'.$codata.'_'.$filename.'.jpg',$imageContents); //把文件保存到本地
//                ob_end_clean();
//        }

//        $objWorksheet = $objPHPExcel->getActiveSheet();
// 4      $i = 0;
// 5      foreach($objWorksheet->getRowIterator() as $row){

// 9                $cellIterator = $row->getCellIterator();
//10                $cellIterator->setIterateOnlyExistingCells(false);
//11 
//12                     if( $i == 0 ){
//13                          echo '<thead>';
//14                     }
//15                foreach($cellIterator as $cell){
//16 
//17                     echo '<td>' . $cell->getValue() . '</td>';
//18 
//19                }
//20                     if( $i == 0 ){
//21                          echo '</thead>';
//22                     }
//23                $i++;

//        $Vdate=array("title"=>"xx系统");
//        //图片tmp path
//        $TmpPath="images/tmp_execl/";
//        //加载Execl读取自定义类
//        $this->load->library('phpexecl/PHPExcel');
//        $objReader = PHPExcel_IOFactory::createReader('Excel5');
//        //$objReader->setReadDataOnly(true);
//        $objPHPExcel = $objReader->load('images/tmp/test.xls');
//        $currentSheet = $objPHPExcel->getActiveSheet();
//        //先处理图片
//        $AllImages= $currentSheet->getDrawingCollection();
//        $ArrayTmp="";
//        foreach($AllImages as $drawing){
//            if($drawing instanceof PHPExcel_Worksheet_MemoryDrawing){
//                $image = $drawing->getImageResource();
//                $filename=$drawing->getIndexedFilename();
//                $XY=$drawing->getCoordinates();
//                //把图片存起来
//                imagepng($image, $TmpPath.$filename);
//                //把图片的单元格的值设置为图片名称
//                $cell = $currentSheet->getCell($XY);
//                $cell->setValue($filename);
//            }
//        }
//        //处理每个单元格的数据
//        $allColumn = $currentSheet->getHighestColumn();
//        $allRow = $currentSheet->getHighestRow(); 
//        for($currentRow = 1;$currentRow<=$allRow;$currentRow++){
//            for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
//                $address = $currentColumn.$currentRow;
//                //获取单元格数据
//                $CellDate=$currentSheet->getCell($address)->getValue();
//                //echo $currentSheet->getCell($address)->getValue()."\t";
//                //写入数据库.....
//                //.....
//            }
//            //echo "\n";
//        }
//        $this->load->view('main_view',$Vdate);
//    }

    }
    
    private function init_report_header()
    {
        
    }
    
    private function init_page_header()
    {
        
    }
    
    private function init_group_header()
    {
        
    }
    
    private function init_detail()
    {
        
    }
    
    private function init_group_footer()
    {
        
    }
    
    private function init_page_footer()
    {
        
    }
    
    private function init_report_footer()
    {
        
    }
}
