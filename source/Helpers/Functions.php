<?php

/**
 * PATHS
 */

/**
 * url
 *
 * @param  string $uri
 * @return string
 */
function url(string $uri = null): string
{
    if ($uri) {
        return SITE["root"] . "/" . ($uri[0] == "/" ? mb_substr($uri, 1) : $uri);
    }
    return SITE["root"];
}

/**
 * site
 *
 * @param  string $param
 * @return string
 */
function site(string $param = null): string
{
    if ($param && !empty(SITE[$param])) {
        return SITE[$param];
    }
    return SITE["root"];
}

/**
 * DATES
 */

/**
 * convertDateToSql
 *
 * @param  mixed $date
 * @return string
 */
function convertDateToSql($date): string
{
    if (count(explode("/", $date)) > 1) {
        return implode("-", array_reverse(explode("/", $date)));
    } elseif (count(explode("-", $date)) > 1) {
        return implode("-", array_reverse(explode("-", $date)));
    }
}

/**
 * convertDateToView
 *
 * @param  mixed $date
 * @return string
 */
function convertDateToView($date): string
{
    return implode("/", array_reverse(explode("-", $date)));
    // return implode("/", array_reverse(explode("-", date("m-d", strtotime($date)))));
}

/**
 * differenceBetweenDates
 *
 * @param  string $start
 * @param  string $end
 * @param  string $period month or year
 * @return int
 */
function differenceBetweenDates(string $start, string $end, string $period): int
{
    $startDate = new Datetime($start);
    $endDate = new DateTime($end);
    $interval = $startDate->diff($endDate);

    if ($period == "month") {
        return (($interval->y * 12) + $interval->m);
    }

    if ($period == "year") {
        return $interval->y;
    }
}

/**
 * monthShifter
 *
 * @param  string $dateFormat
 * @param  int $countPeriod
 * @param  string $period month or year
 * @return string
 */
function monthShifter(string $dateFormat, int $countPeriod, string $period): string
{
    $dateFormat = new DateTime($dateFormat);

    $oldDay = $dateFormat->format("d");
    $dateFormat->add(new DateInterval("P{$countPeriod}{$period}"));
    $newDay = $dateFormat->format("d");

    if ($oldDay != $newDay) {
        $dateFormat->sub(new DateInterval("P" . $newDay . "D"));
    }

    return $dateFormat->format("Y-m-d");
}
