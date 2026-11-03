<?php require_once __DIR__.'/config/config.php'; $me=auth_user(); if($me) require_login();
$id=(int)($_GET['id'] ?? ($me['id'] ?? 0));
if(!$id){ redirect('login.php'); }
$isOwn = $me && (int)$me['id']===$id;
$error=null;
if($isOwn && is_post()){
    verify_csrf();
    $form=$_POST['form']??'account';
    try{
        if($form==='add_link'){
            $label=trim($_POST['label']??''); $linkUrl=trim($_POST['url']??'');
            if($label==='' || $linkUrl==='') throw new RuntimeException(t('Give the link a label and a URL.','សូមដាក់ឈ្មោះ និង URL សម្រាប់តំណ។'));
            if(!preg_match('~^https?://~i',$linkUrl)) $linkUrl='https://'.$linkUrl;
            if(!filter_var($linkUrl,FILTER_VALIDATE_URL)) throw new RuntimeException(t('That does not look like a valid URL.','URL នេះមិនត្រឹមត្រូវទេ។'));
            $countSt=$pdo->prepare('SELECT COUNT(*) FROM user_links WHERE user_id=?'); $countSt->execute([$id]);
            $linkCount=(int)$countSt->fetchColumn();
            if($linkCount>=6) throw new RuntimeException(t('You can add up to 6 linked accounts.','អ្នកអាចបន្ថែមគណនីភ្ជាប់បានយ៉ាងច្រើន ៦។'));
            $pdo->prepare('INSERT INTO user_links(user_id,label,url,position) VALUES(?,?,?,?)')->execute([$id,text_substr($label,0,60),$linkUrl,$linkCount]);
            flash('success', t('Link added.','បានបន្ថែមតំណ។'));
        } elseif($form==='remove_link'){
            $pdo->prepare('DELETE FROM user_links WHERE id=? AND user_id=?')->execute([(int)($_POST['link_id']??0),$id]);
            flash('success', t('Link removed.','បានលុបតំណ។'));
        } else {
            $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??'');
            $bio=trim($_POST['bio']??'');
            if($name==='') throw new RuntimeException(t('Name is required.','ត្រូវបញ្ចូលឈ្មោះ។'));
            if(text_length($bio)>2000) throw new RuntimeException(t('Bio must be 2,000 characters or fewer.','ប្រវត្តិណែនាំខ្លួនត្រូវមាន ២,០០០ តួអក្សរ ឬតិចជាងនេះ។'));
            $newPw=$_POST['new_password']??'';
            if($newPw!==''){
                if(strlen($newPw)<8) throw new RuntimeException(t('New password must be at least 8 characters.','ពាក្យសម្ងាត់ថ្មីត្រូវមានយ៉ាងតិច ៨ តួអក្សរ។'));
                if(($_POST['new_password_confirmation']??'')!==$newPw) throw new RuntimeException(t('New passwords do not match.','ពាក្យសម្ងាត់ថ្មីមិនដូចគ្នាទេ។'));
                $cur=$pdo->prepare('SELECT password_hash FROM users WHERE id=?'); $cur->execute([$id]); $hash=$cur->fetchColumn();
                if(!password_verify($_POST['current_password']??'', (string)$hash)) throw new RuntimeException(t('Current password is incorrect.','ពាក្យសម្ងាត់បច្ចុប្បន្នមិនត្រឹមត្រូវ។'));
            }
            $photo=upload_image('photo','avatars');
            $oldPhoto=null;
            if($photo){
                $old=$pdo->prepare('SELECT photo FROM users WHERE id=?'); $old->execute([$id]); $oldPhoto=$old->fetchColumn();
                $pdo->prepare('UPDATE users SET name=?,phone=?,bio=?,photo=? WHERE id=?')->execute([$name,$phone,$bio,$photo,$id]);
            } else {
                $pdo->prepare('UPDATE users SET name=?,phone=?,bio=? WHERE id=?')->execute([$name,$phone,$bio,$id]);
            }
            if($newPw!==''){
                $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($newPw,PASSWORD_DEFAULT),$id]);
            }
            if($oldPhoto) delete_file($oldPhoto);
            $_SESSION['user']['name']=$name; $_SESSION['user']['phone']=$phone; $_SESSION['user']['bio']=$bio;
            flash('success', t('Profile updated.','បានកែប្រែប្រវត្តិរូប។'));
        }
        redirect('profile.php'.($id!==(int)($me['id']??0)?'?id='.$id:''));
    }catch(Throwable $e){ $error=user_facing_error($e,'profile update'); }
}
$st=$pdo->prepare('SELECT id,name,email,phone,role,photo,bio,created_at FROM users WHERE id=?'); $st->execute([$id]); $profileUser=$st->fetch();
if(!$profileUser){ flash('danger', t('User not found.','រកអ្នកប្រើមិនឃើញ។')); redirect('articles/index.php'); }
$articles=db_ready()?(function() use ($pdo,$id){ $st=$pdo->prepare("SELECT * FROM articles WHERE user_id=? AND status='published' ORDER BY published_at DESC"); $st->execute([$id]); return $st->fetchAll(); })():[];
$links=db_ready()?(function() use ($pdo,$id){ $st=$pdo->prepare('SELECT * FROM user_links WHERE user_id=? ORDER BY position,id'); $st->execute([$id]); return $st->fetchAll(); })():[];
$page_title=$profileUser['name']; include __DIR__.'/includes/header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center"><div class="col-lg-7">
        <div class="content-card p-4 mb-4">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <?php if($profileUser['photo']):?><img class="mini-avatar" style="width:72px;height:72px" src="<?=e(url($profileUser['photo']))?>" alt="" loading="lazy" decoding="async"><?php else:?><div class="mini-avatar d-flex align-items-center justify-content-center" style="width:72px;height:72px"><i class="bi bi-person fs-3"></i></div><?php endif;?>
                <div>
                    <h1 class="h4 fw-bold mb-0"><?=e($profileUser['name'])?></h1>
                    <div class="small text-muted"><?=e(role_label($profileUser['role']))?> · <?=t('Joined','ចូលរួមតាំងពី')?> <?=date('M Y', strtotime($profileUser['created_at']))?></div>
                </div>
            </div>
            <?php if($links):?><div class="d-flex flex-wrap gap-2 mt-3">
                <?php foreach($links as $l):?><a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener nofollow" href="<?=e($l['url'])?>"><i class="bi bi-link-45deg me-1"></i><?=e($l['label'])?></a><?php endforeach;?>
            </div><?php endif; ?>
        </div>
        <section class="profile-about-card mb-4" aria-labelledby="profile-about-title">
            <?php if($profileUser['photo']):?>
                <img class="profile-about-avatar" src="<?=e(url($profileUser['photo']))?>" alt="<?=e($profileUser['name'])?>" loading="lazy" decoding="async">
            <?php else:?>
                <div class="profile-about-avatar profile-about-avatar-placeholder" aria-hidden="true"><i class="bi bi-person"></i></div>
            <?php endif;?>
            <h2 id="profile-about-title"><?=e($profileUser['name'])?></h2>
            <div class="profile-about-label"><?=t('About Me','អំពីខ្ញុំ')?></div>
            <p><?=trim((string)($profileUser['bio']??''))!==''?nl2br(e($profileUser['bio'])):e(t('This user has not added a bio yet.','អ្នកប្រើប្រាស់នេះមិនទាន់បានបន្ថែមប្រវត្តិណែនាំខ្លួននៅឡើយទេ។'))?></p>
        </section>
        <?php if($isOwn):?>
        <div class="content-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><?=t('Account settings','ការកំណត់គណនី')?></h5>
            <?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?=csrf_token()?>"><input type="hidden" name="form" value="account">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label fw-semibold"><?=t('Full name','ឈ្មោះពេញ')?></label><input class="form-control" name="name" value="<?=e($profileUser['name'])?>" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold"><?=t('Phone','លេខទូរស័ព្ទ')?></label><input class="form-control" name="phone" value="<?=e($profileUser['phone']??'')?>"></div>
                    <div class="col-12"><label class="form-label fw-semibold" for="profileBio"><?=t('Bio','ប្រវត្តិណែនាំខ្លួន')?></label><textarea class="form-control" id="profileBio" name="bio" rows="4" maxlength="2000" aria-describedby="profileBioHelp"><?=e($profileUser['bio']??'')?></textarea><div class="form-text" id="profileBioHelp"><?=t('Write a short introduction about yourself. It will be shown publicly on your profile. Maximum 2,000 characters.','សរសេរការណែនាំខ្លីៗអំពីខ្លួនអ្នក។ វានឹងបង្ហាញជាសាធារណៈនៅលើប្រវត្តិរូបរបស់អ្នក។ អតិបរមា ២,០០០ តួអក្សរ។')?></div></div>
                    <div class="col-12"><label class="form-label fw-semibold"><?=t('Profile photo','រូបថតប្រវត្តិរូប')?></label><input class="form-control" type="file" name="photo" accept="image/jpeg,image/png,image/webp"><div class="form-text"><?=t('JPG, PNG or WebP · max 100 MB (optional)','JPG, PNG ឬ WebP · អតិបរមា ១០០ MB (មិនចាំបាច់)')?></div></div>
                </div>
                <hr class="my-4">
                <h6 class="fw-bold"><?=t('Change password (optional)','ប្តូរពាក្យសម្ងាត់ (មិនចាំបាច់)')?></h6>
                <div class="row g-3">
                    <div class="col-md-4"><input class="form-control" type="password" name="current_password" placeholder="<?=t('Current password','ពាក្យសម្ងាត់បច្ចុប្បន្ន')?>"></div>
                    <div class="col-md-4"><input class="form-control" type="password" name="new_password" placeholder="<?=t('New password','ពាក្យសម្ងាត់ថ្មី')?>" minlength="8"></div>
                    <div class="col-md-4"><input class="form-control" type="password" name="new_password_confirmation" placeholder="<?=t('Confirm new password','បញ្ជាក់ពាក្យសម្ងាត់ថ្មី')?>" minlength="8"></div>
                </div>
                <button class="btn btn-primary mt-4"><?=t('Save changes','រក្សាទុកការកែប្រែ')?></button>
            </form>
        </div>
        <div class="content-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><?=t('Linked accounts','គណនីភ្ជាប់')?></h5>
            <p class="text-muted small"><?=t('Add links to your other accounts (Facebook, Telegram, YouTube, personal website...). These show publicly on your profile.','បន្ថែមតំណទៅគណនីផ្សេងទៀតរបស់អ្នក (Facebook, Telegram, YouTube, គេហទំព័រផ្ទាល់ខ្លួន...)។ វានឹងបង្ហាញជាសាធារណៈនៅលើប្រវត្តិរូបរបស់អ្នក។')?></p>
            <?php if($links):?><div class="vstack gap-2 mb-3">
                <?php foreach($links as $l):?>
                <div class="d-flex align-items-center gap-2">
                    <span class="flex-grow-1"><i class="bi bi-link-45deg me-1"></i><strong><?=e($l['label'])?></strong> — <a href="<?=e($l['url'])?>" target="_blank" rel="noopener nofollow" class="text-truncate"><?=e($l['url'])?></a></span>
                    <form method="post"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><input type="hidden" name="form" value="remove_link"><input type="hidden" name="link_id" value="<?=$l['id']?>"><button class="btn btn-sm btn-outline-danger"><?=t('Remove','លុប')?></button></form>
                </div>
                <?php endforeach; ?>
            </div><?php endif; ?>
            <?php if(count($links)<6):?>
            <form method="post" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="csrf" value="<?=csrf_token()?>"><input type="hidden" name="form" value="add_link">
                <input class="form-control" style="max-width:180px" name="label" maxlength="60" placeholder="<?=t('Label (e.g. Facebook)','ឈ្មោះ (ឧ. Facebook)')?>" required>
                <input class="form-control flex-grow-1" name="url" placeholder="https://..." required>
                <button class="btn btn-primary"><?=t('Add link','បន្ថែមតំណ')?></button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <h5 class="fw-bold mb-3"><?=t('Published articles','អត្ថបទដែលបានផ្សព្វផ្សាយ')?></h5>
        <?php if(!$articles):?>
            <p class="text-muted"><?=t('No published articles yet.','មិនទាន់មានអត្ថបទផ្សព្វផ្សាយនៅឡើយទេ។')?></p>
        <?php else: foreach($articles as $r): $cover=article_cover($pdo,(int)$r['id'],$r['photo']); ?>
            <div class="content-card p-3 mb-3 d-flex gap-3 align-items-center">
                <?php if($cover && $cover['type']==='video'):?><video class="mini-avatar" src="<?=url($cover['path'])?>" muted></video>
                <?php elseif($cover):?><img class="mini-avatar" src="<?=url($cover['path'])?>" alt="">
                <?php else:?><div class="mini-avatar d-flex align-items-center justify-content-center"><i class="bi bi-journal-text"></i></div><?php endif;?>
                <div class="flex-grow-1"><a class="fw-bold text-decoration-none text-reset" href="<?=url('articles/view.php?id='.$r['id'])?>"><?=e($r['title'])?></a><div class="small text-muted"><?=time_ago($r['published_at'])?></div></div>
            </div>
        <?php endforeach; endif; ?>
    </div></div>
</div>
<?php include __DIR__.'/includes/footer.php'; ?>
