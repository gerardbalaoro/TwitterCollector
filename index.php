<?php
/**
 * Retrieve Tweets Using Keywords
 */

require_once "vendor/autoload.php";

use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;
use Rakit\Validation\Validator;
use Jenssegers\Blade\Blade;
use Twitter\Text\Autolink;
use Carbon\Carbon;

/**
 * Define Twitter API Key and Secret
 */
define('CONSUMER_KEY', '');
define('CONSUMER_SECRET', '');


/**
 * Create Cache Directories
 */
$directories = [
    'cache/render',
    'cache/search',
    'cache/tweets'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}


/**
 * Initialize Blade Template Renderer
 */
$blade = new Blade('templates', 'cache/render');
$data = [];


/**
 * Helper Functions
 *
 */
function old($name, $default = null)
{
    return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $default);
}

function autolink($tweet)
{
    return (new Autolink())->autoLink($tweet);
}


/**
 * Primary App Logic
 */
if (!empty($_GET['query'])) {
    $validator = new Validator;
    $validation = $validator->validate($_GET, [
        'query'         => 'required',
        'fromDate'      => 'date:m/d/Y',
        'toDate'        => 'date:m/d/Y'. (strtotime($_GET['fromDate']) ? '|after:'.$_GET['fromDate'] : null),
        'maxResults'    => 'numeric|between:10,100',
        'useCache'      => 'in:on,off',
        'hideRT'        => 'in:on,off',
    ]);
    

    if ($validation->fails()) {
        $data['error'] = ['title' => 'Validation Error', 'content' => $validation->errors()->all()];
    } else {
        $useCache = false;

        parse_str($_SERVER['QUERY_STRING'], $query_vars);
        $query_vars = array_filter($query_vars);
        unset($query_vars['disableCache']);
        unset($query_vars['hideRT']);
        $query_str = http_build_query($query_vars);
        $query_hash = hash('sha256', $query_str);

        if (empty($_GET['disableCache']) || $_GET['disableCache'] === 'off') {
            if (file_exists("cache/search/{$query_hash}.json")) {
                $useCache = true;
                $data['tweets'] = json_decode(file_get_contents("cache/search/{$query_hash}.json"));
                $data['cached'] = true;
            }
        }

        if ($useCache == false) {
            try {
                $conn = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

                $data['tweets'] = $conn->get("tweets/search/fullarchive/development", array_filter([
                    'query' => $_GET['query'],
                    'fromDate' => !empty($_GET['fromDate']) ? date('YmdHi', strtotime($_GET['fromDate'])) : null,
                    'fromDate' => !empty($_GET['toDate']) ? date('YmdHi', strtotime($_GET['fromDate'])) : null,
                    'maxResults' => intval($_GET['maxResults']),
                    'next' => !empty($_GET['next']) ? $_GET['next'] : null,
                ]));

                file_put_contents("cache/search/{$query_hash}.json", json_encode($data['tweets'], JSON_PRETTY_PRINT));
            } catch (Exception $e) {
                $data['error'] = ['title' => basename(get_class($e)), 'content' => $e->getMessage()];
            }
        }

        $data['download'] = $query_hash;

        if (isset($data['tweets']) && isset($data['tweets']->results)) {
            if (!empty($_GET['hideRT']) && $_GET['hideRT'] !== 'off') {
                $results = [];
                foreach ($data['tweets']->results as $id => $tweet) {
                    if (empty($tweet->retweeted_status)) {
                        $results[] = $tweet;
                    }
                }

                $data['tweets']->results = $results;
            }
        }
    }
}

echo $blade->make('app', $data);
