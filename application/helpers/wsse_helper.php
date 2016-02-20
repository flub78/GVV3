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
 *    Routines de support Web Services Security (WS-Security, WSS)
 */
if (!defined('BASEPATH'))
    exit ('No direct script access allowed');

if (!function_exists('wsse_header')) {

    /**
     *
     * @param unknown $username
     * @param unknown $password
     * @return string
     */
    function wsse_header($username, $password) {
        $nonce = hash_hmac('sha1', uniqid(null, true), uniqid(), false);
        $created = new DateTime('now', new DateTimezone('UTC'));
        $created = $created->format(DateTime::ISO8601);
        $digest = sha1($nonce . $created . $password, true);
        return sprintf('X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"', $username, base64_encode($digest), $nonce, $created);
    }
}
if (!function_exists('wsse_header_short')) {

    /**
     *
     * @param unknown $username
     * @param unknown $password
     * @return string
     */
    function wsse_header_short($username, $password) {
        $nonce = hash_hmac('sha1', uniqid(null, true), uniqid(), false);
        $created = new DateTime('now', new DateTimezone('UTC'));
        $created = $created->format(DateTime::ISO8601);
        $digest = sha1($nonce . $created . $password, true);
        return sprintf(' UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"', $username, base64_encode($digest), $nonce, $created);
    }
}

if (!function_exists('decode_chunked')) {
function decode_chunked($str) {		// décode les paquets HTTP 1.1 'chunked'
    for ($res = ''; !empty($str); $str = trim($str)) {
        $pos = strpos($str, "\r\n");
        $len = hexdec(substr($str, 0, $pos));
        $res.= substr($str, $pos + 2, $len);
        $str = substr($str, $pos + 2 + $len);
    }
    return $res;
}
}

if (!function_exists('http_request')) {
function http_request(
        $ip,                       /* Target IP/Hostname */
        $uri = '/',                /* Target URI */
        $getdata = array(),        /* HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2') */
        $WSSE_headers = '', /* Custom HTTP headers ie. array('Referer: http://localhost/ */
        $timeout = 1,           /* Socket timeout in seconds */
        $res_hdr = false           /* Include HTTP response headers */
        )
{
    $ret = '';
    $verb = 'GET';             /* HTTP Request Method (GET and POST supported) */
    $port = 80;                /* Target TCP port */
    $cookie_str = '';
    $getdata_str = count($getdata) ? '?' : '';
    $chunked = true;


    foreach ($getdata as $k => $v)
        $getdata_str .= urlencode($k) .'='. urlencode($v) . '&';


        $crlf = "\r\n";
        $req = $verb .' '. $uri . $getdata_str .' HTTP/1.1' . $crlf;
        $req .= 'Host: '. $ip . $crlf;
        $req .= 'Authorization: WSSE profile="UsernameToken"' . $crlf;
        $req .= $WSSE_headers . $crlf;
        $req .= $crlf;

        if (($fp = @fsockopen($ip, $port, $errno, $errstr)) == false)
            return "Error $errno: $errstr\n";

            fputs($fp, $req);
            while ($line = fgets($fp)) $ret .= $line;
            fclose($fp);
            if (strpos($ret,"Transfer-Encoding: chunked")=== false) $chunked = false;

            if (!$res_hdr) $ret = substr($ret, strpos($ret, "\r\n\r\n") +4 );
            if ($chunked) $ret = decode_chunked($ret);

            return $ret;
}
}