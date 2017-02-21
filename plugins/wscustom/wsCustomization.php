<?php

require_once(dirname(__FILE__) . "/../../php/xmlrpc.php");
require_once($rootPath . '/php/cache.php');

eval(getPluginConf('wsCustomization'));

/**
 * Class wsCustomization
 */
class wsCustomization
{
    public $hash = "wsCustomization.dat";
    private $path;
    private $username;

    function __construct()
    {
        $this->setPath();
        $this->setUsername($_SERVER['PHP_AUTH_USER']);
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * isAdmin
     * @return bool
     */
    public static function isAdmin()
    {
        global $ADMINS;
        return in_array($_SERVER['PHP_AUTH_USER'], $ADMINS);
    }

    /**
     * getAllowedLabels
     * @return array
     */
    public static function  getAllowedLabels()
    {
        global $ALLOWED_LABELS;
        return $ALLOWED_LABELS;
    }

    /**
     * getDisabledColumns
     * @return array
     */
    public static function  getDisabledColumns()
    {
        global $DISABLED_COLUMNS_FOR_USERS;
        return $DISABLED_COLUMNS_FOR_USERS;
    }

    /**
     * getHiddenLabels
     * @return array
     */
    public static function getHiddenLabels()
    {
        global $HIDDEN_LABELS;
        return $HIDDEN_LABELS;
    }

    /**
     * @return string
     */
    public function disableAddTorrent()
    {
        return file_get_contents($this->getPath() . '/js/disableAddTorrent.js');
    }

    /**
     * getPath
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * setPath
     */
    public function setPath()
    {
        $this->path = dirname(__FILE__);
    }

    /**
     * removeMenus
     * @return mixed
     */
    public function removeMenus()
    {
        return file_get_contents($this->getPath() . '/js/removeMenus.js');
    }

    /**
     * removeSettings
     * @return mixed
     */
    public function removeSettings()
    {
        return file_get_contents($this->getPath() . '/js/removeSettings.js');
    }

    /**
     * disableColumns
     * @return string
     */
    public function disableColumns()
    {
        return file_get_contents($this->getPath() . '/js/disableColumns.js');
    }

    /**
     * getContextMenu
     * @return string
     */
    public function getContextMenu()
    {
        return file_get_contents($this->getPath() . '/js/getContextMenu.js');
    }

    /**
     * getStartCmd
     * @return string
     */
    public function getStartCmd()
    {
        return file_get_contents($this->getPath() . '/js/getStartCmd.js');
    }

    /**
     * disallowStartsWithoutLabel
     * @return string
     */
    public function disallowStartsWithoutLabel()
    {
        return file_get_contents($this->getPath() . '/js/disallowStartsWithoutLabel.js');
    }


    public function addJsDelay($javascript, $milliseconds)
    {
        return "setTimeout(function() {  $javascript  }, $milliseconds);";;
    }

    /***
     * init
     */
    public static function init()
    {
        global $jEnd, $jResult;

        //$wsc = new wsCustomization();

        /**
         * Common stuff
         */
        // labels
        $jResult .= "allowedLabels = " . json_encode($this->getAllowedLabels()) . ";";
        // disabled columns
        $jResult .= "disabledColumns = " . json_encode($this->getDisabledColumns()) . ";";
        $jResult .= "hiddenLabels = " . json_encode($this->getHiddenLabels()) . ";";

        // infos for current user
        $userinfos = Array(
            "username" => $this->getUsername(),
            "admin" => $this->isAdmin(),
        );
        $jResult .= "userinfo = " . json_encode($userinfos) . ";";

        // Overriding context menu routine
        $jEnd .= $this->getContextMenu();

        // Disabling manually add torrent
        $this->disableAddTorrent();

        // Disabling start a download without a label set
        $jEnd .= $this->disallowStartsWithoutLabel();

        // Overriding start torrent
        $jEnd .= $this->getStartCmd();


        // Restricting access to users
        if (!$this->isAdmin()) {
            // Removing unwanted menu items
            $jEnd .= $this->addJsDelay($this->removeMenus(), 200);
            $jEnd .= $this->removeSettings();
            $jEnd .= $this->addJsDelay($this->disableColumns(), 300);
        }

    }

}
