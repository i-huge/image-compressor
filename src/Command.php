<?php
include('./lib/function.php');
include('./lib/Compress.php');
class Command
{
    /**
     * Command constructor.
     */
    private $compressor = null;
    private $percent = 0.5;
    private $maxWith = -1;
    private $maxHeight = -1;
    private $config = [];
    private $inputDir = './io/input/';
    private $outputDir = './io/output/';
    private $microTime = 1000000;
    public function __construct($percent=1, $maxWidth = -1, $maxHeight = -1)
    {
        $this->percent = $percent;
        $this->maxWith = $maxWidth;
        $this->maxHeight = $maxHeight;
        //获取配置
        $this->reloadConfig();
    }
    /**
     * @param $src
     * @param int $percent
     * @param int $maxWidth
     * @param int $maxHeight
     */
    public function initCompressor($src, $percent=1, $maxWidth = -1, $maxHeight = -1)
    {
        $this->compressor = new Compress($src, $percent, $maxWidth, $maxHeight);
    }
    /**
     *程序入口
     */
    public function run()
    {
        while (true) {
            $this->show();
            $files = [];
            $dirs = [];
            //可执行命令列表
            $command = handleInput([1,2,3,4,5,8,10]);
            $file = '';
            if ($command == 10) {
                $this->help();
                //终止执行
                continue;
            } elseif ($command == 1) {
                writeLine('Please copy picture to the folder '.$this->inputDir);
            } elseif ($command == 2) {
                writeLine('Please copy the picture folder to the folder '.$this->inputDir);
            } elseif ($command == 3 || $command == 4) {
                getAllFiles($this->inputDir, $files, $dirs);
                if ($command == 3) {
                    $re = $this->showFiles($files);
                } else {
                    $re = $this->showFiles($dirs);
                }
                //如果目录为空，终止执行
                if (!$re) {
                    continue;
                }
                writeLine('Please enter the index of file/folder');
                $index =  handleInput([1,$command == 3 ? count($files) : count($dirs)], true);
                $file = $command == 3 ? $files[$index - 1] : $dirs[$index - 1];
                $file = str_replace($this->inputDir, '', $file);
            } elseif ($command == 8) {
                writeLine('Bye!');
                break;
            } elseif ($command == 5) {
                $this->reloadConfig();
                writeLine('Reload success!');
                continue;
            }
            list($radio, $maxWidth, $maxHeight) = $this->imageArguments();
            if (!$file) {
                writeLine('Please enter the name of file/folder ?');
                while (true) {
                    $file =  handleInput([0,0], true, true);
                    if (file_exists($this->inputDir.$file)) {
                        break;
                    }
                    writeLine('file/folder not found,please enter again');
                }
            }
            //执行压缩命令
            $this->compress($file, $radio, $maxWidth, $maxHeight);
        }
    }
    /**
     * 执行压缩
     * @param $fileName
     * @param $radio
     * @param $maxWidth
     * @param $maxHeight
     */
    private function compress($fileName, $radio, $maxWidth, $maxHeight)
    {
        $source = $this->inputDir.$fileName;
        $des = $this->outputDir.$fileName;
        if (is_dir($source)) {
            getAllFiles($source, $files, $dirs);
            if (!is_dir($des)) {
                createDir($des);
            }
            if (empty($files)) {
                writeLine('Empty dir!');
                return false;
            }
            //创建目标目录
            if (!empty($dirs)) {
                foreach ($dirs as $dir) {
                    $dir = str_replace($this->inputDir, $this->outputDir, $dir);
                    if (!is_dir($dir)) {
                        createDir($dir);
                    }
                }
            }
            if (!empty($files)) {
                $count = count($files);
                $completePercent = 0;
                foreach ($files as $k => $file) {
                    $this->initCompressor($file, $radio, $maxWidth, $maxHeight);
                    $land = str_replace($this->inputDir, $this->outputDir, $file);
                    writeLine('Processing '.$file.'---------'.($completePercent += (1/$count) * 100).'%');
                    $this->compressor->compressImg($land);
                    writeLine('Processed '.$land.'---------'.$completePercent.'%');
                    //控制CPU的占用率
                    usleep($this->microTime);
                }
                writeLine('Completed --------- 100%');
            }
        } else {
            $this->initCompressor($source, $radio, $maxWidth, $maxHeight);
            writeLine('Processing '.$source);
            $this->compressor->compressImg($des);
            writeLine('Processed '.$des);
        }
    }
    /**
     * @return array
     */
    private function imageArguments()
    {
        return [isset($this->config['radio']) ? $this->config['radio'] : 1,
                isset($this->config['maxWidth']) ? $this->config['maxWidth'] : -1,
                isset($this->config['maxHeight']) ? $this->config['maxHeight'] : -1];
    }
    /**
     * 展示菜单
     */
    private function show()
    {
        writeLine('=============Menu============');
        writeLine('1.Single picture');
        writeLine('2.Multiple pictures');
        writeLine('3.Scan files');
        writeLine('4.Scan dirs');
        writeLine('5.Reload configuration file');
        writeLine('8.Exit');
        writeLine('10.Help');
        writeLine('========= PHP '.phpversion().' =========');
        writeLine('=============End=============');
    }
    /**
     * 帮助
     */
    private function help()
    {
        writeLine('Change the configuration file to resize the picture,new files will be in '.$this->outputDir.' folder');
    }
    /**
     * 展示文件
     */
    private function showFiles($arr)
    {
        if (empty($arr)) {
            writeLine('Empty !');
            return false;
        } else {
            writeLine('========================');
            foreach ($arr as $k => $v) {
                writeLine(($k + 1).'  '.$v);
            }
            writeLine('========================');
            return true;
        }
    }
    /**
     *重载配置
     */
    private function reloadConfig()
    {
        $this->config = include('./lib/config.php');
        $this->inputDir = isset($this->config['inputDir']) && is_dir($this->config['inputDir']) ? $this->config['inputDir'] : './io/input/';
        $this->outputDir = isset($this->config['outputDir']) && !empty($this->config['outputDir']) ? $this->config['outputDir'] : './io/output/';
        $this->microTime = isset($this->config['microTime']) && !empty($this->config['microTime']) ? $this->config['microTime'] : 100;
        if (substr($this->inputDir, -1, 1) != '/') {
            $this->inputDir .= '/';
        }
        if (substr($this->outputDir, -1, 1) != '/') {
            $this->outputDir .= '/';
        }
        writeLine('radio -------- '.(isset($this->config['radio']) ? $this->config['radio'] : 1));
        writeLine('maxWidth ----- '.(isset($this->config['maxWidth']) ? $this->config['maxWidth'] : -1));
        writeLine('maxHeight ---- '.(isset($this->config['maxHeight']) ? $this->config['maxHeight'] : -1));
        writeLine('inputDir ----- '.$this->inputDir);
        writeLine('outputDir ---- '.$this->outputDir);
        sleep(1);
    }
}
