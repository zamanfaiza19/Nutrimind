<?php
session_start();
require_once "dbconnect.php"; 

$message   = "";
$activeTab = "login"; 

function sanitize_str($s){ return trim((string)$s); }

//Calculating Bmi
function bmi_flags($h_cm, $w_kg){
    $h_cm = is_numeric($h_cm) ? (int)$h_cm : 0;
    $w_kg = is_numeric($w_kg) ? (int)$w_kg : 0;
    if ($h_cm <= 0 || $w_kg <= 0) return ["","",""];
    $bmi = $w_kg / pow($h_cm/100, 2);
    $n=$o=$ob="";
    if ($bmi >= 18.5 && $bmi < 25) $n="Yes";
    elseif ($bmi >= 25 && $bmi < 30) $o="Yes";
    elseif ($bmi >= 30) $ob="Yes";
    return [$n,$o,$ob];
}

// If already logged in, send to the right place
if (isset($_SESSION["is_admin"])) {
    if ($_SESSION["is_admin"] == 1) {
        header("Location: /My_Project/admin_home.php");
        exit;
    } elseif (isset($_SESSION["user_id"])) {
        header("Location: /My_Project/home.php");
        exit;
    }
}

//Handle POST actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* MEMBER LOGIN */
    if (isset($_POST["login"])) {
        $activeTab = "login";
        $email    = sanitize_str($_POST["email"] ?? "");
        $password = (string)($_POST["password"] ?? "");

        if ($email === "" || $password === "") {
            $message = "❌ Please enter email and password.";
        } else {
            $stmt = $conn->prepare("SELECT Member_id, First_name, Password_hash FROM member WHERE Email=? LIMIT 1");
            if (!$stmt) {
                $message = "❌ Database error (prepare).";
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    if (password_verify($password, $row["Password_hash"])) {
                        session_regenerate_id(true);
                        $_SESSION["user_id"]   = (int)$row["Member_id"];
                        $_SESSION["user_name"] = $row["First_name"];
                        $_SESSION["is_admin"]  = 0;
                        header("Location: /My_Project/home.php");
                        exit;
                    } else {
                        $message = "❌ Invalid password.";
                    }
                } else {
                    $message = "❌ No account found for that email.";
                }
                $stmt->close();
            }
        }
    }

/* ADMIN LOGIN (email + password) */
    
