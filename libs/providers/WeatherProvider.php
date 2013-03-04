<?php

abstract class WeatherProvider
{
    const TYPE_TEMPERATURE = 0;
    const TYPE_RAIN = 1;
    
    const PERIOD_NIGHT = 0;
    const PERIOD_MORNING = 1;
    const PERIOD_DAY = 2;
    const PERIOD_EVENING = 3;
    
    const LABEL_NIGHT = 'ночь';
    const LABEL_MORNING = 'утро';
    const LABEL_DAY = 'день';
    const LABEL_EVENING = 'вечер';
    
    public static $aDayPeriods = array(
        self::PERIOD_NIGHT => self::LABEL_NIGHT,
        self::PERIOD_MORNING => self::LABEL_MORNING,
        self::PERIOD_DAY => self::LABEL_DAY,
        self::PERIOD_EVENING => self::LABEL_EVENING,
    );
    protected $sUrl = '';
    protected $sPageContent = '';
    protected $aWeatherData = array();
    protected $iDebug = false;
    
    public function getUrl()
    {
        return $this->sUrl;
    }
    
    public function setUrl($sUrl)
    {
        $this->sUrl = $sUrl;
    }
    
    public function __construct($iDebug = false) 
    {
        $this->iDebug = $iDebug;
    }
    
    public static function getPeriodLabel($iIndex) 
    {
        return (isset(self::$aDayPeriods[$iIndex]) ? self::$aDayPeriods[$iIndex] : false); 
    }
    
    
    protected function getPageContent()
    {
        if (!$this->sUrl) {
            throw new Exception("URL is not specified");
        }
        
        if ($this->sPageContent) {
            return $this->sPageContent;
        }
        
        $this->sPageContent = file_get_contents($this->sUrl);
        
        //$this->sPageContent может быть false
        return $this->sPageContent;
    }

    protected function parsePage()
    {
        //Этот код нужно копировать в переопределяемые функции
        $sPageContent = $this->getPageContent();
    }
    
    protected function getWeatherData()
    {
        if ($this->aWeatherData) {
            return $this->aWeatherData;
        }
        
        $this->aWeatherData = $this->parsePage();
        
        return $this->aWeatherData;
        
    }
    
    public function getTemperature()
    {
        $aWeatherData = $this->getWeatherData();
        
        if (isset($aWeatherData[self::TYPE_TEMPERATURE]) && $aWeatherData[self::TYPE_TEMPERATURE]) {
            return $aWeatherData[self::TYPE_TEMPERATURE];
        } else {
            return false;
        }
    }
    
    public function getRain()
    {
        $aWeatherData = $this->getWeatherData();
    
        if (isset($aWeatherData[self::TYPE_RAIN]) && $aWeatherData[self::TYPE_RAIN]) {
            return $aWeatherData[self::TYPE_RAIN];
        } else {
            return false;
        }
    }
}