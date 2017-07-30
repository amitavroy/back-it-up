<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Frequency of backup
    |--------------------------------------------------------------------------
    |
    | The frequency at which the backup command will run.
    | The variable name should be from the list of
    | allowed functions which comes with the
    | schedule function
    */
    'frequency' => 'everyMinute',

    /*
    |--------------------------------------------------------------------------
    | Folder for backup
    |--------------------------------------------------------------------------
    |
    | The folder inside which the database backup will be stored.
    | Right now only supporting S3 bucket because the link is
    | generated based on the S3 url patter.
    */
    'folder' => 'syncup',

    /*
    |--------------------------------------------------------------------------
    | Flag to send email or not
    |--------------------------------------------------------------------------
    |
    | Enable / Disable the option to send email once a database backup
    | has been taken and uploaded to the desired file system.
    */
    'send-email' => true,

    /*
    |--------------------------------------------------------------------------
    | Email send to an email address and other details
    |--------------------------------------------------------------------------
    |
    | The email address which will be used as the from email address
    | when sending the email if the option is enabled in config.
    */
    'mail-to' => 'amitav.roy@focalworks.in',
    'mail-subject' => 'Backup of Trualta DB done',
];