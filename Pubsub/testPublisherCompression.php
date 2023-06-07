<?php

if (!getenv('GRPC_VERBOSITY') || !getenv('GRPC_TRACE')) {
    echo "Please set GRPC_VERBOSITY and GRPC_TRACE environment variables\n";
    exit(1);
}

/**
 * Source : https://stackoverflow.com/a/32664523
 * Execute the given command by displaying console output live to the user.
 *  @param  string  cmd          :  command to be executed
 *  @return array   exit_status  :  exit status of the executed command
 *                  output       :  console output of the executed command
 */
function liveExecuteCommand($cmd)
{

    while (@ ob_end_flush()); // end all output buffers if any

    $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

    $live_output     = "";
    $complete_output = "";

    while (!feof($proc))
    {
        $live_output     = fread($proc, 4096);
        $complete_output = $complete_output . $live_output;
        echo "$live_output";
        @ flush();
    }

    pclose($proc);

    // get exit status
    preg_match('/[0-9]+$/', $complete_output, $matches);

    // return exit status and intended output
    return array (
                    'exit_status'  => intval($matches[0]),
                    'output'       => str_replace("Exit status : " . $matches[0], '', $complete_output)
                 );
}

$contents = json_decode(file_get_contents("bigData/data.json"), true);
$x = "Actual Data Size, Inbound data(with metadata) size, Compressed size, Percentage savings\n";
file_put_contents('resultBig.txt', $x, FILE_APPEND);
foreach($contents as $content) {
    // file_put_contents('data/temp_data', serialize(json_encode($content)));
    $fileName = $content['fileName'];
    $size = filesize("bigData/" . $fileName);
    $result = liveExecuteCommand("php publishScript.php bigData/" . $fileName);
    $words = explode(' ', $result['output']);
    $key = array_search('Compressed[gzip]', $words);
    if (!$key) {
        file_put_contents(
            'result.txt',
            sprintf("For %s bytes of data, grpc decided not to compress\n", $size),
            FILE_APPEND
        );
        continue;
    }
    $actualSize = $words[$key + 1];
    $compressedSize = $words[$key + 4];
    $percentageSavings = substr($words[$key + 6], 1, -1);

    // Verify is correct properties got extracted
    // $x = sprintf(
    //     "For a data of %s bytes, %s %s bytes vs. %s bytes (%s%% savings)\n",
    //     $size,
    //     $words[$key],
    //     $actualSize,
    //     $compressedSize,
    //     $percentageSavings
    // );
    $x = sprintf("%s, %s, %s, %s\n", $size, $actualSize, $compressedSize, $percentageSavings);

    // Verify is correct properties go");

    // unlink('data/temp_data');
    file_put_contents('result.txt', $x, FILE_APPEND);
}

