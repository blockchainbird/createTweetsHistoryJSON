<?php
/* workflow creating file with tweets history

This script creates a file with a history of tweets out of a source file with tweets that is refreshed everey ten seconds.

Source file: tweets.json
Target file: tweets-history.json

Flow of the script:
-create tweets-history.json
-for every tweet in tweets.json check if it is already in tweets-history.json
-if the tweet is already in tweets-history.json go to next tweet in tweets.json, if not: add the tweet to tweets-history.json
-if desired: do checks before adding tweet, for example: only if number of followers of poster is above 1000, etc
-the script runs every ten seconds via sleep command
-the file should not grow above a certain size (to prevent server crash or undesired hosting costs), so there should be a mechanism that removes tweets from the tail when file reaches a certain size

example code:
- https://stackoverflow.com/questions/31111963/json-manipulation-in-php
- https://stackoverflow.com/questions/34986948/how-to-remove-duplicate-data-of-json-object-using-php

json_encode — Returns the JSON representation of a value
json_decode — Decodes a JSON string, Takes a JSON encoded string and converts it into a PHP variable.

RUN FROM COMMAND LINE:
start:
$php create-tweets-history.php

stop:
Ctrl - C

 */

function processTweets()
{
    $source = 'tweets.json';
    $target = 'tweets-history.json';


    // https://www.php.net/manual/en/function.cli-set-process-title.php
    // $title = "My Amazing PHP Script";
    // $pid = getmypid(); // you can use this to see your process title in ps
    
    // if (!cli_set_process_title($title)) {
    //     echo "Unable to set process title for PID $pid...\n";
    //     exit(1);
    // } else {
    //     echo "The process title '$title' for PID $pid has been set for your process!\n";
    //     sleep(5);
    // }






    // read content from file:
    $arrayTweetsHistorySerialized = file_get_contents($target);

    // JSON string to PHP array
    $arrayTweetsHistory = json_decode($arrayTweetsHistorySerialized);
    echo gettype($arrayTweetsHistory);

    // read content from file:
    $array = file_get_contents($source);

    // JSON string to PHP array
    $array = json_decode($array, true);

    // https://stackoverflow.com/a/34987161
    // will remove outer object and only take array
    // array_unique makes no sense here, TODO: test if this can be removed
    $array = array_values(array_unique($array, SORT_REGULAR));

    //take the only key of the array with only one key (which is an array):
    $array = $array[0];
    //we now have an array with objects

    foreach ($array as $i => $i_value) {
        if ((int) $i_value['user']['followers_count'] >= 750) {
            echo $i_value['user']['followers_count'];
            echo '\n';

            $qualityTweet[] = $i_value;

            // merge the array item with existing array
            $arrayTweetsHistory = array_merge($arrayTweetsHistory, $qualityTweet);
        }
    }

    // to JSON string
    $arraySerialized = json_encode($array);

    // https://stackoverflow.com/a/34987161
    $arrayTweetsHistory = array_values(array_unique($arrayTweetsHistory, SORT_REGULAR));

    // to JSON string
    $arrayTweetsHistorySerialized = json_encode($arrayTweetsHistory);

    file_put_contents('tweets-history.json', $arrayTweetsHistorySerialized);
}

// http://stackoverflow.com/a/23028860
// endless loop, runs every ten seconds
while (true) {
    processTweets();
    sleep(10); // in seconds
}
