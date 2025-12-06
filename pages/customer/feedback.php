<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require '../../database/connection.php';

// ---------------- AUTH CHECK ----------------
if (!isset($_SESSION['customer_id'])) {
    header("Location: ../auth/");
    exit;
}

$customer_id = $_SESSION['customer_id'];

// ---------------- FETCH ALL BRANCHES ----------------
$branches = $pdo->query("SELECT branch_id, name FROM branches WHERE deleted_at IS NULL")->fetchAll();

// ---------------- SUBMIT FEEDBACK ----------------
if (isset($_POST['submit_feedback'])) {
    $branch_id = $_POST['branch_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $message = trim($_POST['message']);

    if (!$branch_id || !$rating) {
        echo "<script>alert('Please select a branch and rating.');</script>";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO feedbacks (branch_id, customer_id, rating, message, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        if ($stmt->execute([$branch_id, $customer_id, $rating, $message])) {
            echo "<script>alert('Thank you for your feedback!'); parent.navigate(null, './activity.php');</script>";
            exit;
        } else {
            echo "<script>alert('Failed to submit feedback.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Feedback</title>

    <link rel="stylesheet" href="../../assets/css/pages/customer/feedback.css" />

    <script src="../../assets/js/components/layer.js" defer></script>

    <style>
        /* Inline rating stars (lightweight styling, you can move to CSS file) */
        .stars {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: var(--padding-md);
        }
        .stars input {
            display: none;
        }
        .stars label {
            font-size: var(--size-3xl);
            color: var(--neutral-200);
            cursor: pointer;
        }
        .stars input:checked ~ label,
        .stars label:hover,
        .stars label:hover ~ label {
            color: var(--color-primary);
        }
    </style>
</head>

<body class="customer">
    <main class="page">
        <header class="header header-page">
            <div class="context">
                <h1>Feedback</h1>
            </div>
            <div class="actions right">
              <button
                onclick="parent.navigate('./helpdesk.php')"
                class="btn btn-primary subnav"
              >
                <span class="btn-label">Help</span>
                <i class="bx bxs-message-circle-question-mark btn-icon"></i>
              </button>
            </div>
        </header>

        <main class="main-container main-scrollable">
            <main class="main">
                <section class="form-container">

                    <form class="form" method="POST" autocomplete="off">

                        <!-- SELECT BRANCH -->
                        <div class="account-field">
                            <label><h4>Select Branch</h4></label>
                            <div class="input-container">
                                <select name="branch_id" required>
                                    <option value="">-- Select Branch --</option>
                                    <?php foreach ($branches as $b): ?>
                                        <option value="<?= $b['branch_id'] ?>">
                                            <?= htmlspecialchars($b['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="bx bxs-store"></i>
                            </div>
                        </div>

                        <!-- STAR RATING -->
                        <div class="account-field">
                            <label><h4>Rating</h4></label>
                            <div class="stars">
                                <input type="radio" id="star5" name="rating" value="5" required />
                                <label for="star5">★</label>

                                <input type="radio" id="star4" name="rating" value="4" />
                                <label for="star4">★</label>

                                <input type="radio" id="star3" name="rating" value="3" />
                                <label for="star3">★</label>

                                <input type="radio" id="star2" name="rating" value="2" />
                                <label for="star2">★</label>

                                <input type="radio" id="star1" name="rating" value="1" />
                                <label for="star1">★</label>
                            </div>
                        </div>

                        <!-- MESSAGE -->
                        <div class="account-field">
                            <label><h4>Message</h4></label>
                            <div class="input-container">
                                <textarea
                                    name="message"
                                    placeholder="Write your feedback..."
                                    rows="5"
                                    style="resize:none;"
                                ></textarea>
                            </div>
                        </div>

                        <!-- ACTIONS -->
                        <div class="account-actions">
                            <button class="btn btn-primary" type="submit" name="submit_feedback">
                                Submit
                            </button>
                        </div>

                    </form>

                </section>
            </main>
        </main>
    </main>
</body>
</html>
