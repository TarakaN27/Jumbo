<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 26.10.15
 * Time: 16.29
 */

namespace console\components\controllerHelper;

use backend\models\BUser;
use common\models\Dialogs;
use common\models\PromisedPayment;


class ManagerMsg
{
	public static function checkForOverduePromisedPayment()
	{
		$arPP = PromisedPayment::getOverduePromisedPayment(); //получаем все просроченный платежи
		if(empty($arPP))
			return TRUE;
		$arPPByUser = [];   //просроченные платежи для каждого пользователя
		foreach($arPP as $pp)
		{
			if(is_object($obCUser = $pp->cuser) && !empty($obCUser->manager_id)) {
				if(!isset($arPPByUser[$pp->cuser_id]['manager_id']))
					$arPPByUser[$pp->cuser_id]['manager_id'] = $obCUser->manager_id;
				$arPPByUser[$pp->cuser_id]['services'][] = (is_object($obServ = $pp->service) ?
						$obServ->name : $pp->service_id ). ' : '.$pp->amount;

				if(!isset($arPPByUser[$pp->cuser_id]['user']))
					$arPPByUser[$pp->cuser_id]['user'] = is_object($obCUser = $pp->cuser) ? $obCUser->getInfo() : $pp->cuser_id;

			}
			unset($obCUser);
		}

		$obAdmin = BUser::getAdmin();   // получаме администратора, нужно от кого-то отправлять сообщения
		if(empty($obAdmin))
			return FALSE;

		$bHasError = FALSE;
		foreach($arPPByUser as $key => $pp) // добавляем дилоги
		{
				$sMsg = \Yii::t('app/book','The user {user} has promised payment overdue for services <br>{services}',[
					'user' => $pp['user'],
					'services' => implode(';<br>',$pp['services'])
				]); // шаблон сообщения

				$obMsg = new Dialogs(); //создаем новый диалог
				$obMsg->type = Dialogs::TYPE_OVERDUE_PP;
				$obMsg->buser_id = $obAdmin->id;
				$obMsg->status = Dialogs::PUBLISHED;
				$obMsg->theme = $sMsg;
				if(!$obMsg->save())
					$bHasError = TRUE;

				$obManager = BUser::findOneByIDCached($pp['manager_id']);
				if(!empty($obManager))
					$obMsg->link('busers',$obManager);  //добаляем к диалогу пользователя

				unset($obMsg);

		}

		return !$bHasError;
	}



}