if (isset($_POST["admin_login"])) {
    $activeTab     = "admin";
    $admin_email   = sanitize_str($_POST["admin_email"] ?? "");
    $admin_pass_in = (string)($_POST["admin_password"] ?? "");

    if ($admin_email === "" || $admin_pass_in === "") {
        $message = "❌ Please enter admin email and password.";
    } else {
        // Add basic error visibility for mysqli
        mysqli_report(MYSQLI_REPORT_OFF);
        if (! $stmt = $conn->prepare("SELECT Admin_id, name, Password_hash FROM Admin WHERE email = ? LIMIT 1")) {
            $message = "❌ Database error (prepare): ".$conn->error;
        } else {
            $stmt->bind_param("s", $admin_email);
            if (! $stmt->execute()) {
                $message = "❌ Database error (execute): ".$stmt->error;
            } else {
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $ok = false;
                    $stored = (string)$row["Password_hash"];

                    if ($stored !== "") {
                        // Normal, secure path
                        $ok = password_verify($admin_pass_in, $stored);
                    } else {
                        // Fallback for legacy plain-text (so you can log in now)
                        $ok = hash_equals($stored, "") && false; // no hash stored, force plain check below
                    }

                    // If there is no hash stored (legacy), allow plain equality ONCE
                    if (!$ok && $stored === $admin_pass_in) {
                        $ok = true; // legacy plain password in DB
                    }

                    if ($ok) {
                        session_regenerate_id(true);
                        $_SESSION["admin_id"]   = (int)$row["Admin_id"];
                        $_SESSION["admin_name"] = $row["name"];
                        $_SESSION["is_admin"]   = 1;

                        header("Location: /My_Project/admin_home.php");
                        exit;
                    } else {
                        $message = "❌ Invalid admin credentials.";
                    }
                } else {
                    $message = "❌ Admin not found (check email).";
                }
            }
            $stmt->close();
        }
    }
}


    /* MEMBER REGISTER */
    if (isset($_POST["register"])) {
        $activeTab = "register";

        $fname  = sanitize_str($_POST["first_name"] ?? "");
        $lname  = sanitize_str($_POST["last_name"]  ?? "");
        $email  = sanitize_str($_POST["email"]      ?? "");
        $gender = sanitize_str($_POST["gender"]     ?? "");
        $pass   = (string)($_POST["newpass"] ?? "");

        $height_cm = sanitize_str($_POST["height_cm"] ?? "");
        $weight_kg = sanitize_str($_POST["weight_kg"] ?? "");

        $dob_raw    = sanitize_str($_POST["dob"] ?? "");
        $birth_year = 0;
        if ($dob_raw !== "") {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob_raw)) $birth_year = (int)substr($dob_raw,0,4);
            elseif (preg_match('/^\d{4}$/', $dob_raw))        $birth_year = (int)$dob_raw;
        }

        $errs = [];
        if ($fname === "" || $lname === "") $errs[] = "First and last name are required.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = "Invalid email address.";
        if ($pass === "") $errs[] = "Password is required.";

        $stmt = $conn->prepare("SELECT 1 FROM member WHERE Email=?");
        if ($stmt) {
            $stmt->bind_param("s",$email);
            $stmt->execute();
            if ($stmt->get_result()->fetch_row()) $errs[] = "Email already registered. Please log in.";
            $stmt->close();
        } else {
            $errs[] = "Database error (prepare).";
        }

        if ($errs) {
            $message = "❌ ".implode("<br>❌ ", $errs);
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            [$flagNormal,$flagOver,$flagObese] = bmi_flags($height_cm,$weight_kg);

            $sql = "INSERT INTO member
                       (Gender, Height_cm, First_name, Last_name, Normal, Overweight, Obese,
                        Weight_kg, Birth_data, Email, Password_hash)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $message = "❌ Database error (prepare).";
            } else {
                $stmt->bind_param(
                    "sisssssiiss",
                    $gender,
                    $height_cm,
                    $fname,
                    $lname,
                    $flagNormal,
                    $flagOver,
                    $flagObese,
                    $weight_kg,
                    $birth_year,
                    $email,
                    $hash
                );
                if ($stmt->execute()) {
                    $message   = "✅ Account created! You can log in now.";
                    $activeTab = "login";
                } else {
                    $message = "❌ Error: ".$stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Nutrimind — Login / Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body{margin:0;font-family:sans-serif;background:url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;min-height:100vh;display:flex;align-items:center;justify-content:center}
    .overlay{position:fixed;inset:0;background:rgba(0,0,0,.4)}
    .card{position:relative;width:min(480px,95vw);background:rgba(255,255,255,.15);backdrop-filter:blur(12px);border-radius:20px;padding:24px;z-index:1;color:#fff}
    .tabs{display:flex;gap:8px;margin:10px 0}
    .tab{flex:1;text-align:center;padding:10px;border-radius:10px;background:rgba(255,255,255,.2);cursor:pointer}
    .tab.active{background:#fff;color:#27ae60;font-weight:bold}
    .form{display:none}.form.active{display:block}
    input,select{width:100%;padding:10px;border:none;border-radius:8px;margin:6px 0}
    input[type=submit],button{background:#27ae60;color:#fff;font-weight:bold;cursor:pointer;border:none;border-radius:8px;padding:10px}
    input[type=submit]:hover,button:hover{background:#2ecc71}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:8px}
    .message{margin-top:10px;background:rgba(255,255,255,.2);padding:10px;border-radius:10px}
  </style>
  <script>
    function switchTab(tab){
      ['login','register','admin'].forEach(t=>{
        document.getElementById(t+'Form')?.classList.remove('active');
        document.getElementById(t+'Tab')?.classList.remove('active');
      });
      document.getElementById(tab+'Form').classList.add('active');
      document.getElementById(tab+'Tab').classList.add('active');
    }
    window.addEventListener('DOMContentLoaded',()=>{ switchTab('<?php echo $activeTab; ?>'); });
  </script>
</head>
<body>
  <div class="overlay"></div>
  <div class="card">
    <h2 style="text-align:center">🥗 Nutrimind</h2>

    <div class="tabs">
      <div id="loginTab" class="tab" onclick="switchTab('login')">User Login</div>
      <div id="registerTab" class="tab" onclick="switchTab('register')">Register</div>
      <div id="adminTab" class="tab" onclick="switchTab('admin')">Admin Login</div>
    </div>

    <!-- USER LOGIN -->
    <form method="POST" id="loginForm" class="form">
      <input type="email"    name="email"    placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="submit"   name="login"    value="Login">
    </form>

    <!-- REGISTER (user) -->
    <form method="POST" id="registerForm" class="form">
      <div class="row">
        <input type="text" name="first_name" placeholder="First name" required>
        <input type="text" name="last_name"  placeholder="Last name"  required>
      </div>

      <select name="gender">
        <option value="">Gender (optional)</option>
        <option>Male</option><option>Female</option><option>Other</option>
      </select>

      <input type="date" name="dob">

      <div class="row">
        <input type="number" step="1"   name="height_cm" placeholder="Height (cm)">
        <input type="number" step="0.1" name="weight_kg" placeholder="Weight (kg)">
      </div>

      <input type="email"    name="email"   placeholder="Email" required>
      <input type="password" name="newpass" placeholder="Create password" required>

      <input type="submit" name="register" value="Create Account">
    </form>

    <!-- ADMIN LOGIN (email + password) -->
    <form method="POST" id="adminForm" class="form">
      <input type="email"    name="admin_email"    placeholder="Admin Email" required>
      <input type="password" name="admin_password" placeholder="Admin Password" required>
      <input type="submit"   name="admin_login"    value="Admin Login">
    </form>

    <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>
  </div>
</body>
</html>
