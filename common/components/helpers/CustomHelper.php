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

    /**
     * Unixtimestamp начала месяца
     * @param null $time
     * @return int
     */
    public static function getBeginMonthTime($time = NULL)
    {
        if(is_null($time))
            $time = time();
        return strtotime('1 '.date('M',$time).' '.date('Y',$time));
    }

    /**
     * Unixtimestamp конец месяца
     * @param null $time
     * @return string
     */
    public static function getEndMonthTime($time = NULL)
    {
        if(is_null($time))
            $time = time();
        return strtotime('23:59:59 '.date('t',$time).'-'.date('M',$time).'-'.date('Y',$time));
    }

    /**
     * Unixtimestamp начала дня
     * @param null $time
     * @return int
     */
    public static function getBeginDayTime($time = NULL)
    {
        if(is_null($time))
            $time = time();

        return  strtotime(date('d',$time).' '.date('M',$time).' '.date('Y',$time));
    }

    /**
     * @param null $time
     * @return int
     */
    public static function getEndDayTime($time = NULL)
    {
        if(is_null($time))
            $time = time();

        return strtotime('23:59:59 '.date('d',$time).'-'.date('M',$time).'-'.date('Y',$time));
    }

    /**
     * Переводч целого числа в строку(прописью)
     * @param $num
     * @return string
     * @link http://xn----7sbfbqq4deedd2d1bu.xn--p1ai/%D0%A7%D0%B8%D1%81%D0%BB%D0%BE-%D0%BF%D1%80%D0%BE%D0%BF%D0%B8%D1%81%D1%8C%D1%8E/
     */
    public static function numPropis($num){ // $num - цело число
        # Все варианты написания чисел прописью от 0 до 999 скомпонуем в один небольшой массив
        $m=array(
            array('ноль'),
            array('-','один','два','три','четыре','пять','шесть','семь','восемь','девять'),
            array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать','пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать'),
            array('-','-','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
            array('-','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот'),
            array('-','одна','две')
        );

        # Все варианты написания разрядов прописью скомпануем в один небольшой массив
        $r=array(
            array('...ллион','','а','ов'), // используется для всех неизвестно больших разрядов
            array('тысяч','а','и',''),
            array('миллион','','а','ов'),
            array('миллиард','','а','ов'),
            array('триллион','','а','ов'),
            array('квадриллион','','а','ов'),
            array('квинтиллион','','а','ов')
            // ,array(... список можно продолжить
        );

        if($num==0)return$m[0][0]; # Если число ноль, сразу сообщить об этом и выйти
        $o=array(); # Сюда записываем все получаемые результаты преобразования

        # Разложим исходное число на несколько трехзначных чисел и каждое полученное такое число обработаем отдельно
        foreach(array_reverse(str_split(str_pad($num,ceil(strlen($num)/3)*3,'0',STR_PAD_LEFT),3))as$k=>$p){
            $o[$k]=array();

            # Алгоритм, преобразующий трехзначное число в строку прописью
            foreach($n=str_split($p)as$kk=>$pp)
                if(!$pp)continue;else
                    switch($kk){
                        case 0:$o[$k][]=$m[4][$pp];break;
                        case 1:if($pp==1){$o[$k][]=$m[2][$n[2]];break 2;}else$o[$k][]=$m[3][$pp];break;
                        case 2:if(($k==1)&&($pp<=2))$o[$k][]=$m[5][$pp];else$o[$k][]=$m[1][$pp];break;
                    }$p*=1;if(!$r[$k])$r[$k]=reset($r);

            # Алгоритм, добавляющий разряд, учитывающий окончание руского языка
            if($p&&$k)switch(true){
                case preg_match("/^[1]$|^\\d*[0,2-9][1]$/",$p):$o[$k][]=$r[$k][0].$r[$k][1];break;
                case preg_match("/^[2-4]$|\\d*[0,2-9][2-4]$/",$p):$o[$k][]=$r[$k][0].$r[$k][2];break;
                default:$o[$k][]=$r[$k][0].$r[$k][3];break;
            }$o[$k]=implode(' ',$o[$k]);
        }

        return implode(' ',array_reverse($o));
    }

    /**
     * Скрипт склонения существительных после числительных
     * $ruCi=[
     *       'year'=>['','лет','год','года'],
     *       'rub'=>['руб','лей','ль','ля']
     *      ];
     *  echo ci(94,$ruCi['year']));
     *  echo ci(14,$ruCi['rub']);
     *
     * первое значение это неизменная часть, которая остается одинаковой в любой форме склонения,
     * а остальные три всего лишь окончания этого значения, которые относятся группе 0, 1 и 2 соответственно.
     * В некоторых случаях, например, как с Годами, общей неизменной части нет, поэтому первое значение пустое,
     * а остальные значения указаны полностью.
     * @param $n
     * @param $c
     * @return string
     */
    public static function ci($n,$c){
        return $c[0].((preg_match("/^[0,2-9]?[1]$/",$n))?$c[2]:((preg_match("/^[0,2-9]?[2-4]$/",$n))?$c[3]:$c[1]));
    }

    /**
     * Cклонение рублей
     * @param $n
     * @return string
     */
    public static function ciRub($n)
    {
        $arRuCi = ['руб','лей','ль','ля'];
        return self::ci($n,$arRuCi);
    }
}