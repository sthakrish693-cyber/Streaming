<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin.php");
    exit();
}

require 'db.php';

$message = "";
$error   = "";

$departments = [
    'CSIT',
    'BCA',
    'BBS',
    'BBA',
    'Business Administration',
    
    'Arts & Humanities',
    'Other',
];

// Handle video upload
if (isset($_POST['upload_video'])) {
    $title      = trim($_POST['title']);
    $desc       = trim($_POST['description']);
    $department = trim($_POST['department']);
    $file       = $_FILES['video'];

    $allowed = ['mp4', 'mkv', 'avi', 'mov', 'webm'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $error = "Only MP4, MKV, AVI, MOV, WEBM files allowed.";
    } elseif ($file['size'] > 500 * 1024 * 1024) {
        $error = "File too large. Max 500MB.";
    } else {
        $filename = uniqid('vid_') . '.' . $ext;
        $dest     = 'uploads/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $stmt = $pdo->prepare("INSERT INTO videos (title, description, filename, department, uploaded_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $desc, $filename, $department, $_SESSION['user_id']]);
            $message = "Video uploaded successfully!";
        } else {
            $error = "Upload failed. Make sure the 'uploads' folder exists.";
        }
    }
}

// Handle delete student
if (isset($_POST['delete_student'])) {
    $id = (int)$_POST['student_id'];
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'")->execute([$id]);
    $message = "Student deleted.";
}

// Handle delete video
if (isset($_POST['delete_video'])) {
    $id    = (int)$_POST['video_id'];
    $video = $pdo->prepare("SELECT filename FROM videos WHERE id = ?");
    $video->execute([$id]);
    $v = $video->fetch();
    if ($v) {
        @unlink('uploads/' . $v['filename']);
        $pdo->prepare("DELETE FROM videos WHERE id = ?")->execute([$id]);
        $message = "Video deleted.";
    }
}

// Fetch data
$students      = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC")->fetchAll();
$videos        = $pdo->query("SELECT v.*, u.name as uploader FROM videos v JOIN users u ON v.uploaded_by = u.id ORDER BY v.created_at DESC")->fetchAll();
$total_videos  = count($videos);
$total_students = count($students);

// Videos by department
$dept_counts = [];
foreach ($videos as $v) {
    $dept_counts[$v['department']] = ($dept_counts[$v['department']] ?? 0) + 1;
}

