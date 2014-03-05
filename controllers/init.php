<?php

/**
 * AppFirst init controller.
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
 * AppFirst init controller.
 *
 * @category   apps
 * @package    appfirst
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2014 ClearCenter
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/appfirst/
 */

class Init extends ClearOS_Controller
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

        try {
            if ($this->app_first->is_initialized())
                return;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load view data
        //---------------

        $data = array();

        // Load views
        //-----------

        $this->page->view_form('appfirst/init', $data, lang('appfirst_app_name'));

    }
}
