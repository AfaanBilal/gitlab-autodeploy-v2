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

// LOG ? (FALSE or 'filename')
define('LOGFILE',           'gitlab-autodeploy-v2.log');

// TimeZone (for LOG)
date_default_timezone_set("Asia/Kolkata");

define('API_ENDPOINT',      'https://gitlab.com/api/v3/projects/'    . REPO_ID       . '/repository');
define('API_COMPARE',       API_ENDPOINT . '/compare?private_token=' . PRIVATE_TOKEN);
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

echo "{status:success}";

// get webhook data
$json = file_get_contents('php://input');
$data = json_decode($json, TRUE);

// send compare request
$compareData = json_decode(file_get_contents(API_COMPARE . '&from=' . $data['before'] . '&to=' . $data['after']), TRUE);

if (in_array("message", array_keys($compareData)))
{
    writeLog("Error: ".$compareData['message']);
    exit;
}

// get file data
$filesRealData = [];
foreach ($compareData['diffs'] as $v) 
{
    if ($v['deleted_file'])
    {
        @unlink($v['old_path']); 
        continue;
    }
    else if ($v['renamed_file'])
    {
        @unlink($v['old_path']);
    }
    
    $fileData = json_decode(file_get_contents(API_FILES . $v['new_path']), TRUE);
    
    if (in_array("message", array_keys($fileData)))
    {
        writeLog("Error: ".$fileData['message']);
        continue;
    }
    
    $filesRealData[] = [
      'path' => $fileData['file_path'],
      'content' => base64_decode($fileData['content'])
    ];
}

// write files
foreach ($filesRealData as $v) 
{
    if (!file_exists(dirname($v['path'])))
        mkdir(dirname($v['path']), 0777, TRUE);
    
    file_put_contents(DEPLOY_DIR . $v['path'], $v['content']);
}

writeLog("Deployment complete.");
