<?php
require_once __DIR__ . '/config/config.php';

if (!is_post()) {
	http_response_code(405);
	header('Allow: POST');
	exit(t('Sign out must be submitted as a form.', 'ការចាកចេញត្រូវធ្វើតាមរយៈទម្រង់។'));
}
verify_csrf();

$_SESSION = [];
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: ./index.php');
exit;
?>