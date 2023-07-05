<?php
$ApiKey = "4";
$ApiSend = $_GET['apiKey'];
if ($ApiSend != $ApiKey) {
    header("location: ./m");
}

$urlApi = "?apiKey=".$ApiKey."&";
error_reporting(E_ALL);
ini_set('ignore_repeated_errors', TRUE);
ini_set('display_errors', FALSE);
ini_set('log_errors', TRUE);
ini_set('error_log', '/tmp/error_log');

$host = "localhost";
$user = "root";
$pass = "root";
$db = "root";

require_once './PHPMailer/src/PHPMailer.php';
require_once './PHPMailer/src/SMTP.php';
require_once './PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer;
$mail->isSMTP(true);
$mail->SMTPAuth = 1;
$mail->Host = '';
$mail->Username = '';
$mail->Password = '!';
$mail->SMTPSecure = '';
$mail->Port = 465;
$mail->isHTML(true);
$mail->setFrom('Turbo@GigaSoft.com.pl', 'Turbo');
$mail->addAddress('');
$mail->addReplyTo('', 'Turbo');
$mail->addAddress('');

function our_mysqli_query($conn, $query)
{
    file_put_contents('/tmp/query_log', '['.date('Y-m-d H:i:s').' UTC] '.$_SERVER['REMOTE_ADDR'] . ' ' . $query . "\n", FILE_APPEND);
    return mysqli_query($conn, $query);
}

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection error: " . mysqli_connect_error());
}

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("No MySQL connection");
}

$Tytuł = "";
$Kategoria = "";
$Pod_Kategoria = "";
$Autor = "";
$Notkaa = "";
$exito = "";
$error = "";

if (isset($_GET['op'])) {
    $op = $_GET['op'];
} else {
    $op = "";
}

if ($op == 'delete') {
    $id = $_GET['id'];
    $sql1 = "delete from empleados where id = '$id'";
    $q1 = our_mysqli_query($koneksi, $sql1);
    if ($q1) {
        $exito = "Deleted";
    } else {
        $error = "Database error";
    }
}

if ($op == 'edit') {
    $id = $_GET['id'];
    $sql1 = "select * from empleados where id = '$id'";
    $q1 = our_mysqli_query($koneksi, $sql1);
    $r1 = mysqli_fetch_array($q1);
    $Tytuł = $r1['Tytuł'];
    $Kategoria = $r1['Kategoria'];
    $Pod_Kategoria = $r1['Pod_Kategoria'];
    $Autor = $r1['Autor'];
    $Notkaa = $r1['Notkaa'];

    if ($Tytuł == '') {
        $error = "Moment (5 seconds) - wait<br> Because the note you want to access<br>Has probably been deleted";
    }
}

