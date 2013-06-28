<?php
/**
 * AppShell file
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::import('Vendor', 'htmlsql', array('file' => 'htmlsql' . DS . 'htmlsql.class.php'));
App::import('Vendor', 'snoopy', array('file' => 'htmlsql' . DS . 'snoopy.class.php'));

App::uses('Shell', 'Console');


/**
 * Application Shell
 *
 * Add your application-wide methods in the class below, your shells
 * will inherit them.
 *
 * @package       app.Console.Command
 */
class AreaShell extends Shell {

  public $uses = array('Area');
  
  // Console/cake area register filename
  public function register() {
    //read xml file
    $xmlData = file_get_contents($this->args[0]);
    if (!$xmlData) {
      $this->out('Error Read File ' . $this->args[0]);
      return;
    }
    
    //convert utf8
    $xmlData = mb_convert_encoding($xmlData, 'utf8', 'sjis-win');
    
    //htmlsql read xml data
    $wsql = new htmlsql();
    if (!$wsql->connect('string', $xmlData)) {
      $this->out('Error Connect htmlsql');
      return;
    }
    
    //Get GeometricFeature tag
    $wsql->query('SELECT * FROM GeometricFeature');
    $geometric = $wsql->fetch_array();
    
    foreach ($geometric as $row) {
      //for save data
      $saveData = array();
      
      $wsql->connect('string', $row['text']);

      //KEN_NAME
      $wsql->query('SELECT text FROM Property WHERE $propertytypename == "KEN_NAME"');
      $kenName = $wsql->fetch_array();
      $saveData['KEN_NAME'] = $kenName[0]['text'];
      
      //GST_NAME
      $wsql->query('SELECT text FROM Property WHERE $propertytypename == "GST_NAME"');
      $gstName = $wsql->fetch_array();
      $saveData['GST_NAME'] = $gstName[0]['text'];
      
      //CSS_NAME
      $wsql->query('SELECT text FROM Property WHERE $propertytypename == "CSS_NAME"');
      $cssName = $wsql->fetch_array();
      $saveData['CSS_NAME'] = $cssName[0]['text'];
      
      //MOJI
      $wsql->query('SELECT text FROM Property WHERE $propertytypename == "MOJI"');
      $moji = $wsql->fetch_array();
      $saveData['MOJI'] = $moji[0]['text'];

      //Coordinates
      $wsql->select('LinearRing');
      $wsql->query('SELECT text FROM Coordinates');
      $coordinates = $wsql->fetch_array();
      $lnglatArray = explode(' ', $coordinates[0]['text']);
      $coordinatesArray = Array();
      foreach ($lnglatArray as $lnglat) {
        $lnglat = explode(',', $lnglat);
        $coordinate = array(floatval($lnglat[0]), floatval($lnglat[1]));
        array_push($coordinatesArray, $coordinate);
      }
      $saveData['geo'] = array(
      	'type' => 'Polygon',
        'coordinates' => array($coordinatesArray),
      );
      
      //save area
      $this->Area->create();
      $savedData = $this->Area->save(array('Area' => $saveData));
    }
  }
}
