<?php
session_start();
require_once __DIR__ . '/dbconnect.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: /My_Project/home.php");
  exit;
}
$firstName = htmlspecialchars($_SESSION['user_name'] ?? 'Friend');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Nutrimind — Daily Intake</title>
<style>
  body {
    margin:0;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;
    background: linear-gradient(135deg, #fde2e4 0%, #fad0c4 100%); /* ✅ pinkish gradient like home.php */
  }
  .card {
    background:#fff;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.1);
    width:350px;
    text-align:center;
    display:flex;
    flex-direction:column;
    align-items:stretch;
  }
  h1 { margin-top:0; font-size:1.5rem; }
  p { color:#6b7280; margin-bottom:20px; }
  .btn {
    display:block;
    width:100%;
    margin:10px 0;
    padding:14px;
    border:none;
    border-radius:12px;
    font-size:1rem;
    font-weight:600;
    cursor:pointer;
    text-decoration:none;
    color:#fff;
    background:linear-gradient(135deg,#8b5cf6,#7c3aed);
    transition:transform .1s ease, background .2s ease;
    box-sizing:border-box;
    text-align:center;
  }
  .btn:hover { 
    transform:scale(1.02); 
    background:linear-gradient(135deg,#9f7aea,#6d28d9);
  }
  .back {
    display:inline-block;
    margin-top:16px;
    font-size:.9rem;
    color:#7c3aed;
    text-decoration:none;
    font-weight:600;
  }
  .img-top {
    width:100%;
    border-radius:12px;
    margin-bottom:15px;
  }
</style>
</head>
<body>
  <div class="card">
    <img class="img-top" src="https://images.unsplash.com/photo-1490645935967-10de6ba17061?q=80&w=1600&auto=format&fit=crop" alt="Healthy lifestyle">
    <h1>Daily Intake</h1>
    <p>Choose what you want to track today:</p>
    <a href="water.php" class="btn">💧 Hydration (Water Intake)</a>
    <a href="meal.php" class="btn">🍽️ Meals (Food Intake)</a>
    <a href="home.php" class="back">← Back to Home</a>
  </div>
</body>
</html>
