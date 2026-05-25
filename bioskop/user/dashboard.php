<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$films    = $conn->query("SELECT * FROM films WHERE status = 'tayang' ORDER BY id DESC LIMIT 8");
$upcoming = $conn->query("SELECT * FROM films WHERE status = 'akan_tayang' ORDER BY id DESC LIMIT 4");

$user_id     = $_SESSION['user_id'];
$total_tiket = $conn->query("SELECT COUNT(*) as total FROM pemesanan WHERE user_id = $user_id")->fetch_assoc()['total'];

$pageTitle = 'Beranda';
$basePath  = '../';
include '../includes/header.php';
?>

<style>
.hero {
    background: linear-gradient(135deg, #0a0a0a 0%, #1a0505 40%, #0a0a0a 100%);
    padding: 3rem 0;
    border-bottom: 1px solid var(--border);
    position: relative;
    overflow: hidden;
}
.hero::before {
    content: '';
    position: absolute; top: -50%; left: -50%;
    width: 200%; height: 200%;
    background: radial-gradient(ellipse at 60% 50%, rgba(192,57,43,0.08) 0%, transparent 60%);
    pointer-events: none;
}
.hero-content { position: relative; z-index: 1; }
.hero h1 { font-size: 2.5rem; font-weight: 900; line-height: 1.2; margin-bottom: 1rem; }
.hero h1 span { color: var(--primary-light); }
.hero p { font-size: 1rem; color: var(--text-gray); margin-bottom: 2rem; max-width: 500px; }

.section-title {
    font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem;
    display: flex; align-items: center; gap: 10px;
}
.section-title::after {
    content: ''; flex: 1; height: 2px;
    background: linear-gradient(to right, var(--primary), transparent);
}

.film-card {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 12px; overflow: hidden; transition: all 0.3s;
    cursor: pointer; display: flex; flex-direction: column;
}
.film-card:hover {
    border-color: var(--primary); transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(192,57,43,0.3);
}
.film-poster-placeholder {
    position: relative; width: 100%; aspect-ratio: 2 / 3;
    background: linear-gradient(135deg, #1a0505, #2a0a0a);
    overflow: hidden; display: flex; align-items: center;
    justify-content: center; font-size: 3.5rem; flex-shrink: 0;
}
.film-poster-placeholder img {
    position: absolute; inset: 0; width: 100%; height: 100%;
    object-fit: cover; display: block;
}
.film-rating-badge {
    position: absolute; top: 8px; right: 8px;
    background: rgba(0,0,0,0.75); border: 1px solid var(--gold);
    color: var(--gold); padding: 0.15rem 0.5rem; border-radius: 5px;
    font-size: 0.7rem; font-weight: 700; z-index: 1; backdrop-filter: blur(4px);
}
.film-info { padding: 0.8rem; flex: 1; display: flex; flex-direction: column; }
.film-title { font-size: 0.88rem; font-weight: 700; margin-bottom: 0.3rem; }
.film-genre { font-size: 0.75rem; color: var(--text-gray); margin-bottom: 0.6rem; }
.film-meta { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
.film-price { color: var(--primary-light); font-weight: 700; font-size: 0.85rem; }
.film-duration { color: var(--text-gray); font-size: 0.75rem; }

.welcome-card {
    background: linear-gradient(135deg, #1a0505, #0a0a0a);
    border: 1px solid var(--primary); border-radius: 12px;
    padding: 1.5rem; margin-bottom: 2rem;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
}
.welcome-card .welcome-text h3 { font-size: 1.2rem; margin-bottom: 0.3rem; }
.welcome-card .welcome-text p { color: var(--text-gray); font-size: 0.9rem; }

/* Card segera hadir — poster lebih kecil */
.upcoming-film-card .film-poster-placeholder {
    aspect-ratio: 3 / 4;
    max-height: 180px;
}
</style>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="welcome-card">
                <div class="welcome-text">
                    <h3>👋 Selamat datang, <?= htmlspecialchars($_SESSION['user_nama']) ?>!</h3>
                    <p>Temukan film favorit Anda dan pesan tiket sekarang</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="films.php" class="btn btn-primary">
                        <i class="fas fa-film"></i> Lihat Film
                    </a>
                    <a href="my-tickets.php" class="btn btn-outline">
                        <i class="fas fa-ticket-alt"></i> Tiket Saya (<?= $total_tiket ?>)
                    </a>
                </div>
            </div>
            <h1>Nonton Film <span>Favorit</span><br>Kapan Saja, Di Mana Saja</h1>
            <p>Pesan tiket bioskop online dengan mudah dan cepat. Pilih kursi terbaik untuk pengalaman menonton yang tak terlupakan.</p>
            <a href="films.php" class="btn btn-primary btn-lg">
                <i class="fas fa-play-circle"></i> Pesan Tiket Sekarang
            </a>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="section-title">
            <i class="fas fa-fire" style="color:var(--primary-light)"></i> Sedang Tayang
        </h2>
        <div class="grid grid-4">
            <?php while ($film = $films->fetch_assoc()):
                $poster_path = '../assets/uploads/posters/' . ($film['poster'] ?? '');
            ?>
            <div class="film-card" onclick="window.location='film-detail.php?id=<?= $film['id'] ?>'">
                <div class="film-poster-placeholder">
                    <?php if (!empty($film['poster']) && file_exists($poster_path)): ?>
                    <img src="<?= htmlspecialchars($poster_path) ?>" alt="<?= htmlspecialchars($film['judul']) ?>">
                    <?php else: ?>🎬<?php endif; ?>
                    <span class="film-rating-badge">⭐ <?= htmlspecialchars($film['rating']) ?></span>
                </div>
                <div class="film-info">
                    <div class="film-title"><?= htmlspecialchars($film['judul']) ?></div>
                    <div class="film-genre">
                        <i class="fas fa-tag" style="color:var(--primary-light); font-size:0.75rem"></i>
                        <?= htmlspecialchars($film['genre']) ?>
                    </div>
                    <div class="film-meta">
                        <span class="film-price">Rp <?= number_format($film['harga'], 0, ',', '.') ?></span>
                        <span class="film-duration"><i class="fas fa-clock"></i> <?= $film['durasi'] ?> mnt</span>
                    </div>
                    <a href="film-detail.php?id=<?= $film['id'] ?>" class="btn btn-primary btn-block btn-sm mt-2">
                        <i class="fas fa-ticket-alt"></i> Pesan Tiket
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-4">
            <a href="films.php" class="btn btn-outline">
                <i class="fas fa-th-large"></i> Lihat Semua Film
            </a>
        </div>
    </div>
</section>

<?php if ($upcoming->num_rows > 0): ?>
<section class="py-4" style="background: var(--bg-card2); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        <h2 class="section-title">
            <i class="fas fa-calendar-alt" style="color:var(--primary-light)"></i> Segera Hadir
        </h2>
        <div class="grid grid-4">
            <?php while ($film = $upcoming->fetch_assoc()):
                $poster_path = '../assets/uploads/posters/' . ($film['poster'] ?? '');
            ?>
            <div class="film-card upcoming-film-card">
                <div class="film-poster-placeholder">
                    <?php if (!empty($film['poster']) && file_exists($poster_path)): ?>
                    <img src="<?= htmlspecialchars($poster_path) ?>" alt="<?= htmlspecialchars($film['judul']) ?>">
                    <?php else: ?>🎬<?php endif; ?>
                    <span class="film-rating-badge" style="background:rgba(0,0,0,0.75); border-color:var(--primary-light); color:var(--primary-light);">
                        Segera Hadir
                    </span>
                </div>
                <div class="film-info">
                    <div class="film-title"><?= htmlspecialchars($film['judul']) ?></div>
                    <div class="film-genre">
                        <i class="fas fa-tag" style="color:var(--primary-light); font-size:0.75rem"></i>
                        <?= htmlspecialchars($film['genre']) ?>
                    </div>
                    <div class="film-meta" style="margin-top:auto;">
                        <span class="film-duration"><i class="fas fa-clock"></i> <?= $film['durasi'] ?> mnt</span>
                        <span style="font-size:0.8rem; color:var(--text-gray);">⭐ <?= htmlspecialchars($film['rating']) ?></span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
