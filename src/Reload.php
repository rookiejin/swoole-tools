<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/18
 * Time: 10:04
 */

namespace Rookiejin\SwooleTools;


class Reload
{


    protected $pid;

    public static $instance;

    public $root;

    public $watcher;

    public $event;

    public $reloading;

    public $intervel;

    public $wathers ;

    public $fileTypes = [];

    public static function getInstance()
    {
        return (self::$instance) ? self::$instance : (self::$instance = new static());
    }

    /**
     * @param $dir 根目录
     * @param $pid master的pid
     */
    public function watch($dir, $pid, $types = [] , $interval = 1)
    {
        if (!is_numeric($pid)) {
            $pid = file_get_contents($pid);
        }
        $this->pid = $pid;
        if (false === posix_kill($this->pid, 0)) {
            $this->output("pid::{$this->pid} not found ! try again", true);
        }
        $this->root = $dir;
        $this->intervel = $interval;
        $this->init();
        $this->fileTypes = $types ;
        $this->addWatch($dir,true);
        swoole_event_wait();
    }

    public function init()
    {
        $this->watcher = inotify_init();
        $this->event   = IN_MODIFY | IN_CREATE | IN_DELETE | IN_MOVE;
        swoole_event_add($this->watcher, function ($fd) {
            $events = inotify_read($this->watcher);
            if (!$events) {
                return;
            }
            foreach ($events as $event) {
                if ($event['mask'] == IN_IGNORED)
                {
                    continue;
                }
                else if ($event['mask'] == IN_CREATE or $event['mask'] == IN_DELETE or $event['mask'] == IN_MODIFY or $event['mask'] == IN_MOVED_TO or $event['mask'] == IN_MOVED_FROM)
                {
                    if (!$this->reloading) {
                        swoole_timer_after($this->intervel * 1000 ,[$this,'reload']);
                    }
                }
            }
        });
    }

    public function reload()
    {
        $this->reloading = true ;
        $this->output("server is reloading!");
        // 重启中
        posix_kill($this->pid ,SIGUSR1);
        // 重新监听 .
        $this->wathers = [] ;
        $this->addWatch($this->root ,true);
        $this->reloading = false ;
    }

    public function addWatch($dir,$root = false)
    {
        if(!is_dir($dir)){
            $this->output("dir :{$dir} not exists",true);
        }
        if($root){
            $this->wathers [] = inotify_add_watch($this->watcher, $dir , $this->event);
        }
        $dirs = scandir($dir);
        for($i = 2 ; $i < count($dirs); $i ++){
            $path = $dir . '/' . $dirs [$i];
            $ext = strstr($path,'.');
            if(is_dir($path)){
                $this->addWatch($path,false);
            }else{
                if(in_array($ext , $this->fileTypes )){
                    $this->wathers [] = inotify_add_watch($this->watcher ,$path ,$this->event);
                }
            }
        }
    }

    public function clearWatch()
    {
        foreach ($this->wathers as $v){
            inotify_rm_watch($this->watcher,$v);
        }
        $this->wathers = [] ;
    }

    public function output($str, $exit = false)
    {
        $date = date("Y-m-d H:i:s", time());
        echo "[ {$date} ]\t" . $str . PHP_EOL;
        if ($exit) {
            exit(0);
        }
    }
}