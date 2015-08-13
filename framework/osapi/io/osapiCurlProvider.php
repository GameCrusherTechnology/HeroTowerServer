<?php
/*
 * Copyright 2008 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * The osapiHttpProvider class is used to define the HTTP layer that will relay
 * your requests to the remote server.
 *
 * @author Dan Holevoet
 */
class osapiCurlProvider extends osapiHttpProvider {
  public function send($url, $method, $postBody = false, $headers = false, $ua = self::USER_AGENT) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($postBody) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    } else {
      curl_setopt($ch, CURLOPT_HTTPGET, 1);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    if ($headers) {
      curl_setopt($ch, CURLOPT_HEADER, true);
    }
    $data = @curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errno = @curl_errno($ch);
    $error = @curl_error($ch);
    @curl_close($ch);
    if ($errno != CURLE_OK) {
      throw new osapiException("HTTP Error: " . $error);
    }
    return array('http_code' => $http_code, 'data' => $data);
  }
}
