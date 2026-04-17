<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body class="login-body" style="margin:0px;">
    <div class="login-wrap">    
        <h1>Log in</h1>
        <form action="proses.php" method="POST"> 
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <button type="submit" name="login">Log in</button>
        </form> 

        <div>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>    
</body>
</html>