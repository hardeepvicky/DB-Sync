<?php

class GitUtility
{

    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getBranchList()
    {
        $current = getcwd();
        chdir($this->path);
        exec($this->path . " git branch", $list);

        $result = array();

        foreach ($list as $name)
        {
            $branch = trim(str_replace("*", "", $name));
            $result[$branch]['name'] = $name;
            $result[$branch]['checkout'] = false;

            if (strpos($name, "*") !== false)
            {
                $result[$branch]['checkout'] = true;
            }
        }

        chdir($current);
        return $result;
    }

    public function getCurrentBranchName()
    {
        $current = getcwd();
        chdir($this->path);
        exec("git branch", $out, $ret);
        chdir($current);

        foreach ($out as $branch_name)
        {
            if (strpos($branch_name, "*") !== false)
            {
                $branch_name = str_replace("*", "", $branch_name);
                $branch_name = strtolower(trim($branch_name));
                return $branch_name;
            }
        }

        return "";
    }

    public function getCommits()
    {
        $current = getcwd();
        chdir($this->path);
        exec("git log", $logs);
        $data = array();
        $i = 0;
        $commit_name = $author_name = $datetime = "";
        foreach ($logs as $str)
        {
            if (str_contain($str, "commit", 0, strlen("commit")))
            {
                $i++;
                $data[$i]["commit"] = trim(substr($str, strpos($str, "commit") + strlen("commit")));
            }
            else if (str_contain($str, "Author:", 0, strlen("Author:")))
            {
                $data[$i]["author"] = trim(substr($str, strpos($str, "Author:") + strlen("Author:")));
            }
            else if (str_contain($str, "Date:", 0, strlen("Date:")))
            {
                $data[$i]["datetime"] = DateUtility::getDate(trim(substr($str, strpos($str, "Date:") + strlen("Date:"))));
            }
            else
            {
                $msg = trim($str);
                if ($msg)
                {
                    $data[$i]["msg"] = $msg;
                }
            }
        }

        foreach ($data as $k => $arr)
        {
            if (!isset($arr['commit']) || !isset($arr['datetime']) || !isset($arr['author']))
            {
                unset($data[$k]);
            }
        }

        chdir($current);
        return $data;
    }

    public function getFilesOfCommit($commit)
    {
        $current = getcwd();
        chdir($this->path);

        $cmd = 'git diff-tree --no-commit-id --name-only -r ' . $commit;
        exec($cmd, $files);

        chdir($current);
        return $files;
    }

}
