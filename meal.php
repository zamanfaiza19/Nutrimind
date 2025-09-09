<?php
session_start();
require_once __DIR__ . '/dbconnect.php';
if (!isset($_SESSION['user_id'])) { header("Location: /My_Project/home.php"); exit; }

$memberId  = (int)$_SESSION['user_id'];
$firstName = htmlspecialchars($_SESSION['user_name'] ?? 'Friend', ENT_QUOTES, 'UTF-8');
$today     = date('Y-m-d');

$gender=""; $height=0; $weight=0; $birth=0; $flagN=$flagO=$flagOb="";
$stmt = $conn->prepare("SELECT Gender, Height_cm, Weight_kg, Birth_data, Normal, Overweight, Obese FROM member WHERE Member_id=?");
$stmt->bind_param("i", $memberId);
$stmt->execute();
$stmt->bind_result($gender,$height,$weight,$birth,$flagN,$flagO,$flagOb);
$stmt->fetch();
$stmt->close();

$age = ($birth>0)? (int)date('Y') - (int)$birth : 25;
if ($height<=0) $height=170;
if ($weight<=0) $weight=70;

$g = strtolower(trim((string)$gender));
$bmr = ($g === "female") ? (10*$weight + 6.25*$height - 5*$age - 161) : (10*$weight + 6.25*$height - 5*$age + 5);
$bmr = max(1000, (int)round($bmr));

/* ===== activity controls ===== */
$activityMap = [
  'sedentary'   => 1.2,
  'light'       => 1.375,
  'moderate'    => 1.55,
  'active'      => 1.725,
  'very_active' => 1.9
];
$activity = $_SESSION['activity'] ?? 'light';
if (!isset($activityMap[$activity])) $activity = 'light';

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_activity'])) {
  $newAct = $_POST['activity'] ?? 'light';
  if (isset($activityMap[$newAct])) {
    $_SESSION['activity'] = $activity = $newAct;
    $_SESSION['meal_flash'] = "Activity updated to: ".ucwords(str_replace('_',' ',$activity))." (×{$activityMap[$activity]})";
  }
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

/* ====== MENU ====== */
$MENU = [
  'breakfast' => [
    'Veg' => [
      ['Oats with milk & banana',320,12,55,7],
      ['Poha with peas',280,7,48,6],
      ['Paneer toast',350,20,35,12],
      ['Idli (2) + sambar',280,10,50,6],
      ['Upma (1 bowl)',300,8,54,7],
      ['Vegetable sandwich',310,10,44,9],
      ['Dosa (1) + chutney',320,9,48,9],
      ['Paratha (1) + curd',350,9,45,13],
      ['Muesli + milk (1 cup)',330,12,56,7],
      ['Besan chilla (2)',300,14,36,9],
    ],
    'Non-Veg' => [
      ['Egg omelette (2 eggs)',240,18,2,16],
      ['Chicken sandwich',360,24,38,10],
      ['Egg bhurji + roti',380,20,28,16],
      ['Scrambled eggs + toast',330,22,30,12],
      ['Smoked salmon + toast',340,25,26,14],
      ['Chicken sausage + toast',370,20,30,18],
    ]
  ],
  'mid_morning' => [
    'Veg' => [
      ['Apple + peanuts (15g)',210,6,22,11],
      ['Yogurt (200g)',150,10,12,5],
      ['Banana + almonds (10)',200,5,28,9],
      ['Smoothie (fruit + milk)',250,9,40,6],
      ['Cucumber + hummus',180,6,18,8],
      ['Dates (4) + walnuts (4)',240,5,28,12],
      ['Cheese cubes (40g)',160,10,2,12],
    ],
    'Non-Veg' => [
      ['Milk + whey (1 scoop)',220,25,8,8],
      ['Boiled eggs (2)',160,14,2,10],
      ['Chicken salad cup',200,18,6,8],
      ['Greek yogurt + whey',230,27,10,5],
    ]
  ],
  'lunch' => [
    'Veg' => [
      ['Rice 1 cup + dal 1 cup',430,16,74,6],
      ['Roti(2) + paneer curry',520,24,56,18],
      ['Veg khichdi (bowl)',380,13,67,6],
      ['Rajma + rice',460,18,78,8],
      ['Chole + roti (2)',500,20,70,12],
      ['Veg pulao (1 bowl)',420,12,72,10],
      ['Mixed veg curry + rice',450,14,68,14],
      ['Curd rice (bowl)',420,12,68,10],
      ['Soya chunk curry + roti(2)',520,30,54,16],
      ['Fried rice (veg, 1 bowl)',520,12,78,14],
      ['Chinese vegetable stir-fry',260,8,24,12],
    ],
    'Non-Veg' => [
      ['Chicken curry + rice',560,35,62,16],
      ['Fish curry + rice',520,32,60,14],
      ['Egg curry + roti(2)',480,26,40,16],
      ['Grilled chicken + rice',540,38,58,15],
      ['Prawn curry + rice',530,34,62,13],
      ['Mutton curry + roti(2)',620,40,46,24],
      ['Tuna rice bowl',520,34,58,14],
      ['Chili chicken (1 plate)',580,38,24,30],
    ]
  ],
  'mid_evening' => [
    'Veg' => [
      ['Chana (boiled 100g)',180,10,30,2],
      ['Tea + biscuits(3)',160,3,26,6],
      ['Fruit salad (1 bowl)',190,4,44,2],
      ['Corn chaat (cup)',220,6,48,4],
      ['Sprouts salad',210,12,32,3],
      ['Peanut chikki (2 pcs)',210,6,18,12],
      ['Roasted makhana (30g)',130,5,14,6],
    ],
    'Non-Veg' => [
      ['Egg sandwich',300,16,32,10],
      ['Chicken wrap (small)',340,20,36,12],
      ['Tuna sandwich',320,22,34,9],
      ['Chicken soup (cup)',180,16,10,6],
    ]
  ],
  'dinner' => [
    'Veg' => [
      ['Roti(2) + mixed sabzi',420,12,62,12],
      ['Tofu stir fry + rice',480,24,58,14],
      ['Vegetable biryani (1 cup)',460,12,78,10],
      ['Palak paneer + roti(2)',520,22,40,20],
      ['Kadhi + rice',400,14,66,8],
      ['Dal tadka + jeera rice',470,16,74,10],
      ['Quinoa + veg bowl',450,16,66,10],
    ],
    'Non-Veg' => [
      ['Grilled chicken + salad',450,40,18,18],
      ['Fish + veggies + roti',470,34,44,12],
      ['Chicken biryani (1 cup)',600,38,72,18],
      ['Mutton stew + rice',640,42,60,24],
      ['Egg fried rice (1 bowl)',520,24,70,14],
      ['Paneer-chicken mix bowl',560,42,36,22],
    ]
  ],
];

