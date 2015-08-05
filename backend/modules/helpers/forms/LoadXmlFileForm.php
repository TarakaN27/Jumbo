<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 05.08.15
 */

namespace backend\modules\helpers\forms;


use common\models\CUser;
use common\models\CUserRequisites;
use yii\base\Model;

class LoadXmlFileForm extends Model{

    public
        $manager,
        $type,
        $file;

    public function rules()
    {
        return [
            [['file','manager','type'], 'required'],
            [['file'], 'safe'],
            [['file'], 'file'],
            [['manager','type'],'integer']
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
            $isResiden = 0;
            if(preg_match("/,(РБ)\s|\s+?(РБ$)|\s(РБ)\W/i", $arData[$i+1]['B']))
                $isResiden = 1;


            $type = 5;
            if(preg_match("/,(ИП)\s|\s+?(ИП$)|\s(ИП)\W|^(ИП)\W/i", $arData[$i+1]['B']))
                $type = 15;

            $arData[$i+1]['B'] = preg_replace("/,(РБ)\s|\s+?(РБ$)|\s(РБ)\W/i"," ",$arData[$i+1]['B']);

            $arData[$i+1]['B'] = str_replace(',','',$arData[$i+1]['B']);

            $arFio = [
                'fname' => 'dummy',
                'lname' => 'dummy',
                'mname' => 'dummy'
            ];
            if($type == 15)
            {
                $arTMP = explode(" ", trim(preg_replace("/(ИП)\s|\s+?(ИП$)|\s(ИП)\W|^(ИП)\W/i"," ",$arData[$i+1]['B'])));
                if(count($arTMP) === 3)
                {
                    $arFio['lname'] = $arTMP[0];
                    $arFio['fname'] = $arTMP[1];
                    $arFio['mname'] = $arTMP[2];
                }else{
                    $arFio['fname'] = str_replace("."," ",$arData[$i+1]['B']);
                }
            }


           // echo $arData[$i+1]['B'];
            $arContractor [] = [
                'name' => trim($arData[$i+1]['B']),
                'ynp' => empty(trim($arData[$i]['D'])) ? 'dummy' : trim($arData[$i]['D']),
                'jaddress' => trim($arData[$i]['F']),
                'paddress' => trim($arData[$i+1]['F']),
                'resident' => $isResiden,
                'type' => $type,
                'fio' => $arFio
            ];
            $i++;
        }


        $trans = \Yii::$app->db->beginTransaction();
        try{
        foreach($arContractor as $contr)
        {

            /** @var Cuser $cM */

            $cM = new CUser();
            $cM->setDummyFields();
            $cM->type = $this->type;
            $cM->manager_id = $this->manager;
            $cM->is_resident = $contr['resident'];
            if($cM->save())
            {

                $modelR = new CUserRequisites();
                $modelR ->j_fname = $contr['fio']['fname'];
                $modelR ->j_lname = $contr['fio']['lname'];
                $modelR ->j_mname = $contr['fio']['mname'];
                $modelR -> type_id = $contr['type'];


                


            }else{
                $trans->rollBack();
                break;
            }



            



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