$active_tab = $_GET['tab'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AcademiaX</title>
    <link href="https://cdn.boxicons.com/3.0.8/fonts/basic/boxicons.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background: #0d0d1a;
            color: #fff;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 240px;
            height: 100vh;
            background: #13132b;
            border-right: 1px solid rgba(255,255,255,0.08);
            display: flex;
            flex-direction: column;
            padding: 30px 0;
            z-index: 100;
        }
        .sidebar-logo {
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 40px;
            color: #fff;
        }
        .sidebar-logo span { color: #7c6fff; }

        .sidebar-menu { list-style: none; flex: 1; }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 28px;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            font-size: 0.92rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            color: #fff;
            background: rgba(124,111,255,0.1);
            border-left-color: #7c6fff;
        }
        .sidebar-menu li a i { font-size: 1.2rem; }

        .sidebar-bottom {
            padding: 20px 28px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-bottom a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        .sidebar-bottom a:hover { color: #ff6b6b; }

        /* Main */
        .main {
            margin-left: 240px;
            padding: 36px 40px;
            min-height: 100vh;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .page-sub {
            color: rgba(255,255,255,0.4);
            font-size: 0.88rem;
            margin-bottom: 32px;
        }

        /* Alerts */
        .alert {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.3); color: #4ade80; }
        .alert-error   { background: rgba(255,107,107,0.1); border: 1px solid rgba(255,107,107,0.3); color: #ff6b6b; }

        /* Stat cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 36px;
        }
        .stat-card {
            background: #13132b;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 24px;
        }
        .stat-label {
            color: rgba(255,255,255,0.45);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #7c6fff;
        }

        /* Dept bars */
        .dept-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
            margin-bottom: 36px;
        }
        .dept-card {
            background: #13132b;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 20px 24px;
        }
        .dept-name { font-size: 0.85rem; margin-bottom: 10px; color: rgba(255,255,255,0.7); }
        .dept-bar-wrap { background: rgba(255,255,255,0.08); border-radius: 4px; height: 6px; }
        .dept-bar { background: #7c6fff; height: 6px; border-radius: 4px; transition: width 0.5s; }
        .dept-count { font-size: 0.8rem; color: #7c6fff; margin-top: 6px; }

        /* Upload form */
        .card {
            background: #13132b;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 28px;
        }
        .card h2 { font-size: 1.1rem; margin-bottom: 20px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 7px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { font-size: 0.8rem; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 0.4px; }
        .form-group input,
        .form-group textarea,
        .form-group select {
            background: #0d0d1a;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 8px;
            color: #fff;
            font-size: 0.9rem;
            padding: 10px 14px;
            outline: none;
            transition: border-color 0.2s;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus { border-color: #7c6fff; }
        .form-group select option { background: #13132b; }
        .form-group textarea { resize: vertical; min-height: 80px; }

        .btn-primary {
            background: #7c6fff;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px 24px;
            font-size: 0.92rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            margin-top: 8px;
        }
        .btn-primary:hover { background: #6a5eee; transform: translateY(-1px); }

        /* Table */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
        th {
            text-align: left;
            padding: 11px 16px;
            color: rgba(255,255,255,0.4);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        td {
            padding: 13px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.8);
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.02); }

        .badge {
            background: rgba(124,111,255,0.15);
            color: #7c6fff;
            border-radius: 4px;
            padding: 3px 8px;
            font-size: 0.75rem;
        }
        .btn-delete {
            background: rgba(255,107,107,0.12);
            color: #ff6b6b;
            border: 1px solid rgba(255,107,107,0.25);
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-delete:hover { background: rgba(255,107,107,0.25); }

        .empty { text-align: center; padding: 40px; color: rgba(255,255,255,0.3); font-size: 0.9rem; }

        /* Hide/show tabs */
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-logo">Academia<span>X</span></div>
    <ul class="sidebar-menu">
        <li><a href="?tab=overview" class="<?= $active_tab === 'overview' ? 'active' : '' ?>"><i class='bx bx-home'></i> Overview</a></li>
        <li><a href="?tab=upload" class="<?= $active_tab === 'upload' ? 'active' : '' ?>"><i class='bx bx-upload'></i> Upload Video</a></li>
        <li><a href="?tab=videos" class="<?= $active_tab === 'videos' ? 'active' : '' ?>"><i class='bx bx-video'></i> Manage Videos</a></li>
        <li><a href="?tab=students" class="<?= $active_tab === 'students' ? 'active' : '' ?>"><i class='bx bx-group'></i> Students</a></li>
    </ul>
    <div class="sidebar-bottom">
        <a href="logout.php"><i class='bx bx-log-out'></i> Logout</a>
    </div>
</div>

<!-- Main Content -->
<div class="main">

    <?php if ($message): ?>
        <div class="alert alert-success"><i class='bx bx-check-circle'></i><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><i class='bx bx-error-circle'></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- OVERVIEW TAB -->
    <div class="tab-content <?= $active_tab === 'overview' ? 'active' : '' ?>">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</p>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Videos</div>
                <div class="stat-value"><?= $total_videos ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Students</div>
                <div class="stat-value"><?= $total_students ?></div>
            </div>
        </div>

        <h2 style="font-size:1rem; margin-bottom:16px; color:rgba(255,255,255,0.6);">Videos by Department</h2>
        <?php if (count($dept_counts) > 0): ?>
        <div class="dept-grid">
            <?php
            $max = max($dept_counts);
            foreach ($dept_counts as $dept => $count):
                $pct = $max > 0 ? round(($count / $max) * 100) : 0;
            ?>
            <div class="dept-card">
                <div class="dept-name"><?= htmlspecialchars($dept) ?></div>
                <div class="dept-bar-wrap">
                    <div class="dept-bar" style="width:<?= $pct ?>%"></div>
                </div>
                <div class="dept-count"><?= $count ?> video<?= $count > 1 ? 's' : '' ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p style="color:rgba(255,255,255,0.3); font-size:0.9rem;">No videos uploaded yet.</p>
        <?php endif; ?>
    </div>

    <!-- UPLOAD TAB -->
    <div class="tab-content <?= $active_tab === 'upload' ? 'active' : '' ?>">
        <h1 class="page-title">Upload Video</h1>
        <p class="page-sub">Add a new lecture to the platform</p>

        <div class="card">
            <h2>Video Details</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Video Title</label>
                        <input type="text" name="title" placeholder="e.g. Introduction to Algorithms" required>
                    </div>
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department" required>
                            <option value="" disabled selected>Select department</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d ?>"><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Description</label>
                        <textarea name="description" placeholder="Brief description of the lecture..."></textarea>
                    </div>
                    <div class="form-group full">
                        <label>Video File (MP4, MKV, AVI, MOV — Max 500MB)</label>
                        <input type="file" name="video" accept="video/*" required>
                    </div>
                </div>
                <button type="submit" name="upload_video" class="btn-primary">
                    <i class='bx bx-upload'></i> Upload Video
                </button>
            </form>
        </div>
    </div>

    <!-- VIDEOS TAB -->
    <div class="tab-content <?= $active_tab === 'videos' ? 'active' : '' ?>">
        <h1 class="page-title">Manage Videos</h1>
        <p class="page-sub"><?= $total_videos ?> video<?= $total_videos !== 1 ? 's' : '' ?> uploaded</p>

        <div class="card">
            <div class="table-wrap">
                <?php if (count($videos) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Department</th>
                            <th>Uploaded</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($videos as $i => $v): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($v['title']) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($v['department']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($v['created_at'])) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Delete this video?')">
                                    <input type="hidden" name="video_id" value="<?= $v['id'] ?>">
                                    <button type="submit" name="delete_video" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty">No videos uploaded yet. Go to Upload Video to add one!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- STUDENTS TAB -->
    <div class="tab-content <?= $active_tab === 'students' ? 'active' : '' ?>">
        <h1 class="page-title">Students</h1>
        <p class="page-sub"><?= $total_students ?> student<?= $total_students !== 1 ? 's' : '' ?> registered</p>

        <div class="card">
            <div class="table-wrap">
                <?php if (count($students) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Student ID</th>
                            <th>Department</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $i => $s): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= htmlspecialchars($s['student_id']) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($s['department']) ?></span></td>
                            <td><?= date('M j, Y', strtotime($s['created_at'])) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Delete this student?')">
                                    <input type="hidden" name="student_id" value="<?= $s['id'] ?>">
                                    <button type="submit" name="delete_student" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty">No students registered yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
</body>
</html>