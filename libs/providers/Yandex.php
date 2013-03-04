<?php

require_once $sRootPath.'/libs/providers/WeatherProvider.php';

class Yandex extends WeatherProvider
{
    protected $sUrl = 'http://pogoda.yandex.by/minsk/details';
    
    protected function parsePage()
    {
        $aWeatherData = array();
        
        if ($this->iDebug) {
            $sPageContent = file_get_contents('samples/yandex_test.html');
        } else {
            $sPageContent = $this->getPageContent();
        }
    
        $oDocument = phpQuery::newDocumentHTML($sPageContent);
    
        $aTemperature = array();
        
        $oElements = $oDocument->find('.b-forecast-detailed__temp')->slice(0, 4);
        
        foreach ($oElements as $iIndex => $oElement) {
            $sTemperatureLine = pq($oElement)->html();
            
            $aMatches = array();
            if (!preg_match('/.*(\+)?(\d+).*(\+)?(\d+)/U', $sTemperatureLine, $aMatches)) {
                continue;
            }
            
            if ($aMatches[1] == '+') {
                $iTemperatureFirst = intval($aMatches[2]);
            } else {
                $iTemperatureFirst = -intval($aMatches[2]);
            }
            
            if ($aMatches[3] == '+') {
                $iTemperatureSecond = intval($aMatches[4]);
            } else {
                $iTemperatureSecond = -intval($aMatches[4]);
            }
            
            $fAvgTemperature = ($iTemperatureFirst + $iTemperatureSecond) / 2;
            
            //В данном случае $iIndex равен периоду
            $aWeatherData[self::TYPE_TEMPERATURE][$iIndex] = $fAvgTemperature;
        }
        
        return $aWeatherData;
    }
}