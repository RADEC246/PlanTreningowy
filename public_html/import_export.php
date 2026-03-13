<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';

function streamCsv(array $rows): string
{
    $output = fopen('php://memory', 'r+');
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    rewind($output);
    $content = stream_get_contents($output);
    fclose($output);
    return $content;
}

// Pobieramy wszystkie dane użytkownika.
$planStmt = $pdo->prepare("SELECT * FROM training_plans WHERE user_id = ?");
$planStmt->execute([$userId]);
$plans = $planStmt->fetchAll();

$workoutStmt = $pdo->prepare("
    SELECT w.*, tp.name AS plan_name
    FROM workouts w
    JOIN training_plans tp ON tp.id = w.plan_id
    WHERE tp.user_id = ?
");
$workoutStmt->execute([$userId]);
$workouts = $workoutStmt->fetchAll();

$exerciseStmt = $pdo->prepare("
    SELECT e.*, w.name AS workout_name, tp.name AS plan_name
    FROM exercises e
    JOIN workout_exercises we ON we.exercise_id = e.id
    JOIN workouts w ON w.id = we.workout_id
    JOIN training_plans tp ON tp.id = w.plan_id
    WHERE tp.user_id = ?
");
$exerciseStmt->execute([$userId]);
$exercises = $exerciseStmt->fetchAll();

$progressStmt = $pdo->prepare("
    SELECT p.*, e.name AS exercise_name, tp.name AS plan_name
    FROM progress p
    JOIN exercises e ON e.id = p.exercise_id
    JOIN workout_exercises we ON we.exercise_id = e.id
    JOIN workouts w ON w.id = we.workout_id
    JOIN training_plans tp ON tp.id = w.plan_id
    WHERE p.user_id = ?
    ORDER BY p.workout_date DESC
");
$progressStmt->execute([$userId]);
$progress = $progressStmt->fetchAll();

if (isset($_GET['export'])) {
    $format = strtolower($_GET['export']);
    $data = [
        'plans' => $plans,
        'workouts' => $workouts,
        'exercises' => $exercises,
        'progress' => $progress,
    ];

    if ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="training_export_' . date('Ymd_His') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($format === 'csv') {
        $rows = [['Ćwiczenie', 'Plan', 'Ciężar', 'Powtórzenia', 'Data']];
        foreach ($progress as $row) {
            $rows[] = [
                $row['exercise_name'],
                $row['plan_name'],
                $row['weight'],
                $row['reps'],
                $row['workout_date'],
            ];
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="progress_export_' . date('Ymd_His') . '.csv"');
        echo streamCsv($rows);
        exit;
    }

    $message = 'Nieobsługiwany format eksportu.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['import_file']) && $_FILES['import_file']['tmp_name']) {
    $file = $_FILES['import_file']['tmp_name'];
    $extension = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));

    try {
        if ($extension === 'json') {
            $payload = json_decode(file_get_contents($file), true);
            if (!is_array($payload) || !isset($payload['progress'])) {
                throw new RuntimeException('Niepoprawny format JSON.');
            }

            $imported = 0;
            foreach ($payload['progress'] as $entry) {
                if (!isset($entry['exercise_name'], $entry['weight'], $entry['reps'], $entry['workout_date'])) {
                    continue;
                }

                $exerciseId = $pdo->prepare("
                    SELECT e.id
                    FROM exercises e
                    JOIN workout_exercises we ON we.exercise_id = e.id
                    JOIN workouts w ON w.id = we.workout_id
                    JOIN training_plans tp ON tp.id = w.plan_id
                    WHERE e.name = ? AND tp.user_id = ?
                    LIMIT 1
                ");
                $exerciseId->execute([$entry['exercise_name'], $userId]);
                $exercise = $exerciseId->fetchColumn();
                if (!$exercise) {
                    continue;
                }

                $stmt = $pdo->prepare("
                    INSERT INTO progress (user_id, exercise_id, weight, reps, workout_date)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $exercise,
                    (float)$entry['weight'],
                    (int)$entry['reps'],
                    $entry['workout_date'],
                ]);
                $imported++;
            }
            $message = "Zaimportowano {$imported} zapisów z JSON.";
        } elseif ($extension === 'csv') {
            $handle = fopen($file, 'rb');
            $header = fgetcsv($handle);

            $columns = array_map('strtolower', $header ?: []);
            $exerciseIndex = array_search('ćwiczenie', $columns, true);
            $weightIndex = array_search('ciężar', $columns, true);
            $repsIndex = array_search('powtórzenia', $columns, true);
            $dateIndex = array_search('data', $columns, true);

            if ($exerciseIndex === false || $weightIndex === false || $repsIndex === false || $dateIndex === false) {
                throw new RuntimeException('Nagłówki CSV muszą zawierać Ćwiczenie, Ciężar, Powtórzenia i Data.');
            }

            $imported = 0;
            while (($row = fgetcsv($handle)) !== false) {
                $exerciseName = $row[$exerciseIndex] ?? '';
                $weight = $row[$weightIndex] ?? '';
                $reps = $row[$repsIndex] ?? '';
                $date = $row[$dateIndex] ?? '';

                $exerciseId = $pdo->prepare("
                    SELECT e.id
                    FROM exercises e
                    JOIN workout_exercises we ON we.exercise_id = e.id
                    JOIN workouts w ON w.id = we.workout_id
                    JOIN training_plans tp ON tp.id = w.plan_id
                    WHERE e.name = ? AND tp.user_id = ?
                    LIMIT 1
                ");
                $exerciseId->execute([$exerciseName, $userId]);
                $exercise = $exerciseId->fetchColumn();

                if (!$exercise || !$exerciseName || !is_numeric($weight) || !is_numeric($reps) || !$date) {
                    continue;
                }

                $stmt = $pdo->prepare("
                    INSERT INTO progress (user_id, exercise_id, weight, reps, workout_date)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $exercise,
                    (float)$weight,
                    (int)$reps,
                    $date,
                ]);
                $imported++;
            }
            fclose($handle);
            $message = "Zaimportowano {$imported} zapisów z CSV.";
        } else {
            throw new RuntimeException('Obsługujemy tylko CSV i JSON.');
        }
    } catch (Throwable $e) {
        $message = 'Import nie powiódł się: ' . $e->getMessage();
    }
}
?>
<link rel="stylesheet" href="style.css">

<h2>Import / eksport danych</h2>

<?php if ($message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<section>
    <h3>Eksport</h3>
    <p>Wybierz format:</p>
    <a href="?export=json">JSON</a>
    <a href="?export=csv">CSV</a>
</section>

<section>
    <h3>Import (CSV lub JSON)</h3>
    <p>Pliki CSV muszą mieć nagłówki: Ćwiczenie, Ciężar, Powtórzenia, Data.</p>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="import_file" accept=".csv,.json" required>
        <button type="submit">Załaduj plik</button>
    </form>
</section>

<br>

<a href="dashboard.php">Powrót do dashboard</a>
