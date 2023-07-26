<?php
namespace Yashsahu\CoreBatch;

require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Core\Batch\BatchTrait;

class BatchProcessor
{
    use BatchTrait;
    private $identifier;


    private static $app;

    public function __construct($options = [])
    {
        $this->identifier = $options['identifier'] ?? uniqid();
        $this->setCommonBatchProperties($options + [
            'identifier' => $this->identifier,
            'batchMethod' => 'batchMethod',
            'debugOutput' => true,
            'batchOptions' => [
                'batchSize' => 1,
                'callPeriod' => 2.0,
                'numWorkers' => 1
            ]
        ]);
    }

    /**
     * Method which is used to flush the jobs
     */
    public function batchMethod(array $args)
    {
        if (!(self::$app)) {
            self::$app = new App;
        }
        self::$app->run($args);
    }

    public function submitJob($item)
    {
        $this->batchRunner->submitItem($this->identifier, $item);
    }

    protected function getCallback()
    {
        return [$this, $this->batchMethod];
    }

    private function createOutputfile()
    {
        $baseDir = sprintf(__DIR__ . '/../output');
        $logFile = sprintf(
            '%s/failed-items-%d',
            $baseDir,
            getmypid()
        );
        $fp = null;
        try {
            $fp = fopen($logFile, 'a');
        } catch (\Throwable $err) {
            echo $err->getMessage() . PHP_EOL;
            $fp = fopen('php://stderr', 'w');
        }
        return $fp;
    }
}
