<?php
// code chức năng xóa user :

function user_delete($id)
{
	global $conn;
	$id = intval($id);
	$sql = "DELETE FROM users WHERE id=$id";
	mysqli_query($conn, $sql) or die(mysqli_error($conn));
}


session_start();
include('functions.php');

empty($_GET['user_id']) ? header('location: list.php') : $encode_user_id = $_SESSION['info_user_id'][$_GET['user_id']];

$user_id = intval($encode_user_id);
if ($_SESSION['user']['user_type'] == "admin") {
    if ($_SESSION['user']['id'] != $user_id) {
        user_delete($user_id);
    }
}

header('location:list.php');

// code chức năng cập nhật thông tin user :

function edit($user_id)
{
	global $conn, $errors, $username, $fullname, $email;

	$username = escape($_POST['username']);
	$email = escape($_POST['email']);
	$fullname = escape($_POST['fullnme']);
	//make sure form is filled property
	if (empty($username)) {
		array_push($errors, "Username is required");
	}
	if (empty($email)) {
		array_push($errors, "Email is required");
	}
	if (empty($fullname)) {
		array_push($errors, "Fullname is required");
	}

	$status_entities = 0;
	$array_entities = array(
		'&', '<', '>', "'", '"', '/'
	);
	foreach ($array_entities as $entitie) {
		if (strlen(strstr(escape($_POST['fullname1']), $entitie)) > 0 || strlen(strstr(escape($_POST['username1']), $entitie)) > 0) {
			$status_entities = 1;
		}
	}
	if ($status_entities == 1) {
		$_SESSION['mess_entities'] = "Chuỗi bạn nhập vào có ký tự bị cấm. Không thể lưu lại thay đổi";
		header('location: index.php');
	} else {
		$username    =  escape($_POST['username1']);
		$fullname    =  escape($_POST['fullname1']);
		$email       =  escape($_POST['email1']);
		$version	 =  intval($_SESSION['version_update'] + 1);
		mysqli_query($conn, "UPDATE `users` SET `username` = '$username', `fullname` = '$fullname', `email`='$email',
	    `version`='$version' WHERE `id` = '$user_id'");

		$_SESSION['success']  = "Change successfully";
		if (isset($_COOKIE["user"]) and isset($_COOKIE["pass"])) {
			setcookie("user", '', time() - 3600);
			setcookie("pass", '', time() - 3600);
		}

		unset($_SESSION['version_update']); // update version edit 
		$_SESSION['success'] = "This User has successfully changed";
		header('location: list.php');
	}
}

// code thực hiện Edit :
<?php
session_start();

if (isset($_GET['edit'])) {
    $link_edit = $_GET['edit'];
    $encode_link = $_SESSION['links_edit'][$link_edit]; //lay gia tri value tu key là $link_edit
    $_SESSION['result_link'] = $link_edit;
} else header('location: home.php');

$user_id = intval($encode_link);


if ($_SESSION['user']['id'] != $user_id && $_SESSION['user']['user_type'] != 'admin') {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}

include('functions.php');

$result = [];
$userName = "";
$fullName = "";
$userEmail = "";
if (isset($_GET['edit'])) {
    if (isLoggedIn()) {
        $query = "SELECT * FROM users WHERE id=" . $user_id;
        $result = mysqli_query($conn, $query);
        $data = mysqli_fetch_assoc($result);
    }
}

//đặt sesion cho đổi mật khẩu với ngườI dùng là user
$_SESSION['user_change'] = getLink($data['id']);
$_SESSION['user_change_id'][$_SESSION['user_change']] = $data['id'];

//đặt sesion cho đổi mật khẩu với ngườI dùng là admin
$_SESSION['admin_change'] = getLink($data['id']);
$_SESSION['admin_change_id'][$_SESSION['admin_change']] = $data['id'];

//đặt session kiểm tra version cho chức năng chỉnh sửa thông tin
$_SESSION['version_update'] = $data['version'];
?>

<html>

<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>

<body>
    <div class="header">
        <h2>Edit User</h2>
    </div>
    </form>

    <form method="post" action="update.php?update=<?= $link_edit ?>">
        <?php echo display_error(); ?>

        <div class="input-group">
            <label>Username</label>
            <input required type="text" name="username1" value="<?php echo $data['username']; ?>" placeholder="Enter username">
        </div>
        <div class="input-group">
            <label>Full Name</label>
            <input required type="text" name="fullname1" value="<?php echo $data['fullname']; ?>" placeholder="Enter fullname">
        </div>
        <div class="input-group">
            <label>Email</label>
            <input required type="email" name="email1" value="<?php echo $data['email']; ?>" placeholder="<?php echo $data['email']; ?>">
        </div>
        <div class="input-group">
            <button type="submit" class="btn" name="save_btn">Save</button>
        </div>
    </form>
    <div class="back" style="text-align: center; padding-top: 10px;">
        <button type="button" class="btn btn-info" onClick="javascript:history.go(-1)">Back</button>
        <?php if (isAdmin()) : ?>
            <a type="button" class="btn btn-info" href="change-password/admin-change.php?code=<?= $_SESSION['admin_change'] ?>">Change Password</a>
        <?php else : ?>
            <a type="button" class="btn btn-info" href="change-password/user-change.php?code=<?= $_SESSION['user_change'] ?>">Change Password</a>
        <?php endif; ?>
    </div>

</body>

</html>

// code chức năng tìm kiếm :
isset($_GET['search']) ? $search = addslashes($_GET['search']) : $search = "";
$options_search = array(
    'where' => "username LIKE '%" . ($search) . "%' or fullname like '%" . ($search) . "%'",
    'limit' => $limit,
    'offset' => $offset,
    'order_by' => 'id ASC'
);
$query = "SELECT * FROM users WHERE username LIKE '%$search%' OR fullname LIKE '%$search%' OR email LIKE  '%$search%'";
global $conn;
$sql = mysqli_query($conn, $query);
$num = mysqli_num_rows($sql);
?>
