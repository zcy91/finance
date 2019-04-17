<?php
namespace console\behaviors\right;

use console\behaviors\BaseBehavior;

class SectionBehavior extends BaseBehavior {

    public function getModels_SectionAdd() {
        return array(
            'console\models\right\InitData_RightSection' => 'sectionAdd',
            'console\models\right\RightSection' => 'add',
            'console\models\right\RightSectionOperate' => 'add'
        );
    }
    
    public function getModels_SectionEdit() {
        return array(
            'console\models\right\InitData_RightSection' => 'sectionEdit',
            'console\models\right\RightSection' => 'modify',
            'console\models\right\RightSectionOperate' => 'add'
        );
    }    

    public function getModels_SectionDelete() {
        return array(
            'console\models\right\InitData_RightSection' => 'sectionDelete',
            'console\models\right\RightSection' => "delete",
            'console\models\right\RightSectionOperate' => 'add'
        );
    }

    public function getModels_SectionList() {
        return array(
            'console\models\right\View_RightSection' => "sectionList"
        );
    }   

}
