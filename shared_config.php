<?php
class SharedConfig
{
    public static $dml_tables = array(
        "menus"
    );
    
    public static function url($file, $args = array())
    {
        $args['load_file'] = $file;
        
        $query_str = "";
        foreach($args as $arg => $v)
        {
            $query_str .= "$arg=$v&";
        }
        
        $query_str = substr($query_str, 0, -1);
        
        return BASE_URL . "index.php?$query_str";
    }
}
