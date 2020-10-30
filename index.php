<?php
include(__DIR__.'/src/framework.php');
$config = include(__DIR__.'/config/config.php');

$framework = framework::getInstance();

$port = $config['port'];

$framework->init( $port );
$framework->setDaemonize( $config['daemonize'] );

$framework->onStart( function($server)use($port) {
    echo "webHook监听服务已经启动于${port}端口\n\n";
    date_default_timezone_set('PRC');
});

$framework->onWorkerStart( function($server, $workerId)use($framework) {
    $this->loadGitConfigs();
});

$framework->onRequest( function($request, $response)use($framework) {
    $this->Router( $request, $response );
});

$framework->start();
