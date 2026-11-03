<?php require_once __DIR__.'/config/config.php'; $items=['major'=>[],'event'=>[],'project'=>[],'announcement'=>[],'gallery'=>[]]; if(db_ready()){ $q=$pdo->query("SELECT * FROM content_items WHERE status=1 AND type IN ('major','event','project','announcement','gallery') ORDER BY published_at DESC, created_at DESC"); foreach($q as $r){ if(isset($items[$r['type']])) $items[$r['type']][]=$r; }}
$truncateExcerpt=static fn(string $text,int $width,string $trimMarker='…'): string => text_truncate($text,$width,$trimMarker);
// Gallery carousel: only images/videos published as Gallery-type content items.
$galleryItems=[];
foreach($items['gallery'] as $r){
	if(empty($r['image']) && empty($r['video']) && !video_embed_url($r['link_url']??'')) continue;
	$galleryItems[]=$r;
	if(count($galleryItems)>=10) break;
}
$page_title=t('Home','ទំព័រដើម');
$heroBackground=settings('home_hero_background');
if($heroBackground!=='' && !preg_match('~\Auploads/settings/[a-f0-9]{32}\.(?:jpg|png|webp)\z~D',$heroBackground)){
	$heroBackground='';
} 
# Latest articles: only published articles, with author name and photo.
$latestArticles=db_ready()?$pdo->query("SELECT a.*, u.name author_name, u.photo author_photo FROM articles a JOIN users u ON u.id=a.user_id WHERE a.status='published' ORDER BY a.published_at DESC LIMIT 3")->fetchAll():[];
include __DIR__.'/includes/header.php'; ?>
<style>
	[id] { scroll-margin-top: 90px; }
	.sx-hero ~ .section { content-visibility: auto; contain-intrinsic-size: auto 720px; }
</style>
<?php include __DIR__.'/includes/hero-spline.php'; ?>
<?php if($galleryItems): ?>
<section id="gallery" class="section pt-0 pb-4">
	<div class="container">
		<div class="d-flex justify-content-between align-items-end mb-4">
			<div><div class="kicker"><?=t('Gallery','វិចិត្រសាល')?></div>
			<h2 class="fw-bold mt-2 mb-2"><?=t('Moments captured at NPIT','ភាពទាក់ទាញរបស់ NPIT')?></h2>
			<p class="text-muted mb-0"><?=t('Photos and videos captured for the Gallery, published live by staff.','រូបថត និងវីដេអូដែលថតសម្រាប់វិចិត្រសាល ដែលបានផ្សព្វផ្សាយដោយបុគ្គលិក')?></p></div>
		</div>
		<div id="galleryCarousel" class="carousel slide gallery-carousel" data-bs-ride="carousel">
			<div class="carousel-indicators">
				<?php foreach($galleryItems as $i=>$r): ?>
				<button type="button" data-bs-target="#galleryCarousel" data-bs-slide-to="<?=$i?>" <?=$i===0?'class="active" aria-current="true"':''?> aria-label="<?=t('Slide','ស្លាយ')?> <?=$i+1?>"></button>
				<?php endforeach; ?>
			</div>
			<div class="carousel-inner">
				<?php foreach($galleryItems as $i=>$r): $embed=video_embed_url($r['link_url']??''); ?>
				<div class="carousel-item <?=$i===0?'active':''?>" data-bs-interval="6000">
					<?php if(!empty($r['video'])): ?>
						<video src="<?=url($r['video'])?>" controls preload="none" muted playsinline<?=$r['image']?' poster="'.e(url($r['image'])).'"':''?>></video>
					<?php elseif($embed): ?>
						<div class="ratio ratio-16x9"><iframe class="embed-video" src="<?=e($embed)?>" title="<?=e(content_title($r))?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>
					<?php else: ?>
						<img src="<?=url($r['image'])?>" alt="<?=e(content_title($r))?>" loading="lazy" decoding="async">
					<?php endif; ?>
					<div class="gallery-caption">
						<span class="glass-chip mb-2"><i class="bi bi-images"></i><?=t('Gallery','វិចិត្រសាល')?></span>
						<h5 class="fw-bold mb-1"><?=e(content_title($r))?></h5>
						<p class="mb-0 small opacity-75 d-none d-sm-block"><?=e($truncateExcerpt(content_desc($r),140))?></p>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev" aria-label="<?=e(t('Previous gallery item','រូបភាពវិចិត្រសាលមុន'))?>">
				<span class="carousel-control-prev-icon" aria-hidden="true"></span>
				<span class="visually-hidden"><?=t('Previous','មុន')?></span>
			</button>
			<button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next" aria-label="<?=e(t('Next gallery item','រូបភាពវិចិត្រសាលបន្ទាប់'))?>">
				<span class="carousel-control-next-icon" aria-hidden="true"></span>
				<span class="visually-hidden"><?=t('Next','បន្ទាប់')?></span>
			</button>
		</div>
	</div>
