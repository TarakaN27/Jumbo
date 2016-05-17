<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 17.5.16
 * Time: 15.42
 */

namespace common\components\tasks;


use common\models\CrmTaskRepeat;

class RecurringTask
{
    function run()
    {
        
        
        
    }


    /**
     * @return mixed
     */
    protected function getTasks()
    {
        return CrmTaskRepeat::find()
            ->where('start_date <= :time && end_date > :time')
            ->params([
                ':time' => time()
            ]);
    }




    
    
    

}