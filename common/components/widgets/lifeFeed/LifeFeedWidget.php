<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 16.07.15
 */

namespace common\components\widgets\lifeFeed;


use yii\base\Widget;

class LifeFeedWidget extends Widget{

    public
        $userID;

    CONST
        NUMBER_OF_FEED = 10;

    public function init()
    {


    }

    public function run()
    {


        return $this->render('life_feed',[]);
    }

} 