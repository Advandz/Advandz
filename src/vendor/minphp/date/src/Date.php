<?php
namespace Minphp\Date;

use DateTimeZone;

/**
 * Provides methods useful in formatting dates and date timestamps.
 */
class Date
{

    const ATOM   = "Y-m-d\TH:i:sP";
    const COOKIE = "l, d-M-y H:i:s T";
    const ISO8601 = "Y-m-d\TH:i:sO";
    const RFC822 = "D, d M y H:i:s O";
    const RFC850 = "l, d-M-y H:i:s T";
    const RFC1036 = "D, d M y H:i:s O";
    const RFC1123 = "D, d M Y H:i:s O";
    const RFC2822 = "D, d M Y H:i:s O";
    const RFC3339 = "Y-m-d\TH:i:sP";
    const RSS = "D, d M Y H:i:s O";
    const W3C = "Y-m-d\TH:i:sP";

    /**
     * @var array Common date formats, predefined for PHP's date function, overwritable
     * by the constructor
     */
    private $formats = array(
        'date' => "F j, Y",
        'day' => "l, F j, Y",
        'month' => "F Y",
        'year' => "Y",
        'date_time' => "M d y g:i:s A",
    );

    private $timezone_from;
    private $timezone_to;

    /**
     * Constructs a new Date component using the given date formats in $formats.
     *
     * @param array $formats An array of key/value pairs of PHP date format strings with the following keys:
     * - date A date
     * - day A date with day reference
     * - month A month and year date
     * - year A year date only
     * - date_time A date time
     * @see Date::cast()
     */
    public function __construct(array $formats = null, $timezone_from = null, $timezone_to = null)
    {
        $this->setFormats($formats);
        $this->setTimezone($timezone_from, $timezone_to);
    }

    /**
     * Set the current time zone to be used during date calculations
     *
     * @param string $from The timezone to convert from
     * @param string $to The timezone to convert to
     * @return this
     */
    public function setTimezone($from = null, $to = null)
    {
        $this->timezone_from = $from;
        $this->timezone_to = $to;
        return $this;
    }

    /**
     * Sets the formats to use as the pre-defined types.
     *
     * @param array $formats An array of key/value pairs of PHP date format strings with the following keys:
     * - date A date
     * - day A date with day reference
     * - month A month and year date
     * - year A year date only
     * - date_time A date time
     * @return this
     */
    public function setFormats(array $formats = null)
    {
        $this->formats = array_merge($this->formats, (array)$formats);
        return $this;
    }

    /**
     * Format a date using one of the date formats provided to the constructor,
     * or predefined in this class.
     *
     * @param string $date The date string to cast into another format, also handles Unix time stamps
     * @param string $format A predefined date format in Date::$formats, a Date constant, or a date string.
     * @return string The date formatted using the given format rule, null on error
     */
    public function cast($date, $format = "date")
    {
        return $this->format((isset($this->formats[$format]) ? $this->formats[$format] : $format), $date);
    }

    /**
     * Format two dates to represent a range between them.
     *
     * @param string $start The start date
     * @param string $end The end date
     * @param array $formats An array of 'start' and 'end' indexes, supplying
     *  options for 'same_day', 'same_month', 'same_year', and 'other' formats.
     *  Select indexes can be supplied to overwrite only specific rules.
     * @return string The date range, null on error
     */
    public function dateRange($start, $end, $formats = null)
    {
        $default_formats = array(
            'start'=>array(
                'same_day'=>"F j, Y",
                'same_month'=>"F j-",
                'same_year'=>"F j - ",
                'other' => "F j, Y - "
            ),
            'end'=>array(
                'same_day'=>"",
                'same_month'=>"j, Y",
                'same_year'=>"F j, Y",
                'other' => "F j, Y"
            )
        );

        $formats = $this->mergeArrays($default_formats, (array)$formats);

        $s_date = date("Ymd", $this->toTime($start));
        $e_date = date("Ymd", $this->toTime($end));

        if ($s_date == $e_date) {
            // Same day
            return $this->format($formats['start']['same_day'], $start)
                . $this->format($formats['end']['same_day'], $end);
        } elseif (substr($s_date, 0, 6) == substr($e_date, 0, 6)) {
            // Same month
            return $this->format($formats['start']['same_month'], $start)
                . $this->format($formats['end']['same_month'], $end);
        } elseif (substr($s_date, 0, 4) == substr($e_date, 0, 4)) {
            // Same year
            return $this->format($formats['start']['same_year'], $start)
                . $this->format($formats['end']['same_year'], $end);
        } else {
            // Other
            return $this->format($formats['start']['other'], $start) . $this->format($formats['end']['other'], $end);
        }
    }

