<?php 
require "vendor/autoload.php"; 
$app = require_once "bootstrap/app.php"; 
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$req = Illuminate\Http\Request::create("/api/delivery/orders/latest", "GET"); 
$req->setUserResolver(function() { return App\Models\User::find(4); }); 
$resp = app()->handle($req); 
echo $resp->getContent();
