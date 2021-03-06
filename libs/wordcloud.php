<?php
/*
@wordCloud
Author: Derek Harvey
Website: www.lotsofcode.com

@Description
PHP Tag Cloud Class, a nice and simple way to create a php tag cloud, a database and non-database solution.
*/
class wordCloud {
  var $wordsArray = array();
  var $refsArray = array();
  /*
  * PHP 5 Constructor
  *
  * @param array $words
  * @return void
  */
  
  function __construct($words = false) {
    if ($words !== false && is_array($words)) {
      foreach($words as $key => $value) {
        $this->addWord($value);
      }
    }
  }
  /*
  * PHP 4 Constructor
  *
  * @param array $words
  * @return void
  */
  
  function wordCloud($words = false) {
    $this->__construct($words);
  }
  /*
  * Assign word to array
  *
  * @param string $word
  * @return string
  */
  
  function addWord($word, $value = 1, $ref = 0) {
    if (array_key_exists($word, $this->wordsArray)) $this->wordsArray[$word]+= $value;
    else $this->wordsArray[$word] = $value;
    $this->refsArray[$word] = $ref;
    return $this->wordsArray[$word];
  }
  /*
  * Shuffle associated names in array
  */
  
  function shuffleCloud() {
    $keys = array_keys($this->wordsArray);
    shuffle($keys);
    if (count($keys) && is_array($keys)) {
      $tmpArray = $this->wordsArray;
      $this->wordsArray = array();
      foreach($keys as $key => $value) $this->wordsArray[$value] = $tmpArray[$value];
    }
  }
  /*
  * Calculate size of words array
  */
  
  function getCloudSize() {
    return array_sum($this->wordsArray);
  }
  /*
  * Get the class range using a percentage
  *
  * @returns int $class
  */
  
  function getClassFromPercent($percent) {
    if ($percent >= 99) $class = 1;
    else if ($percent >= 70) $class = 2;
    else if ($percent >= 60) $class = 3;
    else if ($percent >= 50) $class = 4;
    else if ($percent >= 40) $class = 5;
    else if ($percent >= 30) $class = 6;
    else if ($percent >= 20) $class = 7;
    else if ($percent >= 10) $class = 8;
    else if ($percent >= 5) $class = 9;
    else $class = 0;
    return $class;
  }
  /*
  * Create the HTML code for each word and apply font size.
  *
  * @returns string $spans
  */
  
  function showCloud($returnType = "html") {
    $this->shuffleCloud();
    $this->max = max($this->wordsArray);
    if (is_array($this->wordsArray)) {
      $return = ($returnType == "html" ? "" : ($returnType == "array" ? array() : ""));
      foreach($this->wordsArray as $word => $popularity) {
        $sizeRange = $this->getClassFromPercent(($popularity / $this->max) * 100);
        if ($returnType == "array") {
          $return[$word]['word'] = $word;
          $return[$word]['ref'] = $this->refsArray[$word];
          $return[$word]['sizeRange'] = $sizeRange;
          //if ($currentColour) $return[$word]['randomColour'] = $currentColour;
        } else if ($returnType == "html") {
          $return.= "<span class='word size{$sizeRange}'> {$word} </span>";
        }
      }
      return $return;
    }
  }
}
?>
