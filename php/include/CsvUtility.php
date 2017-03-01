<?php
/**
 * @created    22/04/2015
 * @package    Badge
 * @copyright  Copyright (C) 2015
 * @license    Proprietary
 * @author     Hardeep
 */
class CsvUtility
{
    public $file;
    
    public function __construct($file)
    {
        $this->file = $file;
    }
    
    public function find($fields = array(), $conditions = array())
    {
        $data = self::fetchCSV($this->file);
        if (!$data)
        {
            return array();
        }
        
        if ($conditions)
        {
            $i = 0;
            foreach($data as $k => $row)
            {
                if ($i == 0)
                {
                    $headers = array_keys($row);
                }
                
                foreach($conditions as $field => $val)
                {
                    if (in_array($field, $headers))
                    {
                        if (is_array($val))
                        {
                            if (!in_array($row[$field], $val))
                            {
                                unset($data[$k]);
                            }
                        }
                        else
                        {
                            if ($row[$field] != $val)
                            {
                                unset($data[$k]);
                            }
                        }
                    }
                }
                
                $i++;
            }
        }
        
        return $data;
    }
    
    public static function fetchCSV($filename, $header = true, $delimiter = ',') 
    {
        if (!file_exists($filename) || !is_readable($filename)) 
        {
            return FALSE;
        }
        $data = array();
        $header_data = array();

        if (($handle = fopen($filename, 'r')) !== FALSE) 
        {
            $r = 0;
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) 
            {
                foreach($row as $k  => $v)
                {
                    $row[$k] = utf8_encode($v);
                }
                
                if ($header && $r == 0) 
                {
                    $header_data = $row;               
                } 
                else 
                {
                    if ($header)
                    {
						$insert = false;
						
						foreach($row as $key => $val)
						{
							if (trim($val))
							{
								$insert = true;
							}
						}
						
						if ($insert)
						{
							for($i = 0; $i < count ($row); $i++)
							{	
								$data[$r][$header_data[$i]] = $row[$i];
							}
						}
                    }
                    else
                    {
                        $data[$r] = $row;
                    }
                }
                $r++;
            }
            fclose($handle);
        }
        return $data;
    }

    public static function writeCSV($file, $data, $key_as_header = false, $delimeter = ',', $mode = "w") 
    {
        $print_header = !file_exists($file) || ($mode != "a" && $mode != "a+");
        
        $handle = fopen($file, $mode);
        if ($handle) 
        {
            if ($key_as_header)
            {
                $headers = array_values($data);
                $headers = array_keys($headers[0]);
                if ($print_header)
                {
                    fputcsv($handle, $headers, $delimeter);
                }
            }
            else
            {
                $temp = $data;
                if (isset($temp['header']) && !empty($temp['header'])) 
                {
                    $headers = array_keys($temp['header']);
                    
                    if ($print_header)
                    {
                        $data = array(
                            0 => $temp['header']
                        );       
                        
                        $data = array_merge($data, $temp['data']);
                    }
                    else
                    {
                        $data = $temp['data'];
                    }
                }
                else
                {
                    $data = $temp['data'];
                }
            }
            
            foreach ($data as $line) 
            {
                $row = array();
                if (isset($headers) && $headers)
                {
                    foreach($headers as $field)
                    {
                        $row[$field] = $line[$field];
                    }
                }
                else
                {
                    $row = $line;
                }
                
                fputcsv($handle, $row, $delimeter);
            }
            
            fclose($handle);
            chmod($file, 0777);  
            
            return true;
        }
        else
        {
            return false; 
        }
    }
}