<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'appfirst';
$app['version'] = '1.6.5';
$app['release'] = '1';
$app['vendor'] = 'ClearCenter <developer@clearcenter.com>';
$app['packager'] = 'ClearCenter <developer@clearcenter.com>';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('appfirst_app_description');
$app['tooltip'] = lang('appfirst_app_tooltip');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('appfirst_app_name');
$app['category'] = lang('base_category_cloud');
$app['subcategory'] = lang('base_subcategory_services');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'appfirst'
);

$app['core_file_manifest'] = array(
    'appfirst.conf' => array(
        'target' => '/etc/clearos/appfirst.conf',
        'mode' => '0640',
        'owner' => 'webconfig',
        'group' => 'webconfig',
        'config' => TRUE,
        'config_params' => 'noreplace'
    ),
    'AppFirst' => array(
        'target' => '/etc/AppFirst',
        'mode' => '0640',
        'owner' => 'root',
        'group' => 'root',
        'config' => TRUE,
        'config_params' => 'noreplace'
    ),
    'afcollector.php'=> array('target' => '/var/clearos/base/daemon/afcollector.php'),
);

$app['core_directory_manifest'] = array();

$app['delete_dependency'] = array(
    'app-appfirst-core',
    'appfirst',
);
