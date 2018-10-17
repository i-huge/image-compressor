<?php
/*
如果要保持原图比例，把参数radio设置为1即可。
如果要保持在原图的宽高范围内，把参数radio设置为0。
*/
return [
    //压缩比例
    'radio' => 1,
    //最大宽度
    'maxWidth' => 20,
    //最大高度
    'maxHeight' => 20,
    //源图片存放目录
    'inputDir' => './io/input/',
    //输出文件存放目录
    'outputDir' => './io/output/',
    'microTime' => 100,
];
