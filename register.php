<?php
require_once __DIR__.'/config/config.php';
if(is_logged_in()) redirect('dashboard.php');
$error=null;
if(is_post()){
    verify_csrf();
    $name=trim($_POST['name']??'');
    $email=trim($_POST['email']??'');
    $phone=trim($_POST['phone']??'');
    $role=in_array($_POST['role']??'', ['student','parent'], true)?$_POST['role']:'student';
    $pw=$_POST['password']??'';
    $pw2=$_POST['password_confirmation']??'';
    try{
        if(!db_ready()) throw new RuntimeException(t('Database is not connected.','មិនអាចតភ្ជាប់មូលដ្ឋានទិន្នន័យ។'));
        if($name==='') throw new RuntimeException(t('Name is required.','ត្រូវបញ្ចូលឈ្មោះ។'));
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException(t('Enter a valid email.','សូមបញ្ចូលអ៊ីមែលត្រឹមត្រូវ។'));
        if(user_email_exists($pdo,$email)) throw new RuntimeException(t('An account with that email already exists.','គណនីដែលមានអ៊ីមែលនេះមានរួចហើយ។'));
        if(strlen($pw)<8 || $pw!==$pw2) throw new RuntimeException(t('Passwords must match and be at least 8 characters.','ពាក្យសម្ងាត់ត្រូវដូចគ្នា និងមានយ៉ាងតិច ៨ តួអក្សរ។'));
        $childId=trim($_POST['child_student_id']??'');
        $pdo->beginTransaction();
        $st=$pdo->prepare('INSERT INTO users(name,email,password_hash,role,phone) VALUES(?,?,?,?,?)');
        $st->execute([$name,$email,password_hash($pw,PASSWORD_DEFAULT),$role,$phone]);
        $newUserId=(int)$pdo->lastInsertId();
        $linkNote='';
        if($role==='student'){
            provision_student_profile($pdo,$newUserId,$name,$email,$phone);
        }elseif($role==='parent' && $childId!==''){
            $cst=$pdo->prepare('SELECT id,parent_user_id FROM students WHERE student_id=? LIMIT 1');
            $cst->execute([$childId]);
            $child=$cst->fetch();
            if($child && empty($child['parent_user_id'])){
                $pdo->prepare('INSERT IGNORE INTO parent_link_requests(parent_user_id,student_id) VALUES(?,?)')
                    ->execute([$newUserId,(int)$child['id']]);
                $linkNote=t(' Your child link request will be reviewed by an administrator.',' សំណើភ្ជាប់កូនរបស់អ្នកនឹងត្រូវបានពិនិត្យដោយអ្នកគ្រប់គ្រង។');
            }elseif($child){
                $linkNote=t(' That student is already linked to a parent account. You can contact an administrator if this is an error.',' សិស្សនោះបានភ្ជាប់ជាមួយគណនីអាណាព្យាបាលរួចហើយ។ សូមទាក់ទងអ្នកគ្រប់គ្រង ប្រសិនបើមានកំហុស។');
            }else{
                $linkNote=t(' Your account was created, but that student ID was not found — you can link your child from the Parent portal after signing in.',' គណនីរបស់អ្នកបានបង្កើតរួច ប៉ុន្តែលេខសម្គាល់សិស្សនោះមិនត្រូវបានរកឃើញទេ — អ្នកអាចភ្ជាប់កូនរបស់អ្នកនៅប្រព័ន្ធអាណាព្យាបាល បន្ទាប់ពីចូលគណនី។');
            }
        }
        $pdo->commit();
        flash('success',t('Registration complete. You can sign in now.','ការចុះឈ្មោះបានបញ្ចប់។ ឥឡូវនេះអាចចូលគណនីបាន។').$linkNote);
        redirect('login.php');
    }catch(Throwable $e){
        if($pdo->inTransaction()) $pdo->rollBack();
        $error=$e instanceof PDOException && $e->getCode()==='23000'
            ? t('That email is already registered.','អ៊ីមែលនេះបានចុះឈ្មោះរួចហើយ។')
            : user_facing_error($e,'registration');
    }
}
$page_title=t('Create account','បង្កើតគណនី');
include __DIR__.'/includes/header.php';
?>
<div class="login-wrap py-5"><div class="container"><div class="auth-card mx-auto"><div class="content-card p-4 p-lg-5"><div class="d-flex justify-content-between align-items-center mb-4"><div><div class="kicker"><?=t('Enrollment','ការចុះឈ្មោះ')?></div><h1 class="h3 fw-bold mt-2"><?=t('Create your account','បង្កើតគណនីរបស់អ្នក')?></h1></div><span class="badge badge-soft p-2"><?=t('Free registration','ចុះឈ្មោះដោយឥតគិតថ្លៃ')?></span></div><?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><div class="row g-3"><div class="col-md-6"><label class="form-label fw-semibold"><?=t('Full name','ឈ្មោះពេញ')?></label><input class="form-control" name="name" value="<?=old('name')?>" required></div><div class="col-md-6"><label class="form-label fw-semibold">Email</label><input class="form-control" name="email" value="<?=old('email')?>" type="email" required></div><div class="col-md-6"><label class="form-label fw-semibold"><?=t('Phone','លេខទូរស័ព្ទ')?></label><input class="form-control" name="phone" value="<?=old('phone')?>"></div><div class="col-md-6"><label class="form-label fw-semibold"><?=t('Account type','ប្រភេទគណនី')?></label><select class="form-select" name="role"><option value="student" <?=selected('role','student')?>><?=t('Student','សិស្ស')?></option><option value="parent" <?=selected('role','parent')?>><?=t('Parent','អាណាព្យាបាល')?></option></select></div><div class="col-md-6"><label class="form-label fw-semibold"><?=t('Password','ពាក្យសម្ងាត់')?></label><input class="form-control" name="password" type="password" minlength="8" required></div><div class="col-md-6"><label class="form-label fw-semibold"><?=t('Confirm password','បញ្ជាក់ពាក្យសម្ងាត់')?></label><input class="form-control" name="password_confirmation" type="password" minlength="8" required></div><div class="col-12 parent-only-field" style="<?=old('role')==='parent'?'':'display:none'?>"><label class="form-label fw-semibold"><?=t("Child's Student ID (optional)","លេខសម្គាល់សិស្សរបស់កូន (មិនចាំបាច់)")?></label><input class="form-control" name="child_student_id" value="<?=old('child_student_id')?>" placeholder="NPIT-2026-0001"><div class="form-text"><?=t('Only for parent accounts. Leave blank and link your child later from the Parent portal if you don\'t have it yet.','សម្រាប់តែគណនីអាណាព្យាបាល។ ទុកទទេ ហើយភ្ជាប់កូននៅពេលក្រោយបាន ប្រសិនបើអ្នកមិនទាន់មានលេខសម្គាល់។')?></div></div></div><div class="form-text mt-3"><?=t('Teacher and administrator accounts are created by an administrator.','គណនីគ្រូ និងអ្នកគ្រប់គ្រងត្រូវបង្កើតដោយអ្នកគ្រប់គ្រង។')?></div><button class="btn btn-primary mt-4 w-100 btn-lg"><?=t('Register','ចុះឈ្មោះ')?></button></form></div></div></div></div>
<script type="module">
(()=>{const roleSelect=document.querySelector('select[name="role"]');const field=document.querySelector('.parent-only-field');if(!roleSelect||!field)return;const sync=()=>{field.style.display=roleSelect.value==='parent'?'':'none';};roleSelect.addEventListener('change',sync);sync();})();
</script>
<?php include __DIR__.'/includes/footer.php'; ?>