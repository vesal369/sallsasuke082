<?php require_once __DIR__.'/config/config.php'; require_login(); $u=auth_user(); $page_title=t('Dashboard','ផ្ទាំងគ្រប់គ្រង'); $counts=['students'=>0,'teachers'=>0,'users'=>0,'content'=>0,'attendance'=>0,'outstanding'=>0]; if(db_ready()){ $counts['students']=(int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn(); $counts['teachers']=(int)$pdo->query('SELECT COUNT(*) FROM teachers')->fetchColumn(); $counts['users']=(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(); $counts['content']=(int)$pdo->query('SELECT COUNT(*) FROM content_items')->fetchColumn(); $counts['attendance']=(int)$pdo->query("SELECT COUNT(*) FROM attendance WHERE attendance_date=CURDATE()")->fetchColumn(); $counts['outstanding']=(float)$pdo->query('SELECT COALESCE(SUM(amount-paid_amount),0) FROM fees WHERE status<>\'paid\'')->fetchColumn(); }
// Single source of truth for the portal menu so the desktop sidebar and the
// mobile offcanvas menu can never drift out of sync with each other.
$menuLinks=[];
$menuLinks[]=['articles/index.php','bi-newspaper',t('News feed','ព័ត៌មានថ្មី')];
$menuLinks[]=['articles/my.php','bi-journal-text',t('My articles','អត្ថបទរបស់ខ្ញុំ')];
if(has_role(['admin','teacher'])){
    $menuLinks[]=[has_role('admin')?'admin/content.php':'teacher/content.php','bi-cloud-arrow-up',t('Content & photos','ព័ត៌មាន និងរូបថត')];
    $menuLinks[]=[has_role('admin')?'admin/students.php':'teacher/students.php','bi-mortarboard',t('Students','សិស្ស')];
}
if(has_role('admin')){
    $menuLinks[]=['admin/parent-links.php','bi-person-check',t('Parent link requests','សំណើភ្ជាប់អាណាព្យាបាល')];
    $menuLinks[]=['admin/library.php','bi-book-half',t('e-Library management','គ្រប់គ្រងបណ្ណាល័យឌីជីថល')];
    $menuLinks[]=['admin/articles.php','bi-shield-check',t('Article moderation','ការត្រួតពិនិត្យអត្ថបទ')];
    $menuLinks[]=['admin/teachers.php','bi-person-badge',t('Teachers','គ្រូ')];
    $menuLinks[]=['admin/users.php','bi-people',t('Users','អ្នកប្រើ')];
    $menuLinks[]=['admin/attendance.php','bi-calendar-check',t('Attendance','វត្តមាន')];
    $menuLinks[]=['admin/fees.php','bi-receipt',t('Fees','ថ្លៃសិក្សា')];
    $menuLinks[]=['admin/settings.php','bi-sliders',t('Site settings','ការកំណត់')];
} elseif(has_role('teacher')){
    $menuLinks[]=['teacher/attendance.php','bi-calendar-check',t('Attendance','វត្តមាន')];
} elseif(has_role('student')){
    $menuLinks[]=['student/index.php','bi-mortarboard',t('My profile','ប្រវត្តិរូបរបស់ខ្ញុំ')];
} elseif(has_role('parent')){
    $menuLinks[]=['parent/index.php','bi-people',t('Parent portal','ប្រព័ន្ធអាណាព្យាបាល')];
}
$menuLinks[]=['profile.php','bi-person-circle',t('My account','គណនីរបស់ខ្ញុំ')];
include __DIR__.'/includes/header.php'; ?>
<div class="container-fluid dashboard-shell py-4">
    <div class="row g-0"><aside class="col-xl-2 d-none d-xl-block">
        <div class="sidebar p-3 rounded-start-4">
            <div class="small text-uppercase text-muted fw-bold mb-3"><?=e(role_label($u['role']))?></div><a class="active" href="<?=url('dashboard.php')?>">
                <i class="bi bi-grid-1x2"></i><?=t('Overview','ទូទៅ')?></a>
                <?php foreach($menuLinks as $m):?><a href="<?=url($m[0])?>"><i class="bi <?=$m[1]?>"></i><?=$m[2]?></a><?php endforeach;?>
                <form action="<?=url('logout.php')?>" method="post" class="m-0">
                    <input type="hidden" name="csrf" value="<?=csrf_token()?>">
                    <button type="submit" class="sidebar-logout"><i class="bi bi-box-arrow-right"></i><?=t('Sign out','ចាកចេញ')?></button>
                </form></div></aside>
                    <div class="col-xl-10"><div class="ps-xl-4">
                        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                            <div>
                            <div class="kicker"><?=t('Portal overview','ទិដ្ឋភាពប្រព័ន្ធ')?></div><h1 class="h3 fw-bold mb-0"><?=t('Welcome,','សូមស្វាគមន៍')?> <?=e($u['name'])?></h1></div>
                            <div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="<?=url('index.php')?>"><i class="bi bi-globe me-1"></i><?=t('Website','គេហទំព័រ')?></a>
                            <a class="btn btn-primary d-xl-none" data-bs-toggle="offcanvas" href="#mobileMenu"><?=t('Menu','ម៉ឺនុយ')?></a></div></div><div class="row g-3">
                                <div class="col-sm-6 col-xl-3">
                                    <div class="stat-card p-4">
                                        <div class="icon-box mb-3">
                                            <i class="bi bi-mortarboard"></i></div>
                                            <div class="small text-muted"><?=t('Students','សិស្ស')?></div>
                                            <div class="display-6 fw-bold"><?=number_format($counts['students'])?>
                                        </div></div></div><div class="col-sm-6 col-xl-3">
                                            <div class="stat-card p-4"><div class="icon-box mb-3">
                                                <i class="bi bi-person-badge"></i></div>
                                                <div class="small text-muted"><?=t('Teachers','គ្រូ')?></div>
                                                <div class="display-6 fw-bold"><?=number_format($counts['teachers'])?></div></div></div><div class="col-sm-6 col-xl-3"><div class="stat-card p-4"><div class="icon-box mb-3"><i class="bi bi-images"></i></div><div class="small text-muted"><?=t('Published content','ព័ត៌មានផ្សព្វផ្សាយ')?></div><div class="display-6 fw-bold"><?=number_format($counts['content'])?>
                </div></div></div><div class="col-sm-6 col-xl-3"><div class="stat-card p-4">
                    <div class="icon-box mb-3"><i class="bi bi-calendar-check"></i></div><div class="small text-muted">
                        <?=t('Today attendance rows','កំណត់ត្រាវត្តមានថ្ងៃនេះ')?></div>
                        <div class="display-6 fw-bold"><?=number_format($counts['attendance'])?>
                    </div></div></div></div>
                    <div class="row g-4 mt-1">
                        <div class="col-lg-8"><div class="content-card p-4 h-100">
                            
                        <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="fw-bold mb-0"><?=t('Quick actions','សកម្មភាពរហ័ស')?></h5><span class="badge badge-soft"><?=e(role_label($u['role']))?>
                    </span></div>
                    <div class="row g-3"><div class="col-md-6"><a class="feature-card d-block p-4 h-100" href="<?=url('articles/compose.php')?>">
                        <i class="bi bi-journal-plus fs-3 text-primary"></i>
                    <h6 class="fw-bold mt-3 mb-1"><?=t('Write an article','សរសេរអត្ថបទ')?></h6><p class="small text-muted mb-0"><?=t('Share school news, an activity or a story with photos.','ចែករំលែកព័ត៌មានសាលា សកម្មភាព ឬរឿងរ៉ាវជាមួយរូបថត។')?></p></a>
                </div><?php if(has_role('admin')):?><div class="col-md-6"><a class="feature-card d-block p-4 h-100" href="<?=url('admin/library.php')?>"><i class="bi bi-book-half fs-3 text-primary"></i><h6 class="fw-bold mt-3 mb-1"><?=t('Manage the e-Library','គ្រប់គ្រងបណ្ណាល័យឌីជីថល')?></h6><p class="small text-muted mb-0"><?=t('Upload books, manage authors and categories, and review reading and download statistics.','បង្ហោះសៀវភៅ គ្រប់គ្រងអ្នកនិពន្ធ និងប្រភេទ ព្រមទាំងមើលស្ថិតិការអាន និងទាញយក។')?></p></a></div><?php endif;?><?php if(has_role(['admin','teacher'])):?>
                        <div class="col-md-6"><a class="feature-card d-block p-4 h-100" href="<?=url(has_role('admin')?'admin/content.php':'teacher/content.php')?>">
                            <i class="bi bi-cloud-arrow-up fs-3 text-primary"></i>
                        <h6 class="fw-bold mt-3 mb-1"><?=t('Upload content, photos & videos','បង្ហោះព័ត៌មាន រូបថត និងវីដេអូ')?></h6><p class="small text-muted mb-0"><?=t('Publish majors, events, projects, announcements and videos.','បង្ហោះជំនាញ ព្រឹត្តិការណ៍ គម្រោង ដំណឹង និងវីដេអូ។')?></p></a>
                    </div><div class="col-md-6">
                        <a class="feature-card d-block p-4 h-100" href="<?=url(has_role('admin')?'admin/students.php':'teacher/students.php')?>"><i class="bi bi-mortarboard fs-3 text-primary"></i>
                        <h6 class="fw-bold mt-3 mb-1"><?=t('Manage students','គ្រប់គ្រងសិស្ស')?></h6><p class="small text-muted mb-0"><?=t('Create profiles, assign classes and upload student photos.','បង្កើតប្រវត្តិរូប កំណត់ថ្នាក់ និងបង្ហោះរូបថតសិស្ស។')?></p></a>
                    </div><?php endif;?><?php if(has_role('admin')):?><div class="col-md-6"><a class="feature-card d-block p-4 h-100" href="<?=url('admin/teachers.php')?>"><i class="bi bi-person-badge fs-3 text-primary"></i><h6 class="fw-bold mt-3 mb-1"><?=t('Manage teachers','គ្រប់គ្រងគ្រូ')?></h6><p class="small text-muted mb-0"><?=t('Create teacher profiles and connect them to login accounts.','បង្កើតប្រវត្តិរូបគ្រូ និងភ្ជាប់ទៅគណនីចូលប្រព័ន្ធ។')?></p></a></div><div class="col-md-6"><a class="feature-card d-block p-4 h-100" href="<?=url('admin/settings.php')?>"><i class="bi bi-sliders fs-3 text-primary"></i><h6 class="fw-bold mt-3 mb-1"><?=t('Edit school information','កែព័ត៌មានសាលា')?></h6><p class="small text-muted mb-0"><?=t('Update scholarship, school name, contact and map settings.','កែអាហារូបករណ៍ ឈ្មោះសាលា ទំនាក់ទំនង និងផែនទី។')?></p></a></div><?php endif;?><?php if(has_role('student')):?><div class="col-md-6"><a class="feature-card d-block p-4 h-100" href="<?=url('student/index.php')?>"><i class="bi bi-mortarboard fs-3 text-primary"></i><h6 class="fw-bold mt-3 mb-1"><?=t('My profile','ប្រវត្តិរូបរបស់ខ្ញុំ')?></h6><p class="small text-muted mb-0"><?=t('View your attendance, fees due and guardian on file.','មើលវត្តមាន ថ្លៃសិក្សានៅសល់ និងអាណាព្យាបាលក្នុងកំណត់ត្រា។')?></p></a></div><?php endif;?><?php if(has_role('parent')):?><div class="col-md-6"><a class="feature-card d-block p-4 h-100" href="<?=url('parent/index.php')?>"><i class="bi bi-people fs-3 text-primary"></i><h6 class="fw-bold mt-3 mb-1"><?=t('Parent portal','ប្រព័ន្ធអាណាព្យាបាល')?></h6><p class="small text-muted mb-0"><?=t('Link a child and check their attendance and fee status.','ភ្ជាប់កូន និងពិនិត្យវត្តមាន និងស្ថានភាពថ្លៃសិក្សារបស់ពួកគេ។')?></p></a></div><?php endif;?></div></div></div><div class="col-lg-4"><div class="content-card p-4 h-100"><h5 class="fw-bold"><?=t('Role & security','តួនាទី និងសុវត្ថិភាព')?></h5><div class="vstack gap-3 mt-3 small"><div><i class="bi bi-check-circle text-primary me-2"></i><?=t('Password hashing','ការអ៊ិនគ្រីបពាក្យសម្ងាត់')?></div><div><i class="bi bi-check-circle text-primary me-2"></i><?=t('Session-protected access','ការពារដោយ session')?></div><div><i class="bi bi-check-circle text-primary me-2"></i><?=t('Role-based authorization','សិទ្ធិតាមតួនាទី')?></div><div><i class="bi bi-check-circle text-primary me-2"></i><?=t('CSRF-protected forms','ការពារ CSRF')?></div><div><i class="bi bi-check-circle text-primary me-2"></i><?=t('Prepared SQL statements','Prepared SQL')?></div></div></div></div></div></div></div></div></div>
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu"><div class="offcanvas-header"><h5 class="fw-bold mb-0"><?=t('Portal menu','ម៉ឺនុយប្រព័ន្ធ')?></h5><button class="btn-close" data-bs-dismiss="offcanvas"></button></div><div class="offcanvas-body"><div class="sidebar border-0 p-0 min-vh-0">
    <a href="<?=url('dashboard.php')?>"><i class="bi bi-grid-1x2"></i><?=t('Overview','ទូទៅ')?></a>
    <?php foreach($menuLinks as $m):?><a href="<?=url($m[0])?>"><i class="bi <?=$m[1]?>"></i><?=$m[2]?></a><?php endforeach;?>
    <form action="<?=url('logout.php')?>" method="post" class="m-0">
        <input type="hidden" name="csrf" value="<?=csrf_token()?>">
        <button type="submit" class="sidebar-logout"><i class="bi bi-box-arrow-right"></i><?=t('Sign out','ចាកចេញ')?></button>
    </form>
</div></div></div>
<?php include __DIR__.'/includes/footer.php'; ?>