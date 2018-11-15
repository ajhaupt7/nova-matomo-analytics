<?php

namespace Rocramer\MatomoAnalytics\Cards;

use Illuminate\Http\Request;
use Laravel\Nova\Metrics\TrendResult;
use Rocramer\MatomoAnalytics\CustomizedTrend;
use Rocramer\MatomoAnalytics\Helper\MatomoAPI;

class BounceRate extends CustomizedTrend
{
    /**
     * Calculate the value of the metric.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        // Get Bounce Count
        $url = MatomoAPI::url('VisitsSummary.getBounceCount', $request->range);
        $fetched = file_get_contents($url);
        $bounce_count = unserialize($fetched);

        // Get Page Visits
        $url = MatomoAPI::url('VisitsSummary.getVisits', $request->range);
        $fetched = file_get_contents($url);
        $visits = unserialize($fetched);

        // Divide to get percentage
        $results = [];

        $keys = array_keys($bounce_count);

        foreach ($keys as $key) {

            $value = $bounce_count[$key] / $visits[$key] * 100;
            $value = (int)round($value);
            $results[$key] = $value;
        }
        return (new TrendResult())
            ->trend($results)
            ->showLatestValue()
            ->suffix('percent');
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            7 => '7 Days',
            14 => '14 Days',
            30 => '30 Days',
            90 => '90 Days',
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'bounce-count';
    }
}
