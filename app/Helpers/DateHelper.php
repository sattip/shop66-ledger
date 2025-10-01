<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function getGreekMonthName($month): string
    {
        $months = [
            1 => 'Ιανουάριος',
            2 => 'Φεβρουάριος',
            3 => 'Μάρτιος',
            4 => 'Απρίλιος',
            5 => 'Μάιος',
            6 => 'Ιούνιος',
            7 => 'Ιούλιος',
            8 => 'Αύγουστος',
            9 => 'Σεπτέμβριος',
            10 => 'Οκτώβριος',
            11 => 'Νοέμβριος',
            12 => 'Δεκέμβριος',
        ];

        return $months[$month] ?? '';
    }

    public static function getGreekMonthNameShort($month): string
    {
        $months = [
            1 => 'Ιαν',
            2 => 'Φεβ',
            3 => 'Μαρ',
            4 => 'Απρ',
            5 => 'Μαι',
            6 => 'Ιουν',
            7 => 'Ιουλ',
            8 => 'Αυγ',
            9 => 'Σεπ',
            10 => 'Οκτ',
            11 => 'Νοε',
            12 => 'Δεκ',
        ];

        return $months[$month] ?? '';
    }

    public static function formatGreekDate($date, $format = 'd M Y'): string
    {
        if (! $date) {
            return '';
        }

        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);

        $day = $carbonDate->day;
        $month = self::getGreekMonthNameShort($carbonDate->month);
        $year = $carbonDate->year;

        if ($format === 'd M Y') {
            return "{$day} {$month} {$year}";
        } elseif ($format === 'd/m/Y') {
            return $carbonDate->format('d/m/Y');
        } elseif ($format === 'd F Y') {
            $monthFull = self::getGreekMonthName($carbonDate->month);

            return "{$day} {$monthFull} {$year}";
        }

        return $carbonDate->format($format);
    }
}