</section>
<?php endif; ?>
<?php if($latestArticles):?>
<section id="newsfeed" class="section pt-4 pb-2">
	<div class="container">
		<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
			<div>
				<div class="kicker"><?=t('Community feed','ព័ត៌មានសហគមន៍')?>
			</div>
				<h2 class="fw-bold mb-0"><?=t('News feed','ព័ត៌មានថ្មី')?></h2>
			</div>
			<a class="btn btn-outline-primary" href="<?=url('articles/index.php')?>"><?=t('See all articles','មើលអត្ថបទទាំងអស់')?></a>
		</div>
		<div class="row g-4">
			<?php foreach($latestArticles as $r): $cover=article_cover($pdo,(int)$r['id'],$r['photo']); ?>
			<div class="col-md-4">
				<a class="content-card d-block h-100 overflow-hidden text-decoration-none text-reset" href="<?=url('articles/view.php?id='.$r['id'])?>">
					<?php if($cover):?><?php if($cover['type']==='video'):?><video src="<?=url($cover['path'])?>" class="feed-photo" style="height:180px" muted preload="none"></video><?php else:?><img src="<?=url($cover['path'])?>" class="feed-photo" style="height:180px" alt="<?=e($r['title'])?>" loading="lazy" decoding="async"><?php endif;?><?php endif;?>
					<div class="p-3">
						<div class="d-flex align-items-center gap-2 mb-2">
							<?php if($r['author_photo']):?><img class="mini-avatar" style="width:28px;height:28px" src="<?=url($r['author_photo'])?>" alt="" loading="lazy" decoding="async"><?php else:?><div class="mini-avatar d-flex align-items-center justify-content-center" style="width:28px;height:28px"><i class="bi bi-person small"></i></div><?php endif;?>
							<span class="small text-muted"><?=e($r['author_name'])?> · <?=time_ago($r['published_at'])?></span>
						</div>
						<h6 class="fw-bold mb-1"><?=e($r['title'])?></h6>
						<p class="small text-muted mb-0"><?=e($truncateExcerpt($r['body'],90))?></p>
					</div>
				</a>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
<section class="section pt-4">
	<div class="container">
	<div class="announcement-bar p-3 d-flex align-items-center gap-3">
		<div class="icon-box flex-shrink-0">
			<i class="bi bi-megaphone"></i></div>
			<div class="small">
				<strong><?=t('Latest announcements','ដំណឹងថ្មីៗ')?>:
			</strong> <?php if($items['announcement']):?><?=e(content_title($items['announcement'][0]))?>
				<?php else:?><?=t('Administrators and teachers can publish announcements here.','អ្នកគ្រប់គ្រង និងគ្រូអាចបង្ហោះដំណឹងនៅទីនេះ')?>
				<?php endif;?>
			</div></div></div>
			</section>
