<?php

namespace Sapi;

class Timer
{
    private static function t(): int
    {
        return time();
    }


    /**
     * @desc 返回当前时间的日期id
     * @return string
     */
    public static function getNowDateId(): string
    {
        $currentTime = self::t();
        return date("Ymd", $currentTime);
    }

    /**
     * @desc 返回当前时间的小时id
     * @return string
     */
    public static function getNowHourId(): string
    {
        $currentTime = self::t();
        return date("YmdH", $currentTime);
    }

    /**
     * @desc 返回当前时间的 Unix 时间戳
     * @return int
     */
    public static function getSecond(): int
    {
        return self::t();
    }

    /**
     * @desc 获取今天的零点时间戳，例如：今天是2023-8-1的话获取的则是2023-8-1号的零点时间戳
     * @return int
     */
    public static function getTodayMidnight(): int
    {
        return strtotime('today midnight');
    }

    /**
     * @desc 获取明天的零点时间戳，例如：今天是2023-8-1的话获取的则是2023-8-2号的零点时间戳
     * @return int
     */
    public static function getTomorrowMidnight(): int
    {
        return strtotime('tomorrow midnight');
    }

    /**
     * @desc 获取距离今天结束剩余的时间（秒）
     * @return int
     */
    public static function getRemainingSecondsToMidnight(): int
    {
        // 获取当前时间的时间戳
        $currentTime = self::t();

        // 获取今晚零点零分的时间戳
        $midnight = self::getTomorrowMidnight();

        // 计算距离今晚零点零分的秒数
        $remainingSeconds = $midnight - $currentTime;

        return $remainingSeconds;
    }
}