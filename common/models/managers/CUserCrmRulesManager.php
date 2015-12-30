<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 18.12.15
 * Time: 12.01
 */

namespace common\models\managers;

use common\models\CrmCmpContacts;
use common\models\CUser;
use yii\web\NotFoundHttpException;
use yii\db\Query;
use backend\models\BUser;
use common\models\BUserCrmGroup;
use common\models\BUserCrmRules;

class CUserCrmRulesManager
{
	public static function getBuserIdsByPermission($iCmpID,$iAthID)
	{
		/** @var  CUser $obCmp */
		$obCmp = CUser::findOne($iCmpID);
		if(!$obCmp)
			throw new NotFoundHttpException();

		$arBUIDs = [];
		if($obCmp->is_opened)
		{
			/**
			 * получаем пользователей, которые могут видеть компанию
			 */
			$arBUserIDs = (new Query())
				->select('b.id as id')
				->from(BUser::tableName().' b ')
				->leftJoin(BUserCrmGroup::tableName().' as g','g.id = b.crm_group_id')
				->leftJoin(BUserCrmRules::tableName().' as r','r.role_id = g.role_id')
				->where('(r.rd = :rd OR r.rd = :rd2)')
				->params([
					':rd' => BUserCrmRules::RULE_ALL,
					':rd2' => BUserCrmRules::RULE_OPENED
				])
				->all();

			foreach($arBUserIDs as $user)
				$arBUIDs [] = $user['id'];

			//проверяем не забыли ли пользователя, коотрый создал компанию
			if($iAthID != $obCmp->created_by && !in_array($obCmp->created_by,$arBUserIDs))
			{
				$arOwner = (new Query())
					->select('r.rd as rd')
					->from(BUserCrmRules::tableName().' r ')
					->leftJoin(BUserCrmGroup::tableName().' as g ','g.role_id = r.role_id')
					->leftJoin(BUser::tableName().' as b','b.crm_group_id = g.id')
					->where('b.id = :ID')
					->params([':ID' => $obCmp->created_by])
					->one();
				if(!empty($arOwner) && $arOwner['rd'] == BUserCrmRules::RULE_THEMSELF)
					$arBUIDs[]= $arOwner['rd'];
			}

			// отвественный
			if(!in_array($obCmp->manager_id,$arBUIDs))
				$arBUIDs[] = $obCmp->manager_id;
		}else{
			$arBUIDs [] = (int)$obCmp->manager_id;
			$arBUIDs [] = (int)$iAthID;
			$arBUIDs [] = (int)$obCmp->created_by;
		}

		$arBUIDs = array_unique($arBUIDs);
		$arBUIDs = array_filter($arBUIDs);
		return $arBUIDs;
	}

	public static function getBuserByPermissionsContact($iCntID,$iAthID,$obCmp = NULL)
	{
		/** @var  CrmCmpContacts $obCmp */
		if(is_null($obCmp))
			$obCmp = CrmCmpContacts::findOne($iCntID);
		if(!$obCmp)
			throw new NotFoundHttpException();

		$arBUIDs = [];
		if($obCmp->is_opened)
		{
			/**
			 * получаем пользователей, которые могут видеть компанию
			 */
			$arBUserIDs = (new Query())
				->select('b.id as id')
				->from(BUser::tableName().' b ')
				->leftJoin(BUserCrmGroup::tableName().' as g','g.id = b.crm_group_id')
				->leftJoin(BUserCrmRules::tableName().' as r','r.role_id = g.role_id')
				->where('(r.rd = :rd OR r.rd = :rd2)')
				->params([
					':rd' => BUserCrmRules::RULE_ALL,
					':rd2' => BUserCrmRules::RULE_OPENED
				])
				->all();

			foreach($arBUserIDs as $user)
				$arBUIDs [] = $user['id'];

			//проверяем не забыли ли пользователя, коотрый создал компанию
			if($iAthID != $obCmp->created_by && !in_array($obCmp->created_by,$arBUserIDs))
			{
				$arOwner = (new Query())
					->select('r.rd as rd')
					->from(BUserCrmRules::tableName().' r ')
					->leftJoin(BUserCrmGroup::tableName().' as g ','g.role_id = r.role_id')
					->leftJoin(BUser::tableName().' as b','b.crm_group_id = g.id')
					->where('b.id = :ID')
					->params([':ID' => $obCmp->created_by])
					->one();
				if(!empty($arOwner) && $arOwner['rd'] == BUserCrmRules::RULE_THEMSELF)
					$arBUIDs[]= $arOwner['rd'];
			}

			// отвественный
			if(!in_array($obCmp->assigned_at,$arBUIDs))
				$arBUIDs[] = $obCmp-assigned_at;
		}else{
			$arBUIDs [] = (int)$obCmp->assigned_at;
			$arBUIDs [] = (int)$iAthID;
			$arBUIDs [] = (int)$obCmp->created_by;
			$arBUIDs = array_unique($arBUIDs);
			$arBUIDs = array_filter($arBUIDs);
		}

		return $arBUIDs;
	}
}