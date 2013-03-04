<?php

class Utils
{
    private static $aDays = array(
        0 => array('ВС', 'Воскресенье'),
        1 => array('ПН', 'Понедельник'),
        2 => array('ВТ', 'Вторник'),
        3 => array('СР', 'Среда'),
        4 => array('ЧТ', 'Четверг'),
        5 => array('ПТ', 'Пятница'),
        6 => array('СБ', 'Суббота'),
    );
    
    private static $aMonths = array(
        1 => 'Январь',
        2 => 'Февраль',
        3 => 'Март',
        4 => 'Апрель',
        5 => 'Май',
        6 => 'Июнь',
        7 => 'Июль',
        8 => 'Август',
        9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12 => 'Декабрь',
    );

    public static function getDayShortName($iNumDay) 
    {
        return self::$aDays[$iNumDay][0];
    }

    public static function getDayFullname($iNumDay) 
    {
        return self::$aDays[$iNumDay][1];
    }

    public static function getMonthName($iNumMonth) 
    {
        return self::$aMonths[$iNumMonth];
    }

    public static function inGetPost($name, $default = false) 
    {
        return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $default); 
    }
    
    public static function inGetPostCookie($name, $default = false) 
    {
        return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : (isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default)); 
    }
    
    public static function deb()
    {
        echo "<pre>";
        $aArgs = func_get_args();
        foreach ($aArgs as $arg)
        {
            if ( is_array($arg) || is_object($arg) ) {
                print_r($arg); 
                echo "\n";
            } else {
                var_dump($arg);
            }
        }
        echo "</pre>";
    }
    
    public static function strToTimestamp($date, $format)
    {
        $aDateParts = strptime($date, $format);
        if ( !$aDateParts ) {
            return false;
        }
        if ( $aDateParts['tm_year'] < 1900 ) {
            $aDateParts['tm_year'] += 1900;
        }
        
        return mktime($aDateParts['tm_hour'], $aDateParts['tm_min'], $aDateParts['tm_sec'], 
                      $aDateParts['tm_mon'] + 1, $aDateParts['tm_mday'], $aDateParts['tm_year']);
    }
    
    public static function enableDebug()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        ini_set('output_buffering', 0);
        error_reporting(E_ALL);
        restore_error_handler();
    }
    
    public static function disableDebug()
    {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0);
    }
}

function deb()
{
    echo "<pre>";
    $aArgs = func_get_args();
    foreach ($aArgs as $arg)
    {
        if ( is_array($arg) || is_object($arg) ) {
            print_r($arg); 
            echo "\n";
        } else {
            var_dump($arg);
        }
    }
    echo "</pre>";
}

if(!function_exists('strptime')) {

    function strptime($date, $format)
    {
        $dateTime = array(
            'tm_sec' => 0,
            'tm_min' => 0,
            'tm_hour' => 0,
            'tm_mday' => 0,
            'tm_mon' => 0,
            'tm_year' => 0,
            'unparsed' => ''
        );

        if (!($dateArr = strToDate($date, $format)))
        {
            return false;
        }

        foreach ($dateArr as $key => $val)
        {
            switch($key) {
                case 'd':
                case 'j': $dateTime['tm_mday'] = intval($val); break;
                case 'D': $dateTime['tm_mday'] = intval(date('j', $val)); break;

                case 'm':
                case 'n': $dateTime['tm_mon'] = max(0, intval($val) - 1); break;

                case 'Y': $dateTime['tm_year'] = intval($val); break;
                case 'y': $dateTime['tm_year'] = intval($val)+2000; break;

                case 'G':
                case 'g':
                case 'H':
                case 'h': $dateTime['tm_hour'] = intval($val); break;

                case 'M': //$dateTime['tm_mon'] = intval(date('n', $val)); break;
                case 'i': $dateTime['tm_min'] = intval($val); break;

                case 'S': $dateTime['tm_sec'] = intval($val); break;

                default: $dateTime['unparsed'] .= $val;
            }
        }

        return $dateTime;
    };

    function strToDate($date, $format) {
        $search = array('%d', '%D', '%j', // day
                        '%m', '%n', // month
                        '%Y', '%y', // year
                        '%G', '%g',
                        '%H', '%h', // hour
                        '%i', '%M', //minute
                        '%S');
        $replace = array('(\d{2})', '(\w{3})', '(\d{1,2})', //day
                         '(\d{2})', '(\d{1,2})', // month
                         '(\d{4})', '(\d{2})', // year
                         '(\d{1,2})', '(\d{1,2})', '(\d{2})', '(\d{2})', // hour
                         '(\d{2})',  '(\d{2})', // minute
                        '(\d{2})');

        $pattern = str_replace($search, $replace, $format);

        if (!preg_match("#$pattern(.*)#", $date, $matches))
        {
            return false;
        }

        $dp = $matches;

        if (!preg_match_all('#%(\w)#', $format, $matches))
        {
            return false;
        }

        $id = $matches['1'];

        $ret = array();

        if (count($dp) != count($id) + 1)
        {
            $ret['unparsed'] = join('', array_slice($dp, count($id) + 1 - count($dp)));
        }

        for ($i = 0, $j = count($id); $i < $j; $i++)
        {
            $ret[$id[$i]] = $dp[$i + 1];
        }

        return $ret;
    };
}
