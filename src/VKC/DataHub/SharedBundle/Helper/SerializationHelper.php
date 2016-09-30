<?php

namespace VKC\DataHub\SharedBundle\Helper;

/**
 * SerializationHelper is a simple class providing serialization utility methods.
 *
 * @author Kalman Olah <kalman@inuits.eu>
 */
class SerializationHelper
{
    /**
     * @var string
     */
    const WILDCARD_CHARACTER = '*';

    /**
     * @var string
     */
    const DATETIME_FORMAT = 'c';

    /**
     * Normalize data, converting weird objects to slightly more normal data.
     *
     * @param  mixed &$data
     * @return void
     */
    public static function normalizeData(&$data)
    {
        if (!is_array($data)) {
            return;
        }

        foreach ($data as &$v) {
            if ($v instanceof \MongoId) {
                $v = (string) $v;
            } elseif ($v instanceof \MongoDate) {
                $v = date(static::DATETIME_FORMAT, $v->sec);
            } elseif ($v instanceof \DateTime) {
                $v = date(static::DATETIME_FORMAT, $v->format('U'));
            } elseif (is_array($v)) {
                static::normalizeData($v);
            }
        }
    }

    /**
     * Flatten an array.
     *
     * @param  array      $data      Array of data to flatten.
     * @param  string     $prefix    Array key prefix. Defaults to ''.
     * @param  string     $delimiter Level delimiter. Defaults to '.'.
     * @param  array|null &$result   Result container.
     * @return array                 Flattened array.
     */
    public static function flatten(array $data, $prefix = '', $delimiter = '.', &$result = null)
    {
        if (!$result) {
            $result = [];
        }

        foreach ($data as $k => $v) {
            $key = ($prefix ? $prefix.$delimiter : '').$k;

            if (is_integer($k)) {
                if (!is_array($v)) {
                    $result = array_merge_recursive($result, [$prefix => $v]);
                } else {
                    $result = array_merge_recursive($result, static::flatten($v, $prefix, $delimiter));
                }
            }

            if (is_array($v)) {
                $result = array_merge($result, static::flatten($v, $key, $delimiter));
            } else {
                $result[$key] = $v;
            }
        }

        return $result;
    }

    /**
     * Get a value at the given path from the given data.
     *
     * @param  array      $data      Array of data to get value from.
     * @param  string     $path      Path in to get value from.
     * @param  boolean    $fromRoot  Whether values should be returned in a nested array starting
     *                               from the data root. Defaults to false.
     * @param  string     $delimiter Path delimiter. Defaults to '.'.
     * @param  mixed|null $default   Default value to return if value can not be found. Defaults to null.
     * @return mixed
     */
    public static function getFromPath(array $data, $path, $fromRoot = false, $delimiter = '.', $default = null)
    {
        $parts = explode($delimiter, $path);
        $result = &$data;

        foreach ($parts as $i => $part) {
            // Wildcard char handling
            if (($part == static::WILDCARD_CHARACTER) && is_array($result)) {
                $temp_result = [];
                $temp_path = implode($delimiter, array_slice($parts, $i + 1));
                $path = implode($delimiter, array_slice($parts, 0, $i));

                foreach ($result as $j => $res) {
                    $temp_result[$j] = static::getFromPath($res, $temp_path, $fromRoot, $delimiter, $default);
                }

                $result = $temp_result;
                break;
            } elseif (isset($result[$part])) {
                $result = &$result[$part];
            } else {
                $result = $default;
                break;
            }
        }

        if ($fromRoot) {
            $rootResult = [];
            static::setAtPath($rootResult, $path, $result);
            $result = $rootResult;
        }

        return $result;
    }

    /**
     * Set a value at the given path in the given data.
     *
     * @param array  &$data     Data to set value in.
     * @param string $path      Path to set value at.
     * @param mixed  $value     Value to set.
     * @param string $delimiter Delimiter to use for path. Defaults to '.'.
     */
    public static function setAtPath(array &$data, $path, $value = null, $delimiter = '.')
    {
        $parts = explode($delimiter, $path);
        $result = &$data;

        foreach ($parts as $i => $part) {
            if ($i == (count($parts) - 1)) {
                if (is_numeric($part)) {
                    $part = int($part);
                }

                $result[$part] = $value;
            } else {
                if (is_numeric($part)) {
                    $part = int($part);
                }

                if (!isset($result[$part])) {
                    $result[$part] = [];
                }

                $result = &$result[$part];
            }
        }
    }
}
