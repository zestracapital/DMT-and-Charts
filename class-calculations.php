<?php
/**
 * Calculation helper class for DMT
 * Provides various time-based and statistical calculations
 */
if (!defined('ABSPATH')) exit;

class ZC_DMT_Calculations {
    /**
     * Month-over-Month absolute and percentage change
     */
    public static function calc_mom($current, $previous) {
        $abs = $current - $previous;
        $pct = ($previous != 0) ? ($abs / $previous) * 100 : null;
        return ['absolute' => $abs, 'percent' => $pct];
    }
    /**
     * Quarter-over-Quarter absolute and percentage change
     */
    public static function calc_qoq($current, $previous) {
        return self::calc_mom($current, $previous);
    }
    /**
     * Year-over-Year absolute and percentage change
     */
    public static function calc_yoy($current, $previous) {
        return self::calc_mom($current, $previous);
    }
    /**
     * Rolling average over $period (in months)
     */
    public static function rolling_average($data, $period = 3) {
        $result = [];
        $count = count($data);
        for ($i = 0; $i < $count; $i++) {
            if ($i + 1 >= $period) {
                $slice = array_slice($data, $i + 1 - $period, $period);
                $result[] = array_sum($slice) / $period;
            } else {
                $result[] = null;
            }
        }
        return $result;
    }
    /**
     * Compound Annual Growth Rate
     */
    public static function cagr($beginValue, $endValue, $periods) {
        if ($beginValue <= 0 || $periods <= 0) return null;
        return (pow(($endValue / $beginValue), (1 / $periods)) - 1) * 100;
    }
    /**
     * Yield-like calculation over $duration periods
     */
    public static function yield_rate($values) {
        $first = reset($values);
        $last = end($values);
        $periods = count($values) - 1;
        return self::cagr($first, $last, $periods);
    }
}