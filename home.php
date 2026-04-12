<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'db.php';

$username = htmlspecialchars($_SESSION['username']);

// Fetch videos by category
function getVideos($pdo, $dept) {
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE department = ? ORDER BY created_at DESC");
    $stmt->execute([$dept]);
    return $stmt->fetchAll();
}

$sports     = getVideos($pdo, 'Sports');
$esports    = getVideos($pdo, 'Esports');
$tournament = getVideos($pdo, 'Tournament Highlights');

// Fetch all videos for hero (latest)
$hero = $pdo->query("SELECT * FROM videos ORDER BY created_at DESC LIMIT 1")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademiaX — Home</title>
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg: #0a0a0f;
            --surface: #13131f;
            --border: rgba(255,255,255,0.07);
            --accent: #7c6fff;
            --accent2: #ff4d6d;
            --text: #e8e8f0;
            --muted: rgba(255,255,255,0.4);
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── NAVBAR ── */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            padding: 0 48px;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(to bottom, rgba(10,10,15,0.98), transparent);
            backdrop-filter: blur(8px);
        }
        .nav-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .nav-logo span { color: var(--accent); }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }
        .nav-links a {
            color: var(--muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-links a:hover,
        .nav-links a.active { color: #fff; }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .nav-avatar {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
        }
        .nav-user {
            font-size: 0.88rem;
            color: var(--muted);
        }
        .btn-logout {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
            border-radius: 8px;
            padding: 7px 16px;
            font-size: 0.82rem;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: color 0.2s, border-color 0.2s;
        }
        .btn-logout:hover { color: var(--accent2); border-color: var(--accent2); }

        /* ── HERO ── */
        .hero {
            position: relative;
    height: 88vh;
    min-height: 520px;
    display: flex;
    align-items: flex-start;
    padding: 80px 48px 60px;
    overflow: hidden;
        }
        .hero-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                #0d0d2b 0%,
                #1a0a2e 30%,
                #0d1b3e 60%,
                #0a0a0f 100%
            );
        }
        /* Animated grid lines */
        .hero-bg::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(124,111,255,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(124,111,255,0.06) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: gridMove 20s linear infinite;
        }
        @keyframes gridMove {
            0% { transform: translateY(0); }
            100% { transform: translateY(60px); }
        }
        .hero-bg::after {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at 60% 40%, rgba(124,111,255,0.18) 0%, transparent 60%),
                        radial-gradient(ellipse at 20% 80%, rgba(255,77,109,0.12) 0%, transparent 50%);
        }
        .hero-gradient {
            position: absolute; inset: 0;
            background: linear-gradient(to top, var(--bg) 0%, transparent 50%),
                        linear-gradient(to right, var(--bg) 0%, transparent 40%);
        }

        /* Floating orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.2;
            animation: float 8s ease-in-out infinite;
        }
        .orb-1 { width: 400px; height: 400px; background: var(--accent); top: 10%; right: 15%; animation-delay: 0s; }
        .orb-2 { width: 300px; height: 300px; background: var(--accent2); top: 40%; right: 30%; animation-delay: 3s; }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-30px); }
        }

        .hero-content {
           position: relative;
    z-index: 2;
    width: 100%;
    max-width: 100%;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(124,111,255,0.15);
            border: 1px solid rgba(124,111,255,0.3);
            color: var(--accent);
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 18px;
        }
        .hero-title {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 14px;
            letter-spacing: -1px;
        }
        .hero-title span { color: var(--accent); }
        .hero-desc {
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 28px;
            max-width: 480px;
        }
        .hero-btns { display: flex; gap: 14px; align-items: center; }
        .btn-play {
            display: flex;
            align-items: center;
            gap: 9px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 13px 28px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            text-decoration: none;
        }
        .btn-play:hover { background: #6a5eee; transform: translateY(-2px); }
        .btn-outline {
            display: flex;
            align-items: center;
            gap: 9px;
            background: rgba(255,255,255,0.08);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 13px 28px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
        }
        .btn-outline:hover { background: rgba(255,255,255,0.14); }

        /* ── CONTENT ── */
        .content { padding: 0 48px 80px; }

        /* Section row */
        .section { margin-bottom: 52px; }
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 700;
        }
        .section-icon {
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .icon-sports    { background: rgba(255,165,0,0.15); color: #ffa500; }
        .icon-esports   { background: rgba(124,111,255,0.15); color: var(--accent); }
        .icon-tournament { background: rgba(255,77,109,0.15); color: var(--accent2); }

        .see-all {
            color: var(--accent);
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: gap 0.2s;
        }
        .see-all:hover { gap: 8px; }

        /* Scrollable row */
        .video-row {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            padding-bottom: 12px;
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: var(--accent) transparent;
        }
        .video-row::-webkit-scrollbar { height: 4px; }
        .video-row::-webkit-scrollbar-track { background: transparent; }
        .video-row::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 4px; }

        /* Video card */
        .video-card {
            flex: 0 0 280px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.25s, border-color 0.25s, box-shadow 0.25s;
            text-decoration: none;
            color: inherit;
        }
        .video-card:hover {
            transform: translateY(-6px) scale(1.02);
            border-color: var(--accent);
            box-shadow: 0 20px 40px rgba(124,111,255,0.15);
        }
        .video-thumb {
            width: 100%;
            aspect-ratio: 16/9;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .video-thumb video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.7;
        }
        .thumb-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 60%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .play-circle {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            color: #fff;
            transition: background 0.2s, transform 0.2s;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .video-card:hover .play-circle {
            background: var(--accent);
            transform: scale(1.1);
            border-color: var(--accent);
        }

        /* Thumbnail gradient backgrounds per category */
        .thumb-sports    { background: linear-gradient(135deg, #1a0a00, #3d1c00) !important; }
        .thumb-esports   { background: linear-gradient(135deg, #0d0d2b, #1a0a2e) !important; }
        .thumb-tournament { background: linear-gradient(135deg, #1a0010, #2e0020) !important; }

        .thumb-pattern {
            position: absolute; inset: 0;
            opacity: 0.08;
            background-image: repeating-linear-gradient(
                45deg,
                #fff 0, #fff 1px,
                transparent 0, transparent 50%
            );
            background-size: 20px 20px;
        }

        .video-info { padding: 14px 16px; }
        .video-title {
            font-size: 0.92rem;
            font-weight: 600;
            margin-bottom: 8px;
            line-height: 1.35;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .video-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            color: var(--muted);
        }
        .meta-dot { width: 3px; height: 3px; background: var(--muted); border-radius: 50%; }

        /* Empty state */
        .empty-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 32px 24px;
            background: var(--surface);
            border: 1px dashed var(--border);
            border-radius: 14px;
            color: var(--muted);
            font-size: 0.9rem;
        }
        .empty-row i { font-size: 1.8rem; opacity: 0.4; }

        /* Modal overlay */
        .modal-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.88);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(6px);
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            width: min(820px, 95vw);
            overflow: hidden;
            position: relative;
        }
        .modal video {
            width: 100%;
            display: block;
            max-height: 65vh;
            background: #000;
        }
        .modal-info { padding: 20px 24px; }
        .modal-title { font-size: 1.1rem; font-weight: 700; margin-bottom: 6px; }
        .modal-dept { color: var(--accent); font-size: 0.82rem; }
        .modal-close {
            position: absolute;
            top: 14px; right: 14px;
            width: 36px; height: 36px;
            background: rgba(0,0,0,0.5);
            border: none;
            border-radius: 50%;
            color: #fff;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s;
        }
        .modal-close:hover { background: var(--accent2); }

        /* Welcome banner */
        .welcome-bar {
            background: linear-gradient(135deg, rgba(124,111,255,0.12), rgba(255,77,109,0.08));
            border: 1px solid rgba(124,111,255,0.2);
            border-radius: 14px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .welcome-text { font-size: 0.95rem; }
        .welcome-text span { color: var(--accent); font-weight: 600; }

        @media (max-width: 700px) {
            nav, .hero, .content { padding-left: 20px; padding-right: 20px; }
            .hero { height: auto; padding-top: 100px; padding-bottom: 40px; }
            .video-card { flex: 0 0 220px; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav>
    <a href="home.php" class="nav-logo">Academia<span>X</span></a>
    <ul class="nav-links">
        <li><a href="#sports" class="active">Sports</a></li>
        <li><a href="#esports">Esports</a></li>
        <li><a href="#tournament">Tournament</a></li>
    </ul>
    <div class="nav-right">
        <span class="nav-user">Hi, <?= explode(' ', $username)[0] ?></span>
        <div class="nav-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<!-- Hero -->
<div class="hero">
    <div class="hero-bg">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>
    <div class="hero-gradient"></div>
    <div class="hero-content">
        <div class="hero-badge"><i class='bx bx-trending-up'></i> Now Streaming</div>
        <?php if ($hero): ?>
        <video  autoplay muted controls style="width:100%; border-radius:16px; margin-bottom:20px; max-height:340px; object-fit:cover; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
            <source src="uploads/<?= htmlspecialchars($hero['filename']) ?>" type="video/mp4">
        </video>
        <h1 class="hero-title"><?= htmlspecialchars($hero['title']) ?></h1>
        <p class="hero-desc"><?= htmlspecialchars($hero['description']) ?></p>
        <?php else: ?>
        <h1 class="hero-title">
            College's<br>
            <span>Sports & Esports</span><br>
            Hub
        </h1>
        <p class="hero-desc">
            Watch live tournaments, match highlights, esports battles and college sports events — all in one place.
        </p>
        <?php endif; ?>
        <div class="hero-btns">
            <a href="#sports" class="btn-play"><i class='bx bx-play'></i> Start Watching</a>
            <a href="#tournament" class="btn-outline"><i class='bx bx-trophy'></i> Tournaments</a>
        </div>
    </div>
</div>
<!-- Content -->
<div class="content">

    <!-- Welcome bar -->
    <div class="welcome-bar">
        <span class="welcome-text">Welcome back, <span><?= $username ?></span> 👋 Ready to watch something?</span>
        <i class='bx bx-joystick' style="font-size:1.5rem; color:var(--accent); opacity:0.6;"></i>
    </div>

    <!-- SPORTS -->
    <div class="section" id="sports">
        <div class="section-header">
            <div class="section-title">
                <div class="section-icon icon-sports"><i class='bx bx-football'></i></div>
                Sports
            </div>
            <a href="#" class="see-all">See all <i class='bx bx-chevron-right'></i></a>
        </div>
        <div class="video-row">
            <?php if (count($sports) > 0): ?>
                <?php foreach ($sports as $v): ?>
                <div class="video-card" onclick="openModal('<?= htmlspecialchars($v['filename']) ?>', '<?= htmlspecialchars($v['title']) ?>', '<?= htmlspecialchars($v['department']) ?>')">
                    <div class="video-thumb thumb-sports">
                        <div class="thumb-pattern"></div>
                        <div class="thumb-overlay">
                            <div class="play-circle"><i class='bx bx-play'></i></div>
                        </div>
                    </div>
                    <div class="video-info">
                        <div class="video-title"><?= htmlspecialchars($v['title']) ?></div>
                        <div class="video-meta">
                            <i class='bx bx-football'></i> Sports
                            <span class="meta-dot"></span>
                            <?= date('M j', strtotime($v['created_at'])) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-row">
                    <i class='bx bx-football'></i>
                    <span>No sports videos yet — admin will upload soon!</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ESPORTS -->
    <div class="section" id="esports">
        <div class="section-header">
            <div class="section-title">
                <div class="section-icon icon-esports"><i class='bx bx-joystick'></i></div>
                Esports
            </div>
            <a href="#" class="see-all">See all <i class='bx bx-chevron-right'></i></a>
        </div>
        <div class="video-row">
            <?php if (count($esports) > 0): ?>
                <?php foreach ($esports as $v): ?>
                <div class="video-card" onclick="openModal('<?= htmlspecialchars($v['filename']) ?>', '<?= htmlspecialchars($v['title']) ?>', '<?= htmlspecialchars($v['department']) ?>')">
                    <div class="video-thumb thumb-esports">
                        <div class="thumb-pattern"></div>
                        <div class="thumb-overlay">
                            <div class="play-circle"><i class='bx bx-play'></i></div>
                        </div>
                    </div>
                    <div class="video-info">
                        <div class="video-title"><?= htmlspecialchars($v['title']) ?></div>
                        <div class="video-meta">
                            <i class='bx bx-joystick'></i> Esports
                            <span class="meta-dot"></span>
                            <?= date('M j', strtotime($v['created_at'])) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-row">
                    <i class='bx bx-joystick'></i>
                    <span>No esports videos yet — admin will upload soon!</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- TOURNAMENT -->
    <div class="section" id="tournament">
        <div class="section-header">
            <div class="section-title">
                <div class="section-icon icon-tournament"><i class='bx bx-trophy'></i></div>
                Tournament Highlights
            </div>
            <a href="#" class="see-all">See all <i class='bx bx-chevron-right'></i></a>
        </div>
        <div class="video-row">
            <?php if (count($tournament) > 0): ?>
                <?php foreach ($tournament as $v): ?>
                <div class="video-card" onclick="openModal('<?= htmlspecialchars($v['filename']) ?>', '<?= htmlspecialchars($v['title']) ?>', '<?= htmlspecialchars($v['department']) ?>')">
                    <div class="video-thumb thumb-tournament">
                        <div class="thumb-pattern"></div>
                        <div class="thumb-overlay">
                            <div class="play-circle"><i class='bx bx-play'></i></div>
                        </div>
                    </div>
                    <div class="video-info">
                        <div class="video-title"><?= htmlspecialchars($v['title']) ?></div>
                        <div class="video-meta">
                            <i class='bx bx-trophy'></i> Tournament
                            <span class="meta-dot"></span>
                            <?= date('M j', strtotime($v['created_at'])) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-row">
                    <i class='bx bx-trophy'></i>
                    <span>No tournament videos yet — admin will upload soon!</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Video Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
    <div class="modal">
        <button class="modal-close" onclick="closeModalDirect()"><i class='bx bx-x'></i></button>
        <video id="modalVideo" controls></video>
        <div class="modal-info">
            <div class="modal-title" id="modalTitle"></div>
            <div class="modal-dept" id="modalDept"></div>
        </div>
    </div>
</div>

<script>
function openModal(filename, title, dept) {
    document.getElementById('modalVideo').src = 'uploads/' + filename;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalDept').textContent = dept;
    document.getElementById('modalOverlay').classList.add('open');
}
function closeModal(e) {
    if (e.target === document.getElementById('modalOverlay')) closeModalDirect();
}
function closeModalDirect() {
    const v = document.getElementById('modalVideo');
    v.pause();
    v.src = '';
    document.getElementById('modalOverlay').classList.remove('open');
}
</script>

</body>
</html>