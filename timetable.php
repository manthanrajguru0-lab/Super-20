<?php
require_once __DIR__ . "/../includes/security.php";
csrf_protect();
require_once "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../index.php");
    exit();
}

$message = "";
$error = "";

/* =========================
   ADD TIMETABLE
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_timetable"])) {

    $batch_id = intval($_POST["batch_id"]);
    $day_of_week = trim($_POST["day_of_week"]);
    $subject = trim($_POST["subject"]);
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];
    $room_no = trim($_POST["room_no"]);

    if (
        $batch_id <= 0 ||
        empty($day_of_week) ||
        empty($subject) ||
        empty($start_time) ||
        empty($end_time)
    ) {
        $error = "Please fill all required fields.";
    } elseif ($start_time >= $end_time) {
        $error = "End time must be later than start time.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO timetable
            (batch_id, day_of_week, subject, start_time, end_time, room_no)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isssss",
            $batch_id,
            $day_of_week,
            $subject,
            $start_time,
            $end_time,
            $room_no
        );

        if ($stmt->execute()) {
            $message = "Timetable added successfully!";
        } else {
            $error = "Unable to add timetable.";
        }

        $stmt->close();
    }
}

/* =========================
   DELETE TIMETABLE
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_timetable"])) {

    $timetable_id = intval($_POST["timetable_id"]);

    if ($timetable_id > 0) {

        $stmt = $conn->prepare("
            DELETE FROM timetable
            WHERE id = ?
        ");

        $stmt->bind_param("i", $timetable_id);

        if ($stmt->execute()) {
            $message = "Timetable deleted successfully!";
        } else {
            $error = "Unable to delete timetable.";
        }

        $stmt->close();
    }
}

/* =========================
   FETCH ACTIVE BATCHES
========================= */

$batches = [];

$result = $conn->query("
    SELECT id, batch_name, course
    FROM batches
    WHERE status = 'Active'
    ORDER BY batch_name ASC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $batches[] = $row;
    }
}

/* =========================
   FETCH TIMETABLE
========================= */

$timetable = [];

$result = $conn->query("
    SELECT 
        timetable.id,
        timetable.day_of_week,
        timetable.subject,
        timetable.start_time,
        timetable.end_time,
        timetable.room_no,
        batches.batch_name,
        batches.course
    FROM timetable
    INNER JOIN batches
        ON timetable.batch_id = batches.id
    ORDER BY
        FIELD(
            timetable.day_of_week,
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ),
        timetable.start_time
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $timetable[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Timetable Management | Super20 Academy</title>

    <link rel="stylesheet"
          href="../css/style.css">

    <style>

        .page-container {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin: 0;
        }

        .back-btn {
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            background: #333;
            color: white;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-bottom: 18px;
        }

        .form-group label {
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        .btn {
            border: none;
            padding: 11px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
        }

        .btn-success {
            background: #16a34a;
            color: white;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
            padding: 8px 13px;
            font-size: 13px;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #dcfce7;
            color: #166534;
        }

        .error {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #fee2e2;
            color: #991b1b;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
            white-space: nowrap;
        }

        th {
            background: #f5f5f5;
            font-weight: 700;
        }

        .day-badge {
            font-weight: 700;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #777;
        }

    </style>

</head>

<body>

<div class="page-container">

    <!-- HEADER -->

    <div class="page-header">

        <div>

            <h1>Timetable Management</h1>

            <p>Create and manage academy timetable</p>

        </div>

        <a href="../dashboard.php"
           class="back-btn">

            ← Dashboard

        </a>

    </div>


    <!-- MESSAGES -->

    <?php if (!empty($message)): ?>

        <div class="message">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="error">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- ADD TIMETABLE -->

    <div class="card">

        <h2>Add Timetable</h2>

        <br>

        <form method="POST">
<?php echo csrf_field(); ?>

            <div class="form-group">

                <label>Batch *</label>

                <select name="batch_id" required>

                    <option value="">Select Batch</option>

                    <?php foreach ($batches as $batch): ?>

                        <option value="<?php echo $batch["id"]; ?>">

                            <?php
                            echo htmlspecialchars(
                                $batch["batch_name"] .
                                " - " .
                                $batch["course"]
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label>Day *</label>

                <select name="day_of_week" required>

                    <option value="">Select Day</option>

                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>

                </select>

            </div>


            <div class="form-group">

                <label>Subject *</label>

                <input
                    type="text"
                    name="subject"
                    placeholder="e.g. Mathematics"
                    required
                >

            </div>


            <div class="form-group">

                <label>Start Time *</label>

                <input
                    type="time"
                    name="start_time"
                    required
                >

            </div>


            <div class="form-group">

                <label>End Time *</label>

                <input
                    type="time"
                    name="end_time"
                    required
                >

            </div>


            <div class="form-group">

                <label>Room No.</label>

                <input
                    type="text"
                    name="room_no"
                    placeholder="e.g. Room 101"
                >

            </div>


            <button
                type="submit"
                name="add_timetable"
                class="btn btn-success"
            >

                + Add Timetable

            </button>

        </form>

    </div>


    <!-- TIMETABLE LIST -->

    <div class="card">

        <h2>Academy Timetable</h2>

        <?php if (!empty($timetable)): ?>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>Day</th>
                            <th>Batch</th>
                            <th>Course</th>
                            <th>Subject</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Room</th>
                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($timetable as $row): ?>

                        <tr>

                            <td class="day-badge">

                                <?php
                                echo htmlspecialchars(
                                    $row["day_of_week"]
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row["batch_name"]
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row["course"]
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row["subject"]
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo date(
                                    "h:i A",
                                    strtotime(
                                        $row["start_time"]
                                    )
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo date(
                                    "h:i A",
                                    strtotime(
                                        $row["end_time"]
                                    )
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo !empty($row["room_no"])
                                    ? htmlspecialchars($row["room_no"])
                                    : "-";
                                ?>

                            </td>

                            <td>

                                <form
                                    method="POST"
                                    onsubmit="return confirm('Delete this timetable entry?');"
                                >
<?php echo csrf_field(); ?>

                                    <input
                                        type="hidden"
                                        name="timetable_id"
                                        value="<?php echo $row["id"]; ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_timetable"
                                        class="btn btn-danger"
                                    >

                                        Delete

                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="empty">

                No timetable entries available.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>