    /**
     * Format a date using the supply date string
     *
     * @param string $format The format to use
     * @param string $date The date to format
     * @return string The formatted date
     */
    public function format($format, $date = null)
    {
        // Use current date/time if date is not given
        if ($date === null) {
            $date = time();
        }
        if ($date != "" && $format != "") {
            if ($this->timezone_from !== null) {
                $prev_timezone = $this->setDefaultTimezone($this->timezone_from);
            }

            $time = $this->toTime($date);

            // Set the appropriate timezone
            if ($this->timezone_to !== null) {
                $this->setDefaultTimezone($this->timezone_to);
            }

            // Format the date
            $formatted_date = date($format, $time);

            // Restore the timezone value
            if (isset($prev_timezone)) {
                $this->setDefaultTimezone($prev_timezone);
            }
            return $formatted_date;
        }
        return null;
    }

    /**
     * Convert a date string to Unix time
     *
     * @param string A date string
     * @return int The Unix timestamp of the given date
     */
    public function toTime($date)
    {
        if (!is_numeric($date)) {
            $date = strtotime($date);
        }
        return $date;
    }

    /**
     * Returns an array of months in key/value pairs
     *
     * @param int $start The start month (1 = Jan, 12 = Dec)
     * @param int $end The end month
     * @param string $key_format The format for the key
     * @param string $value_format The format for the value
     * @return array An array of key/value pairs representing the range of months
     */
    public function getMonths($start = 1, $end = 12, $key_format = "m", $value_format = "F")
    {
        $months = array();
        for ($i=$start; $i<=$end; $i++) {
            $time = strtotime(date("Y-" . $i . "-01"));
            $months[date($key_format, $time)] = date($value_format, $time);
        }
        return $months;
    }

    /**
     * Returns an array of keys in key/value pairs
     *
     * @param int $start The 4-digit start year
     * @param int $end The 4-digit end year
     * @param string $key_format The format for the key
     * @param string $value_format The format for the value
     * @return array An array of key/value pairs representing the range of years
     */
    public function getYears($start, $end, $key_format = "y", $value_format = "Y")
    {
        $years = array();
        for ($i=$start; $i<=$end; $i++) {
            $time = strtotime(date($i . "-01-01"));
            $years[date($key_format, $time)] = date($value_format, $time);
        }
        return $years;
    }

    /**
     * Sets the default timezone
     *
     * @param string $timezone The default timezone to set for this instance
     */
    private function setDefaultTimezone($timezone)
    {
        $cur_timezone = null;
        if (function_exists("date_default_timezone_set")) {
            $cur_timezone = date_default_timezone_get();
            date_default_timezone_set($timezone);
        }
        return $cur_timezone;
    }

    /**
     * Retrieve all timezones or those for a specific country
     *
     * @param string $country The ISO 3166-1 2-character country code to fetch
     *  timezone information for (PHP 5.3 or greater)
     * @return array An array of all timezones (or those for the given country)
     *  indexed by primary locale, then numerically indexed for each timezone in that locale
     */
    public function getTimezones($country = null)
    {

        // Hold the array of timezone data
        $tz_data = array();

        $accepted_zones = array_flip(array(
            "Africa",
            "America",
            "Antarctica",
            "Arctic",
            "Asia",
            "Atlantic",
            "Australia",
            "Europe",
            "Indian",
            "Pacific",
            "UTC"
        ));

        if ($country) {
            $listing = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country);
        } else {
            $listing = DateTimeZone::listIdentifiers();
        }
        $num_listings = count($listing);

