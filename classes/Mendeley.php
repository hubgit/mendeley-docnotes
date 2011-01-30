<?php

require __DIR__ . '/lib.php';
require __DIR__ . '/HumanNameParser/Name.php';
require __DIR__ . '/HumanNameParser/Parser.php';

class Mendeley {
  private $server = 'http://www.mendeley.com/oapi/';
  
  function __construct(){
    $this->config = parse_ini_file(__DIR__ . '/../config.ini');
    if (empty($this->config['token']) || empty($this->config['token_secret'])) $this->authorize(); 
  }
  
  function cite($items, $style = 'nlm'){    
    array_walk($items, array($this, 'prepare_doc_for_citeproc'));
    $content = json_encode(array('items' => $items));
    $http = array('method' => 'POST', 'header' => array('Content-Type: application/json'), 'content' => $content);
    $context = stream_context_create(array('http' => $http));
    $url = url('http://127.0.0.1:8085/', array('responseformat' => 'rtf', 'style' => $style));
    return file_get_contents($url, null, $context);
  }
  
  function prepare_doc_for_citeproc(&$doc){
    $authors = array();
    foreach ($doc['authors'] as $author){
      $name = new HumanNameParser_Name($author);
      $parser = new HumanNameParser_Parser($name); 
      $authors[] = array(
        'given' => $parser->getFirst(),
        'family' => $parser->getLast(),
        'static-ordering' => false,
        );
    }
    
    foreach (array('year', 'month', 'day') as $item)
      if (!$doc[$item])
        $doc[$item] = '01';
            
    $doc = array_filter(array(
      'id' => $doc['id'],
      'title' => $doc['title'],
      'author' => $authors,
      'publisher' => $doc['publisher'],
      'volume' => $doc['volume'],
      'issue' => $doc['issue'],
      'page-range' => $doc['pages'],
      'issued' => array(
        'date-parts' => array(array($doc['year'], $doc['month'], $doc['day']))
        ),
      'type' => 'article', //$doc['type'],
      ));
  }
  
  function http($path, $params = array(), $http = array()){
    $url = url($this->server . $path, $params);
    
    $http = array_merge(array('method' => 'GET', 'content' => null, 'header' => array('Accept: application/json')), $http);

    try {    
      $oauth = $this->oauth();
      $oauth->setToken($this->config['token'], $this->config['token_secret']);
      $oauth->fetch($url, $http['content'], constant('OAUTH_HTTP_METHOD_' . $http['method']), $http['header']);
      $response = $oauth->getLastResponse();
      $info = $oauth->getLastResponseInfo();
      if ($info['http_code'] !== 200) return false;
    } catch (OAuthException $e) { debug('Error fetching ' . $url); debug($oauth->debugInfo); exit(); }
    
    return json_decode($response, true);
  }
  
  function authorize(){
    $oauth = $this->oauth();
    try {
      $request_token = $oauth->getRequestToken('http://www.mendeley.com/oauth/request_token/');
    } catch (OAuthException $e){ debug('Error getting request token'); debug($oauth->debugInfo); exit(); };

    $url = url('http://www.mendeley.com/oauth/authorize/', array('oauth_token' => $request_token['oauth_token'], 'callback_url' => 'oob'));
    print 'Authorize at ' . $url . "\n";
    system(sprintf('open %s', escapeshellarg($url)));
    fwrite(STDOUT, "Enter the PIN: ");
    
    $verifier = trim(fgets(STDIN));

    $oauth->setToken($request_token['oauth_token'], $request_token['oauth_token_secret']);
    try {
      $access_token = $oauth->getAccessToken('http://www.mendeley.com/oauth/access_token/', NULL, $verifier);
    } catch (OAuthException $e){ debug('Error getting request token'); debug($oauth->debugInfo); exit(); };

    printf("token = '%s'\ntoken_secret = '%s'\n", $access_token['oauth_token'], $access_token['oauth_token_secret']);
    exit();
  }
  
  function oauth(){
    $oauth = new OAuth($this->config['consumer_key'], $this->config['consumer_secret'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
    $oauth->enableDebug();
    return $oauth;
  }
}