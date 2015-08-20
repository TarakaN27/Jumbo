<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 20.08.15
 */

namespace common\models\managers;


use common\models\Bills;

class BillsManager extends Bills{

    public function getDocument($type)
    {
        if($type == self::TYPE_DOC_DOCX)
            return $this->generateDocx();

        if($type == self::TYPE_DOC_PDF)
            return $this->generatePDF();

        return NULL;
    }



    protected function generateDocx()
    {
        



    }


    protected function generatePDF()
    {




    }


    protected function
} 