<section id="scholarship" class="section">
	<div class="container">
		<div class="row g-5 align-items-center">
		<div class="col-lg-6">
		<div class="kicker"><?=t('Scholarship & enrollment','អាហារូបករណ៍ និងការចុះឈ្មោះ')?></div>
		<h2 class="display-6 fw-bold mt-2"><?=e(settings(lang()==='kh'?'scholarship_title_kh':'scholarship_title_en',t('2-Year 100% Scholarship','អាហារូបករណ៍ ១០០% រយៈពេល ២ ឆ្នាំ'))) ?></h2>
		<p class="text-muted mt-3"><?=nl2br(e(settings(lang()==='kh'?'scholarship_desc_kh':'scholarship_desc_en','')))?></p><div class="d-flex gap-2 mt-4"><a href="<?=url('register.php')?>" class="btn btn-primary"><?=t('Start enrollment','ចាប់ផ្តើមចុះឈ្មោះ')?></a>
		<a href="#contact" class="btn btn-outline-secondary"><?=t('Ask about eligibility','សួរអំពីលក្ខខណ្ឌ')?></a></div></div><div class="col-lg-6"><div class="row g-3">
			<div class="col-12"><div class="feature-card p-4"><div class="icon-box mb-3"><i class="bi bi-award"></i></div>
		<h5 class="fw-bold"><?=t('100% scholarship','អាហារូបករណ៍ ១០០%')?></h5><p class="text-muted mb-0"><?=t('Use the admin content manager to publish official eligibility, benefits and application steps.','ប្រើកម្មវិធីគ្រប់គ្រងព័ត៌មាន ដើម្បីបង្ហោះលក្ខខណ្ឌ អត្ថប្រយោជន៍ និងជំហានដាក់ពាក្យផ្លូវការ')?></p></div></div><div class="col-md-6"><div class="feature-card p-4"><div class="icon-box mb-3"><i class="bi bi-person-check"></i></div><h6 class="fw-bold"><?=t('Student registration','ការចុះឈ្មោះសិស្ស')?></h6>
		<p class="small text-muted mb-0"><?=t('Student and parent self-registration with secure login.','សិស្ស និងអាណាព្យាបាលអាចចុះឈ្មោះដោយខ្លួនឯង និងចូលប្រព័ន្ធដោយសុវត្ថិភាព')?></p></div></div>
		<div class="col-md-6"><div class="feature-card p-4"><div class="icon-box mb-3"><i class="bi bi-shield-check"></i></div><h6 class="fw-bold"><?=t('Secure roles','សិទ្ធិមានសុវត្ថិភាព')?></h6><p class="small text-muted mb-0"><?=t('Admin, teacher, student and parent access are separated.','សិទ្ធិ Admin, Teacher, Student និង Parent ត្រូវបានបែងចែកដោយសុវត្ថិភាព។')?>
	</p></div></div></div></div></div></div>
	</section>
<?php foreach([['major','majors',t('Majors taught at NPIT','ជំនាញដែលបង្រៀននៅ NPIT'),t('Explore majors and read descriptions of the teaching and training experience.','មើលជំនាញ និងអានការពិពណ៌នាអំពីការបង្រៀន និងការបណ្តុះបណ្តាល')],['event','events',t('NPIT events & highlights','ព្រឹត្តិការណ៍ និងសកម្មភាព NPIT'),t('Showcase important school activities, celebrations and community moments.','បង្ហាញសកម្មភាព សង្គម និងព្រឹត្តិការណ៍សំខាន់ៗរបស់សាលា')],['project','projects',t('Student projects & achievements','គម្រោង និងស្នាដៃសិស្ស'),t('Share student projects, practical training and outstanding achievements.','ចែករំលែកគម្រោង ការអនុវត្ត និងស្នាដៃឆ្នើមរបស់សិស្ស។')]] as [$type,$id,$heading,$sub]): ?>
	<section id="<?=$id?>" class="section pt-0"><div class="container">
		<div class="d-flex justify-content-between align-items-end mb-4"><div>
			<div class="kicker"><?=e(ucfirst($type))?></div><h2 class="fw-bold mt-2 mb-2"><?=$heading?></h2><p class="text-muted mb-0"><?=$sub?></p></div></div><?php if(!$items[$type]):?><div class="empty-state text-center"><div class="icon-box mx-auto mb-3">
			<i class="bi bi-images"></i></div>
			<h5 class="fw-bold"><?=t('No public items yet','មិនទាន់មានព័ត៌មានសាធារណៈ')?></h5>
			<p class="text-muted mb-0"><?=t('Admins and teachers can upload content, descriptions and photos from their dashboards.','Admin និង Teacher អាចបង្ហោះព័ត៌មាន ការពិពណ៌នា និងរូបថតពីផ្ទាំងគ្រប់គ្រង។')?></p></div><?php else:?><div class="row g-4"><?php foreach(array_slice($items[$type],0,6) as $r):?>
				<div class="col-md-6 col-lg-4 d-flex">
					<article class="content-card post-card h-100 w-100 overflow-hidden d-flex flex-column">
						<div class="post-media">
							<?php $embed=video_embed_url($r['link_url']??''); if(!empty($r['video'])):?><video src="<?=url($r['video'])?>" class="thumb" controls preload="none" playsinline></video><?php elseif($embed):?>
								<div class="ratio ratio-16x9">
							<iframe class="thumb embed-video" src="<?=e($embed)?>" title="<?=e(content_title($r))?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div><?php elseif($r['image']):?><img src="<?=url($r['image'])?>" class="thumb" alt="<?=e(content_title($r))?>" loading="lazy" decoding="async"><?php else:?><div class="post-media-empty d-flex align-items-center justify-content-center"><i class="bi bi-image fs-1"></i></div><?php endif;?></div><div class="p-4 d-flex flex-column flex-grow-1"><div class="small text-muted
								 mb-2"><?=e(date('M d, Y',strtotime($r['published_at'] ?: $r['created_at'])))?>
								</div><h5 class="fw-bold post-title"><?=e(content_title($r))?></h5>
								<p class="text-muted mb-0 post-excerpt"><?=nl2br(e(content_desc($r)))?></p><div class="post-actions mt-auto pt-3"><?php if($embed):?><span class="badge text-bg-danger"><i class="bi bi-youtube me-1"></i><?=t('YouTube / video','YouTube / វីដេអូ')?></span><?php elseif(safe_http_url($r['link_url']??null)!==null):?><a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="<?=e(safe_http_url($r['link_url']??null)??'')?>"><?=t('Learn more','អានបន្ថែម')?></a><?php endif;?>
								</div>
							</div>
						</article>
					</div>
						<?php endforeach;?></div><?php endif;?>
						</div>
						</section><?php endforeach; ?>
