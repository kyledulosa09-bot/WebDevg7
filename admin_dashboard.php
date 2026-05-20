<?php
/* Admin Dashboard - password is "admin123" */
$correct_password = "admin123";

// Handle edit - saves updated student row to database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {

    $host = "sql107.infinityfree.com";
    $username = "if0_41881409";
    $password = "DIT22PUPG7";
    $database = "if0_41881409_survey_db";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    $id              = intval($_POST['edit_id']);
    $name            = $conn->real_escape_string($_POST['edit_name']);
    $grade_section   = $conn->real_escape_string($_POST['edit_grade_section']);
    $subject         = $conn->real_escape_string($_POST['edit_subject']);
    $teacher_rating  = intval($_POST['edit_teacher_rating']);
    $favorite_lesson = $conn->real_escape_string($_POST['edit_favorite_lesson']);
    $suggestions     = $conn->real_escape_string($_POST['edit_suggestions']);

    $conn->query("UPDATE student_feedback SET 
        name='$name', 
        grade_section='$grade_section', 
        subject='$subject', 
        teacher_rating='$teacher_rating', 
        favorite_lesson='$favorite_lesson', 
        suggestions='$suggestions' 
        WHERE id=$id");

    $conn->close();
    header("Location: admin_dashboard.php");
    exit();
}

// Handle delete - removes a row from any table
if (isset($_GET['delete']) && isset($_GET['id']) && isset($_GET['table'])) {

    $delete_id = intval($_GET['id']);
    // Only allow known table names for safety
    $table = $_GET['table'] === 'tourism_feedback' ? 'tourism_feedback' : 'student_feedback';

    $host = "sql107.infinityfree.com";
    $username = "if0_41881409";
    $password = "DIT22PUPG7";
    $database = "if0_41881409_survey_db";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

    $conn->query("DELETE FROM $table WHERE id = $delete_id");
    $conn->close();

    header("Location: admin_dashboard.php");
    exit();
}

// Handle login - check password before showing dashboard
$user_password = isset($_POST['password']) ? $_POST['password'] : '';

