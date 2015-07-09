<?php

namespace common\models;

use Yii;

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
 * @property string $okpo
 * @property string $inn
 * @property string $kpp
 * @property string $ogrn
 * @property integer $created_at
 * @property integer $updated_at
 */
class CUserRequisites extends AbstractActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cuser_requisites}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                 'corp_name', 'j_fname', 'j_lname', 'j_mname', 'j_post', 'j_doc',
                 'j_address', 'p_address', 'c_fname', 'c_lname', 'c_mname'
             ], 'required'],
            [['reg_date'], 'safe'],
            [['j_address', 'p_address'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [[
                 'corp_name', 'j_fname', 'j_lname', 'j_mname', 'j_post', 'j_doc',
                 'reg_number', 'reg_auth', 'ch_account', 'b_name', 'b_code',
                 'c_fname', 'c_lname', 'c_mname', 'c_email', 'c_phone', 'c_fax',
                 'ynp', 'okpo', 'inn', 'kpp', 'ogrn'
             ], 'string', 'max' => 255],
            ['c_email', 'email'],
            ['reg_date', 'date', 'format' => 'yyyy-m-d'],
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
            'okpo' => Yii::t('app/users', 'Okpo'),
            'inn' => Yii::t('app/users', 'Inn'),
            'kpp' => Yii::t('app/users', 'Kpp'),
            'ogrn' => Yii::t('app/users', 'Ogrn'),
            'created_at' => Yii::t('app/users', 'Created At'),
            'updated_at' => Yii::t('app/users', 'Updated At'),
        ];
    }
}
