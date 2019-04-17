<?php
namespace console\behaviors\usercenter;

use console\behaviors\BaseBehavior;

class MerchandiserBehavior extends BaseBehavior {

    public function getModels_OrderList() {
        return array(
            'console\models\business\List_BusinessOrder' => "orderOwnList"
        );
    }     

}
