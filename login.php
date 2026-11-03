<?php
require_once __DIR__.'/config/config.php';
if (is_logged_in()) redirect('dashboard.php');
$error = null;
if (is_post()) {
    verify_csrf();
    try {
        if (!db_ready()) throw new RuntimeException(t('Database is not connected.','មិនអាចតភ្ជាប់មូលដ្ឋានទិន្នន័យ។'));
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $st = $pdo->prepare('SELECT * FROM users WHERE email=? AND status=1 LIMIT 1');
        $st->execute([$email]);
        $u = $st->fetch();
        if (!$u || !password_verify($password, $u['password_hash'])) {
            throw new RuntimeException(t('Invalid email or password.','អ៊ីមែល ឬពាក្យសម្ងាត់មិនត្រឹមត្រូវ។'));
        }
        session_regenerate_id(true);
        $_SESSION['user'] = $u;
        redirect('dashboard.php');
    } catch (Throwable $e) {
        $error = user_facing_error($e, 'login');
    }
}
$page_title = t('Sign in','ចូលគណនី');
include __DIR__.'/includes/header.php';
?>
<div class="login-wrap py-5"><div class="container">
    <div class="auth-card mx-auto">
        <div class="content-card p-4 p-lg-5"><div class="text-center mb-4">
            <img class="brand-logo-lg mx-auto d-block" src="<?=url('assets/img/logo.png')?>" alt="<?=e(t('NPIT logo','និមិត្តសញ្ញា NPIT'))?>"><h1 class="h3 fw-bold mt-3"><?=t('Welcome back','សូមស្វាគមន៍ត្រឡប់មកវិញ')?></h1>
            <p class="text-muted mb-0"><?=t('Sign in to your school portal','ចូលទៅកាន់ប្រព័ន្ធសាលារបស់អ្នក')?></p></div><?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif; ?>
                <form method="post" autocomplete="on"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><div class="mb-3"><label class="form-label fw-semibold">Email</label>
                <input class="form-control" name="email" type="email" value="<?=old('email')?>" required autocomplete="email"></div><div class="mb-4"><label class="form-label fw-semibold"><?=t('Password','ពាក្យសម្ងាត់')?></label>
                <input class="form-control" name="password" type="password" required autocomplete="current-password"></div><button class="btn btn-primary btn-lg w-100"><?=t('Sign in','ចូលគណនី')?></button></form>
                <div class="text-center mt-4 small text-muted"><?=t('New here?','ថ្មីចំពោះប្រព័ន្ធ?')?> <a href="<?=url('register.php')?>"><?=t('Create an account','បង្កើតគណនី')?></a></div></div></div></div></div>
<?php include __DIR__.'/includes/footer.php'; ?>