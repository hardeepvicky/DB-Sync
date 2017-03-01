<?php
/* 
 * created 14-02-2017
 */
interface File
{
    public static function getAutoincreamentFileName($filename, $ext, $dest_path, $sep = "_", $i = 0);
    public static function createFolder($path);
    public static function delete($path, $exts = array(), $recursive = false);
    public static function getFileList($path, $exts = array(), $recursive = false);
}
