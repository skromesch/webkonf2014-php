<?php

return array(
    /**
     * You can define global configuration settings for the SDK as an array. Typically, you will want to a provide your
     * credentials (key and secret key) and the region (e.g. us-west-2) in which you would like to use your services.
     */
     'aws' => array(
         'key'    => '<your_access_key',
         'secret' => '<your_secret_key>',
         'region' => 'us-east-1'
     )

    /**
     * You can alternatively provide a path to an AWS SDK for PHP config file containing your configuration settings.
     * Config files can allow you to have finer-grained control over the configuration settings for each service client.
     */
    // 'aws' => 'path/to/your/aws-config.php'
);
