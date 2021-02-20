<?php
/**
 * @created    04-Jan-2021
 * @author     Hardeep
 */
namespace csv;

abstract class CsvDataType
{
    const NUMBER = "NUMBER";
    const TEXT = "TEXT";
    const DATE = "DATE";
    const TIME = "TIME";
    const DATETIME = "DATETIME";
    const BOOL = "BOOL";
    
    public static $list = [
        self::NUMBER,
        self::TEXT,
        self::DATE,
        self::TIME,
        self::DATETIME,
        self::BOOL,
    ];
}

class CsvWhere
{
    public $field, $op, $value;
    
    public function __construct($field, $op, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->op = $op;
    }
}

class CsvOrder
{
    const DIR_ASC = "ASC";
    const DIR_DESC = "DESC";

    public $field, $dir;
    
    public function __construct($field, $dir = "ASC")
    {
        $this->field = $field;
        
        if (!in_array($dir, [self::DIR_ASC, self::DIR_DESC]))
        {
            throw new \Exception("Invalid $dir");
        }
    }
}

class CsvUtility
{
    private $file, $headers, $delimeter;

    public function __construct($file, $delimeter = ",")
    {
        $this->file = $file;
        $this->delimeter = $delimeter;
    }
    
    public function setField($field, $data_type)
    {
        if ( !in_array($data_type, CsvDataType::$list))
        {
            throw new \Exception("CsvUtility : Invalid Data Type");
        }
        
        $this->headers[$field] = $data_type;        
    }
    
