<?php
use Dotenv\Dotenv;

require_once 'connection.php';
session_start();

require_once 'mail_function.php';
require_once 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Task 4.4 Requirement: Admin Access Check
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Disable caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$page_title = "Manage Workshop Registrations";
$message = '';
$error = '';

// --- Google Calendar Settings ---
$credentials_path = './workshop-scheduler-478503-4063b65e5ffc.json';
$calendarId = $_ENV['GOOGLE_CALENDAR_ID'];
$timezone = 'Asia/Kuala_Lumpur'; // IMPORTANT: Your local timezone

// --- HELPER FUNCTIONS ---

/**
 * Initializes and returns the Google Calendar service client.
 */
function getGoogleCalendarService($credentials_path) {
    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentials_path);
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->setScopes([Google_Service_Calendar::CALENDAR_EVENTS]);
    return new Google_Service_Calendar($client);
}

/**
 * Creates a Google Calendar Event object with the first participant.
 */
function createCalendarEvent($title, $fname, $lname, $date, $time, $timezone) {
    $participant_name = $fname . ' ' . $lname; // This is the FIRST participant
    $start_datetime_str = $date . ' ' . $time;
    $start_datetime = new DateTime($start_datetime_str, new DateTimeZone($timezone));
    $end_datetime = (clone $start_datetime)->modify('+1 hour'); // Assume 1 hour

    return new Google_Service_Calendar_Event([
        'summary' => "Workshop: $title",
        'description' => "Participants:\n- " . $participant_name, // Description starts with this participant
        'start' => [
            'dateTime' => $start_datetime->format(DateTime::RFC3339),
            'timeZone' => $timezone,
        ],
        'end' => [
            'dateTime' => $end_datetime->format(DateTime::RFC3339),
            'timeZone' => $timezone,
        ],
        'reminders' => [
            'useDefault' => FALSE,
            'overrides' => [
                ['method' => 'email', 'minutes' => 24 * 60],
                ['method' => 'popup', 'minutes' => 60],
            ],
        ],
    ]);
}

/**
 * Approves a registration, creating or updating a "merged" calendar event.
 */
