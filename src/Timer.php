<?php

namespace Sapi;

use InvalidArgumentException;

class Timer
{
    private static function t(): int
    {
        return time();
    }

    /**
     * @desc 获取格式化后当前时间：2023-08-04 00:28:24
     * @return string
     */
    public static function getNowStr(): string
    {
        $currentTime = self::t();
        return date("Y-m-d H:i:s", $currentTime);
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
     * @desc 返回当前时间的微秒
     * @return string
     */
    public static function getMicroseconds(): string
    {
        list($usec, $sec) = explode(' ', microtime());

        return intval(sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000 * 1000));
    }

    /**
     * @desc 返回当前时间的毫秒
     * @return string
     */
    public static function getMilliseconds(): string
    {
        list($usec, $sec) = explode(' ', microtime());
        return intval(sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000));
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

    /**
     * @desc 根据需要输入的小时生成对应的时间
     * @param int $hour 0-23输入需要生成小时
     * @return int
     */
    public static function getTimestampForHourOfDay(int $hour): int
    {
        if ($hour < 0 || $hour > 23) {
            throw new InvalidArgumentException("Hour must be between 0 and 23.");
        }

        // 获取当前日期
        $currentDate = date('Y-m-d');

        // 拼接日期和指定小时，生成字符串表示
        $dateTimeString = $currentDate . ' ' . $hour . ':00:00';

        // 将日期时间字符串转换成时间戳
        return strtotime($dateTimeString);
    }

    /**
     * @desc 根据给定的小时H生成对应的Ymd时间，例如：输入23，当前时间大于等于23且小于23则生成他们之间的零点的Ymd时间
     * @param int $hour
     * @return string
     */
    public static function generateYmdForHourOfDay(int $hour): string
    {
        if ($hour < 0 || $hour > 23) {
            throw new InvalidArgumentException("Hour must be between 0 and 23.");
        }

        $currentDate = new \DateTime(); // 当前日期时间

        $currentHour = (int)$currentDate->format('G'); // 当前小时

        if ($hour <= $currentHour) {
            // 如果给定小时小于等于当前小时，则为明天
            $currentDate->modify('+1 day');
        }

        $currentDate->setTime($hour, 0, 0); // 设置指定小时的时间

        return $currentDate->format('Ymd');
    }
}