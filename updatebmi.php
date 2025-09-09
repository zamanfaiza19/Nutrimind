<?php
session_start();
require_once __DIR__ . '/dbconnect.php'; // your DB connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$memberId = (int)$_SESSION['user_id'];
$banner = '';
$bannerClass = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weight = trim($_POST['weight'] ?? '');
    $goal = trim($_POST['goal'] ?? '');

    if (!is_numeric($weight) || $weight <= 0) {
        $banner = "❌ Please enter a valid current weight.";
        $bannerClass = "warn";
    } elseif (!is_numeric($goal) || $goal <= 0) {
        $banner = "❌ Please enter a valid goal weight.";
        $bannerClass = "warn";
    } else {
        $stmt = $conn->prepare("UPDATE member SET Weight_kg = ?, Goal_weight = ? WHERE Member_id = ?");
        $stmt->bind_param("iii", $weight, $goal, $memberId);

        if ($stmt->execute()) {
            $banner = "✅ Your weight and goal have been updated!";
            $bannerClass = "ok";
        } else {
            $banner = "❌ Database error: " . $conn->error;
            $bannerClass = "warn";
        }
    }
}

// Fetch current values
$stmt = $conn->prepare("SELECT Weight_kg, Goal_weight FROM member WHERE Member_id = ?");
$stmt->bind_param("i", $memberId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Weight</title>
  <style>
    body { font-family: Arial, sans-serif; background: #ffe2e2; text-align: center; padding: 40px; }
    .form-box { background: rgb(255, 207, 207); padding: 20px; border-radius: 10px; width: 400px; margin: auto; }
    .banner.ok { color: green; margin-bottom: 15px; }
    .banner.warn { color: red; margin-bottom: 15px; }
    input { padding: 10px; margin: 10px; width: 80%; border-radius: 5px; border: 1px solid #ccc; }
    button { padding: 10px 20px; background: #ff5252; color: rgb(255, 245, 245); border: none; border-radius: 5px; }
    a { display: block; margin-top: 20px; color: #ff5e5e; text-decoration: none; }
  </style>
</head>
<body>
  <div class="form-box">
    <h2>Update Your Weight</h2>
    <?php if ($banner): ?>
      <div class="banner <?= $bannerClass ?>"><?= htmlspecialchars($banner) ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="number" name="weight" value="<?= htmlspecialchars($user['Weight_kg']) ?>" placeholder="Current Weight (kg)" required>
      <input type="number" name="goal" value="<?= htmlspecialchars($user['Goal_weight']) ?>" placeholder="Goal Weight (kg)" required>
      <br>
      <button type="submit">Update</button>
    </form>

    <a href="home.php">⬅ Back to Home</a>
  </div>
</body>
</html>