function approveRegistration($conn, $registration_id, $service, $calendarId, $timezone) {
    // 1. Get this user's info
    $stmt = $conn->prepare("SELECT * FROM workshop_table WHERE id = ?");
    $stmt->execute([$registration_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_info) return; // User not found

    // 2. Find an existing event ID from other approved participants for this *exact* workshop
    $stmt_find = $conn->prepare("
        SELECT google_calendar_event_id FROM workshop_table 
        WHERE workshop_title = ? AND date = ? AND time = ? 
        AND status = 'approved' AND google_calendar_event_id IS NOT NULL 
        AND id != ? LIMIT 1
    ");
    $stmt_find->execute([$user_info['workshop_title'], $user_info['date'], $user_info['time'], $registration_id]);
    $existing_event_id = $stmt_find->fetchColumn();
    
    $participant_name = $user_info['first_name'] . ' ' . $user_info['last_name'];
    $event_id_to_save = null;

    if ($existing_event_id) {
        // 3a. Event EXISTS. Add this participant to the description.
        $event = $service->events->get($calendarId, $existing_event_id);
        $description = $event->getDescription();
        
        // Avoid adding duplicate names (in case of re-approve)
        $participants = explode("\n", $description);
        $participant_exists = false;
        foreach ($participants as $p) {
            if (trim($p, '- ') == trim($participant_name)) {
                $participant_exists = true;
                break;
            }
        }

        if (!$participant_exists) {
            $new_description = $description . "\n- " . $participant_name;
            $event->setDescription($new_description);
            $service->events->update($calendarId, $existing_event_id, $event);
        }
        $event_id_to_save = $existing_event_id;
    } else {
        // 3b. No event found. CREATE a new one.
        $event = createCalendarEvent(
            $user_info['workshop_title'], $user_info['first_name'], $user_info['last_name'],
            $user_info['date'], $user_info['time'], $timezone
        );
        $created_event = $service->events->insert($calendarId, $event);
        $event_id_to_save = $created_event->getId();
    }

    // 4. Update this user's DB row to 'approved' and store the shared event ID
    $stmt_update = $conn->prepare("UPDATE workshop_table SET status = 'approved', google_calendar_event_id = ? WHERE id = ?");
    $stmt_update->execute([$event_id_to_save, $registration_id]);
}

/**
 * Removes a participant from a "merged" event. Deletes event if they are the last one.
 */
function removeParticipantFromEvent($conn, $registration_id, $service, $calendarId) {
    // 1. Get this user's info
    $stmt = $conn->prepare("SELECT * FROM workshop_table WHERE id = ?");
    $stmt->execute([$registration_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    // If user has no event ID, there's nothing to do.
    if (!$user_info || !$user_info['google_calendar_event_id']) {
        return; 
    }
    
    $event_id = $user_info['google_calendar_event_id'];
    $participant_name = $user_info['first_name'] . ' ' . $user_info['last_name'];

    try {
        $event = $service->events->get($calendarId, $event_id);
        $description = $event->getDescription();
        $participants = explode("\n", $description);

        // Find and remove this participant
        $new_participants_list = [];
        $found = false;
        foreach ($participants as $p) {
            // Keep all lines that DON'T match this participant's name
            if (trim($p, '- ') == trim($participant_name) && !$found) {
                $found = true; // Mark as found, but don't add to new list
            } else {
                $new_participants_list[] = $p;
            }
        }
        
        // Remove "Participants:" header if list is now empty
        if (count($new_participants_list) <= 1) { 
            // 2a. This was the last participant. Delete the whole event.
            $service->events->delete($calendarId, $event_id);
        } else {
            // 2b. Others remain. Update the description.
            $new_description = implode("\n", $new_participants_list);
            $event->setDescription($new_description);
            $service->events->update($calendarId, $event_id, $event);
        }
    } catch (Google\Service\Exception $e) {
        if ($e->getCode() == 404) {
            // Event already deleted. No problem.
        } else {
            throw $e; // Re-throw other API errors
        }
    }
}

// --- END HELPER FUNCTIONS ---


// --- HANDLE DELETE ---
if (isset($_GET['delete_id'])) {
    try {
        $id = $_GET['delete_id'];
        
        // 1. Remove participant from calendar event (if they were approved)
        $service = getGoogleCalendarService($credentials_path);
        removeParticipantFromEvent($conn, $id, $service, $calendarId);

        // 2. Delete from database
        $stmt = $conn->prepare("DELETE FROM workshop_table WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Registration entry deleted and removed from calendar.";
    } catch (Exception $e) {
        $error = "Error deleting entry: " . $e->getMessage();
    }
}

// --- HANDLE STATUS CHANGE (Approve/Reject) ---
if (isset($_POST['update_status'])) {
    try {
        $id = $_POST['id'];
        $new_status = isset($_POST['approve']) ? 'approved' : 'rejected';
        $service = getGoogleCalendarService($credentials_path);
        
        // Get user info for email
        $stmt_user = $conn->prepare("SELECT email, first_name, workshop_title FROM workshop_table WHERE id = ?");
        $stmt_user->execute([$id]);
        $user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);

        if ($new_status === 'approved') {
            // --- APPROVE LOGIC ---
            approveRegistration($conn, $id, $service, $calendarId, $timezone);
            $message = "Registration approved. Email sent and calendar event updated.";
            
        } else {
            // --- REJECT LOGIC ---
            removeParticipantFromEvent($conn, $id, $service, $calendarId);
            // Update DB to 'rejected' and clear event ID
            $stmt = $conn->prepare("UPDATE workshop_table SET status = 'rejected', google_calendar_event_id = NULL WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Registration rejected. Email sent and participant removed from calendar.";
        }

        // Send email notification (for both approve and reject)
        if ($user_info) {
            $email_sent = send_workshop_notification(
                $user_info['email'], $user_info['first_name'], 
                $user_info['workshop_title'], $new_status
            );
            if (!$email_sent) {
                $error = "Status updated, but the notification email could not be sent.";
            }
        }

    } catch (Exception $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}

// --- HANDLE ADD REGISTRATION ---
if (isset($_POST['add_reg'])) {
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $title = $_POST['workshop_title'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $contact = $_POST['contact_number'];

    try {
        // 1. Insert into DB (defaults to 'approved')
        $sql = "INSERT INTO workshop_table (first_name, last_name, email, workshop_title, date, time, contact_number, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')";
        $stmt_insert = $conn->prepare($sql);
        $stmt_insert->execute([$fname, $lname, $email, $title, $date, $time, $contact]);
        $last_id = $conn->lastInsertId();

        // 2. Run the approve logic to create/update the calendar event
        $service = getGoogleCalendarService($credentials_path);
        approveRegistration($conn, $last_id, $service, $calendarId, $timezone);
        
        $message = "New registration added and calendar event created/updated.";
    } catch (Exception $e) {
        $error = "Error adding registration: " . $e->getMessage();
    }
}

// --- HANDLE EDIT REGISTRATION ---
if (isset($_POST['edit_reg'])) {
    $id = $_POST['id'];
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $title = $_POST['workshop_title'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $contact = $_POST['contact_number'];

    try {
        // 1. Get current status BEFORE edit
        $stmt_check = $conn->prepare("SELECT status FROM workshop_table WHERE id = ?");
        $stmt_check->execute([$id]);
        $current_status = $stmt_check->fetchColumn();
        
        $service = getGoogleCalendarService($credentials_path);

        // 2. If approved, remove them from their OLD event first
        if ($current_status === 'approved') {
            removeParticipantFromEvent($conn, $id, $service, $calendarId);
        }

        // 3. Update the database with new info
        $sql = "UPDATE workshop_table SET first_name = ?, last_name = ?, email = ?, workshop_title = ?, date = ?, time = ?, contact_number = ? WHERE id = ?";
        $conn->prepare($sql)->execute([$fname, $lname, $email, $title, $date, $time, $contact, $id]);

        // 4. If they were approved, re-add them to the NEW event
        if ($current_status === 'approved') {
            approveRegistration($conn, $id, $service, $calendarId, $timezone);
        }
        
        $message = "Registration updated successfully. Calendar event (if any) was moved/updated.";
    } catch (Exception $e) {
        $error = "Error updating registration: " . $e->getMessage();
    }
}

// --- FETCH ALL DATA ---
$stmt = $conn->prepare("SELECT * FROM workshop_table ORDER BY date DESC, time DESC");
$stmt->execute();
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Merriweather:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
</head>

<body class="admin-user-manager-page"> <header>
        <nav class="navbar navbar-expand-lg fixed-top admin-navbar"> 
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="main_menu_admin.php">
                    <img src="images/logo.svg" alt="Root Flowers Logo" class="navbar-logo me-2">
                    <span class="navbar-brand brand-logo-text text-white">Root Flowers Admin</span>
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarContent" aria-controls="adminNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars text-white"></i> 
                </button>

                <div class="collapse navbar-collapse" id="adminNavbarContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="btn btn-light rounded-pill ms-lg-3 px-3 py-2" href="main_menu_admin.php" title="Admin Home">
                                <i class="fas fa-home"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="btn btn-light rounded-pill ms-lg-3 px-3 py-2" role="button">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <section class="hero text-center text-white admin-hero-section">
        <div class="hero-content">
            <div class="container py-5">
                <h1 class="display-4 fw-bold text-white">Manage Workshop Registrations</h1>
                <p class="lead">Approve, edit, and manage all workshop signups.</p>
            </div>
    
            <div class="container admin-card-container">
                <div class="row justify-content-center">
                    <div class="col-lg-12"> 
                        <div class="card h-100 shadow-lg admin-card" style="border: none;">
                            <div class="card-body p-4 p-md-5">
                                
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title" style="font-size: 1.5rem; margin-bottom: 0;">All Registrations (<?php echo count($registrations); ?>)</h5>
                                    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addRegModal">
                                        <i class="fas fa-plus me-2"></i> Add Registration
                                    </button>
                                </div>
                                
                                <?php if ($message): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <?php echo $message; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>
                                <?php if ($error): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?php echo $error; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle admin-user-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Workshop</th>
                                                <th>Date & Time</th>
                                                <th>Contact</th>
                                                <th>Status</th>
                                                <th class="text-end" style="min-width: 250px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($registrations)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No registrations found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($registrations as $reg): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($reg['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($reg['workshop_title']); ?></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($reg['date']); ?><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars(date('h:i A', strtotime($reg['time']))); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($reg['contact_number']); ?></td>
                                                    <td>
                                                        <?php
                                                            $status = htmlspecialchars($reg['status']);
                                                            $badge_class = 'text-bg-secondary';
                                                            if ($status === 'approved') $badge_class = 'text-bg-success';
                                                            if ($status === 'pending') $badge_class = 'text-bg-warning';
                                                            if ($status === 'rejected') $badge_class = 'text-bg-danger';
                                                        ?>
                                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                                    </td>
                                                    <td class="text-end">
                                                        <form action="manage_workshop_reg.php" method="POST" class="d-inline-block">
                                                            <input type="hidden" name="id" value="<?php echo $reg['id']; ?>">
                                                            
                                                            <button type="submit" name="approve" class="btn btn-success btn-sm" title="Approve" 
                                                                <?php if ($reg['status'] === 'approved') echo 'disabled'; ?>>
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            
                                                            <button type="submit" name="reject" class="btn btn-warning btn-sm" title="Reject" 
                                                                <?php if ($reg['status'] === 'rejected') echo 'disabled'; ?>>
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                            
                                                            <input type="hidden" name="update_status" value="1">
                                                        </form>
                                                        
                                                        <button class="btn btn-primary btn-sm edit-btn" 
                                                                title="Edit Registration"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editRegModal"
                                                                data-id="<?php echo $reg['id']; ?>"
                                                                data-fname="<?php echo htmlspecialchars($reg['first_name']); ?>"
                                                                data-lname="<?php echo htmlspecialchars($reg['last_name']); ?>"
                                                                data-email="<?php echo htmlspecialchars($reg['email']); ?>"
                                                                data-title="<?php echo htmlspecialchars($reg['workshop_title']); ?>"
                                                                data-date="<?php echo htmlspecialchars($reg['date']); ?>"
                                                                data-time="<?php echo htmlspecialchars($reg['time']); ?>"
                                                                data-contact="<?php echo htmlspecialchars($reg['contact_number']); ?>"
                                                                >
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <a href="manage_workshop_reg.php?delete_id=<?php echo $reg['id']; ?>" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Delete Registration"
                                                        onclick="return confirm('Are you sure you want to delete this registration?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         </div>
    </section>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <div class="modal fade" id="addRegModal" tabindex="-1" aria-labelledby="addRegModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_workshop_reg.php" method="POST">
                    <div class="modal-header admin-navbar text-white">
                        <h5 class="modal-title" id="addRegModalLabel">Add New Registration</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            <div class="col-md-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-12">
                                <label for="workshop_title" class="form-label">Workshop Title</label>
                                <select id="workshop_title" name="workshop_title" class="form-select" required>
                                    <option value="" selected disabled>Choose...</option>
                                    <option value="Hobby Class">Hobby Class</option>
                                    <option value="Hand-tied Bouquet Course">Hand-tied Bouquet Course</option>
                                    <option value="Florist To Be 1">Florist To Be 1</option>
                                    <option value="Florist To Be 2">Florist To Be 2</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="col-md-4">
                                <label for="time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="time" name="time" required>
                            </div>
                            <div class="col-md-4">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="contact_number" name="contact_number">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_reg" class="btn btn-primary">Add Registration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editRegModal" tabindex="-1" aria-labelledby="editRegModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_workshop_reg.php" method="POST">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <div class="modal-header admin-navbar text-white">
                        <h5 class="modal-title" id="editRegModalLabel">Edit Registration</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                            </div>
                            <div class="col-md-12">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                            <div class="col-md-12">
                                <label for="edit_workshop_title" class="form-label">Workshop Title</label>
                                <select id="edit_workshop_title" name="workshop_title" class="form-select" required>
                                    <option value="Hobby Class">Hobby Class</option>
                                    <option value="Hand-tied Bouquet Course">Hand-tied Bouquet Course</option>
                                    <option value="Florist To Be 1">Florist To Be 1</option>
                                    <option value="Florist To Be 2">Florist To Be 2</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="edit_date" name="date" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="edit_time" name="time" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_contact_number" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="edit_contact_number" name="contact_number">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_reg" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- EDIT MODAL SCRIPT ---
            const editRegModal = document.getElementById('editRegModal');
            editRegModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                
                const id = button.getAttribute('data-id');
                const fname = button.getAttribute('data-fname');
                const lname = button.getAttribute('data-lname');
                const email = button.getAttribute('data-email');
                const title = button.getAttribute('data-title');
                const date = button.getAttribute('data-date');
                const time = button.getAttribute('data-time');
                const contact = button.getAttribute('data-contact');

                const modal = this;
                modal.querySelector('#edit_id').value = id;
                modal.querySelector('#edit_first_name').value = fname;
                modal.querySelector('#edit_last_name').value = lname;
                modal.querySelector('#edit_email').value = email;
                modal.querySelector('#edit_workshop_title').value = title;
                modal.querySelector('#edit_date').value = date;
                modal.querySelector('#edit_time').value = time;
                modal.querySelector('#edit_contact_number').value = contact;
            });
        });
    </script>
</body>
</html>