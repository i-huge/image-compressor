<?php
if (!function_exists('getAllFiles')) {
    /**
     * 获取文件夹列表
     * @param $path
     * @param $files
     * @param $dirs
     */
    function getAllFiles($path, &$files, &$dirs)
    {
        foreach (scandir($path) as $file) {
            if ($file === '.'|| $file === '..') {
                continue;
            }
            if (is_dir($path.'/'.$file)) {
                $dirs[] = $path.'/'.$file;
                getAllFiles($path.'/'.$file, $files, $dirs);
            } else {
                $files[] =  $path.'/'.$file;
            }
        }
    }
}
if (!function_exists('handleInput')) {
    /**
     * @param $range
     * @param bool $real
     * @param bool $noRule
     * @return string
     */
    function handleInput($range, $real = false, $noRule = false)
    {
        //接收用户输入
        $input = '';
        while (true) {
            $input = trim(fgets(STDIN));
            writeLine('Entered  '.$input);
            if ($noRule) {
                break;
            }
            if ($real ? ($input < $range[0] || $input > $range[1]) : !in_array($input, $range)) {
                writeLine('Wrong input,please enter again');
            } else {
                break;
            }
        }
        return $input;
    }
}
if (!function_exists('writeLine')) {
    /**
     * @param string $string
     * @param bool $enter
     */
    function writeLine($string = '', $enter = true)
    {
        echo $enter ? $string.PHP_EOL : $string;
    }
}
if (!function_exists('createDir')) {
    /**
     * 深层创建目录
     * @param $parameter
     * @return bool
     */
    function createDir($parameter)
    {
        //判断目录是否已存在
        if (file_exists($parameter)) {
            return false;
        } else {
            //如果给定的目录不存在 / 则在当前目录直接创建目录
            $parameter = rtrim($parameter, '/');
            //指定当前的目录
            $dir = './';
            if (!strpos($parameter, '/')) {
                mkdir($parameter);
            } else {
                //存在 / 则通过循环的方式深层创建目录
                $par_arr = explode('/', $parameter);
                foreach ($par_arr as $key=>$value) {
                    $dir .= $value.'/';
                    if (!file_exists($dir)) {
                        mkdir($dir);
                    }
                }
            }
            return true;
        }
    }
}
