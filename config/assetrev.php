<?php
return array(
    '*' => array(
        'strategies' => [
            'manifest' => \club\assetrev\utilities\strategies\ManifestFileStrategy::class,
            'querystring' => \club\assetrev\utilities\strategies\QueryStringStrategy::class,
            'passthrough' => function ($filename, $config) {
                return $filename;
            },
        ],
        'pipeline' => 'manifest|querystring|passthrough',
        'manifestPath' => 'web/assets/css/rev-manifest.json',
        'assetsBasePath' => '../web/assets/',
        'assetUrlPrefix' => 'assets/css/',
    ),
);