/* ======= fetch allergens and define blocker ======= */
$ALLERGENS = [];
$allQ = $conn->prepare("SELECT food_name FROM food_allergen WHERE member_id=?");
$allQ->bind_param("i", $memberId);
$allQ->execute();
$allQ->bind_result($allName);
while ($allQ->fetch()) {
  $a = trim(mb_strtolower($allName));
  if ($a !== '') $ALLERGENS[$a] = true; // dedupe
}
$allQ->close();
$ALLERGENS = array_keys($ALLERGENS);

function is_blocked(string $name, array $ALLERGENS): bool {
  if (!$ALLERGENS) return false;
  $n = mb_strtolower($name);
  foreach ($ALLERGENS as $a) {
    // basic substring match
    if ($a !== '' && mb_strpos($n, $a) !== false) return true;
  }
  return false;
}

/* ===== nutrition lookups ===== */
$factor     = $activityMap[$activity] ?? 1.375;
$targetTDEE = (int)round($bmr * $factor);

$flat = [];
foreach ($MENU as $cats) foreach ($cats as $items) foreach ($items as $r) {
  $flat[$r[0]] = ['k'=>$r[1],'p'=>$r[2],'c'=>$r[3],'f'=>$r[4]];
}

/* ===== today row ===== */
$todayK=$todayP=$todayC=$todayF=0; $todayRowId=null;
$grab = $conn->prepare("SELECT Intake_id, calories, protein, carbs, fats FROM meal WHERE Member_id=? AND log_date=?");
$grab->bind_param("is",$memberId,$today);
$grab->execute(); $grab->bind_result($todayRowId,$todayK,$todayP,$todayC,$todayF);
$hasRow = $grab->fetch();
$grab->close();

