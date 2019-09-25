# Twitter Collector

Data Collection Tool for Twitter Full Archive

![](assets/img/cover.png)

This is a rough implementation of the Twitter Search API. It retrieves tweets from the Twitter Full Archive using keywords and other parameters, and gives the option to export these results in CSV format. This is intended to be a **data collection tool alone** and **should not** be used in production.

## Usage

- Install composer dependencies

    ```bash
    $ composer install
    ```

- Define Consumer Key and Secret inside the **index.php** file

    ```php
    <?php
    ...

    /**
    * Define Twitter API Key and Secret
    */
    define('CONSUMER_KEY', '');
    define('CONSUMER_SECRET', '');
    ```

## Query Caching

Since Twitter limits the number of calls to their search API, the application, by default, caches all query results to prevent
duplicate searches from costing the monthly quota. To disable this, simply mark the **Disable Caching** checkbox and the application
will retrieve the latest results from the archive and refresh the cache.