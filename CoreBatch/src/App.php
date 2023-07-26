<?php
namespace Yashsahu\CoreBatch;

require_once __DIR__ . '/../vendor/autoload.php';

class App
{
    static $pass = 0;

    public function run(array $args)
    {
        $startTime = microtime(true);

        $this->doTask();

        $endTime = microtime(true);
        $time = $endTime - $startTime;
        echo "Took $time seconds\n";
        printf("%s: %s" . PHP_EOL,$args[0], self::$pass++);
    }

    private function doTask(int $count = 3000)
    {
        $arr = [];
        for ($_ = 0 ; $_ < $count ; $_++) {
            $arr[] = random_int(10, 100000);
        }
        $min = 1000000000;
        for ($i = 0 ; $i < $count ; $i++) {
            for ($j = 0 ; $j < $count ; $j++) {
                $min = min($min, $arr[$i], $arr[$j]);
            }
        }
    }
}