if ($user_password !== $correct_password) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <style>
            *{margin:0;padding:0;box-sizing:border-box;}
            body{font-family:Arial,sans-serif;background:linear-gradient(135deg,#1a73e8,#34a853);min-height:100vh;display:flex;justify-content:center;align-items:center;}
            .login-box{background:white;padding:40px;border-radius:16px;width:350px;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,0.2);}
            h2{margin-bottom:10px;color:#333;}
            input{width:100%;padding:12px;margin:20px 0;border:1px solid #ddd;border-radius:8px;font-size:16px;}
            button{background:#1a73e8;color:white;border:none;padding:12px 24px;border-radius:30px;font-size:16px;cursor:pointer;width:100%;}
            button:hover{background:#1557b0;}
            .back-link{display:block;margin-top:15px;color:#1a73e8;text-decoration:none;font-size:14px;}
            .back-link:hover{text-decoration:underline;}
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>Admin Login</h2>
            <p style="color:#666;font-size:14px;">Enter password to view responses</p>
            <form method="POST">
                <input type="password" name="password" placeholder="Enter password" required>
                <button type="submit">View Dashboard →</button>
            </form>
            <a href="index.html" class="back-link">← Back to Community Portal</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Fetch data - get all rows from both tables
$host = "sql107.infinityfree.com";
$username = "if0_41881409";
$password = "DIT22PUPG7";
$database = "if0_41881409_survey_db";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$student_result = $conn->query("SELECT * FROM student_feedback ORDER BY id DESC");
$tourism_result = $conn->query("SELECT * FROM tourism_feedback ORDER BY id DESC");

$student_count = $student_result->num_rows;
$tourism_count = $tourism_result->num_rows;

// Store student rows in array so we can loop it twice (table + edit modal data)
$student_rows = [];
while ($row = $student_result->fetch_assoc()) {
    $student_rows[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Arial,sans-serif;background:#f0f2f5;padding:20px;}
        .dashboard{max-width:1400px;margin:0 auto;}

        /* Top header bar */
        .header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:30px;background:white;padding:20px;border-radius:12px;}
        .header h1{color:#1a73e8;}
        .back-btn{background:#1a73e8;color:white;text-decoration:none;padding:10px 20px;border-radius:30px;}
        .back-btn:hover{background:#1557b0;}

        /* Count cards at the top */
        .stats{display:flex;gap:20px;margin-bottom:30px;flex-wrap:wrap;}
        .stat-card{background:white;padding:25px;border-radius:12px;flex:1;text-align:center;border-top:4px solid #1a73e8;}
        .stat-number{font-size:40px;font-weight:bold;color:#1a73e8;}

        /* Section labels above each table */
        .section-title{background:white;padding:15px 20px;margin:20px 0 15px;border-radius:8px;border-left:4px solid #1a73e8;}

        /* Filter bar above student table */
        .filter-bar{background:white;padding:16px 20px;border-radius:10px;margin-bottom:14px;display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;}
        .filter-bar label{font-size:13px;font-weight:600;color:#444;display:block;margin-bottom:5px;}
        .filter-bar select{padding:8px 12px;border:1px solid #dadce0;border-radius:8px;font-size:14px;background:white;cursor:pointer;}
        .filter-bar button{padding:8px 18px;background:#e8eaed;border:2px solid #1a73e8;border-radius:30px;color:#1a73e8;font-weight:600;cursor:pointer;font-size:13px;}
        .filter-bar button:hover{background:#1a73e8;color:white;}
        #filterCount{font-size:13px;color:#5f6368;align-self:center;}

        /* Main tables */
        .table-wrapper{background:white;border-radius:12px;overflow-x:auto;margin-bottom:30px;}
        table{width:100%;border-collapse:collapse;font-size:14px;}
        th{background:#1a73e8;color:white;padding:12px;text-align:left;border:1px solid #2d7de0;}
        td{padding:10px 12px;border:1px solid #e0e0e0;vertical-align:top;}
        tr:hover{background:#f5f5f5;}

        /* Action buttons in table rows */
        .delete-btn{background:#dc3545;color:white;text-decoration:none;padding:5px 12px;border-radius:5px;font-size:12px;display:inline-block;}
        .delete-btn:hover{background:#c82333;}
        .edit-btn{background:#1a73e8;color:white;border:none;padding:5px 12px;border-radius:5px;font-size:12px;cursor:pointer;display:inline-block;margin-bottom:4px;}
        .edit-btn:hover{background:#1557b0;}

        .empty{text-align:center;padding:40px;color:#999;}
        .rating-stars{color:#ffc107;letter-spacing:2px;}

        /* Edit Modal - pops up when admin clicks Edit */
        .modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:999;justify-content:center;align-items:center;}
        .modal-overlay.active{display:flex;}
        .modal-box{background:white;border-radius:16px;padding:32px;width:90%;max-width:500px;box-shadow:0 12px 40px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto;}
        .modal-box h3{color:#1a73e8;margin-bottom:20px;font-size:18px;}
        .modal-box label{display:block;font-size:13px;font-weight:600;color:#444;margin-bottom:5px;margin-top:14px;}
        .modal-box input,.modal-box select,.modal-box textarea{width:100%;padding:10px 12px;border:1px solid #dadce0;border-radius:8px;font-size:14px;font-family:inherit;}
        .modal-box textarea{resize:vertical;min-height:80px;}
        .modal-buttons{display:flex;gap:12px;margin-top:20px;justify-content:flex-end;}
        .btn-cancel{padding:10px 20px;background:#e8eaed;border:2px solid #ccc;border-radius:30px;font-weight:600;cursor:pointer;color:#444;}
        .btn-save{padding:10px 20px;background:#1a73e8;color:white;border:none;border-radius:30px;font-weight:600;cursor:pointer;}
        .btn-save:hover{background:#1557b0;}

        @media(max-width:800px){th,td{font-size:12px;padding:6px;}}
    </style>
</head>
<body>
<div class="dashboard">

    <!-- Header -->
    <div class="header">
        <h1>Admin Dashboard</h1>
        <a href="index.html" class="back-btn">← Back to Home</a>
    </div>

    <!-- Stat cards - shows total count of each survey type -->
    <div class="stats">
        <div class="stat-card"><div class="stat-number"><?php echo $student_count; ?></div><div>Student Feedback</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $tourism_count; ?></div><div>Tourism Feedback</div></div>
        <div class="stat-card"><div class="stat-number"><?php echo $student_count + $tourism_count; ?></div><div>Total Responses</div></div>
    </div>

    <!-- Student Feedback Section -->
    <div class="section-title"><h2>📚 Student Feedback</h2></div>

    <!-- Filter controls - filters happen in JS without reloading the page -->
    <div class="filter-bar">
        <div>
            <label>Filter by Subject</label>
            <select id="filterSubject" onchange="filterStudentTable()">
                <option value="">All Subjects</option>
                <option value="Web Development">Web Development</option>
                <option value="Information Management">Information Management</option>
                <option value="System Administration">System Administration</option>
                <option value="Network Administration">Network Administration</option>
                <option value="Physical Activity Towards Health and Fitness">Physical Activity Towards Health and Fitness</option>
                <option value="Business Intelligence">Business Intelligence</option>
                <option value="Human Computer Interaction">Human Computer Interaction</option>
                <option value="Object Oriented Programming">Object Oriented Programming</option>
                <option value="Quantitative Methods with Modeling and Simulation">Quantitative Methods with Modeling and Simulation</option>
            </select>
        </div>
        <div>
            <label>Filter by Rating</label>
            <select id="filterRating" onchange="filterStudentTable()">
                <option value="">All Ratings</option>
                <option value="5">★★★★★ (5)</option>
                <option value="4">★★★★☆ (4)</option>
                <option value="3">★★★☆☆ (3)</option>
                <option value="2">★★☆☆☆ (2)</option>
                <option value="1">★☆☆☆☆ (1)</option>
            </select>
        </div>
        <button onclick="resetStudentFilter()">Reset</button>
        <span id="filterCount"></span>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Course/Section</th>
                    <th>Subject</th>
                    <th>Rating</th>
                    <th>Favorite Lesson</th>
                    <th>Suggestions</th>
                    <th>Anonymous</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="studentBody">
                <?php if($student_count > 0): ?>
                    <?php foreach($student_rows as $row): ?>
                    <tr data-subject="<?php echo htmlspecialchars($row['subject']); ?>" data-rating="<?php echo $row['teacher_rating']; ?>">
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['is_anonymous'] ? 'Anonymous' : htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['grade_section']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td class="rating-stars"><?php echo str_repeat('★', $row['teacher_rating']) . str_repeat('☆', 5 - $row['teacher_rating']); ?></td>
                        <td><?php echo htmlspecialchars($row['favorite_lesson']) ?: '-'; ?></td>
                        <td><?php echo htmlspecialchars(substr($row['suggestions'], 0, 50)) . (strlen($row['suggestions']) > 50 ? '...' : ''); ?></td>
                        <td><?php echo $row['is_anonymous'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo date('M j, Y', strtotime($row['submitted_at'])); ?></td>
                        <td>
                            <button class="edit-btn" onclick="openEditModal(
                                <?php echo $row['id']; ?>,
                                '<?php echo addslashes(htmlspecialchars($row['name'])); ?>',
                                '<?php echo addslashes(htmlspecialchars($row['grade_section'])); ?>',
                                '<?php echo addslashes(htmlspecialchars($row['subject'])); ?>',
                                <?php echo $row['teacher_rating']; ?>,
                                '<?php echo addslashes(htmlspecialchars($row['favorite_lesson'])); ?>',
                                '<?php echo addslashes(htmlspecialchars($row['suggestions'])); ?>'
                            )">Edit</button>
                            <a href="?delete=1&id=<?php echo $row['id']; ?>&table=student_feedback" class="delete-btn" onclick="return confirm('Delete this response?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="empty">No student feedback yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Tourism Feedback Section -->
    <div class="section-title"><h2>🌴 Tourism Feedback</h2></div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nationality</th>
                    <th>Visit Date</th>
                    <th>Cleanliness</th>
                    <th>Amenities</th>
                    <th>Comments</th>
                    <th>Date</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php if($tourism_count > 0): ?>
                    <?php while($row = $tourism_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nationality']); ?></td>
                        <td><?php echo $row['visit_date']; ?></td>
                        <td class="rating-stars"><?php echo str_repeat('★', $row['cleanliness_rating']) . str_repeat('☆', 5 - $row['cleanliness_rating']); ?></td>
                        <td><?php echo htmlspecialchars($row['amenities']) ?: '-'; ?></td>
                        <td><?php echo htmlspecialchars(substr($row['comments'], 0, 50)) . (strlen($row['comments']) > 50 ? '...' : ''); ?></td>
                        <td><?php echo date('M j, Y', strtotime($row['submitted_at'])); ?></td>
                        <td><a href="?delete=1&id=<?php echo $row['id']; ?>&table=tourism_feedback" class="delete-btn" onclick="return confirm('Delete this response?')">Delete</a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="empty">No tourism feedback yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <h3>✏️ Edit Student Response</h3>

        <form method="POST" action="admin_dashboard.php">
            <input type="hidden" name="password" value="admin123">
            <input type="hidden" name="edit_id" id="edit_id">

            <label>Name</label>
            <input type="text" name="edit_name" id="edit_name">

            <label>Course / Section</label>
            <select name="edit_grade_section" id="edit_grade_section">
                <option value="DIT 2-1">DIT 2-1</option>
                <option value="DIT 2-2">DIT 2-2</option>
                <option value="DIT 2-3">DIT 2-3</option>
                <option value="DIT 2-4">DIT 2-4</option>
                <option value="DIT 2-5">DIT 2-5</option>
            </select>

            <label>Subject</label>
            <select name="edit_subject" id="edit_subject">
                <option value="Web Development">Web Development</option>
                <option value="Information Management">Information Management</option>
                <option value="System Administration">System Administration</option>
                <option value="Network Administration">Network Administration</option>
                <option value="Physical Activity Towards Health and Fitness">Physical Activity Towards Health and Fitness</option>
                <option value="Business Intelligence">Business Intelligence</option>
                <option value="Human Computer Interaction">Human Computer Interaction</option>
                <option value="Object Oriented Programming">Object Oriented Programming</option>
                <option value="Quantitative Methods with Modeling and Simulation">Quantitative Methods with Modeling and Simulation</option>
            </select>

            <label>Teacher Rating</label>
            <select name="edit_teacher_rating" id="edit_teacher_rating">
                <option value="5">★★★★★ (5)</option>
                <option value="4">★★★★☆ (4)</option>
                <option value="3">★★★☆☆ (3)</option>
                <option value="2">★★☆☆☆ (2)</option>
                <option value="1">★☆☆☆☆ (1)</option>
            </select>

            <label>Favorite Lesson</label>
            <input type="text" name="edit_favorite_lesson" id="edit_favorite_lesson">

            <label>Suggestions</label>
            <textarea name="edit_suggestions" id="edit_suggestions" rows="3"></textarea>

            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Filter functions for student table
function filterStudentTable() {
    var subjectFilter = document.getElementById('filterSubject').value;
    var ratingFilter = document.getElementById('filterRating').value;
    var rows = document.querySelectorAll('#studentBody tr');
    var visibleCount = 0;
    
    // Skip the first row if it's an empty message row
    for (var i = 0; i < rows.length; i++) {
        var row = rows[i];
        // Skip if row has class "empty" (no data message)
        if (row.querySelector('.empty')) {
            continue;
        }
        
        var subject = row.getAttribute('data-subject');
        var rating = row.getAttribute('data-rating');
        
        var subjectMatch = false;
        var ratingMatch = false;
        
        // Check subject filter
        