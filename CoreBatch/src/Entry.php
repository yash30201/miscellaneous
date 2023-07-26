<?php
namespace Yashsahu\CoreBatch;
use Symfony\Component\Process\Process;

require_once __DIR__ . '/../vendor/autoload.php';
/**
 * Class to initiate batch runner and run the application
 */
class Entry
{
    private const BATCH_DAEMON_PATH = __DIR__ . '/../vendor/bin/google-cloud-batch';

    public function startBatchDaemon()
    {
        // $command = sprintf('%s daemon', self::BATCH_DAEMON_PATH);
        // exec(sprintf('nohup %s &', $command));
        // putenv("IS_BATCH_DAEMON_RUNNING=true");
        $process = new Process([self::BATCH_DAEMON_PATH, 'daemon']);
        $process->start();
        sleep(1);
        $out = $process->getOutput();
        echo $out . PHP_EOL;
        $this->startApplication();
        sleep(3);
        $process->stop();
    }

    public function startApplication()
    {
        putenv("IS_BATCH_DAEMON_RUNNING=true");
        $batch = new BatchProcessor();
        // $app = new App();
        $startTime = microtime(true);
        for ($i = 0 ; $i < 4 ; ++$i) {
            // $app->run(['Hello ' . $i]);
            $batch->submitJob('Hello' . $i);
        }
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        // echo "Took $time seconds\n";
    }

    public function stopDaemon()
    {

    }

}

$entry = new Entry();
$entry->startBatchDaemon();
// $entry->startApplication();
// $entry->stopDaemon();
