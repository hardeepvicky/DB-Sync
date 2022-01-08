<?php
/**
 * Date Utility
 * 
 * 
 * @created    08-11-2016
 * @copyright  Copyright (C) 2016
 * @license    Proprietary
 * @author     Hardeep
 * @version    1.0
 */

class DateUtility 
{
    const DATE_FORMAT = 'Y-m-d';
    const DATETIME_FORMAT = 'Y-m-d H:i:s.u';

    const DATE_OUT_FORMAT = "d-m-Y";
    const DATETIME_OUT_FORMAT = 'd-m-Y h:i a';

    const YEARS = "years";
    const MONTHS = "months";
    const WEEKS = "weeks";
    const DAYS = "days";
    const HOURS = "hours";
    const MINTUES = "minutes";    
    const SECONDS = "seconds";

    /**
     * convert time string to standered date object 
     * @param String $date
     * @param String $timezone
     * @return DateTime object
     */
    protected static function getDateObj($date = "", $timezone = NULL)
    {
        if ($date)
        {
            $timestamp = strtotime($date);

            if ($timestamp == false || $timestamp == -1)
            {
                return false;
            }

            $date = new DateTime(date(self::DATETIME_FORMAT, $timestamp));
        }
        else
        {
            $date = new Datetime();
        }

        if ($timezone)
        {
            $date->setTimezone(new DateTimeZone($timezone));
        }

        return $date;
    }

    /**
     * Convert String Date to Specfic String date Format
     * If Date is given null then current date is formated
     * It does not change the timezone of date
     * 
     * @param String $date NULL
     * @param String $format NULL
     * @param String $timezone NULL
     * @return String date
     */
    public static function getDate($date = NULL, $format = NULL, $timezone = NULL)
    {
        $date = self::getDateObj($date, $timezone);

        if ($date == false)
        {
            return false;
        }

        $format = $format ? $format : self::DATETIME_FORMAT;

        if ($date)
        {
            return $date->format($format);
        }
        else
        {
            return $date->format($format);
        }
    }


    /**
     * 
     * compare dates and return the diffrence between them
     * 
     * 
     * @param String $start_date
     * @param String $end_date
     * @param int $type
     * @return String
     */
    public static function diff($start_date, $end_date, $type = "seconds")
    {
        $from_date = self::getDateObj($start_date);
        $to_date = self::getDateObj($end_date);

        if ($from_date == false || $to_date == false)
        {
            return false;
        }

        $diff = $to_date->diff($from_date);

        $days = $diff->format('%a');

        switch($type)
        {
            case self::YEARS:              
                $result = $diff->y;
            break;

            case self::MONTHS:              
                $result = ($diff->y > 0 ? $diff->y * 12 : 0)  + $diff->m;
            break;

            case self::WEEKS:              
                $result = round($days / 7);
            break;

            case self::DAYS:              
                $result = $days;
            break;

            case self::HOURS:
                $result = $days * 24;
            break;

            case self::MINTUES:
                $result = $days * 24 * 60;
            break;

            default:                
                $result = $days * 24 * 60 * 60;
            break;
        }

        $sign = $diff->format('%R');

        if ($sign == "-")
        {
            $result = $sign . $result;
        }

        return $result;
    }

    /**
     * compare two dates
     * 
     * if from_date is greater than to_date then return > 0
     * if not return < 0
     * 
     * @param type $first_date
     * @param type $second_date
     * @return int
     */
    public static function compare($first_date, $second_date)
    {
        $first_date = self::getDate($first_date, "U");
        $second_date = self::getDate($second_date, "U");

        if ($first_date == false || $second_date == false)
        {
            return false;
        }

        if ($first_date > $second_date)
        {
            return 1;
        }

        if ($first_date < $second_date)
        {
            return -1;
        }

        if ($first_date == $second_date)
        {
            return 0;
        }
    }

    /**
     * Add/Remove days/Months/Years/Weeks/Hours/Seconds in date
     * 
     * @param String $date
     * @param String $duration
     * @param String $duration_type
     * @param String $format
     * @return String
     */
    public static function change($date = "", $duration = "", $duration_type = "days", $format = NULL)
    {
        if (is_numeric($duration))
        {
            if ($duration > 0)
            {
                $duration = "+" . $duration;
            }
        }

        $duration .= " " . $duration_type;

        return self::getDate($date . " " . $duration, $format);
    }

    /**
     * return day list between two dates
     * 
     * @param String $start_date
     * @param String $end_date
     * @param String $key_format {n}, 'Y-m-d', 'd'
     * @param String $format 
     * @return type
     */
    public static function getDayListBetweenTwoDates($start_date, $end_date, $key_format = "{n}", $format = NULL, $hierarchy = FALSE)
    {
        $list = array();

        $diff_days = self::diff($start_date, $end_date, self::DAYS);

        while($diff_days >= 0)
        {
            $str = self::get($start_date, $format);

            $k = "";
            if ($key_format != "{n}")
            {
                $k = self::get($start_date, $key_format);
            }

            if ($hierarchy)
            {
                $year = self::get($start_date, "Y");
                $month = self::get($start_date, "m");

                if($k)
                {
                    $list[$year][$month][$k] = $str;
                }
                else
                {
                    $list[$year][$month][] = $str;
                }
            }
            else 
            {
                if($k)
                {
                    $list[$k] = $str;
                }
                else
                {
                    $list[] = $str;
                }
            }

            $start_date = self::change($start_date, 1);

            $diff_days--;
        }

        return $list;
    }
    
    /**
     * return month list between two dates
     * 
     * @param String $start_date
     * @param String $end_date
     * @return type
     */
    public static function getMonthListBetweenTwoDates($start_date, $end_date)
    {
        $list = array();

        $diff_days = self::diff($start_date, $end_date, self::DAYS);
        
        while($diff_days >= 0)
        {
            $year = self::get($start_date, "Y");
            $month = self::get($start_date, "m");

            $list[$year][$month] = $month;
                
            $start_date = self::change($start_date, 1);

            $diff_days--;
        }

        return $list;
    }
}
