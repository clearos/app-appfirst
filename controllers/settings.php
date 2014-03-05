<?php

/**
 * AppFirst settings controller.
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
 * AppFirst settings controller.
 *
 * @category   apps
 * @package    appfirst
 * @subpackage controllers
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2014 ClearCenter
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/appfirst/
 */

class Settings extends ClearOS_Controller
{
    /**
     * AppFirst summary view.
     *
     * @return view
     */

    function index()
    {
        $this->_view_edit('view');
    }

    /**
     * AppFIrst settings edit controller
     *
     * @return view
     */

    function edit()
    {
        $this->_view_edit('edit');
    }

    /**
     * AppFirst view/edit controller
     *
     * @param string $mode mode
     *
     * @return view
     */

    function _view_edit($mode = NULL)
    {
        // Load libraries
        //---------------

        $this->lang->load('appfirst');
        $this->load->library('appfirst/App_First');
        $this->load->library('web_server/Httpd');

        // Set validation rules
        //---------------------

        $this->form_validation->set_policy('logs', 'appfirst/App_First', 'validate_logs', FALSE);

        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if (($this->input->post('submit') && $form_ok)) {
            try {
                $this->app_first->set_enabled($this->input->post('enabled'));
                $this->app_first->set_logs($this->input->post('logs'));
                $this->app_first->set_tags($this->input->post('tags'));
                $this->page->set_status_updated();
                redirect('/appfirst');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load view data
        //---------------
        $appfirst_tags =$this->app_first->get_tags();
        $sync_tags = array();
        $tagged = array();
        foreach ($appfirst_tags as $name => $meta) {
            $tags[$meta['id']] = $name;
            if (in_array($this->app_first->get_server(), array_values($meta['servers'])))
                $sync_tags[] = $meta['id'];
        }
        $data = array (
            'mode' => $mode,
            'enabled' => $this->app_first->get_enabled(),
            'logs' => $this->app_first->get_log_files(),
            'sync_logs' => $this->app_first->get_logs(),
            'tags' => $tags,
            'sync_tags' => $sync_tags
        );

        // Load views
        //-----------

        $this->page->view_form('appfirst/settings', $data, lang('appfirst_app_name'));

    }
}
