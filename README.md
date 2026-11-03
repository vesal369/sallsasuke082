# NPIT Smart School Management System

Improved PHP 8 + MySQL + Bootstrap 5 school portal.

## Includes
- EN / Khmer language switch
- Persistent dark mode
- Public NPIT landing site
- Bilingual e-Library with searchable books, categories, featured/popular/recommended collections, PDF reading and downloads
- Admin library management for books, covers/PDFs, authors, categories and reading/download statistics
- 2-year 100% scholarship and enrollment section
- Majors, events, projects, announcements and gallery publishing
- Admin / teacher / student / parent roles
- Student and teacher profiles with photo upload
- Daily attendance
- Fee/invoice tracking
- Admin site settings for official school/contact/map information
- Password hashing, sessions, CSRF protection, prepared SQL statements

## Changelog (this update)
- **Registration is now fully wired up**: signing up as a Student auto-creates a linked
  student profile (with a generated Student ID) instead of leaving accounts unlinked.
  Signing up as a Parent can request a child link by Student ID; an administrator must approve
  the request. Admin/teacher-created student profiles auto-link to a matching
  self-registered account by email.
- **Student & Parent portals** now show real attendance %, fees due and profile info
  instead of a placeholder message.
- **Public gallery carousel**: a Bootstrap carousel-with-indicators section on the
  homepage automatically pulls published photos and videos (including the "Gallery"
  content type, which previously had no public display) from across the site.
- **e-Library**: book, author and category tables are created automatically from
  `database/schema.sql`. Administrators can manage the catalog at **Dashboard →
  e-Library management**. PDF uploads must be valid PDF files no larger than 100 MB;
  ensure PHP and the web server allow uploads of that size. Online reading uses the
  browser's built-in PDF viewer.
- **Glassmorphism**: content/feature/stat/post cards use a frosted-glass style
  (`backdrop-filter`, translucent surfaces) with a solid-color fallback for older
  browsers, in both light and dark mode. Public item grids use flexbox so cards stay
  equal height and reflow responsively.
- **Motion and transitions**: shared CSS and JavaScript add viewport-triggered
  reveals, staggered content, and restrained hover feedback across public, library,
  and portal pages. Animations use transforms and opacity, respect
  `prefers-reduced-motion`, and leave content visible when motion APIs are unavailable.
- **Button design system**: shared Bootstrap button styles add consistent gradient,
  outline, glass, and semantic variants with hover/press feedback, keyboard focus,
  compact and touch-friendly sizing, and loading/disabled states. Existing links,
  forms, and button actions remain unchanged; motion respects reduced-motion settings.
- **Editable profile bio**: users can update a 2,000-character bio in their profile
  settings; it is displayed publicly in place of the previous sample text. New
  installations create the `users.bio` field, and existing installations add it
  through the normal automatic schema compatibility check.
- Fixed a dead/always-false conditional in `teacher/attendance.php` (`'student'==='teacher'`)
  left over from a copy-paste of the admin version.

## Install
1. Copy to Apache/PHP `htdocs`.
2. Create/import `database/schema.sql` into MySQL.
3. Configure DB environment variables, or edit `config/config.php`.
4. If upgrading an older copy of this project, run `database/migrate.php` once from the XAMPP Shell/CLI (the application also attempts compatibility migrations automatically).
5. Set `NPIT_ADMIN_PASSWORD` to a unique password of at least 16 characters, then run `database/seed_admin.php` once from the XAMPP Shell/CLI. For example, in PowerShell: `$env:NPIT_ADMIN_PASSWORD='use-a-unique-password-here'; php .\database\seed_admin.php`.
6. Delete `database/seed_admin.php` after creating the first admin.
7. Ensure `uploads/` and its subfolders are writable by PHP.

The seed script creates/checks `admin@example.com`; it never uses a built-in password.
Existing installations should immediately replace any administrator password that
was previously set to the old documented default.

For production, change that password immediately and configure HTTPS.

## Fix for `Unknown column 'published_at'`

That error happens when an older `content_items` table is still being used. This version automatically adds the missing `published_at` and `status` columns. If your MySQL user cannot ALTER tables automatically, run `php database/migrate.php` from the XAMPP Shell, then reload the admin content page.

## Video publishing

Admins and teachers can publish videos from **Content & photos**. The form supports:
- Direct MP4, WebM, or OGG video uploads up to **4 GB**
- JPG, PNG, or WebP photo uploads up to **100 MB**
- Video preview when editing
- Optional YouTube/Vimeo/video URL
- Public HTML5 video playback

### Large upload server settings
The project includes `.user.ini` and Apache `.htaccess` settings for large uploads. The effective PHP configuration must allow at least:

- `upload_max_filesize = 4G`
- `post_max_size = 0` (unlimited request body; PHP's `0` value is intentional)
- `max_execution_time = 7200`
- `max_input_time = 7200`
- `memory_limit = 512M`
- Enable `fileinfo`, `mbstring`, `gd`, and `pdo_mysql` in XAMPP's active `php.ini`.

Apache must allow request bodies of the required size (`LimitRequestBody 0` means
unlimited; it is the Apache default). A hosting provider, reverse proxy, or web
server can impose additional limits. A 4 GB upload also requires enough disk space
and can take a long time on a slow connection.

### Page Loader
The site now uses the supplied 3x3 square animated loader on page load, same-origin navigation, and form submission.

## Open locally from the Desktop

The Desktop launcher starts the existing XAMPP MariaDB server if it is stopped, then opens this full project folder in VS Code. It does not reinstall or reset the database. MariaDB keeps its files under `C:\xampp\mysql\data`, so the saved school records remain on disk when Windows is shut down.

After opening the project, open `index.php` and click the **PHP Server: Serve project** editor button, or run that command from the Command Palette. The workspace config uses XAMPP PHP (with `pdo_mysql`) and serves on `http://127.0.0.1:8002`.

After restarting Windows, double-click the Desktop launcher again to start MariaDB and reopen the project. Stop the PHP Server extension from the Command Palette when you are finished; closing VS Code also stops its PHP server.
