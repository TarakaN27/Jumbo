<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 15.07.15
 */
namespace common\components\helpers;

use Yii;
use \yii\web\NotFoundHttpException;
use \yii\web\ForbiddenHttpException;

class CustomHelper {
    /**
     * Хелпер отдает документ на скачивание
     * @param $fileName
     * @param $realName
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public static function getDocument($fileName,$realName)
    {
        // если файла нет
        if (!file_exists($fileName)) {
            throw new NotFoundHttpException('File not found');
        }

        // получим размер файла
        $fsize = filesize($fileName);
        // дата модификации файла для кеширования
        $ftime = date("D, d M Y H:i:s T", filemtime($fileName));

        // смещение от начала файла
        $range = 0;
        // пробуем открыть
        $handle = @fopen($fileName, "rb");

        // если не удалось
        if (!$handle){
            throw new ForbiddenHttpException('Access denied');
        }

        // Если запрашивающий агент поддерживает докачку
        if (isset($_SERVER["HTTP_RANGE"]) && $_SERVER["HTTP_RANGE"]) {
            $range = $_SERVER["HTTP_RANGE"];
            $range = str_replace("bytes=", "", $range);
            $range = str_replace("-", "", $range);
            // смещаемся по файлу на нужное смещение
            if ($range) fseek($handle, $range);
        }

        // если есть смещение
        if ($range) {
            header("HTTP/1.1 206 Partial Content");
        } else {
            header("HTTP/1.1 200 OK");
        }

        header("Content-Disposition: attachment; filename=\"{$realName}\"");
        header("Last-Modified: {$ftime}");
        header("Content-Length: ".($fsize-$range));
        header("Accept-Ranges: bytes");
        header("Content-Range: bytes {$range}-".($fsize - 1)."/".$fsize);

        // подправляем под IE что б не умничал
        if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
            Header('Content-Type: application/force-download');
        else
            Header('Content-Type: application/octet-stream');

        while(!feof($handle)) {
            $buf = fread($handle,512);
            print($buf);
        }

        fclose($handle);
        Yii::$app->end(200);
    }

    /**
     * @return int
     * время до конца дня
     */
    public static function getTimeToMidnight()
    {
        return  86400 - 3600*date("H") - 60*date("i") - date("s");
    }
} 