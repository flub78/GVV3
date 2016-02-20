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

		// To have full var_dump
		ini_set('xdebug.var_display_max_depth', 5);
		ini_set('xdebug.var_display_max_children', 256);
		ini_set('xdebug.var_display_max_data', 1024);
	}

	/**
	 * Envoie une requête à HEVA
	 */
	public function heva_request($req_uri = "", $params = array())
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
	    $request = $this->heva_request("/persons");

	    if (!$request->success) {
		  echo "status_code = " . $request->status_code . br();
		  echo "success = " . $request->success . br();
		  return;
	    }

		$result = json_decode($request->body, true);

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

		$table = array();
		foreach ($result as $row) {
		    if (isset($row['first_name']) && isset($row['last_name'])) {
		        $row['image'] = $row['first_name'] . ' ' . $row['last_name'];
		    }
		    $row['linked'] = false;
		    $row['create'] = anchor(controller_url($this->controller) . "/edit/" . $row['licence_number'], 'Créer');
		    $row['associe'] = anchor(controller_url($this->controller) . "/edit/" . $row['licence_number'], 'Associe');
		    $row['info'] = anchor(controller_url($this->controller) . "/edit/" . $row['licence_number'], 'Info');
		    $row['sales'] = anchor(controller_url($this->controller) . "/sales_pilote/" . $row['licence_number'], 'Ventes');
		    $row['qualifs'] = anchor(controller_url($this->controller) . "/qualif_pilote/" . $row['licence_number'], 'Qualifications');

		    $actions = array(
		            'Info' => $row['info'],
		            'Ventes' => $row['sales']
		    );

		    $row['actions'] = form_dropdown('target_level', $actions);
		    $table[] = $row;
		}

		$attrs ['fields'] = array('civility', 'first_name', 'last_name', 'licence_number', 'date_of_birth',
		        'comment', 'linked', 'info', 'sales', 'qualifs');
		$attrs['controller'] = $this->controller;
		$data ['controller'] = $this->controller;
		$data ['data_table'] = datatable('heva_licences', $table, $attrs);
		$data['table_title'] = 'Licenciés HEVA';

		$this->load->view('default_table', $data);

	}

	/**
	 * Retourne les informations sur l'association
	 * @param string $id
	 */
    public function association() {
        $id = $this->config->item('ffvv_id');
        $request = $this->heva_request("/associations/$id");

        if (!$request->success) {
            echo "status_code = " . $request->status_code . br();
            echo "success = " . $request->success . br();
            return;
        }

        $result = json_decode($request->body, true);
        var_dump($result);
    }

    /**
     * transforme résultat en un tableau a deux dimensions
     * @param unknown $result
     */
    private function format_sales($result) {
        /**
         * array (size=30)
         0 =>
         array (size=8)
         'created_at' => string '2016-01-09 14:13:15' (length=19)
         'amount' => string '0.00' (length=4)
         'association' =>
         array (size=3)
         'name' => string 'ABBEVILLE' (length=9)
         'code' => string '228002' (length=6)
         'type' => string 'Club' (length=4)
         'season' =>
         array (size=1)
         'short_name' => string '2016' (length=4)
         'payment' => null
         'collecting_association' =>
         array (size=3)
         'name' => string 'FFVV' (length=4)
         'code' => string '100000' (length=6)
         'type' => string 'Federation' (length=10)
         'type' => string 'Affiliate' (length=9)
         'total_amount' => string '0.00' (length=4)

         28 =>
         array (size=8)
         'created_at' => string '2016-01-07 15:08:53' (length=19)
         'amount' => string '10.00' (length=5)
         'association' =>
         array (size=3)
         'name' => string 'ABBEVILLE' (length=9)
         'code' => string '228002' (length=6)
         'type' => string 'Club' (length=4)
         'season' =>
         array (size=1)
         'short_name' => string '2016' (length=4)
         'payment' =>
         array (size=7)
         'method' => string 'pr' (length=2)
         'reference' => string '20160107031' (length=11)
         'recepted_at' => null
         'amount' => string '10.00' (length=5)
         'cheque_number' => null
         'bank' => null
         'tag' => null
         'collecting_association' =>
         array (size=3)
         'name' => string 'FFVV' (length=4)
         'code' => string '100000' (length=6)
         'type' => string 'Federation' (length=10)
         'type' => string 'BadgeSale' (length=9)
         'total_amount' => string '10.00' (length=5)
         */

        $table = array();
        foreach ($result as $row) {
            $row['assoc_name'] = isset($row['association']['name']) ? $row['association']['name'] : "";
            $row['reference'] = isset($row['payment']['reference']) ? $row['payment']['reference'] : "";
            $row['collecting_assoc'] = isset($row['collecting_association']['name']) ? $row['collecting_association']['name'] : "";
            $row['year'] = isset($row['season']['short_name']) ? $row['season']['short_name'] : "";
            // var_dump($row);
            $table[] = $row;
        }
        return $table;
    }

    /**
     * Retourne les informations sur l'association
     * @param string $id
     */
    public function sales() {
        $id = $this->config->item('ffvv_id');
        $request = $this->heva_request("/associations/$id/sales");

        if (!$request->success) {
            echo "status_code = " . $request->status_code . br();
            echo "success = " . $request->success . br();
            return;
        }

        $result = json_decode($request->body, true);

        $table = $this->format_sales($result);

        $attrs ['fields'] = array('created_at', 'year', 'total_amount', 'assoc_name', 'collecting_assoc', 'type', 'reference');
        $data ['controller'] = $this->controller;
        $data ['data_table'] = datatable('heva_sales', $table, $attrs);
        $data['table_title'] = 'Facturation HEVA';

        $this->load->view('default_table', $data);

    }

    /**
     * Retourne les informations sur les licences
     * @param string $id
     */
    public function players() {
        $id = $this->config->item('ffvv_id');
        $request = $this->heva_request("/associations/$id/players", array('page_size' => 50000));

        if (!$request->success) {
            echo "status_code = " . $request->status_code . br();
            echo "success = " . $request->success . br();
            return;
        }

        $result = json_decode($request->body, true);

        /**
          */

        $table = array();
        foreach ($result as $row) {
            $row['first_name'] = isset($row['person']['first_name']) ? $row['person']['first_name'] : "";
            $row['last_name'] = isset($row['person']['last_name']) ? $row['person']['last_name'] : "";
            $row['assoc'] = isset($row['association']['name']) ? $row['association']['name'] : "";
            $row['year'] = isset($row['season']['short_name']) ? $row['season']['short_name'] : "";
            $row['lic_fed'] = isset($row['person']['licence_number']) ? $row['person']['licence_number'] : "";

            $row['type_name'] = isset($row['type']['name']) ? $row['type']['name'] : "";
            // var_dump($row);
            $table[] = $row;
        }
        $attrs ['fields'] = array('licence_number', 'starting_at', 'ending_at', 'first_name', 'last_name', 'lic_fed', 'assoc',
                'year', 'type_name'
        );
        $data ['controller'] = $this->controller;
        $data ['data_table'] = datatable('heva_licences', $table, $attrs);
        $data['table_title'] = 'Licences HEVA';

        $this->load->view('default_table', $data);

    }

    /**
     * Retourne les informations sur l'association
     * @param string $id
     */
    public function info_pilote($pilot = "1029") {
        $id = $this->config->item('ffvv_id');
        $request = $this->heva_request("/persons/$pilot");

        if (!$request->success) {
            echo "status_code = " . $request->status_code . br();
            echo "success = " . $request->success . br();
            return;
        }

        $result = json_decode($request->body, true);
        var_dump($result);
    }

    public function edit ($pilot) {
        $this->info_pilote($pilot);
    }

    /**
     * Retourne les informations sur l'association
     * @param string $id
     */
    public function sales_pilote($pilot = "1029") {
        $id = $this->config->item('ffvv_id');

        // fetch les informations pilotes
        $request = $this->heva_request("/persons/$pilot");
        if (!$request->success) {
            echo "status_code = " . $request->status_code . br();
            echo "success = " . $request->success . br();
            return;
        }
        $info_pilot = json_decode($request->body, true);
        $first_name = isset($info_pilot['first_name']) ? $info_pilot['first_name'] : "";
        $last_name = isset($info_pilot['last_name']) ? $info_pilot['last_name'] : "";

        $request = $this->heva_request("/persons/$pilot/sales");
        if (!$request->success) {
            echo "status_code = " . $request->status_code . br();
            echo "success = " . $request->success . br();
            return;
        }

        $result = json_decode($request->body, true);
        // var_dump($result);

        $table = $this->format_sales($result);

        $attrs ['fields'] = array('created_at', 'year', 'total_amount', 'assoc_name', 'collecting_assoc', 'type', 'reference');
        $data ['controller'] = $this->controller;
        $data ['data_table'] = datatable('heva_sales', $table, $attrs);
        $data['table_title'] = 'Licences HEVA du pilote ' . $first_name . ' ' . $last_name;

        $this->load->view('default_table', $data);
    }

    /**
     * Retourne les informations sur l'association
     * @param string $id
     */
    public function qualif_types() {
        $id = $this->config->item('ffvv_id');
        $request = $this->heva_request("/qualification-types");

        if (!$request->success) {
            echo "status_code = " . $request->status_code . br();
            echo "success = " . $request->success . br();
            return;
        }

        $result = json_decode($request->body, true);

		$attrs ['fields'] = array('id', 'category', 'name', 'is_date_required');
		$data ['controller'] = $this->controller;
		$data ['data_table'] = datatable('heva_qualif_types', $result, $attrs);
		$data['table_title'] = 'Types de qualification HEVA';

		$this->load->view('default_table', $data);
    }

    /**
     * Retourne les informations sur l'association
     * @param string $id
     */
    public function qualif_pilote($pilot = "1029") {
        $id = $this->config->item('ffvv_id');

        // fetch les informations pilotes
        $request = $this->heva_request("/persons/$pilot");
        if (!$request->success) {
            echo "status_code = " . $request->status_code . br();
            echo "success = " . $request->success . br();
            return;
        }
        $info_pilot = json_decode($request->body, true);
        $first_name = isset($info_pilot['first_name']) ? $info_pilot['first_name'] : "";
        $last_name = isset($info_pilot['last_name']) ? $info_pilot['last_name'] : "";

        // fetch les qualifs
        $request = $this->heva_request("/persons/$pilot/qualifications");
        if (!$request->success) {
            echo "status_code = " . $request->status_code . br();
            echo "success = " . $request->success . br();
            return;
        }

        $result = json_decode($request->body, true);
        // var_dump($result);exit;

        $table = array();
        foreach ($result as $row) {
            $row['type_name'] = isset($row['type']['name']) ? $row['type']['name'] : "";

            $table[] = $row;
        }
        $attrs ['fields'] = array('awarded_at', 'type_name');
        $data ['controller'] = $this->controller;
        $data ['data_table'] = datatable('heva_qualifs', $table, $attrs);
        $data['table_title'] = 'Qualifications pour '. $first_name . ' ' . $last_name;

        $this->load->view('default_table', $data);
    }

	/**
	 * Fonction de test
	 */
	function echo_params () {
	    echo "echo_params" . br();
	    echo "\$_SERVER"; var_dump($_SERVER);
	    echo "\$_GET"; var_dump($_GET);
	    echo "\$_POST"; var_dump($_POST);
	}


}
