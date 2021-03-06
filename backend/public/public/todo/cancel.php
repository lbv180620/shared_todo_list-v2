<?php

/** auth */

require_once dirname(__FILE__, 4) . '/vendor/autoload.php';

use App\Utils\SessionUtil;
use App\Utils\Common;
use App\Models\Base;
use App\Models\Users;

SessionUtil::sessionStart();

// ログインチェック
if (!Common::isAuthUser()) {
    header('Location: ../login/login_form.php', true, 301);
    exit;
}

// ログイン情報取得
$login = isset($_SESSION['login']) ? $_SESSION['login'] : null;

// GET送信の値を取得
$login_id = $_GET['login_id'];

try {

    $base = Base::getPDOInstance();

    // ログインユーザーのレコードを1件取得
    $users_table = new Users($base);
    $user = $users_table->getUserById($login_id);
} catch (\PDOException $e) {

    $_SESSION['err']['msg'] = Config::MSG_PDOEXCEPTION_ERROR;
    Logger::errorLog(Config::MSG_PDOEXCEPTION_ERROR, ['file' => __FILE__, 'line' => __LINE__]);
    header('Location: ../error/error.php', true, 301);
    exit;
} catch (\Exception $e) {

    $_SESSION['err']['msg'] = Config::MSG_EXCEPTION_ERROR;
    Logger::errorLog(Config::MSG_EXCEPTION_ERROR, ['file' => __FILE__, 'line' => __LINE__]);
    header('Location: ../error/error.php', true, 301);
    exit;
}

# 失敗メーセージの初期化
$err_msg = isset($_SESSION['err']) ? $_SESSION['err'] : null;
unset($_SESSION['err']);

// ワンタイムトークン生成
$token = Common::generateToken();

?>

<?php

/**
 * headとヘッダー(ナビバー)部分
 *
 */

$title = "退会確認";
$active = "show";
$search = "";
include_once dirname(__FILE__, 3) . '/components/head/auth/head.php';

?>

<!-- コンテナ -->
<div class="container">

    <!-- エラメッセージアラート -->
    <?php include_once dirname(__FILE__, 3) . '/components/alert/auth/alert_err_msg.php' ?>

    <div class="row my-2">
        <div class="col-sm-3"></div>
        <div class="col-sm-6 alert alert-danger">
            <p><?= Common::h($user['user_name']) ?>さん本人であることを確認して、このまま退会しますか？</p>
            <form action="./cancel_action.php" method="post" onsubmit="return checkSubmit()">
                <!-- トークン送信 -->
                <input type="hidden" name="token" value="<?= Common::h($token) ?>">
                <!-- ログインユーザのidを送信 -->
                <input type="hidden" name="login_id" value="<?= Common::h($login_id) ?>">
                <input type="submit" class="btn btn-danger" value="退会">
                <input type="button" value="キャンセル" class="btn btn-success" onclick="location.href='./top.php';">
            </form>
        </div>
        <div class="col-sm-3"></div>
    </div>
</div>
<!-- コンテナ ここまで -->

<?php

/**
 * 確認ダイアログ部分
 */

$message = '本当に退会しますか?';
include_once dirname(__FILE__, 3) . '/components/confirm/auth/js_confirm.php';

?>


<?php

/**
 * フッター部分
 */

include_once dirname(__FILE__, 3) . '/components/foot/auth/foot.php';

?>
