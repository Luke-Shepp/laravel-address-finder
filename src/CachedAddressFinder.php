<?php

namespace CyberDuck\AddressFinder;

use CyberDuck\AddressFinder\Drivers\DriverContract;

/**
 * Class CachedAddressFinder
 *
 * @package CyberDuck\AddressFinder
 */
class CachedAddressFinder extends AddressFinder
{
    /**
     * @var string
     */
    private $store;

    public function __construct()
    {
        $this->store = config('laravel-address-finder.cache.store', 1440);
    }

    /**
     * @param $query
     * @param $country
     * @param $group_id
     * @return Suggestions
     */
    public function suggestions($query, $country, $group_id)
    {
        return \Cache::store($this->store)->remember(
            $this->buildCacheKey([$query, $country, $group_id]),
            config('laravel-address-finder.cache.ttl', 1440),
            function () use ($query, $country, $group_id) {
                return parent::suggestions($query, $country, $group_id);
            }
        );
    }

    /**
     * @param $addressId
     * @param bool $raw
     * @param bool $translated
     * @return Details
     */
    public function details($addressId, bool $raw = false, bool $translated = false)
    {
        $cacheKeyArr = array_filter([
            $addressId,
            $raw ? 'raw' : null,
            $translated ? 'translated' : null,
        ]);

        return \Cache::store($this->store)->remember(
            $this->buildCacheKey($cacheKeyArr),
            config('laravel-address-finder.cache.ttl', 1440),
            function () use ($addressId, $raw, $translated) {
                return parent::details($addressId, $raw, $translated);
            }
        );
    }

    /**
     * @param $params
     * @return string
     */
    private function buildCacheKey($params)
    {
        return implode('-', array_map('str_slug', $params));
    }
}
