<?php


namespace CoinParams\BitWasp;


use CoinParams\Exceptions\ArrayKeyNotFound;

class Internal
{
    /**
     * @param $array array
     * @param $key string
     * @param bool|string $exception: type or message to throw an exception with your message or false to return null.
     * @return null|mixed
     * @throws ArrayKeyNotFound
     */
    public static function aget($array , $key , $exception = false)
    {
        if (is_array($array) AND array_key_exists($key, $array)){
            return $array[$key];
        }elseif ($exception){
            $message = is_string($exception) ? $exception : 'Array value not found for key ' . $key;
            throw new ArrayKeyNotFound($message);
        }else{
            return null;
        }
    }
}
