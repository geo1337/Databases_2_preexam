<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

include 'config.php';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$currentUser = $_SESSION['username'];

// Fetch current user's role
$stmt = $conn->prepare("
    SELECT u.id, u.username, r.role_name 
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.username = ?
");
$stmt->bind_param("s", $currentUser);
$stmt->execute();
$stmt->bind_result($userId, $username, $currentRole);
$stmt->fetch();
$stmt->close();

// Fetch all users for the table
$result = $conn->query("
    SELECT u.id, u.username, u.email, u.created_at, r.role_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    ORDER BY u.id ASC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

 <style>
    table {
    table-layout: fixed;
    width: 100%;
}

td {
    width: 200px !important;
    max-width: 200px !important;
}
 </style>
</head>
<body class="p-4 bg-light">

<div class="container bg-white p-4 rounded shadow">
  <h2 class="mb-4">Welcome, <?= htmlspecialchars($currentUser) ?> (<?= htmlspecialchars($currentRole) ?>)</h2>

    <a href="logout.php" class="btn btn-danger mb-3">Logout</a>
   <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success" id="delete-success">
        ✅ User deleted successfully.
    </div>
    <script>
        setTimeout(function () {
            const alertBox = document.getElementById("delete-success");
            if (alertBox) {
                alertBox.style.transition = "opacity 0.5s ease-out";
                alertBox.style.opacity = 0;
                setTimeout(() => alertBox.remove(), 500);
            }
        }, 5000);
    </script>
  
    <?php endif; ?>
<div class="input-group mb-3 w-50">
  <span class="input-group-text" id="search-icon">
    <i class="bi bi-search"></i>
  </span>
  <input type="text" class="form-control" placeholder="Search users..." id="tableSearch">
</div>

    <table class="table table-bordered table-hover" id="our_table">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Created At</th>
                <th>Delete User</th>
            </tr>
        </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr data-user-id="<?= $row['id'] ?>">
        <td><?= $row['id'] ?></td>

   <td>
    <div class="d-flex align-items-center gap-2">
        <span class="editable username"><?= htmlspecialchars($row['username']) ?></span>
        <?php if ($row['username'] !== $currentUser): ?>
            <button type="button" class="btn btn-sm btn-outline-secondary edit-btn" data-field="username" title="Edit Username">
                <i class="bi bi-pencil"></i>
            </button>
        <?php endif; ?>
    </div>
</td>

<td>
    <div class="d-flex align-items-center gap-2">
        <span class="editable email"><?= htmlspecialchars($row['email']) ?></span>
        <button type="button" class="btn btn-sm btn-outline-secondary edit-btn" data-field="email" title="Edit Email">
            <i class="bi bi-pencil"></i>
        </button>
    </div>
</td>


        <td><?= $row['created_at'] ?></td>

        <td>
            <?php if ($row['username'] !== $currentUser): ?>
                <a href="delete_user.php?id=<?= $row['id'] ?>"
                   onclick="return confirm('Are you sure you want to delete this user?');"
                   class="btn btn-sm btn-outline-danger"
                   title="Delete">
                    <i class="bi bi-trash"></i>
                </a>
            <?php else: ?>
                <button class="btn btn-sm btn-outline-secondary" title="You cannot delete your own account" disabled>
                    <i class="bi bi-trash"></i>
                </button>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</tbody>



    </table>

<button onclick="exportTableToExcel('our_table', 'Database')" class="btn btn-success">
  <i class="bi bi-download me-1"></i> Export
</button>

</div>
<script>
    document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function () {
        const field = this.dataset.field;
        const row = this.closest('tr');
        const userId = row.dataset.userId;
        const span = row.querySelector(`.${field}`);
        const originalText = span.textContent.trim();

        if (row.querySelector(`input[name="${field}"]`)) return;

        const input = document.createElement('input');
        input.type = field === 'email' ? 'email' : 'text';
        input.name = field;
        input.value = originalText;
        input.className = 'form-control form-control-sm';
        input.style.maxWidth = '200px';

        span.replaceWith(input);
        input.focus();

        const replaceWithSpan = (newValue) => {
            const newSpan = document.createElement('span');
            newSpan.className = `editable ${field}`;
            newSpan.textContent = newValue;
            if (input.parentNode) {
                input.replaceWith(newSpan);
            }
        };

        const handleEnter = (newValue) => {
            fetch('edit_user_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId, field: field, value: newValue })
            })
            .then(res => {
                if (!res.ok) throw new Error('Network response not OK');
                return res.json();
            })
            .then(data => {
                replaceWithSpan(data.success ? newValue : originalText);
                if (!data.success) alert("❌ " + data.error);
            })
            .catch((err) => {
                console.error(err);
                replaceWithSpan(originalText);
                alert("❌ Network error");
            });
        };

        const onKeydown = (e) => {
            if (e.key === 'Enter') {
                input.removeEventListener('blur', onBlur); // ✅ Prevent double replace
                const newValue = input.value.trim();
                if (newValue === "" || newValue === originalText) {
                    replaceWithSpan(originalText);
                    return;
                }
                handleEnter(newValue);
            } else if (e.key === 'Escape') {
                input.removeEventListener('blur', onBlur); // ✅ Prevent double replace
                replaceWithSpan(originalText);
            }
        };

        const onBlur = () => {
            replaceWithSpan(originalText);
        };

        input.addEventListener('keydown', onKeydown);
        input.addEventListener('blur', onBlur);
    });
});

</script>
<script>

function exportTableToExcel(tableId, filename = 'data') {
    const table = document.getElementById(tableId);
    const data = [];

    const rows = table.querySelectorAll('tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = [];

        for (let i = 0; i < cells.length - 1; i++) { // exclude last column
            rowData.push(cells[i].innerText);
        }

        data.push(rowData);
    });

    const ws = XLSX.utils.aoa_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Exported_database");
    XLSX.writeFile(wb, `${filename}.xlsx`);
}

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("tableSearch");
    const table = document.getElementById("our_table"); // Replace with actual ID
    const rows = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");

    searchInput.addEventListener("input", function () {
        const filter = searchInput.value.toLowerCase();

        Array.from(rows).forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });
});
</script>

</body>
</html>
