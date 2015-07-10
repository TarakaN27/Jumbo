<?php

use yii\db\Schema;
use yii\db\Migration;

class m150708_143013_create_table_requisits extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cuser_requisites}}', [
            'id' => Schema::TYPE_PK,
            'corp_name' => Schema::TYPE_STRING ,
            'j_fname' => Schema::TYPE_STRING ,
            'j_lname' => Schema::TYPE_STRING ,
            'j_mname' => Schema::TYPE_STRING ,
            'j_post' => Schema::TYPE_STRING ,
            'j_doc' => Schema::TYPE_STRING ,
            'reg_date' => Schema::TYPE_DATE,
            'reg_number' => Schema::TYPE_STRING,
            'reg_auth' => Schema::TYPE_STRING,

            'ch_account' => Schema::TYPE_STRING,
            'b_name' => Schema::TYPE_STRING,
            'b_code' => Schema::TYPE_STRING,

            'j_address' => Schema::TYPE_TEXT ,
            'p_address' => Schema::TYPE_TEXT ,

            'c_fname' => Schema::TYPE_STRING ,
            'c_lname' => Schema::TYPE_STRING ,
            'c_mname' => Schema::TYPE_STRING ,
            'c_email' => Schema::TYPE_STRING,

            'c_phone' => Schema::TYPE_STRING,
            'c_fax' => Schema::TYPE_STRING ,

            'ynp' => Schema::TYPE_STRING,
            'okpo' => Schema::TYPE_STRING,
            'inn' => Schema::TYPE_STRING,
            'kpp' => Schema::TYPE_STRING,
            'ogrn' => Schema::TYPE_STRING,

            'created_at' => Schema::TYPE_INTEGER ,
            'updated_at' => Schema::TYPE_INTEGER ,
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%cuser_requisites}}');
    }

}
