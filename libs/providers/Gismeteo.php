<?php

require_once '/libs/providers/WeatherProvider.php';

class Gismeteo extends WeatherProvider
{
    protected $sUrl = 'http://www.gismeteo.by/ajax/print/4248/short/';
    
    protected function getPageContent()
    {
        if (!$this->sUrl) {
            throw new Exception("URL is not specified");
        }
        
        if ($this->sPageContent) {
            return $this->sPageContent;
        }
    
        //Выполнить запрос CURL'ом
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->sUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //Иначе gismeteo ругается: "Sorry, print page aviable only from gismeteo web-site"  
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Referer: http://www.gismeteo.by/city/daily/4248/'));
    
        $sResult = curl_exec($curl);
    
        if ( !$sResult ) {
            throw new Exception("Error: ".curl_error($curl));
        } else {
            $this->sPageContent = $sResult;
        }
    
        curl_close($curl);
    
        //$this->sPageContent может быть false
        return $this->sPageContent;
    }
    
    protected function parsePage()
    {
        $aWeatherData = array();
        
        if ($this->iDebug) {
            $sPageContent = file_get_contents('samples/gismeteo_test.html');
        } else {
            $sPageContent = $this->getPageContent();
        }
        
        $oDocument = phpQuery::newDocumentHTML($sPageContent);
        
        $aTemperature = array();
        $aTemperature[WeatherProvider::PERIOD_NIGHT]   = pq($oDocument->find('.tc .c1:first'))->html();
        $aTemperature[WeatherProvider::PERIOD_MORNING] = pq($oDocument->find('.tc .c2:first'))->html();
        $aTemperature[WeatherProvider::PERIOD_DAY]     = pq($oDocument->find('.tc .c3:first'))->html();
        $aTemperature[WeatherProvider::PERIOD_EVENING] = pq($oDocument->find('.tc .c4:first'))->html();
        
        foreach ($aTemperature as $iPeriod => $sTemperature) {
            
            $aMatches = array();
            if (!preg_match('/^(.*)(\d+)/', $sTemperature, $aMatches)) {
                continue;
            }
            
            if ($aMatches[1] == '+') {
                $iTemperature = intval($aMatches[2]);
            } else {
                $iTemperature = -intval($aMatches[2]);
            }
            
            $aWeatherData[WeatherProvider::TYPE_TEMPERATURE][$iPeriod] = $iTemperature;
        }
        
        return $aWeatherData; 
    }
}