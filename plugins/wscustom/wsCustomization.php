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
  public static function init()
  {
    global $jEnd, $jResult;

    $wsc = new wsCustomization();

    /**
     * Common stuff
     */
    // labels
    $jResult .= "allowedLabels = " . json_encode($wsc->getAllowedLabels()) . ";";
    // disabled columns
    $jResult .= "disabledColumns = " . json_encode($wsc->getDisabledColumns()) . ";";
    $jResult .= "hiddenLabels = " . json_encode($wsc->getHiddenLabels()) . ";";

    // infos for current user
    $userinfos = Array(
      "username" => $wsc->getUsername(),
      "admin"    => $wsc->isAdmin(),
    );
    $jResult .= "userinfo = " . json_encode($userinfos) . ";";

    // Overriding context menu routine
    $jEnd .= $wsc->getContextMenu();

    // Disabling manually add torrent
    $wsc->disableAddTorrent();

    // Disabling start a download without a label set
    $jEnd .= $wsc->disallowStartsWithoutLabel();

    // Overriding start torrent
    $jEnd .= $wsc->getStartCmd();

    // Restricting access to users
    if (!$wsc->isAdmin()) {
      // Removing unwanted menu items
      $jEnd .= $wsc->removeMenus();
      $jEnd .= $wsc->removeSettings();
      $jEnd .= $wsc->disableColumns();
    }

  }

  /**
   * getAllowedLabels
   * @return array
   */
  public static function getAllowedLabels()
  {
    $directory = '/mnt/FTP/home/rtorrent/finished/';
    $words = array('autodl', '.','temp');
    $dirnames = array_map(function ($dir) use ($directory) {
      return str_replace($directory, '', $dir);
    }, array_filter(glob($directory . '*'), 'is_dir'));
    return array_filter($dirnames, function ($dir) use ($words) {
      $matches = 0;
      foreach ($words as $word) {
        $matches += (strpos(strtolower($dir), strtolower($word)) !== false) ? 1 : 0;
      }
      return (!$matches);
    });
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
    return in_array($_SERVER['PHP_AUTH_USER'], $this->getAdmins());
  }

  private function getAdmins()
  {
    return array(
      'wickedsun',
      'wicked',
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
