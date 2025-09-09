<?php
session_start();
require_once __DIR__ . "/dbconnect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /My_Project/auth.php");
    exit;
}

$memberId  = (int)$_SESSION["user_id"];
$userName  = htmlspecialchars($_SESSION["user_name"] ?? "Friend");
$firstName = htmlspecialchars(explode(' ', trim($userName))[0]);
$msg = "";

/* -------------------- Add food allergy -------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create"])) {
    $food = trim($_POST["food"] ?? "");
    $reminder = trim($_POST["reminder"] ?? "");

    if ($food === "") {
        $msg = "⚠️ Please fill Food name.";
    } else {
        if ($reminder === "") {
            $reminder = "⚠️ Avoid " . $food . ", you are allergic to this food.";
        }
        $stmt = $conn->prepare(
            "INSERT INTO food_allergen (member_id, food_name, reminder) VALUES (?,?,?)"
        );
        $stmt->bind_param("iss", $memberId, $food, $reminder);
        $msg = $stmt->execute()
            ? "✅ Saved allergy: " . htmlspecialchars($food)
            : "❌ Save failed.";
        $stmt->close();
    }
}

/* -------------------- Delete entry -------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $delId = (int)$_POST["delete_id"];
    $stmt = $conn->prepare(
        "DELETE FROM food_allergen WHERE allergen_id=? AND member_id=?"
    );
    $stmt->bind_param("ii", $delId, $memberId);
    $msg = $stmt->execute() ? "🗑️ Deleted." : "❌ Delete failed.";
    $stmt->close();
}

/* -------------------- Fetch all entries -------------------- */
$list = [];
$stmt = $conn->prepare(
    "SELECT allergen_id, food_name, reminder, created_at
     FROM food_allergen
     WHERE member_id=?
     ORDER BY created_at DESC, allergen_id DESC"
);
$stmt->bind_param("i", $memberId);
$stmt->execute();
$stmt->bind_result($aid, $fn, $rm, $ts);
while ($stmt->fetch()) {
    $list[] = ["id" => $aid, "food" => $fn, "reminder" => $rm, "created_at" => $ts];
}
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Nutrimind — Food Allergen</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
<style>
:root {
  --bg1: #f8d4d4;
  --bg2: #f7bfc0;
  --card: #fdeced;
  --ink: #2b2b2b;
  --accent: #e79aa0;
  --accent2: #d77d86;
}
* {
  box-sizing: border-box;
}
body {
  margin: 0;
  font-family: "Fredoka", system-ui, Segoe UI, Roboto, Arial, sans-serif;
  color: var(--ink);
  min-height: 100vh;
  background: linear-gradient(120deg, var(--bg1), var(--bg2));
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 24px;
}
.wrap {
  width: min(980px, 96vw);
}
.hero {
  position: relative;
  height: 260px;
  border-radius: 22px;
  background: #ddd center/cover no-repeat;
  background-image: url("https://images.unsplash.com/photo-1525755662778-989d0524087e?auto=format&fit=crop&w=1600&q=80");
  box-shadow: 0 18px 42px rgba(0,0,0,.18);
}
.hero::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 22px;
  background: linear-gradient(0deg, rgba(0,0,0,.35), rgba(0,0,0,.05));
}
.hero h1 {
  position: absolute;
  left: 22px;
  bottom: 18px;
  margin: 0;
  z-index: 1;
  color: #fff;
  font-size: 34px;
  text-shadow: 0 2px 10px rgba(0,0,0,.35);
}
.card {
  margin-top: -26px;
  background: var(--card);
  border-radius: 22px;
  padding: 22px 20px 14px;
  box-shadow: 0 12px 36px rgba(0,0,0,.18);
}
.sub {
  opacity: .85;
  font-size: 14px;
  margin: 4px 0 12px;
}
.msg {
  margin: 10px 0 8px;
  font-weight: 700;
}
form.add {
  display: grid;
  grid-template-columns: 2fr 3fr auto;
  gap: 10px;
  margin: 12px 0 20px;
}
input[type=text] {
  width: 100%;
  border: 1px solid #e7d9da;
  background: #fff;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 15px;
}
button {
  border: 0;
  background: var(--accent);
  color: #fff;
  padding: 12px 18px;
  border-radius: 14px;
  font-weight: 600;
  cursor: pointer;
}
button:hover {
  background: var(--accent2);
}
.empty {
  padding: 14px 0 8px;
  opacity: .75;
}
.grid {
  display: grid;
  gap: 12px;
}
@media(min-width: 760px) {
  .grid {
    grid-template-columns: 1fr 1fr;
  }
}
.item {
  background: #fff;
  border: 1px solid #f0e2e3;
  border-radius: 16px;
  padding: 14px 14px 12px;
}
.row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
}
.food {
  font-size: 18px;
  font-weight: 700;
}
.rem {
  margin: 6px 0 10px;
  background: #fff3f4;
  border: 1px dashed #f0b9bd;
  padding: 10px 12px;
  border-radius: 12px;
}
.ts {
  font-size: 12px;
  opacity: .6;
}
.del {
  background: #ffdfe2;
  color: #a23b46;
  padding: 8px 12px;
  border-radius: 12px;
  border: 1px solid #ffc6cc;
}
.back {
  display: inline-flex;
  gap: 8px;
  align-items: center;
  margin: 16px 6px 0;
  text-decoration: none;
  color: #6f4c4f;
}
.auto-hint {
  font-size: 12px;
  opacity: .7;
  margin-top: -6px;
}
</style>
</head>
<body>
<div class="wrap">
  <div class="hero" role="img" aria-label="Pasta with herbs on rustic table">
    <h1>Food Allergen</h1>
  </div>
  <div class="card">
    <div class="sub">Hi <?= $firstName ?>, add foods you’re allergic to. Reminder is optional.</div>
    <?php if ($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form class="add" method="POST">
      <input type="hidden" name="create" value="1">
      <input type="text" name="food" placeholder="e.g., Shrimp" maxlength="120" required>
      <input type="text" name="reminder" placeholder="Optional reminder (leave blank to auto-generate)" maxlength="255">
      <button type="submit">Add Allergy</button>
    </form>
    <div class="auto-hint">
      If reminder is empty, we’ll save: “⚠️ Avoid &lt;food&gt;, you are allergic to this food.”
    </div>

    <?php if (!$list): ?>
      <div class="empty">No allergies saved yet.</div>
    <?php else: ?>
      <div class="grid">
        <?php $seq = 1; foreach ($list as $row): ?>
        <div class="item">
          <div class="row">
            <div class="food">#<?= $seq ?> — <?= htmlspecialchars($row["food"]) ?></div>
            <form method="POST" onsubmit="return confirm('Delete this allergy?');">
              <input type="hidden" name="delete_id" value="<?= (int)$row["id"] ?>">
              <button class="del" type="submit">Delete</button>
            </form>
          </div>
          <div class="rem">⚠️ <b>Reminder:</b> <?= htmlspecialchars($row["reminder"]) ?></div>
          <div class="ts">Saved on <?= htmlspecialchars($row["created_at"]) ?></div>
        </div>
        <?php $seq++; endforeach; ?>
      </div>
    <?php endif; ?>

    <a class="back" href="home.php">← Back to Home</a>
  </div>
</div>
</body>
</html>