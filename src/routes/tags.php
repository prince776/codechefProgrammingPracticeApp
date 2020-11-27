<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get tags, specify tag name(to get only similar results), limit (default = 20) and offset (default = 0)
$app->get('/api/tags', function(Request $req, Response $res)
{
    $limit = $req->getParam('limit');
    $offset = $req->getParam('offset');
    $name = $req->getParam('name');

    if ($limit == NULL) $limit = LIMIT;
    if ($offset == NULL) $offset = OFFSET;
    if ($name == NULL) $name = "";

    $userID = 0; // The default one
    // Get user ID
    $token = $req->getParam('token');
    if ($token != NULL)
    {
        try
        {
            $sql = "SELECT userID FROM UserSessions WHERE token = '$token'";
            $db = new db();
            $db = $db->connect();

            $stmt = $db->query($sql);
            $uid = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            if ($uid != NULL)
                $userID = $uid[0]->userID;
        }
        catch (PDOException $e)
        {
            return sendError($res, $e, "Error occured");
        }
    }

    $sql = "SELECT * FROM Tags WHERE (name LIKE '$name%' AND userID in (0, $userID)) LIMIT $limit OFFSET $offset";

    try
    {
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->query($sql);
        $tags = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        return sendJson($res, $tags, "Tags matching name sent successfully");
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }
});

// Get tags of a specefic type (author/actual_tag/private_tag), limit (default = 20) and offset (default = 0)
$app->get('/api/tags/{type}', function(Request $req, Response $res)
{
    $type = $req->getAttribute('type');
    $limit = $req->getParam('limit');
    $offset = $req->getParam('offset');

    if ($limit == NULL) $limit = LIMIT;
    if ($offset == NULL) $offset = OFFSET;

    $userID = 0; // The default one
    // Get user ID
    $token = $req->getParam('token');
    if ($token != NULL)
    {
        try
        {
            $sql = "SELECT userID FROM UserSessions WHERE token = '$token'";
            $db = new db();
            $db = $db->connect();

            $stmt = $db->query($sql);
            $uid = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            if ($uid != NULL)
                $userID = $uid[0]->userID;
        }
        catch (PDOException $e)
        {
            return sendError($res, $e, "Error occured");
        }
    }
    $sql = "SELECT * FROM Tags WHERE (type = '$type' AND userID in (0, $userID)) LIMIT $limit OFFSET $offset";
    try
    {
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->query($sql);
        $tags = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        if ($tags == NULL) return sendError($res, "type not found");

        return sendJson($res, $tags, "Tag matching type sent successfully");
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }
});

// Add a private tag(pre existing/ new, will auto detect) to a problem
$app->post('/api/tags/addToProblem', function(Request $req, Response $res)
{
    // Get used id
    $userID = 0; 
    $token = $req->getParam('token');
    if ($token == NULL)
        return sendError($res, "No user logged in", "Token not provided");
    try
    {
        $sql = "SELECT userID FROM UserSessions WHERE token = '$token'";
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $uid = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        if ($uid == NULL)
            return sendError($res, "Session expired, please re login");

        $userID = $uid[0]->userID;
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }

    // Get tag ID for the private tag provided, if doesn't exist make one
    $tag = $req->getParam('tag');
    $tagID = 0;
    $prevProblemCount = 0;
    if ($tag == NULL) return sendError($res, "No tag provided");
    try
    {
        $sql = "SELECT id, problem_count FROM Tags WHERE (name = '$tag' AND userID = $userID)";
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $tid = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        if ($tid == NULL)
        {
            // Make a new private tag
            $sql = "INSERT INTO Tags (name, problem_count, type, userID) VALUES
            (:name, :problem_count, :type, :userID)";
            try
            {
                $db = new db();
                $db = $db->connect();
                
                $stmt = $db->prepare($sql);
                
                $tag_type = "private_tag";

                $stmt->bindParam(':name', $tag);
                $stmt->bindParam(':problem_count', $prevProblemCount);
                $stmt->bindParam(':type', $tag_type);
                $stmt->bindParam(':userID', $userID);
        
                $stmt->execute();
                $tagID = $db->lastInsertId();
                $db = null;
                // Tag added successfully
            }
            catch (PDOException $e)
            {
                return sendError($res, $e, "Error occured");
            }        
        }
        else
        {
            $tid = $tid[0];
            $tagID = $tid->id;
            $prevProblemCount = $tid->problem_count;
        }
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }

    // Get Problem ID
    $problemID = 0;
    $problem = $req->getParam('problem');
    if ($problem == NULL)
        return sendError($res, "No problem specified");
    try
    {
        $sql = "SELECT id FROM Problems WHERE code = '$problem'";
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $pid = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        if ($pid == NULL)
            return sendError($res, "Invalid Problem Code");

        $problemID = $pid[0]->id;
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }

    // First check if this tag already added to problem
    $sql = "SELECT problemID FROM Tag_Problem_Relation WHERE (tagID = $tagID AND problemID = $problemID)";
    try
    {
        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $rels = $stmt->fetchAll(PDO::FETCH_OBJ);

        if ($rels != NULL)
            return sendJson($res, "This tag is already added on this problem");

    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }  

    // Now we have tagID and problemID, just add a new row to relation table
    $sql = "INSERT INTO Tag_Problem_Relation (tagID, problemID) VALUES
    (:tagID, :problemID)";
    try
    {
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':tagID', $tagID);
        $stmt->bindParam(':problemID', $problemID);

        $stmt->execute();
        $db = null;
        // Tag successfully related to problem
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }  

    // And also increment the problem_count for tag
    $prevProblemCount = $prevProblemCount + 1;
    $sql = "UPDATE Tags SET problem_count = :problem_count WHERE id = :id";
    try
    {
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':problem_count', $prevProblemCount);
        $stmt->bindParam(':id', $tagID);

        $stmt->execute();

        $db = null;
        // Problem count increased
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    } 
    return sendJson($res, "Tag successfully added to this problem");
});

// Get data on a specefic tag (for default users only for now)
$app->get('/api/tag/{name}', function(Request $req, Response $res)
{
    $name = $req->getAttribute('name');

    // Get used id
    $userID = 0; 
    $token = $req->getParam('token');
    if ($token != NULL)
    {
        try
        {
            $sql = "SELECT userID FROM UserSessions WHERE token = '$token'";
            $db = new db();
            $db = $db->connect();

            $stmt = $db->query($sql);
            $uid = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            if ($uid == NULL)
                return sendError($res, "Session expired, please re login");

            $userID = $uid[0]->userID;
        }
        catch (PDOException $e)
        {
            return sendError($res, $e, "Error occured");
        }
    }

    $sql = "SELECT * FROM Tags WHERE (name = '$name' AND userID in (0, $userID))";
    try
    {
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->query($sql);
        $tag = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        if ($tag == NULL) return sendError($res, "Name not found");
        $tag = $tag[0];
        return sendJson($res, $tag, "Tag matching name sent successfully");
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }
});