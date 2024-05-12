<?php
namespace Core\Helpers;
use Configs\GlobalConfig;
class TimeHelper{
    public static function get_current_time($format='Y-m-d H:i:s'){
        $jkt = new \DateTimeZone(GlobalConfig::getTimeZone());
        $date = new \DateTime();
        $date->setTimezone($jkt);
        return $date->format($format);
    }
	/*
	 * convert string time to utc string
	 * */
	public static function convert_to_utc($localtime, $timeZone){
		$UTC = new \DateTimeZone("UTC");
		$serverTZ = new \DateTimeZone($timeZone);
		$date = new \DateTime( $localtime, $serverTZ);
		$date->setTimezone( $UTC );
		return $date->format('Y-m-d H:i:s');
	}	
	public static function get_time_in_utc(){		
		$utc = new \DateTimeZone("UTC");
		$date = new \DateTime();
		$date->setTimezone( $utc );
		return $date->format('Y-m-d H:i:s');
	}
	/*
	 * compare date1 and date2
	 * return + if date2 > date1
	 * return - if date2 < date1 
	 * */
	public static function compare_date_time($date1,$date2){				
		$dateTime = new \DateTime($date1);
		$ret = $dateTime->diff( new \DateTime($date2))->format('%R');
		return $ret;		
	}
    /**
     * check whether current time is between two datetimes     
     * @param string $tzx timezone
     * @param string $dt1 datetime in yyyy-mm-dd hh:mm:ss
     * @param string $tz1 timezone1
     * @param string $dt2 datetime in yyyy-mm-dd hh:mm:ss
     * @param string $tz2 timezone2
     * return boolean
     */     
    public static function current_time_is_between($tzx, $dt1, $tz1, $dt2, $tz2){        
        $date = new \DateTime();
        $date->setTimezone(new \DateTimeZone($tzx));
        
        $contractDateBegin = new \DateTime($dt1, new \DateTimeZone($tz1));
        $contractDateEnd = new \DateTime($dt2, new \DateTimeZone($tz2));
        if (
            $date->getTimestamp() > $contractDateBegin->getTimestamp() && 
            $date->getTimestamp() < $contractDateEnd->getTimestamp()){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    /**
     * check whether 2 time periods overlaps https://stackoverflow.com/a/11098996
     * @param string $startTime1 datetime in yyyy-mm-dd hh:mm:ss
     * @param string $endTime2 datetime in yyyy-mm-dd hh:mm:ss
     * @param string tz1 timezone1
     * @param string $startTime2 datetime in yyyy-mm-dd hh:mm:ss
     * @param string $endTime2 datetime in yyyy-mm-dd hh:mm:ss
     * @param string tz2 timezone2 
     * return boolean
     */     
    public static function isOverlap($startTime1, $endTime1, $tz1, $startTime2, $endTime2, $tz2){
        $p1Start = new \DateTime($startTime1, new \DateTimeZone($tz1));
        $p1End = new \DateTime($endTime1, new \DateTimeZone($tz1));
        $from = $p1Start->getTimestamp();
        $to = $p1End->getTimestamp();
        
        $p2Start = new \DateTime($startTime2, new \DateTimeZone($tz2));
        $p2End = new \DateTime($endTime2, new \DateTimeZone($tz2));
        $from_compare = $p2Start->getTimestamp();
        $to_compare = $p2End->getTimestamp();
        
        //$ret = ($from >= $from_compare && $from <= $to_compare) ||
           //($from_compare >= $from && $from_compare <= $to);
           
        $ret = ($from >= $from_compare && $from < $to_compare) ||
           ($from_compare >= $from && $from_compare < $to);   
        return $ret;

    }
}

