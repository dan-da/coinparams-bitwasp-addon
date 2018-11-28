<?php


namespace CoinParams\BitWasp;


use CoinParams\Exceptions\ArrayKeyNotFound;

class Internal
{
    /**
     * @param $array array
     * @param $key string
     * @param bool|string $execption: type or message to throw an exeption with your message or false to return null.
     * @return null|mixed
     * @throws ArrayKeyNotFound
     */
    public static function aget($array , $key , $execption = false)
    {
        if (is_array($array) AND array_key_exists($key, $array) AND true == $array[$key]){
            return $array[$key];
        }elseif ($execption){
            $message = is_string($execption) ? $execption : 'Array value not found for key ' . $key;
            throw new ArrayKeyNotFound($message);
        }else{
            return null;
        }
    }
}