<?php
session_start();
require_once __DIR__ . "/dbconnect.php";

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }

$flash = "";

//Add Meal 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==="add") {
    $member_id = (int)($_POST['member_id'] ?? 0);
    $date      = $_POST['log_date'] ?? date('Y-m-d');
    $calories  = (int)($_POST['calories'] ?? 0);
    $protein   = (int)($_POST['protein'] ?? 0);
    $carbs     = (int)($_POST['carbs'] ?? 0);
    $fats      = (int)($_POST['fats'] ?? 0);

    $stmt = $conn->prepare("INSERT INTO meal (member_id, log_date, calories, protein, carbs, fats) VALUES (?,?,?,?,?,?)");
    if ($stmt) {
        $stmt->bind_param("isiiii", $member_id,$date,$calories,$protein,$carbs,$fats);
        if ($stmt->execute()) $flash = "✅ Meal added.";
        else $flash = "❌ Add failed: ".$stmt->error;
        $stmt->close();
    }
}

//Delete Meal 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==="delete") {
    $id = (int)$_POST['Intake_id'];
    $stmt = $conn->prepare("DELETE FROM meal WHERE Intake_id=?");
    if ($stmt) {
        $stmt->bind_param("i",$id);
        if ($stmt->execute()) $flash = "🗑️ Meal deleted.";
        else $flash = "❌ Delete failed: ".$stmt->error;
        $stmt->close();
    }
}

//Fetch all meals joined with member 
$sql = "SELECT m.Intake_id, m.member_id, mem.First_name, mem.Last_name,
               m.log_date, m.calories, m.protein, m.carbs, m.fats
        FROM meal m
        LEFT JOIN member mem ON mem.Member_id = m.member_id
        ORDER BY m.Intake_id ASC";
$res = $conn->query($sql);
$meals = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

//Fetch members for dropdown 
$memres = $conn->query("SELECT Member_id, First_name, Last_name FROM member ORDER BY First_name ASC");
$members = $memres ? $memres->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin · Meals</title>
<style>
  body{
    font-family:Arial,sans-serif;
    background:#ffe6f0; /* softer light pink */
    margin:0;
    padding:20px;
  }
  .wrap{max-width:1000px;margin:0 auto;}
  .card{
    background:#fff0f5; /* light pink card */
    padding:16px;
    margin:12px auto;
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,.08);
  }
  h1,h3{margin-top:0;color:#b91c57;} /* dark pink headers */
  table{width:100%;border-collapse:collapse;margin-top:10px}
  th,td{border:1px solid #f7c6d9;padding:8px;text-align:left}
  th{background:#fddde6;color:#b91c57}
  .btn{
    padding:6px 10px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
  }
  .btn-danger{background:#e11d48;color:#fff}
  .btn-add{background:#ec4899;color:#fff}
  .btn-back{
    background:#f472b6;
    color:#fff;
    margin-bottom:10px;
    text-decoration:none;
    display:inline-block;
  }
  .flash{
    padding:10px;
    margin-bottom:10px;
    border-radius:8px;
    background:#fce7f3;
    border:1px solid #f9a8d4;
    color:#9d174d;
  }
</style>
</head>
<body>
<div class="wrap">

  <!-- Back to Admin Home button -->
  <a href="admin_home.php" class="btn btn-back">← Back to Admin Home</a>

  <div class="card">
    <h1>🍽️ Admin · Meals</h1>
    <?php if ($flash): ?><div class="flash"><?= $flash ?></div><?php endif; ?>

    <h3>Add New Meal</h3>
    <form method="post" style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;align-items:end">
      <select name="member_id" required>
        <option value="">Select Member</option>
        <?php foreach($members as $mem): ?>
          <option value="<?= $mem['Member_id'] ?>">
            <?= h($mem['First_name']." ".$mem['Last_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="date" name="log_date" value="<?= date('Y-m-d') ?>">
      <input type="number" name="calories" placeholder="Calories" required>
      <input type="number" name="protein" placeholder="Protein">
      <input type="number" name="carbs" placeholder="Carbs">
      <input type="number" name="fats" placeholder="Fats">
      <input type="hidden" name="action" value="add">
      <button class="btn btn-add" type="submit">Add</button>
    </form>
  </div>

  <div class="card">
    <h3>All Meals</h3>
    <?php if (!$meals): ?>
      <p>No meals logged.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th><th>Member</th><th>Date</th>
            <th>Calories</th><th>Protein</th><th>Carbs</th><th>Fats</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($meals as $i=>$row): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= h($row['First_name']." ".$row['Last_name']) ?></td>
            <td><?= h($row['log_date']) ?></td>
            <td><?= (int)$row['calories'] ?></td>
            <td><?= (int)$row['protein'] ?></td>
            <td><?= (int)$row['carbs'] ?></td>
            <td><?= (int)$row['fats'] ?></td>
            <td>
              <form method="post" style="display:inline" onsubmit="return confirm('Delete this meal?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="Intake_id" value="<?= (int)$row['Intake_id'] ?>">
                <button class="btn btn-danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
