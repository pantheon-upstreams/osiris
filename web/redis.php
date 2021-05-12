<?php
require_once(__DIR__ . "/src/Osiris/PHPInfo.php");


  \Pantheon\Osiris\PHPInfo::getCacheControl();
  $action = isset($_POST['action']) ? $_POST['action'] : '';
  header('Cache-Control: max-age=0, private, no-cache, no-store, must-revalidate');

  echo "<pre>";

  //Connecting to Redis server on localhost
  $redis = new Redis();
  $redis->connect($_ENV['CACHE_HOST'], $_ENV['CACHE_PORT']);
  // echo "Connected to Redis server sucessfully (maybe?)\n";

  $redis->auth($_ENV['CACHE_PASSWORD']);
  // echo "Authenticated with Redis server (maybe?)\n";

  // Check whether server is running or not
  // echo "Server is running: ".$redis->ping() . "\n";

  if ($action == 'Clear') {
    $redis->flushAll();
  }
  if ($action == 'Add 50 Random') {
    for ($i = 0; $i < 50; ++$i) {
      $redis->set('random-' . mt_rand(), mt_rand());
    }
  }

  $count = $redis->dbSize();
  echo "Redis has $count keys\n";

  // Using an aribtrary key, write an arbitrary, ever-changing value
  // into the Redis cache. Report the old and new values.
  $test_key = 'redis-test-key';
  $server_time = $redis->time();
  $new_value = $server_time[0];
  $old_value = $redis->get($test_key);
  $redis->set($test_key, $new_value);

  echo "Old 'redis-test-key' value: " . var_export($old_value, true) . " New value to write: " . var_export($new_value, true) . "\n";

  // Get general information from Redis server
  // $info = $redis->info();
  // var_export($info);

  echo "</pre>";

?>

<form action="redis.php" method="post">
  <p>
    <input type="submit" value="Add 50 Random" name="action"/>
    <input type="submit" value="Clear" name="action"/>
    <input type="submit" value="Reload" name="action"/>
  </p>
</form>
