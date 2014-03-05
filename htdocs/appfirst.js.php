<?php

/**
 * Javascript helper for Marketplace.
 *
 * @category   apps
 * @package    marketplace
 * @subpackage javascript
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2011 ClearCenter
 * @license    http://www.clearcenter.com/app_license ClearCenter license
 * @link       http://www.clearcenter.com/support/documentation/clearos/marketplace/
 */

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('marketplace');
clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type: application/x-javascript');

echo "
$(document).ready(function() {
    $('#username').css('min-width', '180px');
    $('#apikey').css('min-width', '180px');
    $('select').css('min-width', '180px');
    af_sync();
});

function af_sync() {
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/app/appfirst/sync',
        data: '',
        success: function(json) {
            if (json.code < 0) {
                $('#enabled_text').html(json.errmsg);
                window.setTimeout(af_sync, 3000);
            }
        }
    });
}

";
