<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 16.07.15
 */

namespace common\components\widgets\liveFeed;

use common\components\managers\DialogManager;
use yii\base\Widget;

class LiveFeedWidget extends Widget{

    public
        $userID;

    CONST
        NUMBER_OF_FEED = 10;

    public function init()
    {
        parent::init();
    }

    /**
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function run()
    {
        $obDMan = new DialogManager(['userID' => $this->userID]);
        $arDialogs = $obDMan->loadLiveFeedDialogs(0);
        $pages = $obDMan->getPages();

        return $this->render('life_feed',[
            'arDialogs' => $arDialogs,
            'pages' => $pages
        ]);
    }

} 