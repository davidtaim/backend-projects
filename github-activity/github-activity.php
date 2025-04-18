<?php

declare(strict_types=1);

if ($argc != 2) {
    print "there is an error with the paramaters it just need one\n";
    print "php github-activity.php [username]\n";
    return;
}

$url = "https://api.github.com/users/%s/events";

$curl = curl_init(sprintf($url, $argv[1]));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_USERAGENT, "curl.php");
$response = curl_exec($curl);
$error = curl_error($curl);

if ($error) {
    print "there was an error performing the request: $error\n";
    return;
}

$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if ($httpCode == 404) {
    print "username does not exist!\n";
    return;
}

$response = json_decode($response, true);

foreach ($response as $event) {
    switch ($event["type"]) {
        case "WatchEvent":
            print "Starred a new repo: " . $event["repo"]["name"];
            break;
        case "PushEvent":
            $count = count($event["payload"]["commits"]);
            print "Pushed $count commits to: " . $event["repo"]["name"];
            break;
        case "IssuesEvent":
            $status = ucfirst($event["payload"]["action"]);
            print "$status an issue on: " . $event["repo"]["name"];
            break;
        case "IssueCommentEvent":
            $status = $event["payload"]["action"];
            $issue = $event["payload"]["issue"]["number"];
            print "Comment $status on issue #$issue on repo: " . $event["repo"]["name"];
            break;
        case "ForkEvent":
            print "Forked repo: " . $event["payload"]["forkee"]["full_name"] .
            " from original repo: " . $event["repo"]["name"];
            break;
        default:
            //! Use 2 as a param because switch is a loop so it's 2 level deep
            continue 2;
    }
    print PHP_EOL;
}
print PHP_EOL;