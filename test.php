<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
echo "Redis is connected: " . ($redis->ping() ? 'PONG' : 'FAIL');
