--TEST--
Test of swoole_http_client->post
--SKIPIF--
<?php include "skipif.inc"; ?>
--FILE--
<?php
include "include.inc";

function start_swoole_http_server() {
	$code = <<<'DOC'
		$http = new swoole_http_server("127.0.0.1", 9501, SWOOLE_BASE);
		$http->set(array(
				'worker_num' => 2,
		));
		$http->on('request', function ($request, swoole_http_response $response) {
				$route = $request->server['request_uri'];
				if($route == '/info'){
						$response->end($request->post['bat']);
						return;
				}else{
					$cli = new swoole_http_client('127.0.0.1', 9501);
					$cli->set(array(
							'timeout' => 0.3,
					));
					$cli->setHeaders(array('User-Agent' => "swoole"));
					$cli->on('close', function($cli)use($response){
					});
					$cli->on('error', function($cli) use ($response){
							echo "error";
							$response->end("error");
					});
					$cli->post('/info', array('bat' => "man"), function($cli)use( $response){
						$response->end($cli->body."\n");
					});
				}
		});

		$http->start();
DOC;
	
	swoole_php_fork($code);
}

start_swoole_http_server();
sleep(1);
echo file_get_contents("http://127.0.0.1:9501/");
?>
Done
--EXPECTREGEX--
man
Done.*
--CLEAN--
