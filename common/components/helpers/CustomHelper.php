<?php
/**
 * Created by PhpStorm.
 * Corp: Webmart Soft
 * User: E. Motuz
 * Date: 15.07.15
 */
namespace common\components\helpers;

use Yii;
use yii\base\InvalidParamException;
use \yii\web\NotFoundHttpException;
use \yii\web\ForbiddenHttpException;

class CustomHelper {
    /**
     * Хелпер отдает документ на скачивание
     * Замена Yii::$app()->response->getDocument()
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
     * Перевод целого числа в строку(прописью)
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
    * Склоняем словоформу
    * @ author runcore
    */
    public static function morph($n, $f1, $f2, $f5) {
        $n = abs(intval($n)) % 100;
        if ($n>10 && $n<20) return $f5;
        $n = $n % 10;
        if ($n>1 && $n<5) return $f2;
        if ($n==1) return $f1;
        return $f5;
    }

    /**
     * Возвращает сумму прописью с копеечками
     * @author runcore
     * @uses morph(...)
     */
    public  static function num2str($num,$units = NULL) {
        $nul='ноль';
        $ten=array(
            array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
            array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
        );
        $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
        $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
        $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
        if(is_null($units))
            $unit=[ // Units
                ['копейка' ,'копейки' ,'копеек',	1],
                ['рубль'   ,'рубля'   ,'рублей'    ,0],
                ['тысяча'  ,'тысячи'  ,'тысяч'     ,1],
                ['миллион' ,'миллиона','миллионов' ,0],
                ['миллиард','милиарда','миллиардов',0],
            ];
        else
            $unit = $units;
        //
        list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub)>0) {
            foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
                if (!intval($v)) continue;
                $uk = sizeof($unit)-$uk-1; // unit key
                $gender = $unit[$uk][3];
                list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
                else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                // units without rub & kop
                if ($uk>1) $out[]= self::morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
            } //foreach
        }
        else $out[] = $nul;
        $out[] = self::morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
        $out[] = self::cent2letter($kop).' '.self::morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
    }
    
    public static function cent2letter($num){
        if(strlen($num) == 1){
            $num .='0'.$num;
        }
        return $num;
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
        //$arRuCi = ['руб','лей','ль','ля'];
        //return self::ci($n,$arRuCi);
        return self::format_by_count($n,'рубль','рубля','рублей');
    }

    /**
     * Склонение существительных после числительных
     * @param $count
     * @param $form1
     * @param $form2
     * @param $form3
     * @return mixed
     */
    protected static function format_by_count($count, $form1, $form2, $form3)
    {
        $count = abs($count) % 100;
        $lcount = $count % 10;
        if ($count >= 11 && $count <= 19) return($form3);
        if ($lcount >= 2 && $lcount <= 4) return($form2);
        if ($lcount == 1) return($form1);
        return $form3;
    }

    /**
     * Расширение файла
     * @param $filename
     * @return string
     */
    public static  function getExtension($filename) {
        return substr(strrchr($filename, '.'), 1);
    }

    /**
     * Возвращаем НДС
     * @todo сделать глобальные настройки и изменять от туда
     * @return float
     */
    public static function getVat()
    {
        return 20;
    }

    /**
     * @param $amount
     * @return float
     */
    public static function getVatMountByAmount($amount,$customVat = NULL)
    {
        $vat = is_null($customVat) ? CustomHelper::getVat() : $customVat;
        return round($amount/(1+$vat/100),8);
    }

    /**
     * Подсветка слова $search в тексте $html
     * @param $search
     * @param $html
     * @return mixed
     */
    public static function highlight($search,$html)
    {
        return preg_replace("#($search)#iu", "<span style='color:#FF0000; background:#FFFF00;'>$1</span>", $html);
    }

    /**
     * Округление до 50 000 тысяч.
     * @param $amount
     * @return float
     */
    public static function roundBy50000UP($amount)
    {
        $t_p=ceil($amount/100000) * 100000;// 120 рублей ровно
        return ($t_p-$amount)>=50000?$t_p-50000:$t_p;
    }

    /**
     * Проверка что папка существет, если нет, то создает папку
     * @param $path
     * @param int $rights
     * @return bool
     */
    public static function isDirExist($path,$rights = 0777)
    {
        $path = \Yii::getAlias($path);
        //проверяем ,что папка существует
        if(is_dir($path))
            return TRUE;

        //создаем папку и назначаем права
        if(mkdir($path,$rights))
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Перевод кирилицы в латиницу. SLUG подобная строка.
     * @param $text
     * @param bool|TRUE $toLowCase
     * @return string
     */
    public static function cyrillicToLatin($text, $toLowCase = TRUE)
    {
        $matrix=array(
            "й"=>"i","ц"=>"c","у"=>"u","к"=>"k","е"=>"e","н"=>"n",
            "г"=>"g","ш"=>"sh","щ"=>"shch","з"=>"z","х"=>"h","ъ"=>"",
            "ф"=>"f","ы"=>"y","в"=>"v","а"=>"a","п"=>"p","р"=>"r",
            "о"=>"o","л"=>"l","д"=>"d","ж"=>"zh","э"=>"e","ё"=>"e",
            "я"=>"ya","ч"=>"ch","с"=>"s","м"=>"m","и"=>"i","т"=>"t",
            "ь"=>"","б"=>"b","ю"=>"yu",
            "Й"=>"I","Ц"=>"C","У"=>"U","К"=>"K","Е"=>"E","Н"=>"N",
            "Г"=>"G","Ш"=>"SH","Щ"=>"SHCH","З"=>"Z","Х"=>"X","Ъ"=>"",
            "Ф"=>"F","Ы"=>"Y","В"=>"V","А"=>"A","П"=>"P","Р"=>"R",
            "О"=>"O","Л"=>"L","Д"=>"D","Ж"=>"ZH","Э"=>"E","Ё"=>"E",
            "Я"=>"YA","Ч"=>"CH","С"=>"S","М"=>"M","И"=>"I","Т"=>"T",
            "Ь"=>"","Б"=>"B","Ю"=>"YU",
            "«"=>"","»"=>""," "=>"-",

            "\""=>"", "\."=>"", "–"=>"_", "\,"=>"", "\("=>"", "\)"=>"",
            "\?"=>"", "\!"=>"", "\:"=>"",

            '#' => '', '№' => '',' - '=>'_', '/'=>'_', '  '=>'_',
        );

        // Enforce the maximum component length
        $maxlength = 100;
        $text = implode(array_slice(explode('<br>',wordwrap(trim(strip_tags(html_entity_decode($text))),$maxlength,'<br>',false)),0,1));
        //$text = substr(, 0, $maxlength);

        foreach($matrix as $from=>$to)
            $text=mb_eregi_replace($from,$to,$text);

        // Optionally convert to lower case.
        if ($toLowCase)
        {
            $text = strtolower($text);
        }

        return $text;
    }

    /**
     * Форматирование времяени для отображения на странице задач
     * @param $time
     * @return string
     */
    public static function getFormatedTaskTime($time)
    {
        return sprintf('%02d:%02d', $time/3600, ($time % 3600)/60);
    }

    /**
     * Обрезаем строку до длины $len
     * @param $string
     * @param int $len
     * @return string
     */
    public static function cuttingString($string,$len = 200)
    {
        if(mb_strlen($string,'UTF-8') > $len) {
            $string = strip_tags($string);
            $string = mb_substr($string, 0, $len,'UTF-8');
            $string = rtrim($string, "!,.-");
            $string = mb_substr($string, 0, mb_strrpos($string, ' ','UTF-8'),'UTF-8');
            $string = $string."… ";
        }
        return  $string;
    }

    /**
     * Удаляем элемент массива по значению
     * @param array $array
     * @param $value
     * @return array
     */
    public static function removeArrayItemByValue(array $array,$value)
    {
        if(($key = array_search($value,$array)) !== FALSE){
            unset($array[$key]);
        }
        return $array;
    }

    /**
     * Первый символ заглавный
     * @param $string
     * @param string $e
     * @return bool|mixed|string
     */
    public static function my_ucfirst($string, $e ='utf-8') {
        if (function_exists('mb_strtoupper') && function_exists('mb_substr') && !empty($string)) {
            $string = mb_strtolower($string, $e);
            $upper = mb_strtoupper($string, $e);
            preg_match('#(.)#us', $upper, $matches);
            $string = $matches[1] . mb_substr($string, 1, mb_strlen($string, $e), $e);
        } else {
            $string = ucfirst($string);
        }

        return $string;
    }

    /**
     * Конвертируем csv в массив
     * @param string $filename
     * @param string $delimiter
     * @param bool|FALSE $convert
     * @return array|bool
     */
    public static function csv_to_array($filename='', $delimiter=',',$convert = FALSE)
    {
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 10000, $delimiter)) !== FALSE)
            {
                //if($convert)
                //    foreach($row as &$r)
                //        $r = iconv('windows-1251', 'utf-8',$r);

                foreach($row as &$r)
                    $r = trim($r);
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
                //$data[] = $row;
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * Проверяем строка -- это валидный json ?
     * @param $string
     * @return bool
     */
    public static function isValidJSON($string)
    {
        if (is_int($string) || is_float($string)) {
            return true;
        }

        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Определяем является ли время time текущим днем
     * @param $time
     * @return bool
     */
    public static function isCurrentDay($time)
    {
        if(!is_numeric($time))
            throw new InvalidParamException('time must be an integer');
        return date('d.m.Y',time()) == date('d.m.Y',time());
    }

    /**
     * Функция получает $time -- unixTimeStamp и кол-во месяцев на которое нужно увеличить или уменьшить текущую
     * @param $time
     * @param $numMonth
     * @return int
     */
    public static function getDateMinusNumMonth($time,$numMonth,$sign = '-')
    {
        $obDate = new \DateTime();
        $obDate->setTimestamp($time);
        $obDate->modify($sign.$numMonth.' month');
        return $obDate->getTimestamp();
    }

    /**
     * @param $time
     * @param $numYear
     * @param string $sign
     * @return int
     */
    public static function getDateMinusNumYear($time,$numYear,$sign = '-')
    {
        $obDate = new \DateTime();
        $obDate->setTimestamp($time);
        $obDate->modify($sign.$numYear.' years');
        return $obDate->getTimestamp();
    }

    public static function getDiffInMonth($data1, $data2){
        $dateTime1 = new \DateTime();
        $dateTime1->setTimestamp(strtotime(date("Y-m-02",$data1)));
        $dateTime2 = new \DateTime();
        $dateTime2->setTimestamp(strtotime(date("Y-m-01",$data2)));
        $interval = $dateTime1->diff($dateTime2);
        if($interval)
        unset($date1,$date2);
        return $interval->y*12 + $interval->m;    //вренем разницу в месяцах между двумя датами
    }

    /**
     * Получет массив, собирает массив где ключи переменная массива, а значение - само занчение массива
     * @param array $array
     * @param $attribute
     * @return array
     */
    public static function getMapObjectByAttribute(array $array,$attribute)
    {
        if(count($array) === 0)
            return [];

        $arResult = [];
        foreach ($array as $item)
        {
            $key = is_object($item) ? $item->$attribute : $item[$attribute];
            $arResult[$key] = $item;
        }
        return $arResult;
    }

    /**
     * @param array $array
     * @param $attribute
     * @return array
     */
    public static function getMapArrayObjectByAttribute(array $array,$attribute)
    {
        if(count($array) === 0)
            return [];

        $arResult = [];
        foreach ($array as $item)
        {
            $key = is_object($item) ? $item->$attribute : $item[$attribute];
            $arResult[$key][] = $item;
        }
        return $arResult;
    }

    /**
     * на сколько процентов отличается первое число от второго
     * @param $firstNumber
     * @param $secondNumber
     * @return float
     */
    public static function getDiffTwoNumbersAtPercent($oldNumber, $newNumber)
    {
        return (float)($newNumber-$oldNumber)/(float)$oldNumber*100;
    }
    



}