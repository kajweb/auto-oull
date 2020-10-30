<?php
return [
	"kajweb/stop-debugger" => [
		"pull" => true,
		"path" => "/mnt/www/kajweb/stop-debugger"
	],
    "kajweb/githook" => [
		"pull" => true,
        "reload" => true,
		"path" => "/mnt/www/kajweb/githook",
		"script" => [
			"date +'%Y/%m/%d %H:%M:%S'>./build.txt",
			"npx marked -i wiki.md -o wiki/index.html",
            "echo markBuildTime",
            "\\rm -rf ./dist/*",
            "date +'%Y-%m-%d %H:%M:%S'>./dist/build.txt",
            "echo Mission Completed"
		]
	]
];