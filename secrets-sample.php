<?php 

// Get a random string from somewhere like https://www.random.org/strings/?num=1&len=20&digits=on&upperalpha=on&loweralpha=on&unique=on&format=html&rnd=new
// You'll use this to run this script.
define( 'EXEC_SECRET', '' );

// You can get this from https://github.com/settings/tokens/new
// The token needs "repo" and "notifications" access
define( 'GITHUB_TOKEN', '' );

// Your GitHub username (without the @)
define( 'GITHUB_USERNAME', '' );

// The Todoist list ID where you'd like GitHub todos to go.
// To get this, load the list you want to use in the todoist web interface.  At the end of the URL, you'll
// see something like "project%2F1282571104".  The numbers AFTER the "F" are the target list ID
define( 'GITHUB_TARGET_LIST',  );

// You're going to need to create an oAuth app and authorize it to get this token. Sorry!
define( 'WPC_TOKEN', '' );

// The Todoist list ID where you'd like WPCom todos to go.
define( 'WPC_TARGET_LIST',  );

// The Todoist list ID where you'd like WPCom P2 notices to go.
define( 'WPC_P2_TARGET_LIST',  );

// 1. Go to https://developer.todoist.com/appconsole.html
// 2. Create a new app.  Name it anything.
// 3. Scroll down and click "Create Test Token"
define( 'TODOIST_TOKEN', '' );