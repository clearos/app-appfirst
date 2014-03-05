<?php

/**
 * AppFirst init view.
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

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

$items = array();

foreach ($archives as $archive) {
    $buttons = array(
        anchor_custom('/app/appfirst/download/' . $archive, lang('base_download'), 'high'),
        anchor_custom('/app/appfirst/backup/restore/' . $archive, lang('base_restore'), 'high'),
        anchor_custom('/app/appfirst/backup/delete/' . $archive, lang('base_delete'), 'low')
    );
    $item = array(
        'title' => $archive,
        'action' => '', // TODO: mobile mode
	    'anchors' => button_set($buttons),
        'details' => array($archive),
    );

    $items[] = $item;
}
echo summary_table(
    lang('appfirst_archives'),
    array(anchor_custom('/app/appfirst/create_archive', lang('appfirst_backup_now'), 'high')),
    array(lang('base_filename')),
    $items
);
