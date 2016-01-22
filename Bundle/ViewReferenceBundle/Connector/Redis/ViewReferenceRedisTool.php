<?php

namespace Victoire\Bundle\ViewReferenceBundle\Connector\Redis;

/**
 * Class ViewReferenceRedisTool.
 */
class ViewReferenceRedisTool
{
    /**
     * This method generated a string that can be persisted for redis with data.
     *
     * @param $data
     *
     * @return string
     */
    public function redislize($data)
    {
        // Only serialize if it's not a string or a integer
        if (!is_string($data) && !is_int($data)) {
            return urlencode(serialize($data));
        }
        // Encode string to escape wrong saves
        return urlencode($data);
    }

    /**
     * This method unredislize a string.
     *
     * @param $data
     *
     * @return mixed|string
     */
    public function unredislize($data)
    {
        $data = urldecode($data);

        if (self::isSerialized($data)) {
            $unserializedData = @unserialize($data);
            if ($unserializedData !== false) {
                return $unserializedData;
            }
        }

        return $data;
    }

    /**
     * Redislize an array (key, valueToRedislize).
     *
     * @param array $data
     *
     * @return array
     */
    public function redislizeArray(array $data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $result[$key] = $this->redislize($value);
            }
        }

        return $result;
    }

    /**
     * unredislize an array (key, valueToUnredislize).
     *
     * @param array $data
     *
     * @return array
     */
    public function unredislizeArray(array $data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = $this->unredislize($value);
        }

        return $result;
    }

    /**
     * This method generate a key hash.
     *
     * @param $alias
     * @param $id
     *
     * @return string
     */
    public function generateKey($alias, $id)
    {
        return $alias.':'.$id;
    }

    public static function isSerialized($data)
    {
        // if it isn't a string, it isn't serialized
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (!preg_match('/^([adObis]):/', $data, $badions)) {
            return false;
        }
        switch ($badions[1]) {
            case 'a':
            case 'O':
            case 's':
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                    return true;
                }
                break;
            case 'b':
            case 'i':
            case 'd':
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                    return true;
                }
                break;
        }

        return false;
    }
}
