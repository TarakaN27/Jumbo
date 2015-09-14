<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 14.09.15
 * Скрывающийся/открывающийся блок
 */

namespace common\components\widgets\hideShowBlock;

use yii\base\Widget;
use yii\helpers\Html;

class HideShowBlockWidget extends Widget{

    /**
     * @var array the HTML attributes (name-value pairs) for the form tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    public $btnTmpl = '{btn}';

    /**
     *
     */
    public function init()
    {
        parent::init();
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        echo '<section id="'.$this->options['id'].'" class="hideSection">';
    }

    /**
     * @return string|void
     */
    public function run()
    {
        echo '</section>';
        echo $this->renderBtn();
        $this->registerJs();
    }

    /**
     * @return string
     */
    public function renderBtn()
    {
        return strtr($this->btnTmpl,[
            '{btn}' => Html::a(\Yii::t('app/common','Show detail').' <i class="fa fa-angle-double-down"></i>',NULL,[
                'class' => 'hideShowBtnClass',
                'data-id' => $this->options['id'],
                'onclick' => 'hideShowBlock(this);'
            ])
        ]);
    }

    /**
     *
     */
    public function registerJs()
    {
        $view = $this->getView();
        $btnDown = \Yii::t('app/common','Show detail').' <i class="fa fa-angle-double-down"></i>';
        $btnUp = \Yii::t('app/common','Hide detail').' <i class="fa fa-angle-double-up"></i>';
        $view->registerJs("
            function hideShowBlock(this1)
            {
                var
                    btn = jQuery(this1),
                    dataID = btn.attr('data-id'),
                    section = jQuery('#'+dataID);

                if(section.hasClass('open'))
                {
                    section.fadeOut(200);
                    section.removeClass('open');
                    btn.html('$btnDown');
                }else{
                    section.fadeIn(200);
                    section.addClass('open');
                    btn.html('$btnUp');
                }
            }
        ",$view::POS_END,'hideShowBlockFunctionKey');

        $view->registerJs("
            jQuery('.hideSection').fadeOut(200);
        ",$view::POS_READY,'hideShowBlockInitKey');
    }
} 