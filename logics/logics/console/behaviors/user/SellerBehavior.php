<?php
namespace console\behaviors\user;

use console\behaviors\BaseBehavior;

class SellerBehavior extends BaseBehavior {

    public function getModels_FetchSiteInfo() {
        return array(
            'console\models\user\View_UserShop' => 'fetchSiteInfo'
        );
    }

}
