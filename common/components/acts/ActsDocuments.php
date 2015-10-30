<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 29.10.15
 * Time: 11.57
 */

namespace common\components\acts;


use common\models\ActsTemplate;
use common\models\CUser;
use common\models\LegalPerson;
use common\models\Services;
use yii\base\Exception;
use yii\web\NotFoundHttpException;

class ActsDocuments
{
	public
		$iTplID = NULL,
		$iCuserID = NULL,
		$iServID = NULL,
		$iActID = NULL,
		$iLegPersID = NULL,
		$amount = NULL;


	protected
		$data = [],
		$LPInfo = [ //параметры юр лица
			'legalName' => '',
			'legalReq' => '',
			'site' => '',
			'email' => ''
		],
		$CntrInfo = [   //параметры контрагента
			'corpName' => '',
			'requisites' => '',
			'address' => '',
			'email' => '',
			'site' => ''
		];


	CONST
		FOLDER_RIGHT = 0777,
		ACTS_PATH = '@common/upload/docx_acts';

	/**
	 * @param $iTplID
	 * @param $iCuserID
	 * @param $iServID
	 * @param $amount
	 * @throws Exception
	 */
	public function __construct($iActID,$iTplID,$iLegPersID,$iCuserID,$iServID,$amount)
	{
		$this->iActID = $iActID;
		$this->iTplID = $iTplID;
		$this->iLegPersID = $iLegPersID;
		$this->$iCuserID = $iCuserID;
		$this->iServID = $iServID;
		$amount = $amount;

		if(!$this->isDirExist())    //проверяем чтобы существовала директория(если нет, то пробуем создать)
			throw new Exception('Folder for acts not exist!!');
	}


	/**
	 * @return bool
	 */
	protected function isDirExist()
	{
		$path = \Yii::getAlias(self::ACTS_PATH);
		//проверяем ,что папка существует
		if(is_dir($path))
			return TRUE;

		//создаем папку и назначаем права
		if(mkdir($path,self::FOLDER_RIGHT))
		{
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @return string
	 */
	protected function generateName()
	{
		return 'Act_'.uniqid().'.docx';
	}

	/**
	 * @param $name
	 * @return string
	 */
	protected function generateRealPath($name)
	{
		return \Yii::getAlias(self::ACTS_PATH).'/'.$name;
	}

	/**
	 * @return mixed|null|static
	 * @throws NotFoundHttpException
	 */
	protected function getTemplate()
	{
		/** @var ActsTemplate $obTpl */
		$obTpl = ActsTemplate::findOneByIDCached($this->iTplID);
		if(!$obTpl || !file_exists($obTpl->getFilePath()))
			throw new NotFoundHttpException('Act template not found');
		return $obTpl;
	}

	/**
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
	protected function getCUserRequisites()
	{
		$obCuser = CUser::find()->with('requisites')->where(['id' => $this->iCuserID])->one();
		if(!$obCuser)
			throw new NotFoundHttpException('Contractor not found');
		return $obCuser;
	}


	protected function getLegalPerson()
	{
		/** @var LegalPerson $obLP */
		$obLP = LegalPerson::findOneByIDCached($this->iLegPersID);

		return $this->LPInfo = [
			'legalName' => $obLP->name,
			'legalReq' => $obLP->doc_requisites,
			'site' => $obLP->doc_site,
			'email' => $obLP->doc_email
		];
	}


	public function generateDocument()
	{
		$obTpl = $this->getTemplate();  //шаблон для акта
		$arLP = $this->getLegalPerson(); //данные о юр лице



	}








}