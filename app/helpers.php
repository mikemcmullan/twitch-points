<?php

/**
 * Check if we are in the api based on the host name.
 * @param $host
 *
 * @return bool
 */
function isApi($host)
{
    return substr($host, 0 , 3) === 'api';
}

/**
 * Display time in days and hours.
 *
 * @param $minutes
 *
 * @return string
 */
function presentTimeOnline($minutes)
{
    if ($minutes >= 1440) {
        $minutesInDay = 60 * 24;
        $days = floor($minutes / $minutesInDay);
        $hours = floor(floor($minutes - ($days * $minutesInDay)) / 60);

        $output =  $days . ' days';

        if ($hours > 0) {
            $output .= ', ' . $hours . ' hours';
        }

        return $output;
    }

    if ($minutes > 60 && $minutes < 1440) {
        $hours = floor($minutes / 60);

        return $hours . ' hours';
    }

    return $minutes . ' minutes';
}