/* reset today */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['reset_today'])) {
  if ($hasRow) {
    $del = $conn->prepare("DELETE FROM meal WHERE Intake_id=?");
    $del->bind_param("i",$todayRowId);
    $del->execute(); $del->close();
  }
  $_SESSION['meal_flash'] = "Today's totals have been reset.";
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

/* save */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_meals'])){
  $addK=0; $addP=0; $addC=0; $addF=0;
  $selItems = $_POST['meal_item'] ?? [];
  foreach ($selItems as $slot=>$name){
    $name = trim($name);
    if ($name!=='' && isset($flat[$name])){
      // Safety: if user force-posted a blocked item via devtools, ignore it
      if (is_blocked($name, $ALLERGENS)) continue;
      $addK += $flat[$name]['k'];
      $addP += $flat[$name]['p'];
      $addC += $flat[$name]['c'];
      $addF += $flat[$name]['f'];
    }
  }
  if ($addK==0 && $addP==0 && $addC==0 && $addF==0) {
    $_SESSION['meal_flash'] = "No items selected.";
    header("Location: ".$_SERVER['PHP_SELF']); exit;
  }
  $newK = $todayK + $addK;
  $newP = $todayP + $addP;
  $newC = $todayC + $addC;
  $newF = $todayF + $addF;
  if ($hasRow) {
    $u = $conn->prepare("UPDATE meal SET calories=?, protein=?, carbs=?, fats=? WHERE Intake_id=?");
    $u->bind_param("iiiii",$newK,$newP,$newC,$newF,$todayRowId);
    $u->execute(); $u->close();
  } else {
    $i = $conn->prepare("INSERT INTO meal (Member_id, log_date, calories, protein, carbs, fats) VALUES (?,?,?,?,?,?)");
    $i->bind_param("isiiii",$memberId,$today,$newK,$newP,$newC,$newF);
    $i->execute(); $i->close();
  }
  $_SESSION['meal_flash'] = "Saved today’s meals. Added: {$addK} kcal · P {$addP}g · C {$addC}g · F {$addF}g";
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

/* refresh totals */
$todayK=$todayP=$todayC=$todayF=0;
$grab2 = $conn->prepare("SELECT calories, protein, carbs, fats FROM meal WHERE Member_id=? AND log_date=?");
$grab2->bind_param("is",$memberId,$today);
$grab2->execute(); $grab2->bind_result($todayK,$todayP,$todayC,$todayF);
$grab2->fetch();
$grab2->close();

$flash = $_SESSION['meal_flash'] ?? "";
if ($flash!=="") unset($_SESSION['meal_flash']);

$heroUrl = 'https://images.unsplash.com/photo-1543353071-10c8ba85a904?q=80&w=1600&auto=format&fit=crop';
$icons = [
  'breakfast'   => 'https://img.icons8.com/fluency/48/croissant.png',
  'mid_morning' => 'https://img.icons8.com/fluency/48/banana.png',
  'lunch'       => 'https://img.icons8.com/fluency/48/meal.png',
  'mid_evening' => 'https://img.icons8.com/fluency/48/tea.png',
  'dinner'      => 'https://img.icons8.com/fluency/48/steak.png',
];
$labels = [
  'breakfast'    => 'Breakfast',
  'mid_morning'  => 'Mid-morning Snack',
  'lunch'        => 'Lunch',
  'mid_evening'  => 'Mid-evening Snack',
  'dinner'       => 'Dinner'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Nutrimind — Meals</title>
<style>
  body{margin:0;min-height:100vh;display:flex;justify-content:center;align-items:flex-start;padding:40px 16px;font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;background:linear-gradient(135deg,#fde2e4 0%,#fad0c4 100%)}
  .card{background:#fff;width:820px;max-width:95vw;padding:22px;border-radius:18px;box-shadow:0 12px 34px rgba(0,0,0,.12);display:flex;flex-direction:column;gap:14px}
  .hero{width:100%;height:200px;border-radius:12px;object-fit:cover;object-position:center;box-shadow:0 6px 18px rgba(0,0,0,.08)}
  h1{margin:6px 0 2px;text-align:center}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  .panel{background:#f8fafc;border:1px solid #eef;border-radius:12px;padding:12px}
  .label{color:#6b7280;font-size:.92rem}
  .num{font-size:1.2rem;font-weight:700}
  .section{border:1px solid #eee;border-radius:12px;padding:12px}
  .section-header{display:flex;align-items:center;gap:10px;margin:4px 0 10px}
  .section-header img{width:28px;height:28px}
  .section-title{font-weight:800}
  .grid{display:grid;grid-template-columns:1fr;gap:10px}
  select{width:100%;padding:12px;border-radius:10px;border:1px solid #e5e7eb;font-size:1rem;background:#fff}
  .btn{display:inline-block;padding:12px 16px;border:none;border-radius:12px;cursor:pointer;font-weight:700;color:#fff;background:linear-gradient(135deg,#8b5cf6,#7c3aed);transition:transform .1s ease}
  .btn:hover{transform:scale(1.02)}
  .muted{color:#6b7280}
  .flash{color:#7c3aed;font-weight:600}
  .link{color:#7c3aed;text-decoration:none;font-weight:600}
  .flex{display:flex;gap:10px;align-items:center}
  .allergy-note{font-size:.9rem;color:#9b1c1c;background:#ffe4e6;border:1px solid #fecdd3;padding:8px 10px;border-radius:10px}
</style>
</head>
<body>
  <div class="card">
    <img class="hero" src="<?= htmlspecialchars($heroUrl, ENT_QUOTES) ?>" alt="Meals photo" loading="eager">
    <h1>Meals — <?= htmlspecialchars($today) ?></h1>

    <div class="row">
      <div class="panel">
        <div class="label">Base (BMR)</div>
        <div class="num"><?= (int)$bmr ?> kcal</div>
      </div>
      <div class="panel">
        <div class="label">Target (TDEE) = BMR × Activity</div>
        <div class="num"><?= (int)$targetTDEE ?> kcal <span class="muted"> (×<?= $factor ?> · <?= ucwords(str_replace('_',' ',$activity)) ?>)</span></div>
      </div>
    </div>

    <form method="post" class="panel" style="display:flex;gap:10px;align-items:center;justify-content:space-between">
      <div class="label">Daily Activity</div>
      <div class="flex" style="flex:1">
        <select name="activity" style="flex:1">
          <?php foreach ($activityMap as $key=>$mult): ?>
            <option value="<?= htmlspecialchars($key) ?>" <?= $activity===$key?'selected':'' ?>>
              <?= ucwords(str_replace('_',' ',$key)) ?> (×<?= $mult ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <button class="btn" name="update_activity" value="1">Update Activity</button>
      </div>
    </form>

    <?php if ($flash): ?><div class="flash"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

    <?php
      // Count how many are hidden to show a small notice
      $hiddenCount = 0;
      foreach ($MENU as $k=>$groups){
        foreach ($groups as $type=>$items){
          foreach ($items as $r){
            if (is_blocked($r[0], $ALLERGENS)) $hiddenCount++;
          }
        }
      }
    ?>
    <?php if (!empty($ALLERGENS)): ?>
      <div class="allergy-note">
        Allergens on food: <b><?= htmlspecialchars(implode(', ', $ALLERGENS)) ?></b>.
        We’ve hidden <?= (int)$hiddenCount ?> item<?= $hiddenCount==1?'':'s' ?> that match your allergens.
        Manage your list in <a class="link" href="allergy.php">Food Allergen</a>.
      </div>
    <?php endif; ?>

    <form method="post">
      <?php foreach ($labels as $key=>$title): ?>
        <div class="section">
          <div class="section-header">
            <img src="<?= htmlspecialchars($icons[$key], ENT_QUOTES) ?>" alt="">
            <div class="section-title"><?= htmlspecialchars($title) ?></div>
          </div>
          <div class="grid">
            <select name="meal_item[<?= $key ?>]">
              <option value="">— Select item —</option>
              <optgroup label="Veg">
                <?php foreach ($MENU[$key]['Veg'] as $r): ?>
                  <?php if (!is_blocked($r[0], $ALLERGENS)): ?>
                    <option value="<?= htmlspecialchars($r[0],ENT_QUOTES) ?>">
                      <?= htmlspecialchars($r[0]) ?> — <?= $r[1] ?> kcal · P<?= $r[2] ?> C<?= $r[3] ?> F<?= $r[4] ?>
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </optgroup>
              <optgroup label="Non-Veg">
                <?php foreach ($MENU[$key]['Non-Veg'] as $r): ?>
                  <?php if (!is_blocked($r[0], $ALLERGENS)): ?>
                    <option value="<?= htmlspecialchars($r[0],ENT_QUOTES) ?>">
                      <?= htmlspecialchars($r[0]) ?> — <?= $r[1] ?> kcal · P<?= $r[2] ?> C<?= $r[3] ?> F<?= $r[4] ?>
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </optgroup>
            </select>
          </div>
        </div>
      <?php endforeach; ?>

      <div style="margin-top:12px; display:flex; gap:10px;">
        <button class="btn" type="submit" name="save_meals" value="1">Save Today’s Meals</button>
        <button class="btn" type="submit" name="reset_today" value="1" style="background:linear-gradient(135deg,#ef4444,#dc2626)">Reset Today</button>
      </div>
    </form>

    <div class="panel">
      <div class="label">Today's total</div>
      <div class="num"><?= (int)$todayK ?> kcal · P <?= (int)$todayP ?>g · C <?= (int)$todayC ?>g · F <?= (int)$todayF ?>g</div>
    </div>

    <div class="muted" style="margin-top:8px">Hi <?= $firstName ?>, selections add onto today’s totals. Use “Reset Today” to start fresh.</div>
    <div style="text-align:center;margin-top:8px;">
      <a class="link" href="intake.php">← Back to Daily Intake</a>
    </div>
  </div>
</body>
</html>