<?php
/** Meslek verisini senkron JS değişkeni olarak üretir (panelin anlık açılması için). */
$root = dirname(__DIR__) . '/netlify-src';
$json = file_get_contents($root . '/assets/data/careers.json');
$data = json_decode($json, true);
$out = 'window.CAREER_DATA = ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ";\n";
file_put_contents($root . '/assets/data/careers-data.js', $out);
echo count($data), " meslek -> ", strlen($out), " bayt\n";