<section id="contact" class="section pt-0">
	<div class="container">
	<div class="content-card p-4 p-lg-5">
		<div class="row g-4 align-items-center">
			<div class="col-lg-7">
		<div class="kicker"><?=t('Contact & location','ទំនាក់ទំនង និងទីតាំង')?></div>
	<h2 class="fw-bold mt-2"><?=t('Talk to NPIT','ទំនាក់ទំនងជាមួយ NPIT')?></h2>
	<p class="text-muted"><?=t('Your official school address, phone, email and map link can be edited by an administrator.','អាសយដ្ឋាន លេខទូរស័ព្ទ អ៊ីមែល និងតំណផែនទីផ្លូវការអាចកែសម្រួលដោយអ្នកគ្រប់គ្រង')?></p>
	<div class="d-flex flex-wrap gap-2 mt-3"><a class="btn btn-primary" href="mailto:<?=e(settings('contact_email',''))?>">
		<i class="bi bi-envelope me-1"></i><?=t('Email us','ផ្ញើអ៊ីមែល')?></a><?php if(safe_http_url(settings('contact_map_url'))!==null):?><a class="btn btn-outline-secondary" target="_blank" rel="noopener" href="<?=e(safe_http_url(settings('contact_map_url'))??'')?>">
		<i class="bi bi-geo-alt me-1"></i><?=t('Open map','បើកផែនទី')?></a><?php endif;?></div></div><div class="col-lg-5">
			<div class="vstack gap-3">
				<div><div class="small text-muted"><?=t('Phone','លេខទូរស័ព្ទ')?></div><div class="fw-semibold"><?=e(settings('contact_phone'))?></div></div>
				<div><div class="small text-muted">Email</div><div class="fw-semibold"><?=e(settings('contact_email'))?></div></div>
				<div><div class="small text-muted"><?=t('Address','អាសយដ្ឋាន')?></div><div class="fw-semibold">
					<?=e(settings(lang()==='kh'?'contact_address_kh':'contact_address_en'))?></div></div>
			</div>
		</div>
		</div>
	</div>
</div>
</section>
<section class="section pt-0">
	<div class="container">
		<div class="text-center">
			<div class="small text-muted"><?=t('Copyright','រក្សាសិទ្ធិ')?> &copy; <?=date('Y')?> <?=e(settings('school_name','NPIT'))?></div>
		</div>
	</div>
</section>
<?php include __DIR__.'/includes/footer.php'; ?>
