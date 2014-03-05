<?php

/**
 * AppFirst controller.
 *
 * @category   apps
 * @package    appfirst
 * @subpackage controllers
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
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * AppFirst controller.
 *
 * @category   apps
 * @package    appfirst
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2014 ClearCenter
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/appfirst/
 */

class AppFirst extends ClearOS_Controller
{
    /**
     * AppFirst summary view.
     *
     * @return view
     */

    function index()
    {
        // Load libraries
        //---------------

        $this->lang->load('appfirst');
        $this->load->library('appfirst/App_First');

        // Load views
        //-----------

        $views = array('appfirst/server', 'appfirst/account', 'appfirst/init', 'appfirst/settings');

        $this->page->view_forms($views, lang('appfirst_app_name'));
    }

    /**
     * Ajax sync request to AppFirst Ws.
     *
     * @return JSON
     */

    function sync()
    {

        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        // Load libraries
        //---------------

        $this->load->library('appfirst/App_First');

        echo $this->app_first->sync();
    }
}
