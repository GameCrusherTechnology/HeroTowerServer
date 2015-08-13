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
 * The osapiLocalHttpProvider class is used to define a mock HTTP layer that
 * returns a pre-defined response.
 *
 * @author Dan Holevoet
 */
class osapiLocalHttpProvider extends osapiHttpProvider {
  protected $response;
  
  public function __construct($response) {
    $this->response = $response;
  }
  
  public function send($url, $method, $postBody = false, $headers = false, $ua = self::USER_AGENT) {
    $nextResponse = array_pop($this->response);
    return array('http_code' => 200, 'data' => $nextResponse);
  }
  
  public function setResponse($response) {
    $this->response = $response;
  }
}
