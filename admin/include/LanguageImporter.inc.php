<?php
require_once 'XML/Parser.php';

class LanguageImporter extends XML_Parser
{

  public function getMessages() {
    return $this->rows;
  }

  public function startHandler($parser, $name, $attribs) {
    if($name == 'ooo_row') {
      $this->currentRow = array();
    }
    elseif ($name == 'id' || $name == 'message') {
      $this->currentTag = $name;
    }
  }

  public function endHandler($parser, $name) {
    $this->currentTag = null;
    if($name == 'ooo_row') {
      $this->rows[] = $this->currentRow;
    }
  }

  public function cdataHandler($parser, $data) {
    $data = trim($data);
    if(empty($data)) {
      return true;
    }
    if($this->currentTag != null) {
      $this->currentRow[$this->currentTag] = $data;
    }
  }

  private $rows = array();
  private $currentRow = array();
  private $currentTag = '';
}