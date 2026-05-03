<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
<table>
    <td>
        <h1>Вход в админ. панель</h1>
        <?php if (!empty($error)) echo "<div class='errors'>$error</div>"; ?>
        <form action="{{ route('login.file') }}" method="post" enctype="multipart/form-data">
            @csrf
            <label id="login_label">Логин:</label>
            <input type="text" name="login" required>
            <label id="password_label">Пароль:</label>
            <input type="password" name="password" required><br>
            <input type="submit" name="login_enter" value="Войти">
        </form>
    </td>
</table>
</body>
</html>
