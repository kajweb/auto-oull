<?php
include(__DIR__.'/git.php');

Class framework{

	public $swoole;

	public $gitConfig;

	public $ROOT;

    private static $instance;


    // （单例模式）获得实例
    static function getInstance(...$args)
    {
        if(!(self::$instance instanceof self)){
            self::$instance = new static(...$args);
        }
        return self::$instance;
    }

    // 初始化swoole对象
    function init( $port ){
    	$this->ROOT = realpath(__DIR__.'/../');
    	$this->swoole = new swoole_http_server("0.0.0.0", $port);
    	return $this->swoole;
    }

    // 设置启动swoole进程时是否使用守护进程
    function setDaemonize( $bool ){
    	if( $bool ){
	    	$this->swoole->set([
			    'daemonize'=> $bool,
			    'log_file'=> $this->ROOT.'/log/log_file.log',
			    'pid_file' => $this->ROOT.'/log/server.pid',
			]);
    	} else {
	    	$this->swoole->set([
			    'daemonize'=> $bool,
			    'log_file'=> $this->ROOT.'/log/swoole_error.log'
			]); 
    	}
    }

    // 获得swoole实例
    function getSwoole(){
    	return $this->swoole;
    }

    // 启动进程
    function start(){
    	$this->getSwoole()->start();
    }

    // 重启进程
    function reload(){
    	echo "执行重启操作中\n";
    	return $this->swoole->reload();
    }

    // 停止进程
    function shutdown(){
    	echo "停止任务\n";
    	return $this->swoole->shutdown();
    }

    // 向页面输出UTF-8编码头
    private function commonHeader( $response ){
	    $response->header("Content-Type", "text/plain; charset=UTF-8");
    }

    // 路由，根据相应的源分发到具体的控制器
	function Router( $request, $response ){
		$gitConfig = $this->getGitConfig();
		$this->commonHeader( $response );
	    $uri = $request->server['request_uri'];
	    switch ($uri) {
	        case "/favicon.ico":
	            return false;
	            break;
	        case "/reload":
	            $this->reload();
	            $response->end( "发送重启任务成功\n");
	            break;
	        case "/shutdown":
	            $this->shutdown();
	            $response->end( "停止任务成功\n");
	            break;
	        case '/github':
	            GIT::getInstance()->github( $request, $response, $gitConfig["github"] );
	        	break;
	        case '/gitlab':
	            GIT::getInstance()->gitlab( $request, $response, $gitConfig["gitlab"] );
	        	break;
	        case '/gitee':
	        default:
	            GIT::getInstance()->gitee( $request, $response, $gitConfig["gitee"] );
	            break;
	    }
	}

	// 加载git配置文件
	function loadGitConfigs(){
		$basePath = $this->ROOT."/config/git/";
	    $gitConfig = [
	        "gitee" => include("${basePath}gitee.php"),
	        "github" => include("${basePath}github.php"),
	        "gitlab" => include("${basePath}gitlab.php")
	    ];
	    $this->gitConfig = $gitConfig;
	}

	// 获得git配置文件
	function getGitConfig(){
		return $this->gitConfig;
	}

    function onStart( $func ){
    	$this->getSwoole()->on("start", $func->bindTo($this));
    }

    function onWorkerStart( $func ){
    	$this->getSwoole()->on("WorkerStart", $func->bindTo($this));
    }

    function onRequest( $func ){
    	$this->getSwoole()->on("request", $func->bindTo($this));
    }
}