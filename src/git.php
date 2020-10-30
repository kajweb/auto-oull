<?php
Class GIT{
    private static $instance;

    // （单例模式）获得实例
    static function getInstance(...$args)
    {
        if(!(self::$instance instanceof self)){
            self::$instance = new static(...$args);
        }
        return self::$instance;
    }

    // 检查项目是否存在于配置文件
    private function isProjectExits( $nameSpace, $config ){
    	return array_key_exists( $nameSpace, $config );
    }

    // 检查是否为pull请求
    private function isProjectNeedPull( $nameSpace, $config ){
    	return $config[$nameSpace]['pull'];
    }

    // 获取项目pull以后需要执行的代码
    private function getProjectScript( $nameSpace, $config ){
    	return $config[$nameSpace]['script'];
    }

    // 检查代码路径是否设置
    private function isSetPath( $nameSpace, $config ){
    	return isset($config[$nameSpace]['path']);
    }

    // 代码更新完, 是否需要更新本githook(一般用于自身更新)
    private function isProjectNeedReload( $nameSpace, $config ){
    	return isset($config[$nameSpace]['reload']);
    }

    // 当请求来源为gitee时需要处理的代码
	function gitee( $request, $response, $config ){
		$command = ["cd {$path}"];
	    $now = date("Y-m-d H:i:s");
	    echo "\n[{$now}]\n";
	    $uri = $request->server['request_uri'];
	    echo "请求地址： {$uri}\n";

	    //获得参数
	    $hookBody = $request->rawContent();
	    if( !$hookBody ){
	        echo "非gitee发送过来的请求，请求终止\n";
	        $response->end("非gitee发送过来的请求，请求终止\n");
	        return false;
	    }

	    $data = json_decode($hookBody,true);
	    $name = $data['repository']['name'];	//项目名称
	    $nameSpace = $data['repository']['path_with_namespace'];	//项目命名空间

	    echo "当前请求的项目为：" . $name . "\n";

	    // 判断该项目是否配置
	    $isProjectExits = self::isProjectExits( $nameSpace, $config );
	    if( !$isProjectExits ){
			echo "没有找到 {$name}({$nameSpace}) 项目配置\n";
	        $response->end("没有找到 {$name} 项目配置\n");
	        return false;
	    }

	    // 判断该项目是否需要pull
	    $isProjectNeedPull = self::isProjectNeedPull( $nameSpace, $config );
	    $isSetPath = self::isSetPath( $nameSpace, $config );
	    if( $isProjectNeedPull && $isSetPath ){
	    	echo "项目 {$name}({$nameSpace}) 开启自动pull\n";
			$getGitPullCommand = $this->getGitPull( $config[$nameSpace]['path'] );
	    	$command = array_merge( $command, $getGitPullCommand );
	    } elseif( !$isProjectNeedPull ){
	        echo "项目 {$name}({$nameSpace}) 自动pull没有开启\n";
	    } elseif( !$isSetPath ){
	        echo "项目 {$name} path未设置或设置有误\n";
	        $response->end("项目 {$name} path未设置或设置有误\n");
	        return false;
	    } else {
	        echo "项目 {$name} 其他设置有误\n";
	        $response->end("项目 {$name} 其他设置有误\n");
	        return false;
	    }

	    // 是否存在其他需要执行的语句
	    $projectScript = self::getProjectScript( $nameSpace, $config );
	    if( $projectScript ){
	    	$command = array_merge( $command, $projectScript );
	    }

	    // 执行指定的git语句
	    $return = $this->execPull( $command );

	    echo implode("\n", $return);

	    $isProjectNeedReload = self::isProjectNeedReload( $nameSpace, $config );
	    if( $isProjectNeedReload ){
	        echo "\n项目 {$name}({$nameSpace}) 需要重启swoole\n";
	        framework::getInstance()->reload();
	    }
	    
	    echo "执行语句结果：" . end($return)."\n";
	    $response->end( "执行语句结果：" . end($return)."\n");
	    return true;
	}

	function gitee( $request, $response, $config ){
	    // todo
		$response->end( "当前暂不支持自动部署 github 平台的代码\n");
		return false;
	}

	function gitlab( $request, $response, $config ){
	    // todo
		$response->end( "当前暂不支持自动部署 gitlab 平台的代码\n");
		return false;
	}

	// 获得git pull命令
	function getGitPull( $path ){
	    $command = [];
	    $command[] = "git pull";
	    return $command;
	}

	// 执行脚本代码
	function execPull( $command ){
		$commandLine = implode(" && ", $command);
		$return = [];
	    exec( $commandLine, $return );
	    return $return;
	}
}