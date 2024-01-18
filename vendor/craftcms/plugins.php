<?php

$vendorDir = dirname(__DIR__);
$rootDir = dirname(dirname(__DIR__));

return array (
  'carlcs/craft-redactorcustomstyles' => 
  array (
    'class' => 'carlcs\\redactorcustomstyles\\Plugin',
    'basePath' => $vendorDir . '/carlcs/craft-redactorcustomstyles/src',
    'handle' => 'redactor-custom-styles',
    'aliases' => 
    array (
      '@carlcs/redactorcustomstyles' => $vendorDir . '/carlcs/craft-redactorcustomstyles/src',
    ),
    'name' => 'Redactor Custom Styles',
    'version' => '3.0.4',
    'description' => 'Redactor Custom Styles plugin for Craft CMS',
    'developer' => 'carlcs',
    'developerUrl' => 'https://github.com/carlcs',
    'documentationUrl' => 'https://github.com/carlcs/craft-redactorcustomstyles',
    'changelogUrl' => 'https://github.com/carlcs/craft-redactorcustomstyles/raw/v3/CHANGELOG.md',
    'downloadUrl' => 'https://github.com/carlcs/craft-redactorcustomstyles/archive/v3.zip',
  ),
  'hybridinteractive/craft-position-fieldtype' => 
  array (
    'class' => 'rias\\positionfieldtype\\PositionFieldtype',
    'basePath' => $vendorDir . '/hybridinteractive/craft-position-fieldtype/src',
    'handle' => 'position-fieldtype',
    'aliases' => 
    array (
      '@rias/positionfieldtype' => $vendorDir . '/hybridinteractive/craft-position-fieldtype/src',
    ),
    'name' => 'Position Fieldtype',
    'version' => '1.0.17',
    'schemaVersion' => '1.0.0',
    'description' => 'Brings back the Position fieldtype from Craft 2',
    'developer' => 'Hybrid Interactive',
    'developerUrl' => 'https://hybridinteractive.io',
    'documentationUrl' => 'https://github.com/hybridinteractive/craft-position-fieldtype/blob/master/README.md',
    'changelogUrl' => 'https://raw.githubusercontent.com/riasvdv/craft-position-fieldtype/master/CHANGELOG.md',
    'hasCpSettings' => false,
    'hasCpSection' => false,
  ),
  'marionnewlevant/snitch' => 
  array (
    'class' => 'marionnewlevant\\snitch\\Snitch',
    'basePath' => $vendorDir . '/marionnewlevant/snitch/src',
    'handle' => 'snitch',
    'aliases' => 
    array (
      '@marionnewlevant/snitch' => $vendorDir . '/marionnewlevant/snitch/src',
    ),
    'name' => 'Snitch',
    'version' => '3.0.4.1',
    'description' => 'Report when two people might be editing the same element (eg entry, category, or global) or field',
    'developer' => 'Marion Newlevant',
    'developerUrl' => 'http://marion.newlevant.com',
    'documentationUrl' => 'https://github.com/marionnewlevant/craft-snitch/blob/v2/README.md',
    'changelogUrl' => 'https://raw.githubusercontent.com/marionnewlevant/craft-snitch/v2/CHANGELOG.md',
    'hasCpSettings' => false,
    'hasCpSection' => false,
    'components' => 
    array (
      'collision' => 'marionnewlevant\\snitch\\services\\Collision',
    ),
  ),
  'carlcs/craft-diywidget' => 
  array (
    'class' => 'carlcs\\diywidget\\Plugin',
    'basePath' => $vendorDir . '/carlcs/craft-diywidget/src',
    'handle' => 'diy-widget',
    'aliases' => 
    array (
      '@carlcs/diywidget' => $vendorDir . '/carlcs/craft-diywidget/src',
    ),
    'name' => 'Do It Yourself widget',
    'version' => '2.1.0',
    'description' => 'Do It Yourself widget for Craft CMS',
    'developer' => 'carlcs',
    'developerUrl' => 'https://github.com/carlcs',
    'documentationUrl' => 'https://github.com/carlcs/craft-diywidget',
    'changelogUrl' => 'https://github.com/carlcs/craft-diywidget/raw/v2/CHANGELOG.md',
    'downloadUrl' => 'https://github.com/carlcs/craft-diywidget/archive/v2.zip',
  ),
  'clubstudioltd/craft-asset-rev' => 
  array (
    'class' => 'club\\assetrev\\AssetRev',
    'basePath' => $vendorDir . '/clubstudioltd/craft-asset-rev/src',
    'handle' => 'assetrev',
    'aliases' => 
    array (
      '@club/assetrev' => $vendorDir . '/clubstudioltd/craft-asset-rev/src',
    ),
    'name' => 'Asset Rev',
    'version' => '6.0.2',
    'description' => 'A plugin to aid cache-busting in Craft 3',
    'developer' => 'Club Studio Ltd',
    'developerUrl' => 'https://clubstudio.co.uk',
    'documentationUrl' => 'https://github.com/clubstudioltd/craft-asset-rev',
    'changelogUrl' => 'https://github.com/clubstudioltd/craft-asset-rev/blob/master/CHANGELOG.md',
    'downloadUrl' => 'https://github.com/clubstudioltd/craft-asset-rev/archive/v6.zip',
    'hasCpSettings' => false,
    'hasCpSection' => false,
  ),
  'jalendport/craft-readtime' => 
  array (
    'class' => 'jalendport\\readtime\\ReadTime',
    'basePath' => $vendorDir . '/jalendport/craft-readtime/src',
    'handle' => 'read-time',
    'aliases' => 
    array (
      '@jalendport/readtime' => $vendorDir . '/jalendport/craft-readtime/src',
    ),
    'name' => 'Read Time',
    'version' => '1.6.0',
    'description' => 'Calculate the estimated read time for content.',
    'developer' => 'Jalen Davenport',
    'developerUrl' => 'https://jalendport.com',
    'documentationUrl' => 'https://github.com/jalendport/craft-readtime/blob/master/README.md',
    'changelogUrl' => 'https://raw.githubusercontent.com/jalendport/craft-readtime/master/CHANGELOG.md',
    'hasCpSettings' => true,
    'hasCpSection' => false,
  ),
  'verbb/default-dashboard' => 
  array (
    'class' => 'verbb\\defaultdashboard\\DefaultDashboard',
    'basePath' => $vendorDir . '/verbb/default-dashboard/src',
    'handle' => 'default-dashboard',
    'aliases' => 
    array (
      '@verbb/defaultdashboard' => $vendorDir . '/verbb/default-dashboard/src',
    ),
    'name' => 'Default Dashboard',
    'version' => '1.0.8',
    'description' => 'A Craft CMS plugin to set default dashboard widgets for users.',
    'developer' => 'Verbb',
    'developerUrl' => 'https://verbb.io',
    'developerEmail' => 'support@verbb.io',
    'documentationUrl' => 'https://github.com/verbb/default-dashboard',
    'changelogUrl' => 'https://raw.githubusercontent.com/verbb/default-dashboard/master/CHANGELOG.md',
  ),
  'craftcms/redactor' => 
  array (
    'class' => 'craft\\redactor\\Plugin',
    'basePath' => $vendorDir . '/craftcms/redactor/src',
    'handle' => 'redactor',
    'aliases' => 
    array (
      '@craft/redactor' => $vendorDir . '/craftcms/redactor/src',
    ),
    'name' => 'Redactor',
    'version' => '2.10.12',
    'description' => 'Edit rich text content in Craft CMS using Redactor by Imperavi.',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://pixelandtonic.com/',
    'developerEmail' => 'support@craftcms.com',
    'documentationUrl' => 'https://github.com/craftcms/redactor/blob/v2/README.md',
  ),
  'craftcms/contact-form' => 
  array (
    'class' => 'craft\\contactform\\Plugin',
    'basePath' => $vendorDir . '/craftcms/contact-form/src',
    'handle' => 'contact-form',
    'aliases' => 
    array (
      '@craft/contactform' => $vendorDir . '/craftcms/contact-form/src',
    ),
    'name' => 'Contact Form',
    'version' => '2.5.2',
    'description' => 'Add a simple contact form to your Craft CMS site',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://pixelandtonic.com/',
    'developerEmail' => 'support@craftcms.com',
    'documentationUrl' => 'https://github.com/craftcms/contact-form/blob/v2/README.md',
    'components' => 
    array (
      'mailer' => 'craft\\contactform\\Mailer',
    ),
  ),
  'craftcms/feed-me' => 
  array (
    'class' => 'craft\\feedme\\Plugin',
    'basePath' => $vendorDir . '/craftcms/feed-me/src',
    'handle' => 'feed-me',
    'aliases' => 
    array (
      '@craft/feedme' => $vendorDir . '/craftcms/feed-me/src',
    ),
    'name' => 'Feed Me',
    'version' => '4.8.0',
    'description' => 'Import content from XML, RSS, CSV or JSON feeds into entries, categories, Craft Commerce products, and more.',
    'developer' => 'Pixel & Tonic',
    'developerUrl' => 'https://pixelandtonic.com/',
    'developerEmail' => 'support@craftcms.com',
    'documentationUrl' => 'https://docs.craftcms.com/feed-me/v4/',
  ),
);
