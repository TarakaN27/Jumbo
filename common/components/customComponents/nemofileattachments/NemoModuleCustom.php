<?php
/**
 * Created by PhpStorm.
 * User: zhenya
 * Date: 11.5.16
 * Time: 11.06
 */

namespace common\components\customComponents\nemofileattachments;


use nemmo\attachments\Module;

class NemoModuleCustom extends Module
{
    /**
     * переопредеим метод, чтобы можно было самим указывать shortName
     * @param $obj
     * @return mixed
     */
    public function getShortClass($obj)
    {
        if(method_exists($obj,'getShortClassCustom'))
            return $obj->getShortClassCustom();
        $className = get_class($obj);
        if (preg_match('@\\\\([\w]+)$@', $className, $matches)) {
            $className = $matches[1];
        }
        return $className;
    }

}