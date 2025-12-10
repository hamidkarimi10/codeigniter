<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 | jdf helper for CodeIgniter
 | Jalali Date Functions - Complete Version
*/

function div($a, $b) {
    return (int) ($a / $b);
}

function jdate($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa') {

    if ($timestamp == '') {
        $timestamp = time();
    }

    date_default_timezone_set($time_zone);

    $g_y = date('Y', $timestamp);
    $g_m = date('m', $timestamp);
    $g_d = date('d', $timestamp);

    list($j_y, $j_m, $j_d) = gregorian_to_jalali($g_y, $g_m, $g_d);

    $replace = array(
        "Y" => $j_y,
        "m" => str_pad($j_m, 2, '0', STR_PAD_LEFT),
        "d" => str_pad($j_d, 2, '0', STR_PAD_LEFT),
    );

    $result = $format;

    foreach ($replace as $key => $value) {
        $result = str_replace($key, $value, $result);
    }

    return $result;
}

function jstrftime($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa') {
    return jdate($format, $timestamp, $none, $time_zone, $tr_num);
}

function jmktime($h = '', $m = '', $s = '', $jm = '', $jd = '', $jy = '', $is_dst = -1) {
    if ($jy == '') return time();

    list($gy, $gm, $gd) = jalali_to_gregorian($jy, $jm, $jd);
    return mktime($h, $m, $s, $gm, $gd, $gy, $is_dst);
}

function jgetdate($timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa') {
    if ($timestamp == '') $timestamp = time();

    $gdate = getdate($timestamp);

    list($jy, $jm, $jd) = gregorian_to_jalali($gdate['year'], $gdate['mon'], $gdate['mday']);

    return array(
        "seconds" => $gdate["seconds"],
        "minutes" => $gdate["minutes"],
        "hours" => $gdate["hours"],
        "mday" => $jd,
        "wday" => $gdate["wday"],
        "mon" => $jm,
        "year" => $jy,
        "yday" => $gdate["yday"],
        "weekday" => $gdate["weekday"],
        "month" => $gdate["month"],
        0 => $timestamp
    );
}


function jcheckdate($jm, $jd, $jy) {
    return checkdate(...jalali_to_gregorian($jy, $jm, $jd));
}


/* ----------------------------
 | Conversion Functions
 ---------------------------- */

function gregorian_to_jalali($gy, $gm, $gd) {
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

    $gy = (int) $gy - 1600;
    $gm = (int) $gm - 1;
    $gd = (int) $gd - 1;

    $g_day_no = 365*$gy + div($gy+3,4) - div($gy+99,100) + div($gy+399,400);

    for ($i = 0; $i < $gm; ++$i)
        $g_day_no += $g_days_in_month[$i];

    if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)))
        ++$g_day_no;

    $g_day_no += $gd;

    $j_day_no = $g_day_no - 79;

    $j_np = div($j_day_no, 12053);
    $j_day_no %= 12053;

    $jy = 979 + 33*$j_np + 4*div($j_day_no,1461);

    $j_day_no %= 1461;

    if ($j_day_no >= 366) {
        $jy += div($j_day_no-366, 365);
        $j_day_no = ($j_day_no-366) % 365;
    }

    for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i)
        $j_day_no -= $j_days_in_month[$i];

    $jm = $i + 1;
    $jd = $j_day_no + 1;

    return array($jy, $jm, $jd);
}

function jalali_to_gregorian($jy, $jm, $jd) {
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    $jy -= 979;
    $jm -= 1;
    $jd -= 1;

    $j_day_no = 365*$jy + div($jy, 33)*8 + div(($jy%33)+3,4);

    for ($i = 0; $i < $jm; ++$i)
        $j_day_no += ($i < 6) ? 31 : 30;

    $j_day_no += $jd;

    $g_day_no = $j_day_no + 79;

    $gy = 1600 + 400*div($g_day_no,146097);
    $g_day_no %= 146097;

    $leap = 1;

    if ($g_day_no >= 36525) {
        $g_day_no--;
        $gy += 100*div($g_day_no,36524);
        $g_day_no %= 36524;

        if ($g_day_no >= 365)
            $g_day_no++;
        else
            $leap = 0;
    }

    $gy += 4*div($g_day_no,1461);
    $g_day_no %= 1461;

    if ($g_day_no >= 366) {
        $leap = 0;
        $g_day_no--;
        $gy += div($g_day_no,365);
        $g_day_no %= 365;
    }

    for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i==1 && $leap); $i++)
        $g_day_no -= $g_days_in_month[$i] + ($i==1 && $leap);

    $gm = $i + 1;
    $gd = $g_day_no + 1;

    return array($gy, $gm, $gd);
}
        // تبدیل تاریخ میلادی به شمسی برای نمایش در input
function gregorian_to_jalali_input($gregorian_date) {
    if (empty($gregorian_date)) return '';
    
    // بررسی فرمت تاریخ
    if (strpos($gregorian_date, '-') !== false) {
        $timestamp = strtotime($gregorian_date);
    } else {
        // اگر فرمت دیگر داشت
        $timestamp = strtotime($gregorian_date);
    }
    
    if ($timestamp === false) return $gregorian_date;
    
    return jdate('Y/m/d', $timestamp);
}

// تبدیل تاریخ شمسی به میلادی برای ذخیره در دیتابیس
function jalali_to_gregorian_input($jalali_date) {
    if (empty($jalali_date)) return '';
    
    // پاکسازی و استانداردسازی
    $jalali_date = str_replace(['-', '\\'], '/', $jalali_date);
    $parts = explode('/', $jalali_date);
    
    if (count($parts) !== 3) return '';
    
    list($year, $month, $day) = $parts;
    
    // استفاده از تابع موجود در هلپر
    list($g_year, $g_month, $g_day) = jalali_to_gregorian($year, $month, $day);
    
    return sprintf('%04d-%02d-%02d', $g_year, $g_month, $g_day);
}

// بررسی معتبر بودن تاریخ شمسی
function is_valid_jalali_date($date) {
    if (empty($date)) return false;
    
    $date = str_replace(['-', '\\'], '/', $date);
    $parts = explode('/', $date);
    
    if (count($parts) !== 3) return false;
    
    list($year, $month, $day) = $parts;
    
    // بررسی عدد بودن
    if (!is_numeric($year) || !is_numeric($month) || !is_numeric($day)) {
        return false;
    }
    
    // بررسی محدوده ماه
    if ($month < 1 || $month > 12) return false;
    
    // بررسی محدوده روز
    if ($day < 1 || $day > 31) return false;
    
    // بررسی روزهای ماه
    $days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
    
    // بررسی سال کبیسه
    if ($month == 12 && $day == 30) {
        // محاسبه سال کبیسه
        $leap = (($year % 33) == 1 || ($year % 33) == 5 || ($year % 33) == 9 || 
                ($year % 33) == 13 || ($year % 33) == 17 || ($year % 33) == 22 || 
                ($year % 33) == 26 || ($year % 33) == 30);
        if ($leap) {
            $days_in_month[11] = 30; // اسفند 30 روزه
        }
    }
    
    if ($day > $days_in_month[$month - 1]) return false;
    
    return true;
}

