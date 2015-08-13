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
 * Authentication class that uses the security token to authenticate
 * requests. Singing in this case means simply adding ?st=<token> to
 * the url
 *
 * @author Chris Chabot
 */
class osapiSecurityToken extends osapiAuth {
  private $securityToken;

  public function __construct($securityToken) {
    $this->securityToken = $securityToken;
  }

  public function sign($method, $url, $params = array(), $postBody = false) {
    $seperator = strpos($url, '?') === false ? '?' : '&';
    return $url . $seperator . 'st=' . $this->securityToken . implode('&', $params);
  }
}
