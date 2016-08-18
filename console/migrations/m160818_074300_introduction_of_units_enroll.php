<?php

use yii\db\Migration;
use yii\db\Schema;

class m160818_074300_introduction_of_units_enroll extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%units_enroll}}', [
            'id' => $this->primaryKey()->notNull(),
            'name' => $this->char(100),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11),
        ], $tableOptions);
        $time = time();
        $this->batchInsert('{{%units_enroll}}',['id','name', 'created_at', 'updated_at'],[[1, 'Доллары США', $time, $time], [2,'Яндекс единица' ,$time,$time], [3, 'Белорусские рубли',$time,$time], [4, 'Российские рубли',$time,$time]]);

        $this->addColumn('{{%payment_condition}}', 'enroll_unit_id', $this->integer(4));
        $this->addForeignKey('FK-units_enroll','{{%payment_condition}}','enroll_unit_id','{{%units_enroll}}','id');

        $sql = 'Update {{%payment_condition}} c INNER JOIN {{%services}} s ON  c.service_id = s.id INNER JOIN {{%units_enroll}} u ON u.name = s.enroll_unit set c.enroll_unit_id = u.id';
        $this->execute($sql);

        $this->addColumn('{{%enrollment_request}}', 'enroll_unit_id', $this->integer(4));
        $sql = 'Update {{%enrollment_request}} r INNER JOIN {{%services}} s ON  r.service_id = s.id INNER JOIN {{%units_enroll}} u ON u.name = s.enroll_unit set r.enroll_unit_id = u.id';
        $this->execute($sql);
        $this->addColumn('{{%enrolls}}', 'enroll_unit_id', $this->integer(4));
        $sql = 'Update {{%enrolls}} r INNER JOIN {{%services}} s ON  r.service_id = s.id INNER JOIN {{%units_enroll}} u ON u.name = s.enroll_unit set r.enroll_unit_id = u.id';
        $this->execute($sql);
        $this->dropColumn('{{%services}}', 'enroll_unit');
    }

    public function down()
    {
        echo "m160818_074300_introduction_of_units_enroll cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
