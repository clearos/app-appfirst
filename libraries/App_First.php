<?php

/**
 * App_First class.
 *
 * @category   apps
 * @package    appfirst
 * @subpackage libraries
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2014 ClearCenter
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/appfirst/
 */

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\appfirst;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('appfirst');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\log_viewer\Log_Viewer as Log_Viewer;
use \clearos\apps\base\File as File;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Daemon');
clearos_load_library('base/Engine');
clearos_load_library('log_viewer/Log_Viewer');
clearos_load_library('base/File');

// Exceptions
//-----------

use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * App_First class.
 *
 * @category   apps
 * @package    appfirst
 * @subpackage libraries
 * @author     ClearCenter <developer@clearcenter.com>
 * @copyright  2014 ClearCenter
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/appfirst/
 */

class App_First extends Daemon
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const FILE_CONFIG = '/etc/clearos/appfirst.conf';
    const FILE_CONFIG_ENGINE = '/etc/AppFirst';
    const URL_NEW_ACCOUNT = 'https://wwws.appfirst.com/accounts/signup/?referral=clearcenter';
    const URL_BASE_API = 'https://wwws.appfirst.com/api/';

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $loaded = FALSE;
    protected $config = array();
    protected $config_engine = array();

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * App_First constructor.
     */

    function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct('afcollector');
    }

    /** Has the server been initialized with the AppFirst cloud service.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function has_account_credentials()
    {
        clearos_profile(__METHOD__, __LINE__);
            
        try {
            if (! $this->loaded)
                $this->_load();

            if (empty($this->config['username']) || empty($this->config['apikey']))
                return FALSE;
            return TRUE;
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /** Enable/disable the collector.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function set_enabled($status)
    {
        clearos_profile(__METHOD__, __LINE__);
            
        try {
            if ($status === 'on' || $status == 1 || $status == TRUE)
                $status = TRUE;
            else
                $status = FALSE;
            $this->_set_engine_parameter('configuration', 'Enabled', ($status ? 'True' : 'False'));
            $this->restart(TRUE);
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /** Has the server been initialized with the AppFirst cloud service.
     *
     * @return void
     * @throws Engine_Exception
     */

    public function is_initialized()
    {
        clearos_profile(__METHOD__, __LINE__);
            
        try {
            if (! $this->loaded)
                $this->_load();

            if ($this->config_engine['configuration']['Tenant'] == 1) {
                $response = json_decode($this->_request('user_profiles/'));
                $tenant_id = $response->data[0]->tenant_id;
                if (is_numeric($tenant_id)) {
                    $this->_set_engine_parameter('configuration', 'Tenant', $tenant_id);
                } else {
                    return FALSE;
                }
            }
            return TRUE;
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Set username.
     *
     * @param string $username username
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_username($username)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_username($username));

        $this->_set_parameter('username', $username);
    }

    /**
     * Set apikey.
     *
     * @param string $apikey apikey
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_apikey($apikey)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_apikey($apikey));

        $this->_set_parameter('apikey', $apikey);
    }

    /**
     * Set logs.
     *
     * @param array $logs logs
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_logs($logs)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_logs($logs));

        $this->_set_parameter('logs', json_encode($logs));
    }

    /**
     * Set tags.
     *
     * @param array $tags tags
     *
     * @return void
     * @throws Validation_Exception, Engine_Exception
     */

    function set_tags($tags)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_tags($tags));
        $appfirst_tags = $this->get_tags();
        foreach ($appfirst_tags as $name => $tag) {
            if (in_array($this->config['server'], $tag['servers']) && !in_array($tag['id'], $tags)) {
                // Need to remove tag
                $list = array_diff($tag['servers'], array($this->config['server']));
                $response = json_decode(
                    $this->_request(
                        'server_tags/' . $tag['id'] . '/',
                        array(
                            'name=' => $name,
                            'servers' => '[' . implode(',', $list) . ']'
                        ),
                        'PUT'
                    )
                );
            } else if (!in_array($this->config['server'], $tag['servers']) && in_array($tag['id'], $tags)) {
                // Need to add tag
                $list = $tag['servers'];
                $list[] = $this->config['server'];
                $response = json_decode(
                    $this->_request(
                        'server_tags/' . $tag['id'] . '/',
                        array(
                            'name=' => $name,
                            'servers' => '[' . implode(',', $list) . ']'
                        ),
                        'PUT'
                    )
                );
            }
        }
        // TODO - Should do some response code checking
    }

    /**
     * Reset account credentials.
     *
     * @return void
     * @throws Engine_Exception
     */

    function reset_account()
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            $this->_set_parameter('username', NULL);
            $this->_set_parameter('apikey', NULL);
            $this->_set_parameter('server', NULL);
            $this->_set_parameter('logs', NULL);
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Get server ID.
     *
     * @return void
     * @throws Engine_Exception
     */

    function get_server()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->loaded)
            $this->_load();

        if (empty($this->config['server']))
            return NULL;

        return $this->config['server'];
    }

    /**
     * Get enable status.
     *
     * @return void
     * @throws Engine_Exception
     */

    function get_enabled()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->loaded)
            $this->_load();

        if (!empty($this->config_engine['configuration']['Enabled']) && $this->config_engine['configuration']['Enabled'] == 'True')
            return TRUE;

        return FALSE;
    }

    /**
     * Get log file list to sync with AppFirst.
     *
     * @return void
     * @throws Engine_Exception
     */

    function get_logs()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->loaded)
            $this->_load();

        if (!empty($this->config['logs']))
            return json_decode($this->config['logs']);

        return array();
    }

    /**
     * Get server tags to sync with AppFirst.
     *
     * @return void
     * @throws Engine_Exception
     */

    function get_tags()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->loaded)
            $this->_load();

        $tags = array();

        try {
            if ($this->config['server'] > 1) {
                $response = json_decode($this->_request('server_tags/'));
                foreach ($response->data as $id => $tag) {
                    $tags[$tag->name] = array(
                        'id' => $tag->id,
                        'servers' => $tag->servers
                    );
                }
            }
            return $tags;
        } catch (Exception $e) {
            return $tags;
        }
    }

    /**
     * Get log file list.
     *
     * @return void
     * @throws Engine_Exception
     */

    function get_log_files()
    {
        clearos_profile(__METHOD__, __LINE__);
        try {
            $list = array();
            $log_viewer = new Log_Viewer();
            $files = $log_viewer->get_log_files();
            foreach ($files as $file) {
                if (preg_match('/.*\d$/', $file))
                    continue;
                $list[Log_Viewer::FOLDER_LOG_FILES . '/' . $file] = $file;
            }
            return $list;
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * Sync settings with AppFirst.
     *
     * @return JSON encoded status
     * @throws Engine_Exception
     */

    function sync()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! $this->loaded)
            $this->_load();

        // We're not even configured
        if (empty($this->config['username']))
            return json_encode(array('code' => 255));

        // System ID
        // ---------
        try {
            if (empty($this->config['server'])) {
                $response = json_decode($this->_request('servers/'));
                foreach ($response->data as $server) {
                    if (gethostname() == $server->hostname) {
                        $this->_set_parameter('server', $server->id);
                        $this->_load();
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            return json_encode(array('code' => 1, 'errmsg' => clearos_exception_message($e)));
        }

        // If server field is still empty, no need to go further
        if (empty($this->config['server'])) {
            if ($this->get_running_state())
                $this->restart(TRUE);
            else
                $this->set_running_state(TRUE);
            return json_encode(array('code' => -1, 'errmsg' => lang('appfirst_pending_initialization')));
        }

        // Log files
        // ---------
        try {
            $logs_to_sync = $this->get_logs();
            $response = json_decode($this->_request('logs/'));
            $af_logs = array();
            foreach ($response->data as $id => $log_entry) {
                if ($log_entry->type != 'FILE' || $log_entry->server != $this->config['server'])
                    continue;
                $af_logs[$log_entry->id] = $log_entry->source;
            }
            foreach ($logs_to_sync as $log_file) {
                if (!in_array($log_file, array_values($af_logs))) {
                    $fields = array(
                        'server' => $this->config['server'],
                        'type' => 'FILE',
                        'source' => $log_file
                    );
                    $response = json_decode($this->_request('logs/', $fields, 'POST'));
                }
            }
            foreach ($af_logs as $id => $log_file) {
                if (!in_array($log_file, $logs_to_sync) && in_array($log_file, array_keys($this->get_log_files())))
                    $response = json_decode($this->_request('logs/' . $id, NULL, 'DELETE'));
            }
        } catch (Exception $e) {
            return json_encode(array('code' => 2, 'errmsg' => clearos_exception_message($e)));
        }

        return json_encode(array('code' => 0));
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Load and parse configuration files.
     *
     * @access private
     *
     * @return void
     * @throws Engine_Exception
     */

    protected function _load()
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->loaded = FALSE;

        // ClearOS Config file
        $configfile = new Configuration_File(self::FILE_CONFIG);

        $this->config = $configfile->load();

        // AppFirst Config file
        $lines = array();
        $section = NULL;

        $file = new File(self::FILE_CONFIG_ENGINE, TRUE);

        if ($file->exists())
            $lines = $file->get_contents_as_array();

        foreach ($lines as $line) {

            if (preg_match('/^<\\/.*>$/', $line, $match))
                continue;
            if (preg_match('/^<(.*)>$/', $line, $match)) {
                $section = $match[1];
                continue;
            }
            if ($section == NULL)
                continue;
            if (preg_match('/^\s*([a-zA-Z0-9]+)\s+(.*$)/', $line, $match)) {
                $this->config_engine[$section][$match[1]] = $match[2];
            }
        }

        $this->loaded = TRUE;

    }

    /**
     * Generic set routine.
     *
     * @param string $key   key name
     * @param string $value value for the key
     *
     * @return  void
     * @throws Engine_Exception
     */

    private function _set_parameter($key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $regex = str_replace("[", "\\[", $key);
            $regex = str_replace("]", "\\]", $regex);
            $file = new File(self::FILE_CONFIG, TRUE);
            $match = $file->replace_lines("/^$regex\s*=\s*/", "$key = $value\n");
            if (!$match)
                $file->add_lines("$key = $value\n");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->is_loaded = FALSE;
    }

    /**
     * Generic set routine for App First Config.
     *
     * @param string $section section key name
     * @param string $key     key name
     * @param string $value   value for the key
     *
     * @return  void
     * @throws Engine_Exception
     */

    private function _set_engine_parameter($section, $key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            if (! $this->loaded)
                $this->_load();

            // AppFirst Config file
            $lines = array();
            $newlines = array();
            $_section = NULL;

            $file = new File(self::FILE_CONFIG_ENGINE, TRUE);

            if ($file->exists())
                $lines = $file->get_contents_as_array();

            foreach ($lines as $line) {

                if (preg_match('/^<\\/.*>$/', $line, $match)) {
                    $newlines[] = $line;
                    continue;
                }
                if (preg_match('/^<(.*)>$/', $line, $match)) {
                    $_section = $match[1];
                    $newlines[] = $line;
                    continue;
                }
                if ($section == NULL || $_section != $section) {
                    $newlines[] = $line;
                    continue;
                }
                if (preg_match('/(^\s*' . $key . ')\s+.*$/', $line, $match)) {
                    $newlines[] = $match[1] . ' ' . $value;
                } else {
                    $newlines[] = $line;
                }
            }

            $file->dump_contents_from_array($newlines);

            $this->is_loaded = FALSE;

        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    /**
     * A generic way to communicate with the Service Delivery Network (SDN) using REST.
     *
     * @param string $resource resource
     * @param string $fields   fields
     * @param string $custom   custom request (PUT, DELETE etc.)
     *
     * @return string JSON encoded response
     * @throws Engine_Exception
     */

    private function _request($resource, $fields = NULL, $custom = 'GET')
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            if (! $this->loaded)
                $this->_load();

            if (isset($ch))
                unset($ch);

            $ch = curl_init();

            // Check for upstream proxy settings
            //----------------------------------

            if (clearos_app_installed('upstream_proxy')) {
                clearos_load_library('upstream_proxy/Proxy');

                $proxy = new \clearos\apps\upstream_proxy\Proxy();

                $proxy_server = $proxy->get_server();
                $proxy_port = $proxy->get_port();
                $proxy_username = $proxy->get_username();
                $proxy_password = $proxy->get_password();

                if (! empty($proxy_server))
                    curl_setopt($ch, CURLOPT_PROXY, $proxy_server);

                if (! empty($proxy_port))
                    curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);

                if (! empty($proxy_username))
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_username . ':' . $proxy_password);
            }

            // Set main curl options
            //----------------------

            curl_setopt($ch, CURLOPT_URL, self::URL_BASE_API . $resource);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_USERPWD, $this->config['username'] . ":" . $this->config['apikey']);
            if ($fields != NULL && is_array($fields)) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $custom);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $curl_response = chop(curl_exec($ch));
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            // Return useful errno messages for the most common errnos
            //--------------------------------------------------------

            if ($errno == 0) {
                return $curl_response;
            } else if ($errno == CURLE_OPERATION_TIMEOUTED) {
                throw new Engine_Exception(lang('appfirst_unable_to_contact_remote_server'), CLEAROS_INFO);
            } else {
                throw new Engine_Exception(lang('appfirst_connection_failed') . ': ' . $error, CLEAROS_INFO);
            }
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Validation routine for username.
     *
     * @param string $username username
     *
     * @return mixed void if username is valid, errmsg otherwise
     */

    public function validate_username($username)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $username))
            return lang('appfirst_invalid_username');
    }

    /**
     * Validation routine for API key.
     *
     * @param string $apikey API key
     *
     * @return mixed void if API key is valid, errmsg otherwise
     */

    public function validate_apikey($apikey)
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!preg_match("/^([a-zA-Z0-9])+$/", $apikey))
            return lang('appfirst_invalid_apikey');
    }

    /**
     * Validation routine for log files.
     *
     * @param array $logs log file array
     *
     * @return mixed void if log file is OK, errmsg otherwise
     */

    public function validate_logs($logs)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for tags.
     *
     * @param array $tags tags array
     *
     * @return mixed void if tags is OK, errmsg otherwise
     */

    public function validate_tags($tags)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (!is_array($tags) && !empty($tags))
            return lang('appfirst_invalid_tag');
        foreach ($tags as $tag) {
            if (!is_numeric($tag))
                return lang('appfirst_invalid_tag');
        }
    }
}
