<?php

# Composer autoloader
require_once 'vendor/autoload.php';

# Guzzle Client

$guzzleClient = new Guzzle\Http\Client();
$guzzleClient->setUserAgent("Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1664.3 Safari/537.36");


// If the URL is set, read it
if (!isset($argv[1])) {
    echo "ERROR: You didn't enter any URL" . PHP_EOL;
    exit();
}

$url = $argv[1];

// Initial Banner
echo PHP_EOL;
echo ConsoleColors::foregroundColor('brown', "Validating: ") . $url . PHP_EOL . PHP_EOL;

try {
    $html = $guzzleClient->get($url)->send();
} catch (Exception $ex) {
    echo ConsoleColors::foregroundColor('red', "Error fetching the URL") . PHP_EOL . PHP_EOL;
    exit();
}


// Check with W3C Validator
try {
    $response = $guzzleClient
        ->post(
            "http://validator.w3.org/check",
            null,
            array(
                "fragment" => $html,
                "output" => "json"
            )
        )
        ->send();

    $json = (string)$response->getBody();
    $validationResults = json_decode($json);
} catch (Exception $ex) {
    echo ConsoleColors::foregroundColor('red', "Validation Error: ") . $ex->getMessage() . PHP_EOL . PHP_EOL;
    exit();
}

// Display the responses
foreach ($validationResults->messages as $result) {
    if ($result->type != "info") {
        echo ConsoleColors::foregroundColor('bold_purple', ucfirst($result->type) . ": ") . ConsoleColors::foregroundColor('green', "Line {$result->lastLine}, Column {$result->lastColumn} " . PHP_EOL);
        echo ConsoleColors::foregroundColor('blue', "Message: ") . ucfirst($result->message) . PHP_EOL . PHP_EOL;
    }
}

echo PHP_EOL;

// Class for console colors

class ConsoleColors
{
    private static $foreground = array(
        'black' => '0;30',
        'dark_gray' => '1;30',
        'red' => '0;31',
        'bold_red' => '1;31',
        'green' => '0;32',
        'bold_green' => '1;32',
        'brown' => '0;33',
        'yellow' => '1;33',
        'blue' => '0;34',
        'bold_blue' => '1;34',
        'purple' => '0;35',
        'bold_purple' => '1;35',
        'cyan' => '0;36',
        'bold_cyan' => '1;36',
        'white' => '1;37',
        'bold_gray' => '0;37',
    );

    private static $background = array(
        'black' => '40',
        'red' => '41',
        'magenta' => '45',
        'yellow' => '43',
        'green' => '42',
        'blue' => '44',
        'cyan' => '46',
        'light_gray' => '47',
    );

    /**
     * Make string appear in color
     */
    public static function foregroundColor($color, $string)
    {
        if (!isset(self::$foreground[$color])) {
            throw new Exception('Foreground color is not defined');
        }

        return "\033[" . self::$foreground[$color] . "m" . $string . "\033[0m";
    }

    /**
     * Make string appear with background color
     */
    public static function backgroundColor($color, $string)
    {
        if (!isset(self::$background[$color])) {
            throw new Exception('Background color is not defined');
        }

        return "\033[" . self::$background[$color] . 'm' . $string . "\033[0m";
    }

}