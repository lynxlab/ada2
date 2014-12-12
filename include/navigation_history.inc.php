<?php
/**
 * Class NavigationHistory
 *
 * class NavigationHistory, used to track user navigation in ADA.
 * It stores a predefined amount of page loads into a circular
 * array of fixed dimension $items_max_size
 *
 * PHP version >= 5.0
 *
 * @package		view
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		navigation_history
 * @version		0.1
 */

class NavigationHistory
{
  /**
   * the circular array used to store loaded pages
   *
   * @var array
   */
  private $items;
  private $arguments_for_item;
  /**
   * max number of elements $items can store
   *
   * @var int
   */
  private $items_max_size;

  /**
   * vito, 5 giugno 2009
   * è il  modulo precedente visitato. Es. sono in view, vado su upload,
   * $last_page punta a view.
   */
  private $last_page;
  private $arguments_for_last_page;

  /**
   * pointer to the next free position in $items
   *
   * @var int
   */
  private $free_ptr;

  public function __construct($items_size) {
    $this->items              = array();
    $this->arguments_for_item = array();
    // vito, 5 giugno 2009
    $this->last_page = null;
    $this->arguments_for_last_page = null;

    $this->items_max_size = $items_size;
    $this->free_ptr       = 0;
  }

  /**
   * function addItem, used to add a page into $items
   *
   * @param string $item - the absolute path of the page added
   */
  public function addItem($item) {
    //$this->free_ptr = $this->free_ptr % $this->items_max_size;
    $arguments = '';
    foreach ($_GET as $argument=>$value) {
      $arguments .= "$argument=$value&";
    }

    // vito 5 giugno 2009
    if($this->free_ptr>0 && $this->items[($this->free_ptr-1) % $this->items_max_size] != $item) {
      $this->last_page = $this->items[($this->free_ptr-1) % $this->items_max_size];
      $this->arguments_for_last_page = $this->arguments_for_item[($this->free_ptr-1) % $this->items_max_size];
    }

    $this->items[$this->free_ptr % $this->items_max_size] = $item;
    $this->arguments_for_item[$this->free_ptr % $this->items_max_size] = $arguments;
    $this->free_ptr++;
  }
    public function removeLastItem()
    {
        $this->free_ptr--;
        $this->items[($this->free_ptr) % $this->items_max_size]=null;
        $this->arguments_for_item[($this->free_ptr) % $this->items_max_size]=null;
    }
  /**
   * function previousItem, returns the last added page (free_ptr - 2, not the last added page)
   *
   * @return string
   */
  public function previousItem() {
    return $this->items[($this->free_ptr-2) % $this->items_max_size];
  }

  public function previousPage() {
    $root_dir      = $GLOBALS['root_dir'];
    $http_root_dir = $GLOBALS['http_root_dir'];

    $page_index = ($this->free_ptr-2) % $this->items_max_size;
    $root_dir_relative = $this->items[$page_index]."?".$this->arguments_for_item[$page_index];

    return str_replace($root_dir,$http_root_dir,$root_dir_relative);
  }

  // vito, 5 giugno 2009
  public function lastModule() {
    $root_dir      = $GLOBALS['root_dir'];
    $http_root_dir = $GLOBALS['http_root_dir'];

    if ($this->last_page === null || $this->arguments_for_last_page === null) {
      return $http_root_dir;
    }

    if ($this->arguments_for_last_page != '') {
        $root_dir_relative = $this->last_page."?".$this->arguments_for_last_page;
    } else {
        $root_dir_relative = $this->last_page;
    }

    return str_replace($root_dir,$http_root_dir,$root_dir_relative);
  }

  public function callerModuleWas($module_name) {
    return strpos($this->last_page, "$module_name.php") !== FALSE;
  }

  public function userComesFromLoginPage() {
      return strcmp(HTTP_ROOT_DIR . '/index.php?', $this->previousPage()) == 0;
  }
}