<?php
session_start();
class Session
{
    public static function write($key, $val)
    {
        $_SESSION[$key] = $val;
    }
    
    public static function read($key = null)
    {
        if ($key)
        {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : false;
        }
        else
        {
            return $_SESSION;
        }
    }
    
    public static function has($key)
    {
        return isset($_SESSION[$key]) ? true : false;
    }
    
    public static function hasFlash($key)
    {
        return self::has("flash." . $key);
    }
    
    public function writeFlash($key, $val)
    {
        self::write("flash." . $key, $val);
    }
    
    public function readFlash($key)
    {
        $key = "flash." . $key;
        
        $msg = self::read($key);
        
        unset($_SESSION[$key]);
        
        return $msg;
    }
}
