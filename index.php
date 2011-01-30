<?php
require __DIR__ . '/classes/Mendeley.php';
$mendeley = new Mendeley;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>DocNotes</title>
  <style>
    body { font-family: Helvetica, Arial, sans-serif; }
    th, td { text-align: left; vertical-align: top; padding: 2px 5px; }
    li { margin-bottom: 1em; }
    .citation { color: green; }
  </style>
</head>
<body>

<? if ($collection = $_GET['collection']): ?>

<?
$docs = $mendeley->http('library/collections/' . urlencode($collection));

$items = array();
foreach ($docs['document_ids'] as $id){
  $doc = $mendeley->http('library/documents/' . $id);
  if (!$doc['notes']) continue;
  $doc['id'] = $id;
  $items[] = $doc;
}

$citations = $mendeley->cite($items, 'nlm');
preg_match_all('!<div class="csl-right-inline">(.+?)</div>!s', $citations, $matches);
?>

<h1>DocNotes</h1>
<ol id="notes">
<? foreach ($items as $key => $doc): ?>
  <li class="item" id="doc-<? h($doc['id']); ?>">
    <div class="citation"><?= $matches[1][$key]; ?></div>
    <div class="notes"><? h($doc['notes']); ?></div>
  </li>
<? endforeach; ?>
</ol>

<? else: ?>

<? $collections = $mendeley->http('library/collections'); ?>

<h1>Groups</h1>
<table>
  <thead>
    <tr>
      <th>Group</th>
      <th>Documents</th>
    </tr>
  </thead>
  <tbody>
<? foreach ($collections as $collection): ?>
    <tr>
      <td><a href="<? h(url('./', array('collection' => $collection['id']))); ?>"><? h($collection['name']); ?></a></td>
      <td><? h($collection['size']); ?></td>
    </tr>
<? endforeach; ?>
  </tbody>
</table>

<? endif; ?>

</body>
</html>