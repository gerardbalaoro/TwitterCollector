<?php
/**
 * Export Search Results to CSV
 */

require_once "vendor/autoload.php";

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;
use Rakit\Validation\Validator;

/**
 * Initialize Validator Instance
 */
$validator = new Validator;
$validation = $validator->validate($_GET, [
    'id' => 'required',
]);

/**
 * Primary Export Logic
 */
if (!$validation->fails()) {
    $file = "cache/search/{$_GET['id']}.json";

    if (file_exists($file)) {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename={$_GET['id']}.csv");

        $output = fopen('php://output', 'w');

        fputcsv($output, array('id', 'user', 'text', 'created_at', 'hashtags'));
        $data = json_decode(file_get_contents($file));

        foreach ($data->results as $tweet) {
            $hashtags = [];

            if (empty($_GET['noRT']) || ($_GET['noRT'] !== 'off' && !isset($tweet->retweeted_status))) {
                foreach ($tweet->entities->hashtags as $tag) {
                    $hashtags[] = $tag->text;
                }

                fputcsv($output, [$tweet->id, $tweet->user->screen_name, $tweet->text, $tweet->created_at, implode(', ', $hashtags)]);
            }
        }
    } else {
        http_response_code(404);
    }
} else {
    http_response_code(400);
}
