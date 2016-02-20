<?php
/**
 *    Project {$PROJECT}
 *    Copyright (C) 2015 {$AUTHOR}
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @filesource Metadata.php
 * @package controllers
 * Development tools controler
 *
 * It is just a controller for experiments and to display information useful
 * during development. Could be disabled in production.
 *
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// First, include Requests
include(APPPATH . '/third_party/Requests.php');

// Next, make sure Requests can load internal classes
Requests::register_autoloader();

/**
 * Development controller
 * @author frederic
 *
 */
class FFVV extends MY_Controller {

    var $controller = 'FFVV';

	function __construct() {
		parent :: __construct();

		$this->load->config('club');
		$this->load->helper('wsse');
	}

	/**
	 * Display phpinfo
	 */
	public function heva_request($req_uri = "/persons", $params = array())
	{

		$FFVV_Heva_Host="api.licences.ffvv.stadline.com";

		$head = wsse_header_short($this->config->item('ffvv_id'), $this->config->item('ffvv_pwd'));
		$url = "http://" . $FFVV_Heva_Host . $req_uri;
		return Requests::get($url, array('X-WSSE' => $head), $params);
	}

	/**
	 * Get licences information from HEVA
	 */
	public function licences() {
	    $request = $this->heva_request();

	    if (!$request->success) {
		  echo "status_code = " . $request->status_code . br();
		  echo "success = " . $request->success . br();
		  return;
	    }

		$result = json_decode($request->body, true);
		// echo "body = " . $request->body . br();

		/**
		 * array (size=79)
  0 =>
    array (size=17)
      'civility' => string 'M.' (length=2)
      'first_name' => string 'Jérome' (length=7)
      'last_name' => string 'DELABRE' (length=7)
      'licence_number' => string '28256' (length=5)
      'date_of_birth' => string '1966-10-24' (length=10)
      'comment' => string '' (length=0)
      'city_of_birth' => string 'ABBEVILLE' (length=9)
      'country_of_birth' => string 'FR' (length=2)
      'insee_category' => string 'Chef d'entreprise/commerçant' (length=29)
      'nationality' => string 'Français(e)' (length=12)
      'is_commercial_use' => boolean false
      'card_sent_at' => null
      'address' =>
        array (size=9)
          'delivery_point' => string '' (length=0)
          'localisation' => string '' (length=0)
          'address' => string '6 rue des Moines' (length=16)
          'distribution' => string '' (length=0)
          'postal_code' => string '80100' (length=5)
          'cedex' => string '' (length=0)
          'city' => string 'ABBEVILLE' (length=9)
          'country' => string 'FR' (length=2)
          'receiver' => null
      'email' =>
        array (size=2)
          'value' => string 'j.delabre@delabre.fr' (length=20)
          'is_private' => boolean false
      'mobile' =>
        array (size=2)
          'value' => string '0607279764' (length=10)
          'is_private' => boolean true
      'phone' =>
        array (size=2)
          'value' => string '0322249907' (length=10)
          'is_private' => boolean true
      'players' =>
        array (size=1)
          0 =>
            array (size=7)
              ...

		 */
// 		foreach ($result as $row) {
// 		    var_dump($row);
// 		}

		$attrs ['fields'] = array('civility', 'first_name', 'last_name', 'licence_number', 'date_of_birth',
		        'comment');
		$data ['controller'] = $this->controller;
		$data ['data_table'] = datatable('heva_licences', $result, $attrs);
		$data['table_title'] = 'Licenciés HEVA';

		$this->load->view('default_table', $data);

	}


	function echo_params () {
	    echo "echo_params" . br();
	    echo "\$_SERVER"; var_dump($_SERVER);
	    echo "\$_GET"; var_dump($_GET);
	    echo "\$_POST"; var_dump($_POST);
	}


}
