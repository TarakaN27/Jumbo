<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 04.08.15
 */

namespace console\controllers;


use common\models\CUserTypes;
use console\components\AbstractConsoleController;

use common\models\CrmCmpContacts;
use common\models\CUser;
use common\models\CUserRequisites;
use common\components\helpers\CustomHelper;
use Yii;
use backend\models\BUser;
use yii\db\Query;


class DataBaseController extends AbstractConsoleController{

	public function actionImport()
	{
		$dir = '@frontend/runtime/import';
		$fCmp = Yii::getAlias($dir.'/'.'companies.csv');
		$fCnt = Yii::getAlias($dir.'/'.'contacts.csv');
		$fLead = Yii::getAlias($dir.'/'.'leads.csv');

		$arCmp = $this->csv_to_array($fCmp,';');
		$arCnt = $this->csv_to_array($fCnt,';');
		$arLead = $this->csv_to_array($fLead,';');
		$arResult = [];
		$arCmpCnt = [];
		$arDublicat = [];

		foreach($arCmp as $cmp)
		{
			if(!isset($arResult[trim($cmp['nazvanie-kompanii'])]))
			{
				$arResult[trim($cmp['nazvanie-kompanii'])] = $cmp;
			}
			else
				$arDublicat[] = $cmp;
		}

		foreach($arLead as $lead)
		{
			if(!isset($arResult[trim($lead['﻿"nazvanie-lida'])]))
			{
				$arResult[trim($lead['﻿"nazvanie-lida'])] = $lead;
			}else{
				$arDublicat[] = $lead;
			}
		}


		$arManager = [];
		foreach($arResult as $r)
		{
			//if(!in_array($r['otvetstvennyi'],$arManager))
			$arManager [] = $r['otvetstvennyi'];
		}

		foreach($arCnt as $c)
		{
			$arManager [] = $c['otvetstvennyi'];
		}

		$arManager = array_unique($arManager);
		$notAdded = [];
		$arDiff = [];



		$arBuser = BUser::find()->all();
		$arBID = [];
		/** @var BUser $user */
		foreach($arBuser as $user)
		{
			$arBID[mb_strtolower($user->fname.' '.$user->lname)] = $user->id;
		}

		$arCMPTypes = [
			'Отказной' => 2,
			'Холодный' => 3,
			'Горячий' => 4,
			'Активный' => 5,
			'Пассивный' => 6
		];

		$defaultType = 7;
		$defaultManager = 12;

		$flagError = FALSE;
		$arCompany = [];

		//$tr = Yii::$app->db->beginTransaction();

		foreach($arResult as $res)
		{
			$modelR = new CUserRequisites();
			$modelR->type_id = 2;
			$extIDPrefix = '';
			if(isset($res['nazvanie-kompanii']))
			{
				$modelR->corp_name = $res['nazvanie-kompanii'];
				$email = explode(',',$res['rabochii-e-mail']);

				$fEmail = '';
				$dopEmail = '';

				if(!empty($email) && isset($email[0])) {
					$fEmail = $email[0];
					unset($email[0]);
				}

				if(!empty($email))
					$dopEmail = implode(',',$email);



				if(!empty($fEmail))
					$modelR->c_email = trim($fEmail);
				if(!empty($dopEmail))
					$modelR->description = 'Емаил:'.$dopEmail;

				$modelR->c_phone = $res['rabochii-telefon'];

				if(!empty($res['mobilnyi-telefon']))
					$modelR->description.='; mobilnyi-telefon: '.$res['mobilnyi-telefon'];
				$extIDPrefix = 'cmp_';
			}
			else {
				$modelR->corp_name = $res['﻿"nazvanie-lida'];

				$email = explode(',',$res['rabochii-e-mail']);

				$fEmail = '';
				$dopEmail = '';

				if(!empty($email) && isset($email[0])) {
					$fEmail = $email[0];
					unset($email[0]);
				}

				if(!empty($email))
					$dopEmail = implode(',',$email);



				if(!empty($fEmail))
					$modelR->c_email = trim($fEmail);
				if(!empty($dopEmail))
					$modelR->description = 'Емаил:'.$dopEmail;

				$modelR->c_phone = $res['rabochii-telefon'];

				if(!empty($res['mobilnyi-telefon']))
					$modelR->description.='; mobilnyi-telefon: '.$res['mobilnyi-telefon'];
				$extIDPrefix = 'lead_';
			}

			if(!$modelR->save())
			{
				$notAdded [] = $res;
			}
			if(!empty($modelR->id)) {
				$obCuser = new CUser();
				//$obCuser->ext_id = $res['id'];

				$obCuser->is_opened = CUser::IS_OPENED;
				if (isset($res['tip-kompanii']))
					$obCuser->type = isset($arCMPTypes[$res['tip-kompanii']]) ? $arCMPTypes[$res['tip-kompanii']] : $defaultType;
				else
					$obCuser->type = $defaultType;

				$obCuser->manager_id = isset($arBID[mb_strtolower($res['otvetstvennyi'])]) ? $arBID[mb_strtolower($res['otvetstvennyi'])] : $defaultManager;

				$obCuser->is_resident = 1;
				$obCuser->role = CUser::ROLE_USER;
				$obCuser->requisites_id = $modelR->id;
				$obCuser->setDummyFields();
				if (!$obCuser->save()) {
					$notAdded [] = $res;
				} else {
					$arCompany[$modelR->corp_name] = $obCuser->id;
					$arDiff [] = [$res['id'],$obCuser->id];
				}
				unset($obCuser,$modelR);
			}
			unset($modelR);
		}

		if(!$flagError)
			foreach($arCnt as $cnt)
			{
				$obContact = new CrmCmpContacts();
				$obContact->setIsConsole(TRUE);
				$obContact->ext_id = $cnt['id'];
				$obContact->fio = $cnt['﻿"imya'].' '.$cnt['otchestvo'].' '.$cnt['familiya'];
				$obContact->type = CrmCmpContacts::TYPE_CLIENT;
				$obContact->post = $cnt['dolzhnost'];
				$obContact->assigned_at = isset($arBID[mb_strtolower($cnt['otvetstvennyi'])]) ? $arBID[mb_strtolower($cnt['otvetstvennyi'])] : $defaultManager;

				if(isset($arCompany[trim($cnt['kompaniya'])]))
					$obContact->cmp_id = $arCompany[trim($cnt['kompaniya'])];

				$obContact->phone = $cnt['rabochii-telefon'];

				if(!empty($cnt['mobilnyi-telefon']))
					$obContact->description = 'mobilnyi-telefon: '.$cnt['mobilnyi-telefon'];

				$email = explode(',',$cnt['rabochii-e-mail']);

				$fEmail = '';
				$dopEmail = '';

				if(!empty($email) && isset($email[0])) {
					$fEmail = $email[0];
					unset($email[0]);
				}

				if(!empty($email))
					$dopEmail = implode(',',$email);

				if(!empty($fEmail))
					$obContact->email = trim($fEmail);
				if(!empty($dopEmail))
					$obContact->description = 'Емаил:'.$dopEmail;

				$obContact->is_opened = CrmCmpContacts::IS_OPENED;

				if(!$obContact->save())
				{
					$notAdded [] = $cnt;
				}

			}




		$fp = fopen(Yii::getAlias($dir.'/'.'diff.csv'), 'w');

		foreach ($arDiff as $fields) {
			fputcsv($fp, $fields,';');
		}
		fclose($fp);

		$fp = fopen(Yii::getAlias($dir.'/'.'not_added.csv'), 'w');

		foreach ($notAdded as $fields) {
			fputcsv($fp, $fields,';');
		}
		fclose($fp);

		$fp = fopen(Yii::getAlias($dir.'/'.'dublicat.csv'), 'w');

		foreach ($arDublicat as $fields) {
			fputcsv($fp, $fields,';');
		}
		fclose($fp);

		echo 'done';

		//$arLead = $this->csv_to_array($fLead,';');
		echo '<pre>';
		print_r($notAdded);
		//print_r($arDublicat);
		echo '</pre>';
		die('a');
	}

