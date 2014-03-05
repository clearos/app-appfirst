<?php

/**
 * AppFirst account view.
 *
 * @category   apps
 * @package    appfirst
 * @subpackage views
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2014 ClearCenter
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/appfirst/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//  
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('appfirst');

echo infobox_highlight(
    lang('appfirst_getting_started'),
    "<div>" . lang('appfirst_account_setup_intro') . "</div>" .
    "<ol>" .
    "<li style='padding: 3 0;'>" . lang('appfirst_account_setup_1') . "<span style='padding-left: 10px;'>" . anchor_custom($url, lang('base_go')) . "</span></li>" .
    "<li style='padding: 3 0;'>" . lang('appfirst_account_setup_2') . "</li>" .
    "<li style='padding: 3 0;'>" . lang('appfirst_account_setup_3') . "</li>" .
    "</ol>"
);

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('appfirst/account/submit_credentials');
echo form_header(lang('appfirst_account'));

///////////////////////////////////////////////////////////////////////////////
// Form fields and buttons
///////////////////////////////////////////////////////////////////////////////

$buttons = array(form_submit_custom('submit', lang('appfirst_submit')));

echo field_input('username', $account, lang('appfirst_username'), FALSE);
echo field_input('apikey', $apikey, lang('appfirst_apikey'), FALSE);
echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();

