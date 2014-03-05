<?php

/**
 * AppFirst account controller.
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\appfirst\App_First as App_First;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * AppFirst account controller.
 *
 * @category   apps
 * @package    appfirst
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2014 ClearCenter
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/appfirst/
 */

class Account extends ClearOS_Controller
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
            if ($this->app_first->has_account_credentials())
                return;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load view data
        //---------------

        $data = array('url' => App_First::URL_NEW_ACCOUNT);

        // Load views
        //-----------

        $this->page->view_form('appfirst/account', $data, lang('appfirst_app_name'));
    }

    /**
     * Submit/Update AppFirst credentials.
     *
     * @return void
     */

    function submit_credentials()
    {
        // Load libraries
        //---------------

        $this->lang->load('appfirst');
        $this->load->library('appfirst/App_First');

        // Set validation rules
        //---------------------

        $this->form_validation->set_policy('username', 'appfirst/App_First', 'validate_username', TRUE);
        $this->form_validation->set_policy('apikey', 'appfirst/App_First', 'validate_apikey', TRUE);

        $form_ok = $this->form_validation->run();

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $this->app_first->set_username($this->input->post('username'));
                $this->app_first->set_apikey($this->input->post('apikey'));
                $this->page->set_status_updated();
                redirect('/appfirst');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        $this->page->view_form('appfirst/account', $data, lang('appfirst_app_name'));

    }

    /**
     * Reset AppFirst credentials.
     *
     * @return void
     */

    function reset()
    {
        // Load libraries
        //---------------

        $this->lang->load('appfirst');
        $this->load->library('appfirst/App_First');

        $this->app_first->reset_account();

        redirect('/appfirst');
    }

}
