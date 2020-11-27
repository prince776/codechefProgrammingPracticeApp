<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Sign up
$app->get('/api/user/signup', function(Request $req, Response $res)
{
    $username = $req->getParam('username');
    $password = $req->getParam('password');

    if ($username == NULL)
        return sendError($res, "username not provided");
    if ($password == NULL)
        return sendError($res, "password not provided");

    // Verify the username is new
    try
    {
        $sql = "SELECT username FROM Users WHERE username = '$username'";
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $user = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;

        if ($user != NULL)
            return sendError($res, "Username already taken");        
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }

    $passwordEnc = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO Users (username, password) VALUES
    (:username, :password)";

    // Register user in DB
    @$userID = "";
    try
    {
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $passwordEnc);

        $stmt->execute();
        $userID = $db->lastInsertId();
        $db = null;
        // User registered successfully
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }

    // Now return an auth token
    $tokenSeed = date('Y-m-d H:i:s');
    $tokenSeed .= $username;

    $token = password_hash($tokenSeed, PASSWORD_DEFAULT);
    
    // Delete all old sessions first
    try
    {
        $sql = "DELETE FROM UserSessions WHERE username = '$username'";
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->query($sql);
        $db = null;
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured in DB");
    }

    $sql = "INSERT INTO UserSessions (token, username, userID) VALUES
    (:token, :username, :userID)";
    try
    {
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':userID', $userID);

        $stmt->execute();
        $db = null;
        
        @$data->username = $username;
        $data->token = $token;

        // Cookies not working /shrug
        // $setcookies = new Slim\Http\Cookies();
        // $setcookies->set('token',['value' => $token, 'expires' => '30 days']);
        // $res = $res->withHeader('Set-Cookie', $setcookies->toHeaders());

        return sendJson($res, $data, "Sign up Successful");
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "User reigstered but couldn't log in, please try to login again");
    }
});

// Login
$app->get('/api/user/login', function(Request $req, Response $res)
{
    $username = $req->getParam('username');
    $password = $req->getParam('password');

    if ($username == NULL)
        return sendError($res, "username not provided");
    if ($password == NULL)
        return sendError($res, "password not provided");

    // Verify the username and password
    @$userID = "";
    try
    {
        $sql = "SELECT id, username, password FROM Users WHERE username = '$username'";
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $user = $stmt->fetchAll(PDO::FETCH_OBJ);

        $db = null;

        if ($user == NULL)
            return sendError($res, "Username doesn't exists");
        if (!password_verify($password, $user[0]->password))
            return sendError($res, "Wrong password entered");
        $userID = $user[0]->id;
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }

    $tokenSeed = date('Y-m-d H:i:s');
    $tokenSeed .= $username;

    $token = password_hash($tokenSeed, PASSWORD_DEFAULT);
    
    // Delete all old sessions first
    try
    {
        $sql = "DELETE FROM UserSessions WHERE username = '$username'";
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->query($sql);
        $db = null;
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }

    $sql = "INSERT INTO UserSessions (token, username, userID) VALUES
    (:token, :username, :userID)";
    try
    {
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':userID', $userID);

        $stmt->execute();
        $db = null;
        
        @$data->username = $username;
        $data->token = $token;

        // Cookies not working /shrug
        // $setcookies = new Slim\Http\Cookies();
        // $setcookies->set('token',['value' => $token, 'expires' => '30 days']);
        // $res = $res->withHeader('Set-Cookie', $setcookies->toHeaders());

        return sendJson($res, $data, "Login Successful");
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }
});

// Authenticate session
$app->get('/api/user/authenticate', function(Request $req, Response $res)
{
    $token = $req->getParam('token');

    if ($token == NULL)
        return sendError($res, "No Token provided");

    // Verify the usersession
    try
    {
        $sql = "SELECT username FROM UserSessions WHERE token = '$token'";
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $username = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        if ($username == NULL)
            return sendError($res, "Session doesn't exists");
        $username = $username[0];
        @$data->username = $username->username;
        $data->authenticated = true;

        return sendJson($res, $data, "Authentication successful");
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }
});

// Logout
$app->get('/api/user/logout', function(Request $req, Response $res)
{
    $token = $req->getParam('token');

    if ($token == NULL)
        return sendError($res, "Not logged in", "No token provided");

    // Delete the usersession (and if not exists, just say logged out)
    try
    {
        $sql = "DELETE FROM UserSessions WHERE token = '$token'";
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $db = null;

        return sendJson($res, "Logged out successfully");
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }
});