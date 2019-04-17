<?php
namespace console\behaviors\right;

use console\behaviors\BaseBehavior;

class PostBehavior extends BaseBehavior {

    public function getModels_PostAdd() {
        return array(
            'console\models\right\InitData_RightPost' => 'postAdd',
            'console\models\right\RightPost',
            'console\models\right\RightPostModule',
            'console\models\right\RightPostBase',
            'console\models\right\RightPostSection',
            'console\models\right\RightPostOperate'
        );
    }
    
    public function getModels_PostEdit() {
        return array(
            'console\models\right\InitData_RightPost' => 'postEdit',
            'console\models\right\RightPost',
            'console\models\right\RightPostModule' => 'deleteAdd',
            'console\models\right\RightPostBase' => 'deleteAdd',
            'console\models\right\RightPostSection' => 'deleteAdd',
            'console\models\right\RightPostOperate' => 'add',
        );
    }    

    public function getModels_PostDelete() {
        return array(
            'console\models\right\InitData_RightPost' => 'postDelete',
            'console\models\right\RightPost' => 'deletePost',
            'console\models\right\RightPostModule' => 'deletePost',
            'console\models\right\RightPostBase' => 'deletePost',
            'console\models\right\RightPostSection' => 'deletePost',
            'console\models\right\RightPostOperate' => 'add',
        );
    }

    public function getModels_PostList() {
        return array(
            'console\models\right\RightPost'
        );
    }   
    
    public function getModels_PostSingle() {
        return array(
            'console\models\right\RightPost'=>'viewSingle'
        );
    }   

}
