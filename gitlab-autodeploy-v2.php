<?php
/*
 *  GitLab AutoDeploy V2
 *  https://afaan.ml/gitlab-autodeploy
 *
 *  Automatically deploy your web app repositories on "git push" or any other hook. 
 *      New in V2:
 *          - Complete rewrite
 *          - Only fetch and deploy changes instead of complete redeployment
 *
 *  Author: Afaan Bilal
 *  Author URL: https://google.com/+AfaanBilal
 *  
 *  - No Shell Access Required
 *  - Best for Shared Hosting Platforms
 *  - Public, internal and private repositories supported
 * 
 *  (c) 2016 Afaan Bilal
 *
 */
 
// Repo ID
define('REPO_ID',           0);

// Private Token
define('PRIVATE_TOKEN',     '[PRIVATE-TOKEN]');

// Deploy Directory 
// (RELATIVE TO THIS FILE)
// Set to empty string in case of current directory.
// If not empty, then MUST end with a forward-slash
define('DEPLOY_DIR',        '');

// Branch name
define('BRANCH',            'master');

// Logging 
//
// if you don't want logging, just set to FALSE
// else set a filename or leave as is.
define('LOGFILE',           'gitlab-autodeploy-v2.log');

// TimeZone (for Logging)
date_default_timezone_set("Asia/Kolkata");

// The GitLab API endpoint for project repositories
define('API_ENDPOINT',      'https://gitlab.com/api/v3/projects/'    . REPO_ID       . '/repository');

// API URL for comparing two commits
//
// We need the 'diffs' part of the response 
// to check what files have changed
define('API_COMPARE',       API_ENDPOINT . '/compare?private_token=' . PRIVATE_TOKEN);

// API URL for getting file data
define('API_FILES',         API_ENDPOINT . '/files?private_token='   . PRIVATE_TOKEN . '&ref=' . BRANCH . '&file_path=');

// Logging
function writeLog($data)
{
    if (LOGFILE == FALSE)
        return;
    
    if (!file_exists(LOGFILE))
    {
        $logFile = fopen(LOGFILE, "a+");
        fwrite($logFile, "--------------------------------------------------------\n");
        fwrite($logFile, "|   PHP GitLab AUTO-DEPLOY V2                          |\n");
        fwrite($logFile, "|   https://afaan.ml/gitlab-autodeploy                 |\n");
        fwrite($logFile, "|   (c) Afaan Bilal ( https://afaan.ml )               |\n");
        fwrite($logFile, "--------------------------------------------------------\n");
        fwrite($logFile, "\n\n");
        fclose($logFile);
    }
    
    $fh = fopen(LOGFILE, "a+");
    fwrite($fh, "\nTimestamp: ".date("d-m-Y h:i:s a"));
    fwrite($fh, "\n\n {$data}");
    fwrite($fh, "\n\n");
    fclose($fh);
}

// send some response to the GitLab server
// GitLab doesn't care what we send
// but just to be nice :)
echo "{status:success}";

// get the webhook data send by GitLab
$json = file_get_contents('php://input');
$data = json_decode($json, TRUE);

// send compare request
//
// $data['before'] contains the starting commit sha1 of the push and
// $data['after'] contains the ending commit sha1 of the push
// so we just compare the two to see what has changed
$compareData = json_decode(file_get_contents(API_COMPARE . '&from=' . $data['before'] . '&to=' . $data['after']), TRUE);

// if there is an error, log it and exit
if (in_array("message", array_keys($compareData)))
{
    writeLog("Error: ".$compareData['message']);
    exit;
}

// get file data
$filesRealData = [];
foreach ($compareData['diffs'] as $v) 
{
    // Log every changed file
    writeLog(
        " == FileChanged == \r\n".
        "Path:    {$v['old_path']} => {$v['new_path']}\r\n".
        "New:     {$v['new_file']}\r\n".
        "Renamed: {$v['renamed_file']}\r\n".
        "Deleted: {$v['deleted_file']}\r\n".
        " == =========== =="
        );
    
    if ($v['deleted_file'])
    {
        // the file has been deleted in the repo
        // so just delete it here as well and continue
        // we don't need to do anything else with it
        @unlink($v['old_path']); 
        continue;
    }
    else if ($v['renamed_file'])
    {
        // the file has been renamed in the repo
        // so just delete the old named file
        // and treat the renamed one as a new file
        @unlink($v['old_path']);
    }
    
    // get the actual file data
    $fileData = json_decode(file_get_contents(API_FILES . $v['new_path']), TRUE);
    
    // if there is an error, log it and continue
    if (in_array("message", array_keys($fileData)))
    {
        writeLog("Error: ".$fileData['message']);
        continue;
    }
    
    // collect the files to be written to disk
    // and decode the content (it's base64 encoded)
    $filesRealData[] = [
      'path' => $fileData['file_path'],
      'content' => base64_decode($fileData['content'])
    ];
}

// write files to disk
foreach ($filesRealData as $v) 
{
    if (!file_exists(dirname($v['path'])))
        mkdir(dirname($v['path']), 0777, TRUE);
    
    file_put_contents(DEPLOY_DIR . $v['path'], $v['content']);
}

// and we are done!
writeLog("Deployment complete.");
