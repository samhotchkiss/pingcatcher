# pingcatcher
Catches GitHub and WordPress.com mentions, adds them to todoist.

To set this up, you'll need to upload this folder to a web server you have somewhere, and define 7 configuration variables at the top of pingcatcher.php.

Then you'll need to set up a cron job to regularly (I recommend every 15 minutes) hit yourserver.com/pingcatcher/pingcatcher.php?secret=EXEC_SECRET