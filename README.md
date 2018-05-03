# pingcatcher
Catches GitHub and WordPress.com mentions, adds them to todoist.

To set this up, you'll need to upload this folder to a web server you have somewhere, rename `secrets-sample.php` to `secrets.php` and define 7 configuration variables in that file.

Then you'll need to set up a cron job to regularly (I recommend every 15 minutes) hit yourserver.com/pingcatcher/pingcatcher.php?secret=EXEC_SECRET -- Try this in a browser, first, to make sure that you're seeing things show up in your Todoist!