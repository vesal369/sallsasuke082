<?php
require_once __DIR__.'/config/config.php';
$next=$_GET['lang']??null;
if (!is_string($next) || !in_array($next, ['km','en'], true)) {
    http_response_code(400);
    exit(t('Invalid language selection.','ការជ្រើសរើសភាសាមិនត្រឹមត្រូវទេ។'));
}
$_SESSION['lang']=$next;
$isSecure = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';
setcookie('lang', $next, [
    'expires' => time() + 365 * 24 * 60 * 60,
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

$referer=parse_url($_SERVER['HTTP_REFERER']??'');
$requestHost=parse_url('//'.($_SERVER['HTTP_HOST']??''), PHP_URL_HOST);
$requestPort=parse_url('//'.($_SERVER['HTTP_HOST']??''), PHP_URL_PORT);
$refererHost=$referer['host']??'';
$refererPort=$referer['port']??null;
$samePort = $refererPort === null || $requestPort === null || $refererPort === $requestPort;
if(is_array($referer) && $requestHost!==false && $requestHost!==null && $refererHost!=='' && strcasecmp($refererHost,$requestHost)===0 && $samePort && in_array(strtolower($referer['scheme']??''),['http','https'],true)){
    $back=($referer['path']??'/').(isset($referer['query'])?'?'.$referer['query']:'').(isset($referer['fragment'])?'#'.$referer['fragment']:'');
    if($back!=='' && $back[0]==='/' && !str_starts_with($back,'//') && !str_contains($back, '\\') && !preg_match('/[\r\n]/', $back)){
        header('Location: '.$back);
        exit;
    }
}
redirect('index.php');
?>