        // Associate each timezone identifier with its meta data
        for ($i=0; $i<$num_listings; $i++) {
            // Convert timezone identifier to timezone array
            $zone = new DateTimeZone($listing[$i]);
            $zone_info = $zone->getTransitions(time(), time());

            $timezone = $this->timezoneFromIdentifier($zone_info[0], $listing[$i]);
            $primary_zone_name = isset($timezone['zone'][0]) ? $timezone['zone'][0] : false;

            // Only allow accepted zones into the listing
            if (!isset($accepted_zones[$primary_zone_name])) {
                continue;
            }

            // Set the timezone to appear under its primary location
            $tz_data[$primary_zone_name][] = $timezone;
        }

        // Sort each section by UTC offset
        foreach ($tz_data as $zone => $data) {
            $this->insertionSort($tz_data[$zone], "offset");
        }

        return $tz_data;
    }

    /**
     * Constructs the timezone meta data using the given timezone and its identifier
     *
     * @param arary $zone_info An array of timezone information for the given identifier including:
     * - ts Current time stamp
     * - time Date/Time
     * - offset The UTC offset in seconds
     * - isdst Whether or this timezone is observing daylight savings (true/false)
     * - abbr The abbreviation for this timezone
     * @param string $identifier The timezone identifier
     * @return An array of timezone meta data including:
     * - id The timezone identifier
     * - name The locale name
     * - offset The offset from UTC in seconds
     * - utc A string containg the HH::MM UTC offset
     * - zone An array of zone names
     */
    private function timezoneFromIdentifier(&$zone_info, $identifier)
    {
        $zone = explode('/', $identifier, 2);

        $offset = isset($zone_info['offset']) ? $zone_info['offset'] : 0; // offset

        $offset_h = str_pad(abs((int)($offset/3600)), 2, '0', STR_PAD_LEFT); // offset in hours
        $offset_h = ($offset < 0 ? true : false ? "-" : "+") . $offset_h;
        $offset_m = str_pad(abs((int)(($offset/60)%60)), 2, '0', STR_PAD_LEFT); // offset in mins

        $timezone = array(
            'id' => $identifier,
            'name' => str_replace('_', ' ', isset($zone[1]) ? $zone[1] : $zone[0]),
            'offset' => (int)$offset,
            'utc' => $offset_h . ":" . $offset_m . (isset($zone_info['isdst']) && $zone_info['isdst'] ? " DST" : ""),
            'zone' => $zone
        );

        return $timezone;
    }

    /**
     * Insertion sort algorithm for numerically indexed arrays with string indexed elements.
     * Will sort items in $array based on values in the $key index. Sorts arrays in place.
     *
     * @param array $array The array to sort
     * @param string $key The index to sort on
     */
    private static function insertionSort(&$array, $key)
    {
        for ($i=1; $i<count($array); $i++) {
            self::insertSortInsert($array, $i, $array[$i], $key);
        }
    }

    /**
     * Insertion sort in inserter. Performs comparison and insertion for the given
     * element within the given array.
     *
     * @param array $array The array to sort
     * @param int $length The length to sort through
     * @param array $element The element of $array to insert somewhere in $array
     * @param string $key The index to compare
     */
    private static function insertSortInsert(&$array, $length, $element, $key)
    {
        $i = $length-1;
        for (; $i >= 0 && ($array[$i][$key] > $element[$key]); $i--) {
            $array[$i+1] = $array[$i];
        }
        $array[$i+1] = $element;
    }

    /**
     * Extends one array using another to overwrite existing values. Recursively merges
     * data.
     *
     * @param array $arr1 The array (default) to be merged into
     * @param array $arr2 The array to merge into $arr1
     * @return array The merged arrays
     */
    private function mergeArrays(array $arr1, array $arr2)
    {

        foreach ($arr2 as $key => $value) {
            if (array_key_exists($key, $arr1) && is_array($value)) {
                $arr1[$key] = $this->mergeArrays($arr1[$key], $arr2[$key]);
            } else {
                $arr1[$key] = $value;
            }
        }
        return $arr1;
    }
}