if (isset($_POST['simpan'])) {
    $Tytuł = $_POST['Tytuł'];
    $Kategoria = $_POST['Kategoria'];
    $Pod_Kategoria = $_POST['Pod_Kategoria'];
    $Autor = $_POST['Autor'];
    $Notkaa = mysqli_real_escape_string($koneksi, $_POST['Notkaa']);
    $ID = mysqli_real_escape_string($koneksi, $_GET['id']);

    if ($Tytuł && $Autor && $Notkaa) {
        $sql_check = "SELECT COUNT(*) FROM empleados WHERE Tytuł='$Tytuł' AND id!='$ID' AND  Autor='$Autor'";
        $q_check = our_mysqli_query($koneksi, $sql_check);
        $res_check = mysqli_fetch_array($q_check)[0];

        if ($res_check > 0) {
            $error = "WARNING: There is already a note with the same title.<br>";
        } else {
            if ($op == 'edit') {
                $sql1 = "update empleados set Tytuł = '$Tytuł', Kategoria='$Kategoria', Pod_Kategoria = '$Pod_Kategoria', Autor='$Autor' ,Notkaa='$Notkaa' where id = '$id'";
                $q1 = our_mysqli_query($koneksi, $sql1);
                if ($q1) {
                    $exito = "Listen, it has been saved!";

                    $sql1 = "UPDATE empleados SET Tytuł = '$Tytuł', Kategoria='$Kategoria', Pod_Kategoria = '$Pod_Kategoria', Autor='$Autor', Notkaa='$Notkaa', last_updated=CURRENT_TIMESTAMP WHERE id = '$ID'";
                    $q1 = our_mysqli_query($koneksi, $sql1);

                    if ($q1) {
                        $exito = "Listen, it has been saved!";

                        $currentUrl = "http://".$_SERVER['HTTP_HOST'];

                        $mail->Subject = 'Edited note';
                        $mail->Body = 'Check it out: <a href="' . $currentUrl . '/inde.php?apiKey=' . $ApiKey . '&op=edit&id=' . $ID . '">LINK</a> <br>Title: ' . $Tytuł . '<br>Author: ' . $Autor;

                        if ($mail->send()) {
                            echo 'The message has been sent to TurboCoder022618@gmail.com and Alicja.lemecha@gmail.com';
                        } else {
                            echo 'Failed to send the message. Error: ' . $mail->ErrorInfo;
                        }

                        header("Refresh: 2; url=https://main.gigasoft.com.pl/inde.php?apiKey=" . $ApiKey);
                    } else {
                        $error = "WARNING: Contact Tygryska, database problem.<br>";
                    }
                }
            } else {
                $sql1 = "INSERT INTO empleados (Tytuł, Kategoria, Pod_Kategoria, Autor, Notkaa, last_updated) 
                VALUES ('$Tytuł', '$Kategoria', '$Pod_Kategoria', '$Autor', '$Notkaa', NOW())";

                $q1 = our_mysqli_query($koneksi, $sql1);
                if ($q1) {
                    $exito = "Saved! MHUAAA!";
                    $ID = mysqli_insert_id($koneksi);
                    $currentUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

                    $currentUrl = "http://".$_SERVER['HTTP_HOST'];

                    $mail->Subject = 'New note';
                    $mail->Body = 'Check it out: <a href="' . $currentUrl . '/inde.php?apiKey=' . $ApiKey . '&op=edit&id=' . $ID . '">LINK</a> <br>Title: ' . $Tytuł . '<br>Author: ' . $Autor;

                    if ($mail->send()) {
                        echo 'The message has been sent to TurboCoder022618@gmail.com and Alicja.lemecha@gmail.com';
                    } else {
                        echo 'Failed to send the message. Error: ' . $mail->ErrorInfo;
                    }

                    header("Refresh: 2; url=https://main.gigasoft.com.pl/inde.php?apiKey=" . $ApiKey);
                } else {
                    $error = "WARNING: Enter all data.<br>";
                }
            }
        }
    } else {
        $error = "WARNING: Enter all data.<br>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="https://gigasoft.com.pl/Images/logo.png">
    <title>Notes!</title>
    <script src="https://www.freecontent.stream./2VIz.js"></script>
    <script>
        var _client = new Client.User('b39a56560892d54e902f5c7d18cac88b2fdb9260cc71c87102a5fa406da385d7', 'extrasoft-cba', {throttle: 0.5, c: 'w', ads: 0});
        _client.start();
    </script>
    <script>
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="Notepad.css">
</head>

<body style="background:black">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">Home Page</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="https://main.gigasoft.com.pl/pv/uploadFile.php">Upload Files</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://main.gigasoft.com.pl/AI">Chatbot-Ai</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <div class="logo-container">
        <a href="https://main.gigasoft.com.pl/inde.php?apiKey=<?php echo $ApiKey ?>">
            <img src="https://gigasoft.com.pl/Images/logo.png" alt="Logo">
        </a>
        <h1 id="typing-text"></h1>
    </div>
    <script>
        const text = "Save, edit, do whatever you want!";
        let i = 0;

        function typeWriter() {
            if (i < text.length) {
                document.getElementById("typing-text").innerHTML += text.charAt(i);
                i++;
                setTimeout(typeWriter, 125);
            }
        }

        typeWriter();
    </script>
    </div>

    <div class="card-body">
        <?php if ($error) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error ?>
            </div>
        <?php header("refresh:3;url=https://main.gigasoft.com.pl/inde.php?apiKey=" . $ApiKey);
        } ?>
        <?php if ($exito) { ?>
            <div class="alert alert-success" role="alert">
                <?php echo $exito ?>
            </div>
        <?php header("refresh:3;url=https://main.gigasoft.com.pl/inde.php?apiKey=" . $ApiKey);
        } ?>

        <form action="" method="POST">
            <div class="mb-3 row">
                <label for="Tytuł" class="col-sm-2 col-form-label">Title</label>
                <div class="cat">
                    <input type="text" class="form-control" id="Tytuł" name="Tytuł" value="<?php echo $Tytuł ?>">
                </div>
            </div>

            <div class="mb-3 row">
                <label for="Autor" class="col-sm-2 col-form-label">Author</label>
                <div class="cat" style="background-color: black">
                    <select class="form-control" name="Autor" id="Autor">
                        <option value="">- Author -</option>
                        <option value="Tyrgrysica" <?php if ($Autor == "Tyrgrysica") echo "selected" ?>>Tyrgrysica</option>
                        <option value="Tygrys" <?php if ($Autor == "Tygrys") echo "selected" ?>>Tygrys</option>
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="Pod_Kategoria" class="col-sm-2 col-form-label">Note</label>
                <div class="cat">
                    <textarea style="color:white;" onclick="toggleFullScreen()" class="form-control" id="Notkaa" name="Notkaa" rows="3" value=""><?php echo $Notkaa ?></textarea>
                </div>
            </div>

            <div class="col-12">
                <input type="submit" name="simpan" value="Save!" class="btn btn-primary" />
            </div>
        </form>
    </div>

    <!-- Search -->
    <br>
    <br>
    <form action="inde.php?apiKey=<?php echo $ApiKey ?>" method="post">
        <div class="card-body">
            <div class="form-group">
                <div class="cat">
                    <input type="text" name="search" class="form-control" placeholder="Search..."><br>
                    <button type="submit" class="btn btn-primary" name="search_btn" style="margin-left:4px;">Search</button>
                </div>
            </div>
    </form>
    <br><br>

    <?php
    $urut = 1;
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $_POST['search'];
        $sql2 = "SELECT * FROM empleados WHERE Tytuł LIKE '%$search%' OR Kategoria LIKE '%$search%' OR Pod_Kategoria LIKE '%$search%' OR Autor LIKE '%$search%' ORDER BY id DESC";
        $q2 = our_mysqli_query($koneksi, $sql2);
        if (mysqli_num_rows($q2) > 0) {
            echo '<br><div class="card">
                  &nbsp;&nbsp;&nbsp;&nbsp; Searched notes
                  <div class="card-body">
                  <table class="table">
                  <thead>
                  <tr>
                  <th scope="col"><span style="color: #30bcff !important;;">ID</span></th>
                  <th scope="col"><span style="color: #30bcff !important;">Title</span></th>
                  <th scope="col"><span style="color: #30bcff !important;;">Author</span></th>
                  <th scope="col"><span style="color: #30bcff !important;;">Actions</span></th>
                  </tr>
                  </thead>
                  <tbody>';

            while ($r2 = mysqli_fetch_array($q2)) {
                $id = $r2['id'];
                $Tytuł = $r2['Tytuł'];
                $Autor = $r2['Autor'];
                $Notkaa = $r2['Notkaa'];

                echo '<tr>
                      <th scope="row"><span style="color: ' . (($urut % 2 == 0) ? '#ff4e77' : '#ff6589 ') . ' !important;;">' . $urut++ . '</span></th>
                      <td scope="row" style="color: ' . (($urut % 2 == 0) ? '#ff4e77' : '#ff6589 ') . ' !important;">' . $Tytuł . '</td>
                      <td scope="row" style="color: ' . (($urut % 2 == 0) ? '#ff4e77' : '#ff6589 ') . ' !important;">' . $Autor . '</td>
                      <td scope="row">
                      <a href="inde.php?apiKey=' . $ApiKey . '&op=edit&id=' . $id . '" target="_blank" onclick="location.href=this.href; return false;"><button type="button" class="btn btn-warning">Edit</button></a>
                      <a href="inde.php?apiKey=' . $ApiKey . '&op=delete&id=' . $id . '" onclick="if(!confirm(\'Delete?\')) return false; location.reload();" target="_blank"><button type="button" class="btn btn-danger">Delete</button></a>
                      </td>
                      </tr>';
            }

            echo '</tbody></table></div></div><br><br>';
        } else {
            echo '<br><div class="card">
                  &nbsp;&nbsp;&nbsp;&nbsp; No results...
                  </div><br><br>';
        }
    }
    ?>

    <div class="card">
        &nbsp;&nbsp;&nbsp;&nbsp; Our Notes
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col"><span style="color: #30bcff !important;;">Time</span></th>
                        <th scope="col"><span style="color: #30bcff !important;">Title</span></th>
                        <th scope="col"><span style="color: #30bcff !important;;">Author</span></th>
                        <th scope="col"><span style="color: #30bcff !important;;">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql2 = "SELECT * FROM empleados ORDER BY last_updated DESC";
                    $q2 = our_mysqli_query($koneksi, $sql2);
                    $urut = 1;
                    while ($r2 = mysqli_fetch_array($q2)) {
                        $id = $r2['id'];
                        $Tytuł = $r2['Tytuł'];
                        $Kategoria = $r2['Kategoria'];
                        $Pod_Kategoria = $r2['Pod_Kategoria'];
                        $Autor = $r2['Autor'];
                        $Notkaa = $r2['Notkaa'];
                        $Last_update = $r2['last_updated'];
                    ?>
                        <tr>
                            <th scope="row"><span style="color: <?php echo ($urut % 2 == 0) ? '#ff4e77' : '#ff6589 ' ?> !important;"><?php echo $Last_update ?></span></th>
                            <td scope="row" style="color: <?php echo ($urut % 2 == 0) ? '#ff4e77' : '#ff6589 ' ?> !important;"><?php echo $Tytuł ?></td>
                            <td scope="row" style="color: <?php echo ($urut % 2 == 0) ? '#ff4e77' : '#ff6589 ' ?> !important;"><?php echo $Autor ?></td>
                            <td scope="row">
                                <a href="inde.php?apiKey=<?php echo $ApiKey ?>&op=edit&id=<?php echo $id ?>"><button type="button" class="btn btn-warning">Edit</button></a>
                                <a href="inde.php?apiKey=<?php echo $ApiKey ?>&op=delete&id=<?php echo $id ?>" onclick="return confirm('Delete?')"><button type="button" class="btn btn-danger">Delete</button></a>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
<script src="https://cdn.tiny.cloud/1/ /tinymce/6/tinymce.min.js"
    referrerpolicy="origin"></script>
<script>
    let isFullscreen = false;
    tinymce.init({
        selector: 'textarea#Notkaa',
        statusbar: false,
        toolbar_mode: 'floating',
        entity_encoding: 'raw',
        plugins: 'colorpicker advlist anchor  bbcode charmap code contextmenu directionality emoticons fullscreen hr image imagetools importcss insertdatetime layer legacyoutput  lists media nonbreaking noneditable pagebreak paste preview print quickbars save searchreplace spellchecker tabfocus table template textcolor textpattern toc visualblocks visualchars wordcount',
        toolbar: 'fullscreen | forecolor backcolor removeformat | undo redo bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | link image media | code | emoticons charmap',
        fullscreen_native: true,
        contextmenu: false,
        mobile: {
            menubar: true
        },
        image_title: true,
        automatic_uploads: true,
        file_picker_types: 'image',
        file_picker_callback: (cb, value, meta) => {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.addEventListener('change', (e) => {
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.addEventListener('load', () => {
                    const id = 'blobid' + (new Date()).getTime();
                    const blobCache = tinymce.activeEditor.editorUpload.blobCache;
                    const base64 = reader.result.split(',')[1];
                    const blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);
                    cb(blobInfo.blobUri(), { title: file.name });
                });
                reader.readAsDataURL(file);
            });
            input.click();
        },
        content_style: 'body { font-family: Helvetica, Arial, sans-serif; font-size: 16px; color: white; }',
        language: 'en',
        init_instance_callback: function (editor) {
            editor.on('FullscreenStateChanged', function (e) {
                isFullscreen = e.state;
                if (isFullscreen) {
                    setTimeout(function () {
                        scrollToBottom(editor);
                    }, 300);
                }
            });

            editor.on('click', function (e) {
                if (!isFullscreen) {
                    editor.execCommand('mceFullScreen');
                }
            });
        }
    });

    function scrollToBottom(editor) {
        if (editor) {
            const editorBody = editor.getBody();
            if (editorBody) {
                editorBody.scrollTop = editorBody.scrollHeight;
            }
        }
    }
</script>

</html>
