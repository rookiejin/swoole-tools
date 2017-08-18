# SwooleTools 
   
* 监听目录变化自动重启swoole_server 


## Usage

`
    composer require rookiejin/swoole_tooles 
    
    touch  reload.php 
    # code 如下 
    <?php 
    
    $master_pid = isset($argv[1]) ? $argv[1]:false;
    require_once $dir . '/vendor/autoload.php' ;
    if($master_pid){
        $reload = \Rookiejin\SwooleTools\Reload::getInstance()->watch($dir,$master_pid,['.php']);
    }else{
        echo "please input pid" . PHP_EOL ;
    }
    
    ?>
    
   // 在命令行执行 
    # 23311 是swoole_server的pid
    # 可以用 ps -axf | grep php 查看  

    23869 pts/30   S      0:00 php index.php
    24139 pts/30   S      0:00  \_ php index.php
    24140 pts/30   S      0:00  \_ php index.php
    24141 pts/30   S      0:00  \_ php index.php
    24142 pts/30   S      0:00  \_ php index.php
    24143 pts/30   S      0:00  \_ php index.php
    24144 pts/30   S      0:00  \_ php index.php
    24145 pts/30   S      0:00  \_ php index.php
    24146 pts/30   S      0:00  \_ php index.php

     php reload.php  23869  
   
   或者 
   # pid file 是将pid保存在这个文件里面了，程序直接会去读这个文件。
   php reload.php  /tmp/swoole.pid 
`

    #### 注意 要先启动 swoole_server 再启用reload 
    * 感谢 @matyhtf [swoole/auto_reload](https://github.com/swoole/auto_reload)
    
    
    
    
    