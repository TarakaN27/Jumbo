<?php

namespace common\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%cuser_requisites}}".
 *
 * @property integer $id
 * @property string $corp_name
 * @property string $j_fname
 * @property string $j_lname
 * @property string $j_mname
 * @property string $j_post
 * @property string $j_doc
 * @property string $reg_date
 * @property string $reg_number
 * @property string $reg_auth
 * @property string $ch_account
 * @property string $b_name
 * @property string $b_code
 * @property string $j_address
 * @property string $p_address
 * @property string $c_fname
 * @property string $c_lname
 * @property string $c_mname
 * @property string $c_email
 * @property string $c_phone
 * @property string $c_fax
 * @property string $ynp
 * @property string $inn
 * @property string $kpp
 * @property string $ogrn
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $type_id
 * @property string $pasp_series
 * @property integer $pasp_number
 * @property string $pasp_ident
 * @property string $pasp_auth
 * @property string $pasp_date
 * @property string $site
 * @property string $description
 * @property string $bank_address
 */
class CUserRequisites extends AbstractActiveRecord
{

    CONST
        TYPE_J_PERSON  = 5, // Юр. лицо
        TYPE_F_PERSON = 10, // Физ. лицо
        TYPE_I_PERSON = 15; // ИП

    public
        $allow_expense = AbstractActiveRecord::NO,  //разрешить затраты по пользователю
        $contructor = CUser::CONTRACTOR_NO,         //пользователь является контрагентом
        $isResident = true;                         //пользователь резидент

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_requisites}}';
    }

    /**
     * @return array
     */
    public static function getTypeArr()
    {
        return [
            self::TYPE_F_PERSON => Yii::t('app/users', 'Type_f_person'),
            self::TYPE_J_PERSON => Yii::t('app/users', 'Type_j_person'),
            self::TYPE_I_PERSON => Yii::t('app/users', 'Type_i_person')
        ];
    }

    /**
     * @return string
     */
    public function getTypeStr()
    {
        $tmp = self::getTypeArr();
        return isset($tmp[$this->type_id]) ? $tmp[$this->type_id] : 'N/A';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['j_fname', 'j_lname', 'j_mname','type_id'],
                'required',
                'when' => function($model) {
                    if($this->contructor != CUser::CONTRACTOR_YES) //если компания не контрагнет, то поля можно не заполнять
                        return FALSE;
                    return TRUE;
                },
                'whenClient' => "function (attribute, value) {
                    var
                        cntr = $('#cuser-contractor').val();
                    if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."')
                    {
                        return false;
                    }
                    return true;
                }"
            ],
            [['new_ch_account'],  'string', 'max' => 34,'min' => 34],
            [['new_ch_account'], 'match', 'pattern' => '/^[a-zA-Z0-9]+$/'],
            [['reg_date'], 'safe'],
            [['j_address', 'p_address'], 'string'],
            [['created_at', 'updated_at','type_id','pasp_number'], 'integer'],
            [[
                 'corp_name', 'j_fname', 'j_lname', 'j_mname', 'j_post', 'j_doc',
                 'reg_number', 'reg_auth', 'ch_account', 'b_name', 'b_code',
                 'c_fname', 'c_lname', 'c_mname', 'c_email', 'c_phone', 'c_fax',
                 'ynp', 'inn', 'kpp', 'ogrn','pasp_auth','pasp_ident','site','bank_address'
             ], 'string', 'max' => 255],
            [['pasp_series'], 'string', 'max' => 4],
            ['c_email', 'email'],
            [['reg_date','pasp_date'], 'date', 'format' => 'yyyy-m-d'],

            // обязательные поля для физика
            [[
                 'pasp_date','pasp_auth','pasp_ident',
                 'pasp_number','pasp_series','p_address',
             ],
             'required',
             'when' => function($model) {
                    if($this->contructor != CUser::CONTRACTOR_YES) //если компания не контрагнет, то поля можно не заполнять
                        return FALSE;
                    return $model->type_id == CUserRequisites::TYPE_F_PERSON;
             },
             'whenClient' => "function (attribute, value) {
                    var
                        cntr = $('#cuser-contractor').val();
                    if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."')
                    {
                        return false;
                    }
                    return $('#cuserrequisites-type_id input:checked').val() == '".CUserRequisites::TYPE_F_PERSON."';
                }"
            ],
            // обязательные поля для юриков
            [['corp_name', 'j_post', 'j_doc','ch_account', 'b_name',
              'b_code','j_address', 'p_address'],
             'required',
             'when' => function($model) {
                     if($this->contructor != CUser::CONTRACTOR_YES) //если компания не контрагнет, то поля можно не заполнять
                         return FALSE;
                    return $model->type_id == CUserRequisites::TYPE_J_PERSON;
            },
             'whenClient' => "function (attribute, value) {
                    var
                        cntr = $('#cuser-contractor').val();
                    if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."')
                    {
                        return false;
                    }

                    return $('#cuserrequisites-type_id input:checked').val() == '".CUserRequisites::TYPE_J_PERSON."';
                }"
            ],


            // обязательные поля для юриков
            [['corp_name'],
                'required',
                'when' => function($model) {
                    return $model->type_id == CUserRequisites::TYPE_J_PERSON;
                },
                'whenClient' => "function (attribute, value) {
                    return $('#cuserrequisites-type_id input:checked').val() == '".CUserRequisites::TYPE_J_PERSON."';
                }"
            ],

            // юрик или ИП резидент
            [['ynp'],
             'required',
             'when' => function($model) {
                     if($this->contructor != CUser::CONTRACTOR_YES) //если компания не контрагнет, то поля можно не заполнять
                         return FALSE;
                    return (
                        $model->type_id == CUserRequisites::TYPE_J_PERSON ||
                        $model->type_id == CUserRequisites::TYPE_I_PERSON
                    ) && $this->isResident;
                },
             'whenClient' => "function (attribute, value) {
                    var
                        cntr = $('#cuser-contractor').val();
                    if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."')
                    {
                        return false;
                    }

                    return ($('#cuserrequisites-type_id input:checked').val() == '".CUserRequisites::TYPE_J_PERSON."'
                    || $('#cuserrequisites-type_id input:checked').val() == '".CUserRequisites::TYPE_I_PERSON."') && $('#cuserrequisites-isresident').val() == 'true';
                }"

            ],
            // юрик или ИП не резидент
            [['inn', 'kpp', 'ogrn'],
             'required',
             'when' => function($model) {

                 if($this->contructor != CUser::CONTRACTOR_YES) //если компания не контрагнет, то поля можно не заполнять
                     return FALSE;

                 return (
                        $model->type_id == CUserRequisites::TYPE_J_PERSON ||
                        $model->type_id == CUserRequisites::TYPE_I_PERSON
                    ) && !$this->isResident;
                },
             'whenClient' => "function (attribute, value) {
                    var
                        cntr = $('#cuser-contractor').val();
                    if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."')
                    {
                        return false;
                    }

                    return ($('#cuserrequisites-type_id input:checked').val() == '".CUserRequisites::TYPE_J_PERSON."'
                    || $('#cuserrequisites-type_id input:checked').val() == '".CUserRequisites::TYPE_I_PERSON."') && $('#cuserrequisites-isresident').val() != 'true';
                }"
            ],
            // ИП
            [
                [
                //    'pasp_date','pasp_auth','pasp_ident','pasp_number', 'pasp_series',
                    'ch_account', 'b_name','b_code', 'p_address'
                ],
             'required',
             'when' => function($model) {
                 if($this->contructor != CUser::CONTRACTOR_YES) //если компания не контрагнет, то поля можно не заполнять
                     return FALSE;

                 return $model->type_id == CUserRequisites::TYPE_I_PERSON;
             },
             'whenClient' => "function (attribute, value) {
                    var
                        cntr = $('#cuser-contractor').val();
                    if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."')
                    {
                        return false;
                    }
                    return $('#cuserrequisites-type_id input:checked').val() == '".CUserRequisites::TYPE_I_PERSON."';
                }"
            ],
            //[
            //   'site',
            //    'url',
            //    'pattern' => '/^{schemes}:\/\/(([а-яеёА-ЯЕЁA-Z0-9][а-яеёА-ЯЕЁA-Z0-9_-]*)(\.[а-яеёА-ЯЕЁA-Z0-9][а-яеёА-ЯЕЁA-Z0-9_-]*)+)/i'
            //],
            ['description','string'],


            ///////////////////////для пользоватлей для которых разершены затраты
            [
                ['j_fname', 'j_lname', 'j_mname','type_id'],
                'required',
                'when' => function($model) {
                    if(
                        $this->allow_expense == AbstractActiveRecord::YES &&
                        $this->contructor != CUser::CONTRACTOR_YES &&
                        ($model->type_id == CUserRequisites::TYPE_I_PERSON || $model->type_id == CUserRequisites::TYPE_F_PERSON)
                    )
                        return TRUE;
                    return FALSE;
                },
                'whenClient' => "function (attribute, value) {
                        var
                            typeID = $('#cuserrequisites-type_id input:checked').val(),
                            allowExpense = $('#cuser-allow_expense').val(),
                            cntr = $('#cuser-contractor').val();
                        if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."' && allowExpense != undefined && allowExpense == '".AbstractActiveRecord::YES."' && (typeID == '".CUserRequisites::TYPE_I_PERSON."' || typeID == '".CUserRequisites::TYPE_F_PERSON."' ) )
                        {
                            return true;
                        }
                        return false;
                    }"
            ],
            ['p_address',
                'required',
                'when' => function($model) {
                    if(
                        $this->allow_expense == AbstractActiveRecord::YES &&
                        $this->contructor != CUser::CONTRACTOR_YES &&
                        ($model->type_id == CUserRequisites::TYPE_I_PERSON || $model->type_id == CUserRequisites::TYPE_F_PERSON)
                    )
                        return TRUE;
                    return FALSE;
                },
                'whenClient' => "function (attribute, value) {
                        var
                            typeID = $('#cuserrequisites-type_id input:checked').val(),
                            allowExpense = $('#cuser-allow_expense').val(),
                            cntr = $('#cuser-contractor').val();
                        if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."' && allowExpense != undefined && allowExpense == '".AbstractActiveRecord::YES."' && (typeID == '".CUserRequisites::TYPE_I_PERSON."' || typeID == '".CUserRequisites::TYPE_F_PERSON."' ) )
                        {
                            return true;
                        }
                        return false;
                    }"],
            [['corp_name','j_address'],
                'required',
                'when' => function($model) {
                    if(
                        $this->allow_expense == AbstractActiveRecord::YES &&
                        $this->contructor != CUser::CONTRACTOR_YES &&
                        $model->type_id == CUserRequisites::TYPE_J_PERSON
                    )
                        return TRUE;
                    return FALSE;
                },
                'whenClient' => "function (attribute, value) {
                        var
                            typeID = $('#cuserrequisites-type_id input:checked').val(),
                            allowExpense = $('#cuser-allow_expense').val(),
                            cntr = $('#cuser-contractor').val();
                        if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."' && allowExpense != undefined && allowExpense == '".AbstractActiveRecord::YES."' && typeID == '".CUserRequisites::TYPE_J_PERSON."')
                        {
                            return true;
                        }
                        return false;
                    }"

            ],
            [
                ['ch_account', 'b_name','b_code', 'p_address','bank_address'],
                'required',
                'when' => function($model) {
                    if(
                        $this->allow_expense == AbstractActiveRecord::YES &&
                        $this->contructor != CUser::CONTRACTOR_YES &&
                        ($model->type_id == CUserRequisites::TYPE_J_PERSON || $model->type_id == CUserRequisites::TYPE_I_PERSON )
                    )
                        return TRUE;
                    return FALSE;
                },
                'whenClient' => "function (attribute, value) {
                        var
                            typeID = $('#cuserrequisites-type_id input:checked').val(),
                            allowExpense = $('#cuser-allow_expense').val(),
                            cntr = $('#cuser-contractor').val();
                        if(cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."' && allowExpense != undefined && allowExpense == '".AbstractActiveRecord::YES."' && (typeID == '".CUserRequisites::TYPE_I_PERSON."' || typeID == '".CUserRequisites::TYPE_J_PERSON."' ))
                        {
                            return true;
                        }
                        return false;
                    }"
            ],
            [
                ['ynp'],
                'required',
                'when' => function($model) {
                    if(
                        $this->isResident &&
                        $this->allow_expense == AbstractActiveRecord::YES &&
                        $this->contructor != CUser::CONTRACTOR_YES &&
                        ($model->type_id == CUserRequisites::TYPE_J_PERSON || $model->type_id == CUserRequisites::TYPE_I_PERSON )
                    )
                        return TRUE;
                    return FALSE;
                },
                'whenClient' => "function (attribute, value) {
                        var
                            typeID = $('#cuserrequisites-type_id input:checked').val(),
                            allowExpense = $('#cuser-allow_expense').val(),
                            cntr = $('#cuser-contractor').val();
                        if($('#cuserrequisites-isresident').val() == 'true' && cntr != undefined && cntr != '".CUser::CONTRACTOR_YES."' && allowExpense != undefined && allowExpense == '".AbstractActiveRecord::YES."' && (typeID == '".CUserRequisites::TYPE_I_PERSON."' || typeID == '".CUserRequisites::TYPE_J_PERSON."' ))
                        {
                            return true;
                        }
                        return false;
                    }"
            ]


        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/users', 'ID'),
            'corp_name' => Yii::t('app/users', 'Corp Name'),
            'j_fname' => Yii::t('app/users', 'J Fname'),
            'j_lname' => Yii::t('app/users', 'J Lname'),
            'j_mname' => Yii::t('app/users', 'J Mname'),
            'j_post' => Yii::t('app/users', 'J Post'),
            'j_doc' => Yii::t('app/users', 'J Doc'),
            'reg_date' => Yii::t('app/users', 'Reg Date'),
            'reg_number' => Yii::t('app/users', 'Reg Number'),
            'reg_auth' => Yii::t('app/users', 'Reg Auth'),
            'ch_account' => Yii::t('app/users', 'Ch Account'),
            'new_ch_account' => Yii::t('app/users', 'New ch Account'),
            'b_name' => Yii::t('app/users', 'B Name'),
            'b_code' => Yii::t('app/users', 'B Code'),
            'j_address' => Yii::t('app/users', 'J Address'),
            'p_address' => Yii::t('app/users', 'P Address'),
            'c_fname' => Yii::t('app/users', 'C Fname'),
            'c_lname' => Yii::t('app/users', 'C Lname'),
            'c_mname' => Yii::t('app/users', 'C Mname'),
            'c_email' => Yii::t('app/users', 'C Email'),
            'c_phone' => Yii::t('app/users', 'C Phone'),
            'c_fax' => Yii::t('app/users', 'C Fax'),
            'ynp' => Yii::t('app/users', 'Ynp'),
            'inn' => Yii::t('app/users', 'Inn'),
            'kpp' => Yii::t('app/users', 'Kpp'),
            'ogrn' => Yii::t('app/users', 'Ogrn'),
            'bank_address' => Yii::t('app/users','Bank address'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
            'type_id' => Yii::t('app/users', 'Type_id'),
            'pasp_date' => Yii::t('app/users', 'Passport_date'),
            'pasp_series' => Yii::t('app/users', 'Passport_series'),
            'pasp_number' => Yii::t('app/users', 'Passport_number'),
            'pasp_auth' => Yii::t('app/users', 'Passport_auth'),
            'pasp_ident' => Yii::t('app/users', 'Passport_identity_number'),
            'site' => Yii::t('app/users', 'Site'),
            'description' => Yii::t('app/users', 'Description'),
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $arBhvrs = parent::behaviors();
        return ArrayHelper::merge(
            $arBhvrs,
            [
            ]);
    }

    /**
     * @return string
     */
    public function getCorpName()
    {
        if($this->type_id == self::TYPE_I_PERSON)
            return 'ИП '.$this->j_lname.' '.$this->j_fname.' '.$this->j_mname;

        if($this->type_id == self::TYPE_F_PERSON)
            return 'ФИЗ '.$this->j_lname.' '.$this->j_fname.' '.$this->j_mname;
        else
            return $this->corp_name;
    }

    /**
     * @return string
     */
    public function getContactFIO()
    {
        return $this->c_lname.' '.$this->c_fname.' '.$this->c_mname;
    }

    /**
     * @return string
     */
    public function getCorpNameWithSite()
    {
        $str = $this->getCorpName();

        if(!empty($this->site))
            $str.=' ('.$this->site.')';

        return $str;
    }

    public static function getCorpNameByDataArray($arData)
    {
        if($arData['type_id'] == self::TYPE_I_PERSON)
            return 'ИП '.$arData['j_lname'].' '.$arData['j_fname'].' '.$arData['j_mname'];

        if($arData['type_id'] == self::TYPE_F_PERSON)
            return 'ФИЗ '.$arData['j_lname'].' '.$arData['j_fname'].' '.$arData['j_mname'];
        else
            return $arData['corp_name'];
    }
    public static function getCorpNameWithSiteByDataArray($arData)
    {
        $str = static::getCorpNameByDataArray($arData);

        if(!empty($arData['site']))
            $str.=' ('.$arData['site'].')';
        return $str;
    }

    public function getSiteUrl(){
        if($this->site) {
            if(strpos($this->site, 'http')!==false)
                return $this->site;
            else
                return 'http://'.$this->site;
        }
    }
}

