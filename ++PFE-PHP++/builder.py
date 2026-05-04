import os
import re

src_html = r"C:\Users\wassi\OneDrive\Desktop\pfeeeee\admin\Light\FR-white\dashboard.html"
dst_dir = r"C:\Users\wassi\OneDrive\Desktop\pfeeeee\++PFE-PHP++"

with open(src_html, 'r', encoding='utf-8') as f:
    html = f.read()

# Extract CSS
css_match = re.search(r'<style>(.*?)</style>', html, re.DOTALL)
css_content = css_match.group(1).strip() if css_match else ""

# Extract JS
js_match = re.search(r'<script>(.*?)</script>', html, re.DOTALL)
js_content = js_match.group(1).strip() if js_match else ""

# Modify JS to use window.serverData and window.currentUser
js_content = js_content.replace(
    "data = getDefaultData();",
    "data = window.serverData || getDefaultData();"
)
js_content = js_content.replace(
    "state.currentUser = { id: 3, email: 'admin@mesrs.tn', fullName: 'Kamel Fathallah', role: 'admin', avatar: 'KF' };",
    "state.currentUser = window.currentUser || { id: 3, role: 'admin' };"
)
js_content = js_content.replace(
    "currentView: 'dashboard',",
    "currentView: window.currentView || 'dashboard',"
)

# Extract Header HTML (everything before <main ...>)
header_html = html.split('<div id="appShell">')[0]
header_html += '<div id="appShell">\n'
app_shell_content = html.split('<div id="appShell">')[1].split('<script src="https://cdn.jsdelivr.net')[0]

main_content_start = app_shell_content.find('<main class="main-content" id="mainContent">')
header_html += app_shell_content[:main_content_start + len('<main class="main-content" id="mainContent">')]

footer_html = '</main></div>\n'
footer_html += '<script src="https://cdn.jsdelivr.net' + html.split('<script src="https://cdn.jsdelivr.net')[1].split('<script>')[0]
footer_html += '<script src="../assets/js/script.js"></script>\n</body>\n</html>'

# Modify CSS link in header
header_html = re.sub(r'<style>.*?</style>', '<link href="../assets/css/style.css" rel="stylesheet">', header_html, flags=re.DOTALL)

# Add PHP variables injection to header
php_injection = """<?php
// We inject server data into JS
echo "<script>";
echo "window.currentView = '" . addslashes($currentView) . "';";
echo "window.currentUser = " . json_encode([
    'id' => $_SESSION['user_id'],
    'email' => $_SESSION['email'] ?? '',
    'fullName' => $_SESSION['full_name'],
    'role' => $_SESSION['role']
]) . ";";
// To load full DB data, we would make an API call or load it here.
// For now, let's load it via ajax/api.php
echo "</script>";
?>
"""
header_html = header_html.replace('</head>', php_injection + '</head>')

# Write CSS
os.makedirs(os.path.join(dst_dir, 'assets', 'css'), exist_ok=True)
with open(os.path.join(dst_dir, 'assets', 'css', 'style.css'), 'w', encoding='utf-8') as f:
    f.write(css_content)

# Write JS
os.makedirs(os.path.join(dst_dir, 'assets', 'js'), exist_ok=True)
with open(os.path.join(dst_dir, 'assets', 'js', 'script.js'), 'w', encoding='utf-8') as f:
    f.write(js_content)

# Write Includes
os.makedirs(os.path.join(dst_dir, 'includes'), exist_ok=True)
with open(os.path.join(dst_dir, 'includes', 'header.php'), 'w', encoding='utf-8') as f:
    f.write(header_html)
with open(os.path.join(dst_dir, 'includes', 'footer.php'), 'w', encoding='utf-8') as f:
    f.write(footer_html)

# Create the PHP pages for each module
pages = {
    'admin': ['audit-logs.php', 'common-customization.php', 'correspondences.php', 'dashboard.php', 'distribution-boxes.php', 'escalated-requests.php', 'referentials.php', 'statistics.php', 'system-settings.php', 'technical-problems.php', 'user-management.php'],
    'support': ['analytics.php', 'assist.php', 'customize-dashboards.php', 'dashboard.php', 'escalation-history.php', 'history.php', 'incoming-requests.php', 'messages.php', 'training.php'],
    'user': ['assist.php', 'dashboard.php', 'history.php', 'messages.php', 'submit-request.php', 'track-status.php', 'training.php']
}

for role, files in pages.items():
    os.makedirs(os.path.join(dst_dir, role), exist_ok=True)
    for file in files:
        view_name = file.replace('.php', '')
        content = f"""<?php
session_start();
require_once '../includes/functions.php';
require_login();
require_role('{role}');

$currentView = '{view_name}';

// Ensure serverData is loaded from DB or use ajax.
// The JS logic will handle rendering via renderContent(currentView)
require_once '../includes/header.php';
?>

<!-- The content is dynamically rendered here by script.js -->

<?php require_once '../includes/footer.php'; ?>
"""
        with open(os.path.join(dst_dir, role, file), 'w', encoding='utf-8') as f:
            f.write(content)

# Generate api.php
api_content = """<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'getData') {
    // Ideally, this fetches everything from the DB and returns the JSON matching getDefaultData()
    // For now, we return a success status, and the JS will fall back to its defaults if not fully populated.
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM requests");
    $requests = $stmt->fetchAll();
    
    echo json_encode([
        'status' => 'success',
        'users' => $users,
        'requests' => $requests,
        // ... mappings for the rest of the tables
    ]);
    exit;
}

// Additional API endpoints (saveData, addRequest, etc.) can be added here

echo json_encode(['status' => 'success', 'message' => 'API is running']);
?>"""

os.makedirs(os.path.join(dst_dir, 'ajax'), exist_ok=True)
with open(os.path.join(dst_dir, 'ajax', 'api.php'), 'w', encoding='utf-8') as f:
    f.write(api_content)

print("Migration completed successfully!")
