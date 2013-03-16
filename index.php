<?php

session_start();

mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");

date_default_timezone_set("Europe/Minsk");
$sRootPath = dirname(__FILE__);
set_include_path(get_include_path() . PATH_SEPARATOR . $sRootPath);

require_once $sRootPath.'/libs/Utils.php';
Utils::enableDebug();

require_once $sRootPath.'/libs/phpQuery/phpQuery/phpQuery.php';
require_once $sRootPath.'/libs/providers/Gismeteo.php';
require_once $sRootPath.'/libs/providers/Yandex.php';
require_once $sRootPath.'/libs/providers/Pogoda.php';

$fStartTime = microtime(true);

$iDebug = Utils::inGetPost('debug');

$oGismeteo = new Gismeteo($iDebug);
$aGismeteoTemperature = $oGismeteo->getTemperature();

// $oYandex = new Yandex($iDebug);
// $aYandexTemperature = $oYandex->getTemperature();

$oPogoda = new Pogoda($iDebug);
$aPogodaTemperature = $oPogoda->getTemperature();
$aPogodaRain = $oPogoda->getRain();

$aRainPeriods = array();

foreach (WeatherProvider::$aDayPeriods as $iPeriodIndex => $sPeriodLabel) {
    
    //pogoda.by не выдает результаты для уже прошедших периодов суток
    if (!isset($aPogodaTemperature[$iPeriodIndex])) {
        continue;
    }
    
    $aAvgTemperature[$iPeriodIndex] = 0;
    $aAvgTemperature[$iPeriodIndex] += $aGismeteoTemperature[$iPeriodIndex];
//     $aAvgTemperature[$iPeriodIndex] += $aYandexTemperature[$iPeriodIndex];
    $aAvgTemperature[$iPeriodIndex] += $aPogodaTemperature[$iPeriodIndex];
    $aAvgTemperature[$iPeriodIndex] = sprintf("%.2f", $aAvgTemperature[$iPeriodIndex] / 2);
    
    if ($aPogodaRain[$iPeriodIndex] == 1) {
        $aRainPeriods[$iPeriodIndex] = $sPeriodLabel;
    }
}

$bEmailError = false;
        
if ($aRainPeriods) {
    $aEmailData = array();
    $aEmailData['to'] = '375298588760@sms.mts.by';
    $aEmailData['subject'] = mb_convert_encoding('Today will rain', 'windows-1251', 'utf-8');
    $aEmailData['body'] = mb_convert_encoding('Day periods: '.join(', ', $aRainPeriods), 'windows-1251', 'utf-8');
    if (!Utils::sendEmail($aEmailData)) {
        $bEmailError = true;
    }
}

$fEndTime = microtime(true);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<title></title>
</head>
<body>
Сегодня: <?php echo date("d.m.Y")?><br />
<table>
  <tr>
    <td><strong>Период</strong></td>
    <td><strong>Средняя</strong></td>
    <td><strong>Gismeteo</strong></td>
<!--     <td><strong>Yandex</strong></td> -->
    <td><strong>Pogoda</strong></td>
    <td><i>Дождь</i></td>
    <td><strong></strong></td>
  </tr>
  <?php 
    foreach ($aAvgTemperature as $iPeriodIndex => $fTemperature) {
        echo "<tr>";
        echo "<td>".WeatherProvider::getPeriodLabel($iPeriodIndex).":</td>";
        echo "<td>".$fTemperature."°</td>";
        echo "<td>".$aGismeteoTemperature[$iPeriodIndex]."°</td>";
//         echo "<td>".$aYandexTemperature[$iPeriodIndex]."°</td>";
        echo "<td>".$aPogodaTemperature[$iPeriodIndex]."°</td>";
        echo "<td>".$aPogodaRain[$iPeriodIndex]."</td>";
        echo "<td></td>";
        echo "</tr>";
    }
    
    echo "</table><br />";
    
    if ($bEmailError) {
        echo "Произошла ошибка при отправке письма!<br />";
    }
    
    echo "Время выполнения: ".sprintf("%.3f", $fEndTime - $fStartTime);
?>
</body>
</html>