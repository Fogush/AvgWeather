<?php

require_once '/libs/providers/WeatherProvider.php';

class Pogoda extends WeatherProvider
{
    
    protected $sUrl = 'http://pda.pogoda.by';
    
    protected function parsePage()
    {
        $aWeatherData = array();
        
        if ($this->iDebug) {
            $sPageContent = file_get_contents('samples/pogoda_test.html');
        } else {
            $sPageContent = $this->getPageContent();
        }
    
        $oDocument = phpQuery::newDocumentHTML($sPageContent, 'windows-1251');
    
        $oElements = $oDocument->find('h2:first ~ p')->slice(4, 9);
        
        foreach ($oElements as $iIndex => $oElement) {
            
            $oPrevElement = pq($oElement)->prev();
            
            if ($oPrevElement->get(0)->tagName == 'p') {
                break;
            }
            
            //Иначе предыдущий элемент это <b></b> - заголовок периода суток (ночь, утро, день, вечер)
            $sPeriodLabel = preg_replace('/[^\w]/', '', $oPrevElement->find('u')->html());
            $sPeriodLabel = mb_convert_encoding($sPeriodLabel, 'utf-8', 'windows-1251');
            $iPeriodIndex = array_search($sPeriodLabel, self::$aDayPeriods);
            
            //то есть это элементы <p></p>
            $sCurrentContent = pq($oElement)->html();
            $sCurrentContent = mb_convert_encoding($sCurrentContent, 'utf-8', 'windows-1251');
            preg_match('/Температура воздуха ((\-|\+)\d{1,2})\.\.((\-|\+)\d{1,2})/', $sCurrentContent, $aMatches);
            
            if (empty($aMatches)) {    //"Температура воздуха около 0°C"
                $aMatches[1] = 0;
                $aMatches[3] = 0;
            }
            
            $iTemperatureFirst = intval($aMatches[1]);
            $iTemperatureSecond = intval($aMatches[3]);
            $fAvgTemperature = ($iTemperatureFirst + $iTemperatureSecond) / 2;
            
            $aWeatherData[self::TYPE_TEMPERATURE][$iPeriodIndex] = $fAvgTemperature;
            
            if (mb_strpos($sCurrentContent, "дожд") !== false) {
                $aWeatherData[self::TYPE_RAIN][$iPeriodIndex] = 1;
            } else {
                $aWeatherData[self::TYPE_RAIN][$iPeriodIndex] = 0;
            }
            
        }
        
        return $aWeatherData;
    }
}