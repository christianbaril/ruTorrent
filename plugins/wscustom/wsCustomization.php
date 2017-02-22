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

    /***
     * init
     */
    public function init()
    {
        global $jEnd, $jResult;

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
            "admin"    => $this->isAdmin(),
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

    /**
     * getAllowedLabels
     * @return array
     */
    public static function getAllowedLabels()
    {
        return array(
            'Anime',
            'French',
            'Learning',
            'Mac',
            'Misc',
            'Movies',
            'Music',
            'NintendoDS',
            'Porn',
            'PS3',
            'PSP',
            'TVShows',
            'Wii',
            'Windows',
            'XBox360'
        );
    }

    /**
     * getDisabledColumns
     * @return array
     */
    public static function getDisabledColumns()
    {
        return array(
            'ratioday',
            'ratioweek',
            'ratiomonth',
            'priority',
            'owner',
            'keep',
            'ratiogroup',
            'throttle',
            'channel'
        );
    }

    /**
     * getHiddenLabels
     * @return array
     */
    public static function getHiddenLabels()
    {
        return array(
            'TVshows_Auto',
            'Movies_Auto'
        );
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
    public function isAdmin()
    {
        return in_array($this->username, $this->getAdmins());
    }

    /**
     * @return array
     */
    private function getAdmins()
    {
        return array(
            'wickedsun',
            'mrb'
        );
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
     * @return string
     */
    public function disableAddTorrent()
    {
        return file_get_contents($this->getPath() . '/js/disableAddTorrent.js');
    }

    /**
     * disallowStartsWithoutLabel
     * @return string
     */
    public function disallowStartsWithoutLabel()
    {
        return file_get_contents($this->getPath() . '/js/disallowStartsWithoutLabel.js');
    }

    /**
     * getStartCmd
     * @return string
     */
    public function getStartCmd()
    {
        return file_get_contents($this->getPath() . '/js/getStartCmd.js');
    }

    public function addJsDelay($javascript, $milliseconds)
    {
        return "setTimeout(function() {  $javascript  }, $milliseconds);";;
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

}
