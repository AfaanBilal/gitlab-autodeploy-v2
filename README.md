GitLab AutoDeploy v2
==============

Author: **Afaan Bilal ([@AfaanBilal](https://github.com/AfaanBilal))**   
Author URL: **[Google+][1]**

##### Project Page: [afaan.ml/gitlab-autodeploy](https://afaan.ml/gitlab-autodeploy)

## Introduction
**GitLab AutoDeploy v2** is a PHP script to easily deploy web app repositories on `git push`. It 
is based on a [similar script][2] [I][1] wrote for GitHub. The **v2** is a complete 
rewrite of the [original][5] with the major change being that only changes are deployed instead of a complete 
redeployment. So you need to manually deploy at least once and then let it work its magic.

## Features
- No SSH access required.
- No shell access required.
- Fully compatible with shared servers.
- Public, internal and private repos are supported.

## Setup
1. Download [gitlab-autodeploy-v2.php](gitlab-autodeploy-v2.php).
2. Update the `[PRIVATE_TOKEN]` and `[REPO_ID]` with your GitLab [Private token and Project ID][3].
3. Set the deploy directory by replacing `[DEPLOY_DIR]` relative to the script.
4. Set your timezone.
5. Upload the script to your server.
6. On GitLab, [add a WebHook][4] to your repo for a `push` event (or any other) and
set it to the uploaded script.
7. You're done!

Now, whenever you `git push` to your gitlab repo, it will be automatically deployed
to your web server!

## Getting your `Private Token` and `Project ID`
1. Your private token can be found in your GitLab profile in `Profile Settings` &raquo; `Account`. 
2. For your project ID, go to https://gitlab.com/api/v3/projects/?private_token=PRIVATE-TOKEN 
(Replace PRIVATE-TOKEN with your private token)
3. Find your project in the returned JSON and copy the ID associated with it.


## Adding a WebHook
*You must have administrative access to the repo for adding WebHooks*

1. Go to your GitLab repo &raquo; `Settings` &raquo; `Web Hooks`.  
2. Enter the URL of the gitlab-autodeploy.php script in the `URL` field.  
3. If your server does not use SSL, then uncheck `Enable SSL Verification`.  
4. Leave everything else as is, click `Add Web Hook`.  
5. You're done!  

## Contributing
All contributions are welcome. Please create an issue first for any feature request
or bug. Then fork the repository, create a branch and make any changes to fix the bug 
or add the feature and create a pull request. That's it!
Thanks!

## License
**GitLab AutoDeploy v2** is released under the MIT License.
Check out the full license [here](LICENSE).

[1]: https://google.com/+AfaanBilal                   "Afaan Bilal"
[2]: https://github.com/AfaanBilal/github-autodeploy  "GitHub AutoDeploy"
[3]: #getting-your-private-token-and-project-id       "Getting your Private Token and Project ID"
[4]: #adding-a-webhook                                "Adding a WebHook"
[5]: https://github.com/AfaanBilal/gitlab-autodeploy  "GitLab AutoDeploy"