	protected function csv_to_array($filename='', $delimiter=',',$convert = FALSE)
	{
		if(!file_exists($filename) || !is_readable($filename))
			return FALSE;

		$header = NULL;
		$data = array();
		if (($handle = fopen($filename, 'r')) !== FALSE)
		{
			while (($row = fgetcsv($handle, 10000, $delimiter)) !== FALSE)
			{
				//if($convert)
				//    foreach($row as &$r)
				//        $r = iconv('windows-1251', 'utf-8',$r);

				foreach($row as &$r) {
					if (!$header)
					{
						$r = CustomHelper::cyrillicToLatin($r);
					}
					$r = trim($r);
				}

				if(!$header)
				{

					$header = $row;
				}

				else
					$data[] = array_combine($header, $row);
				//$data[] = $row;
			}
			fclose($handle);
		}
		return $data;
	}

	public function actionExportUser()
	{
		$obUser = (new Query())
			->from(CUser::tableName().' cu ')
			->leftJoin(CUserRequisites::tableName().' as re','cu.requisites_id = re.id')
			->leftJoin(CUserTypes::tableName().'as ty','ty.id = cu.type')
			->leftJoin(BUser::tableName().'as bu','cu.manager_id = bu.id')
			->select('
			cu.id,cu.manager_id,bu.fname as manager_fname,bu.mname as manager_mname,bu.lname as manager_lname,cu.status,
			cu.created_at,cu.is_resident,cu.r_country,cu.is_opened,cu.contractor,cu.archive,cu.prospects_id,ty.name as type,
			,re.*')

		//	->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql;
	//	echo $obUser;die('2');
			->all();
		$arrHead = [];
		if(isset($obUser[0]))
			$arrHead = array_keys($obUser[0]);

		//preDump($arData);die;
		$fp = fopen(Yii::getAlias('@app/runtime/cuser.csv'), 'w');
		fputcsv($fp,$arrHead,';');
//fputcsv($fp, $arHeader,';');
		foreach ($obUser as $fields) {
			fputcsv($fp, $fields,';');
		}
		fclose($fp);

		echo 'done';
	}

} 