<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$search = trim($_POST['search'] ?? $_GET['search'] ?? '');
$genre  = trim($_GET['genre'] ?? '');

$where  = "WHERE status = 'tayang'";
$params = [];
$types  = '';

if ($search) { $where .= " AND judul LIKE ?"; $params[] = "%$search%"; $types .= 's'; }
if ($genre)  { $where .= " AND genre LIKE ?"; $params[] = "%$genre%";  $types .= 's'; }

if ($params) {
    $stmt = $conn->prepare("SELECT * FROM films $where ORDER BY id DESC");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $films = $stmt->get_result();
} else {
    $films = $conn->query("SELECT * FROM films $where ORDER BY id DESC");
}

$genres_raw = $conn->query("SELECT DISTINCT genre FROM films WHERE status='tayang'");
$all_genres = [];
while ($g = $genres_raw->fetch_assoc()) {
    foreach (explode(',', $g['genre']) as $gn) {
        $gn = trim($gn);
        if ($gn && !in_array($gn, $all_genres)) $all_genres[] = $gn;
    }
}

$pageTitle = 'Daftar Film';
$basePath  = '../';
include '../includes/header.php';
?>

<style>
.films-hero {
    background: linear-gradient(135deg, #0a0a0a 0%, #1a0505 100%);
    padding: 2.5rem 0; border-bottom: 1px solid var(--border); margin-bottom: 2rem;
}
.search-bar { display: flex; gap: 1rem; max-width: 600px; }
.search-bar .form-control { flex: 1; }
.genre-filters { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; }
.genre-btn {
    padding: 0.3rem 0.9rem; border-radius: 20px; border: 1px solid var(--border);
    background: transparent; color: var(--text-gray); font-size: 0.8rem;
    cursor: pointer; transition: all 0.3s; text-decoration: none;
}
.genre-btn:hover, .genre-btn.active {
    border-color: var(--primary); color: var(--primary-light);
    background: rgba(192,57,43,0.1);
}
.film-card {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 12px; overflow: hidden; transition: all 0.3s; display: flex; flex-direction: column;
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
.film-genre { font-size: 0.75rem; color: var(--text-gray); margin-bottom: 0.5rem; }
.film-sinopsis {
    font-size: 0.78rem; color: var(--text-gray); line-height: 1.5; margin-bottom: 0.6rem;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.film-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; margin-top: auto; }
.film-price { color: var(--primary-light); font-weight: 700; font-size: 0.85rem; }
.film-duration { color: var(--text-gray); font-size: 0.75rem; }
.no-results { text-align: center; padding: 4rem 2rem; color: var(--text-gray); }
.no-results .icon { font-size: 4rem; margin-bottom: 1rem; }
</style>

<div class="films-hero">
    <div class="container">
        <h1 style="font-size:2rem; font-weight:800; margin-bottom:0.5rem">
            <i class="fas fa-film" style="color:var(--primary-light)"></i>
            Daftar <span style="color:var(--primary-light)">Film</span>
        </h1>
        <p style="color:var(--text-gray); margin-bottom:1.5rem">Pilih film favorit Anda dan pesan tiket sekarang</p>
        <form method="GET" action="films.php">
            <div class="search-bar">
                <input type="text" name="search" class="form-control"
                       placeholder="🔍 Cari judul film..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
                <?php if ($search || $genre): ?>
                <a href="films.php" class="btn btn-outline">Reset</a>
                <?php endif; ?>
            </div>
            <div class="genre-filters">
                <a href="films.php" class="genre-btn <?= !$genre ? 'active' : '' ?>">Semua</a>
                <?php foreach ($all_genres as $g): ?>
                <a href="films.php?genre=<?= urlencode($g) ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                   class="genre-btn <?= $genre === $g ? 'active' : '' ?>">
                    <?= htmlspecialchars($g) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>
</div>

<div class="container py-4">
    <?php if ($films->num_rows > 0): ?>
    <p style="color:var(--text-gray); margin-bottom:1.5rem; font-size:0.9rem">
        Menampilkan <strong style="color:white"><?= $films->num_rows ?></strong> film
    </p>
    <div class="grid grid-4">
        <?php while ($film = $films->fetch_assoc()):
            $poster_path = '../assets/uploads/posters/' . ($film['poster'] ?? '');
        ?>
        <div class="film-card">
            <div class="film-poster-placeholder">
                <?php if (!empty($film['poster']) && file_exists($poster_path)): ?>
                <img src="<?= htmlspecialchars($poster_path) ?>" alt="<?= htmlspecialchars($film['judul']) ?>">
                <?php else: ?>🎬<?php endif; ?>
                <span class="film-rating-badge">⭐ <?= htmlspecialchars($film['rating']) ?></span>
            </div>
            <div class="film-info">
                <div class="film-title"><?= htmlspecialchars($film['judul']) ?></div>
                <div class="film-genre">
                    <i class="fas fa-tag" style="color:var(--primary-light); font-size:0.7rem"></i>
                    <?= htmlspecialchars($film['genre']) ?>
                </div>
                <div class="film-sinopsis"><?= htmlspecialchars($film['sinopsis']) ?></div>
                <div class="film-meta">
                    <span class="film-price">Rp <?= number_format($film['harga'], 0, ',', '.') ?></span>
                    <span class="film-duration"><i class="fas fa-clock"></i> <?= $film['durasi'] ?> mnt</span>
                </div>
                <a href="film-detail.php?id=<?= $film['id'] ?>" class="btn btn-primary btn-block btn-sm">
                    <i class="fas fa-ticket-alt"></i> Pesan Tiket
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="no-results">
        <div class="icon">🎬</div>
        <h3>Film tidak ditemukan</h3>
        <p>Coba kata kunci lain atau hapus filter</p>
        <a href="films.php" class="btn btn-primary mt-3">Lihat Semua Film</a>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