    public function find($fields = [], $conditions = [], $orders = null, $limit = null)
    {
        if (!file_exists($this->file))
        {
            return [];
        }
        
        if (!is_readable($this->file))
        {
            throw new \Exception ("File is not readable");
        }
        
        $records = $header_row = [];
        $row_count = 0;
        if (($handle = fopen($this->file, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 0, $this->delimeter)) !== FALSE)
            {
                $row_count++;
                
                if ($row_count == 1)
                {
                    $header_row = $row;
                }
                else
                {
                    $row = $this->getRow($row, $header_row);

                    if ($row !== null)
                    {
                        if ($conditions)
                        {
                            $result = $this->applyConditions($row, $conditions);

                            if ($result)
                            {
                                $records[] = $row;

                                if ($limit && !$orders)
                                {
                                    if ( $limit >= count($records) )
                                    {
                                        return $records;
                                    }
                                }
                            }
                        }
                        else
                        {
                            $records[] = $row;

                            if ($limit && !$orders)
                            {
                                if ( $limit >= count($records) )
                                {
                                    return $records;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        fclose($handle);
        
        if ($orders)
        {
            $records = $this->applyOrder($records, $orders);
            
            if ($limit)
            {
                $c = count($records);
                for ( $i = $limit; $i < $c; $i++ )
                {
                    unset($records[$i]);
                }
            }
        }
        
        foreach($records as $k => $row)
        {
            $filter_row = [];
            if ( empty($fields) )
            {
                $filter_row = $row;
            }
            else
            {
                foreach($fields as $f)
                {
                    if ( isset($row[$f]) )
                    {
                        $filter_row[$f] = $row[$f]; 
                    }
                    else
                    {
                        throw new \Exception("Field $f not found");
                    }
                }
            }
            
            $records[$k] = $filter_row;
        }
        
        return $records;
    }
    
    private function getRow($row, $header_row)
    {
        if ($row === NULL)
        {
            return NULL;
        }
        
        $result_row = [];
        
        foreach($row as $f => $v)
        {
            if (isset($header_row[$f]))
            {
                $f = trim($header_row[$f]);
                
                if ( isset( $this->headers[$f] ))
                {
                    $data_type = $this->headers[$f];

                    switch($data_type)
                    {
                        case CsvDataType::NUMBER:
                            $v = floatval($v);
                            break;

                        case CsvDataType::DATE:
                            $v = date("d-M-Y", strtotime($v));
                            break;

                        case CsvDataType::DATETIME:
                            $v = date("d-M-Y h:i:s a", strtotime($v));
                            break;

                        case CsvDataType::TIME:
                            $v = date("h:i:s a", strtotime($v));
                            break;

                        case CsvDataType::BOOL:
                            $v = boolval($v);
                            break;
                    }
                }
                
                $result_row[$f] = $v;
            }
        }
        
        return $result_row;
    }
    
    private function applyConditions($row, $conditions)
    {
        foreach($conditions as $wh)
        {
            if (!$wh instanceof CsvWhere)
            {
                throw new \Exception("Conditions should be instance of CsvWhere class");
            }
            
            $f = $wh->field;
            if ( isset($row[$f]) )
            {
                $v = strtolower(trim($row[$f]));
                $compare_v = strtolower(trim($wh->value));
                $op = strtolower(trim($wh->op));     
                if ( in_array($op, [">", ">=", "<", "<=", "=", "!="]) )
                {
                    if ( !isset($this->headers[$f]) )
                    {
                        throw new \Exception("Data Type of $f should be set in fields");
                    }
                    
                    $data_type = $this->headers[$f];
                    
                    switch($data_type)
                    {
                        case CsvDataType::NUMBER:
                            $v = floatval($v);
                            $compare_v = floatval($compare_v);
                            break;

                        case CsvDataType::DATE:
                        case CsvDataType::DATETIME:
                        case CsvDataType::TIME:
                            $v = strtotime($v);
                            $compare_v = strtotime($compare_v);
                            
                            break;

                        case CsvDataType::TEXT:
                            throw new Exception("Opreation $op not valid on Text");
                            break;
                        case CsvDataType::BOOL:
                            throw new Exception("Opreation $op not valid on Bool");
                            break;
                    }
                    
                    switch ($op)
                    {
                        case ">":
                            if ($v <= $compare_v)
                            {
                                return false;
                            }
                            break;

                        case ">=":
                            if ($v < $compare_v)
                            {
                                return false;
                            }
                            break;

                        case "<":
                            if ($v >= $compare_v)
                            {
                                return false;
                            }
                            break;

                        case "<=":
                            if ($v > $compare_v)
                            {
                                return false;
                            }
                            break;

                        case "=":
                            if ($compare_v != $v)
                            {
                                return false;
                            }
                            break;

                        case "!=":
                            if ($compare_v == $v)
                            {
                                return false;
                            }
                            break;
                    }
                }
                else if ($op == "not")
                {
                    if ( $compare_v == $v )
                    {
                        return false;
                    }
                }
                else if (empty($op) || is_null($op))
                {
                    if ( $compare_v != $v )
                    {
                        return false;
                    }
                }
            }
            else
            {
                return false;
            }
        }
        
        return true;
    }
    
    private function applyOrder($records, $orders)
    {
        foreach($orders as $order_by)
        {
            if (!$order_by instanceof CsvOrder)
            {
                throw new \Exception("Orders should be instance of CsvOrder class");
            }

            $data_type = null;
            $f = trim($order_by->field);
            $dir = trim($order_by->dir);
            if ( isset( $this->headers[$f] ))
            {
                $data_type = $this->headers[$f];
            }
            else
            {
                throw new \Exception("Order By Field $f should be set in fields");
            }
            
            usort($records, function($row_a, $row_b) use ($data_type, $f, $dir)
            {
                $a = $row_a[$f];
                $b = $row_b[$f];
                
                if ($data_type == CsvDataType::TEXT)
                {
                    $result = strcmp($a, $b);
                    if ($dir == CsvOrder::DIR_ASC)
                    {
                        return $result;
                    }
                    else
                    {
                        return $result * -1;
                    }
                }
                else
                {
                    switch($data_type)
                    {
                        case CsvDataType::NUMBER:
                            $a = floatval($a);
                            $b = floatval($b);
                            break;

                        case CsvDataType::DATE:
                        case CsvDataType::DATETIME:
                        case CsvDataType::TIME:
                            $a = strtotime($a);
                            $b = strtotime($b);                        
                            break;

                        case CsvDataType::BOOL:
                            $a = (int) $a;
                            $b = (int) $b;
                            break;
                    }

                    if ($dir == CsvOrder::DIR_ASC)
                    {
                        $result = $a > $b ? 1 : ($a < $b ? -1 : 0);
                        
                        return $result;
                    }
                    else
                    {
                        $result = $a > $b ? -1 : ($a < $b ? 1 : 0);
                        
                        return $result;
                    }
                }
            });
        }
        
        
        return $records;
    }
    
    public static function sortColumn($records, $order)
    {
        foreach($records as $k => $record)
        {
            $row = [];
            foreach($order as $col)
            {
                if ( isset($record[$col]) )
                {
                    $row[$col] = $record[$col];
                }
                else
                {
                    throw new \Exception("$col is not set in record");
                }
            }
            
            $records[$k] = $row;
        }
        
        return $records;
    }
    
    public function write($records, $mode = "w")
    {
        $print_header = ($mode == "w" || $mode == "w+");
        
        $first_row = $this->find([], [], [], 1);
        
        if ( empty($first_row) )
        {
            $print_header = true;
        }

        $handle = fopen($this->file, $mode);
        
        if (!$handle)
        {
            throw new \Exception("Unable to open file");
        }
        
        $header_row = [];
        $row_count = 0;
        foreach($records as $record)
        {
            $row_count++;
            if ($row_count == 1)
            {
                $header_row = array_keys($record);
                
                if ($print_header)
                {
                    fputcsv($handle, $header_row, $this->delimeter);
                }
            }
            
            $data = self::sortColumn([$record], $header_row);            
            $record = $this->beforeInsert($data[0]);            
            fputcsv($handle, $record, $this->delimeter);            
        }
        
        fclose($handle);
        chmod($this->file, 0777);
    }
    
    public function beforeInsert($row)
    {
        $filter_row = [];
        foreach($row as $f => $v)
        {
            $f = trim($f);
            
            if ( isset( $this->headers[$f] ))
            {
                $data_type = $this->headers[$f];

                switch($data_type)
                {
                    case CsvDataType::NUMBER:
                        $v = floatval($v);
                        break;

                    case CsvDataType::DATE:
                        $v = date("d-M-Y", strtotime($v));
                        break;

                    case CsvDataType::DATETIME:
                        $v = date("d-M-Y h:i:s a", strtotime($v));
                        break;

                    case CsvDataType::TIME:
                        $v = date("h:i:s a", strtotime($v));
                        break;

                    case CsvDataType::BOOL:
                        $v = boolval($v);
                        break;
                }
            }
            $filter_row[$f] = $v;
        }
        
        return $filter_row;
    }
}
