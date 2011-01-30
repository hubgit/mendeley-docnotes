<?php

function url($url, $params = array()){
  if (!empty($params)) $url .= '?' . http_build_query($params);
  return $url;
}

function debug($output){
  print print_r($output, true) . "\n";
}

function h($output){
  print htmlspecialchars($output, ENT_QUOTES, 'UTF-8'); 
}
