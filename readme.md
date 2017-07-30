# Back it up
This is a Laravel based micro service which can be used to 
create database backups and then compress the file using tar to
create a xyz.tar.gz file and then upload that compressed file
to S3 bucket. 

Right now this package is configured heavily to upload the backup
file on S3 bucket and most of the settings are configured around 
that, but soon I will plan to support multiple file system. 

The main backup of SQL is a package on which I am replying on
developed by Spatie. The reason for this package is that, I have 
customised it as per my and my client's requirements.

Right now, this is a Laravel based application, but my idea is to 
make this a package so that it can be just required inside any 
application and it will just work fine instead of running a 
separate application in itself. But there is also an advantage of
running a different application which is process load and CPU 
utilisation can be checked easily when backups are taken because 
the cron will be different.