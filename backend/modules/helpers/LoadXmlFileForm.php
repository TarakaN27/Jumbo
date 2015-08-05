<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 05.08.15
 */

namespace backend\modules\helpers;


use common\models\CUser;
use yii\base\Model;

class LoadXmlFileForm extends Model{

    public
        $manager,
        $file;

    public function rules()
    {
        return [
            [['file','manager'], 'required'],
            [['file'], 'safe'],
            [['file'], 'file'],
            [['manager'],'integer']
        ];
    }



    public function makeRequest()
    {
        $fileName = $this->file->tempName;


        $excelReader = \PHPExcel_IOFactory::createReaderForFile($fileName);
        //if we dont need any formatting on the data
        $excelReader->setReadDataOnly();
        $excelObj = $excelReader->load($fileName);
        $excelObj->getActiveSheet()->toArray(null, true,true,true);

        $worksheetNames = $excelObj->getSheetNames($fileName);
        $return = array();
        foreach($worksheetNames as $key => $sheetName){
            //set the current active worksheet by name
            $excelObj->setActiveSheetIndexByName($sheetName);
            //create an assoc array with the sheet name as key and the sheet contents array as value
            $return[$sheetName] = $excelObj->getActiveSheet()->toArray(null, true,true,true);
        }

        $arContractor = [];
        $arData = $return['Sheet1'];
        for($i=7;$i<count($arData)-1;$i++)
        {
            $arContractor [] = [
                'name' => $arData[$i+1]['B'],
                'ynp' => $arData[$i]['D'],
            ];
            $i++;
        }


/*
        $objPHPExcel = \PHPExcel_IOFactory::load($fileName);
        $objPHPExcel->setActiveSheetIndex(0);
        $aSheet = $objPHPExcel->getActiveSheet();

        //этот массив будет содержать массивы содержащие в себе значения ячеек каждой строки
        $array = array();
        //получим итератор строки и пройдемся по нему циклом
        foreach($aSheet->getRowIterator() as $row){
            //получим итератор ячеек текущей строки
            $cellIterator = $row->getCellIterator();
            //пройдемся циклом по ячейкам строки
            //этот массив будет содержать значения каждой отдельной строки
            $item = array();
            foreach($cellIterator as $cell){

                //заносим значения ячеек одной строки в отдельный массив
                //	array_push($item, charset_x_win(iconv('windows-1251','UTF-8', $cell->getCalculatedValue())));
                $text = $cell->getCalculatedValue();
                array_push($item,iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text));

            }
            //заносим массив со значениями ячеек отдельной строки в "общий массв строк"
            array_push($array, $item);
        }
*/


        $trans = \Yii::$app->db->beginTransaction();
        try{
        foreach($arContractor as $contr)
        {
            $cM = new CUser();
            $cM->email = uniqid('dummy').'@dummy.com';


            
            $cM->is_resident = '';


        }
        }catch (\Exception $e)
        {
            $trans->rollBack();
        }


        echo '<pre>';
        print_r($arContractor);
        echo '</pre>';
        die;

        die;






    }

} 