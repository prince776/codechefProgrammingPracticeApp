<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Get problems associated with tag(s) specified with limit (default = 20) and offset (default = 0).
$app->get('/api/problems', function(Request $req, Response $res)
{
    // Get and parse params.
    $tagsStr = $req->getParam('tags');
    $tags = NULL;
    if ($tagsStr != NULL) $tags = explode(",", $tagsStr);
    
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

    // Make SQL query.
    $sql = NULL;
    if ($tags == NULL)
    {
        $sql = "SELECT Problems.* 
        FROM Tags
        INNER JOIN Tag_Problem_Relation ON Tags.id = Tag_Problem_Relation.tagID
        INNER JOIN Problems ON Tag_Problem_Relation.problemID = Problems.id LIMIT $limit OFFSET $offset";
    }
    else
    {
        $tagsLen = count($tags);

        $sql = "SELECT * FROM Problems 
        WHERE code IN (SELECT PROBLEM FROM (SELECT Problems.code AS PROBLEM,COUNT(*) as CNT FROM Tag_Problem_Relation 
        INNER JOIN Problems ON Problems.id = Tag_Problem_Relation.problemID
        WHERE Tag_Problem_Relation.tagID IN 
        (SELECT Tags.id FROM Tags WHERE (Tags.name IN (";

        foreach ($tags as $tag)
        {
            $sql .= "'$tag',";
        }
        $sql = substr($sql, 0, -1);
        $sql .= ") AND Tags.userID in (0, $userID)))
        GROUP BY Tag_Problem_Relation.problemID) AS SUBQUERY
        WHERE CNT = $tagsLen) LIMIT $limit OFFSET $offset";
    }

    try
    {
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->query($sql);
        $problems = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        return sendJson($res, $problems, "Problems matching tags sent successfully");
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }
});

// Get data of a specefic problem.
$app->get('/api/problem/{code}', function(Request $req, Response $res)
{
    $code = $req->getAttribute('code');

    $sql = "SELECT * FROM Problems WHERE code = '$code'";
    try
    {
        $db = new db();
        $db = $db->connect();
        
        $stmt = $db->query($sql);
        $tag = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        if ($tag == NULL) return sendError($res, "Code not found");
        $tag = $tag[0];
        return sendJson($res, $tag, "Problem matching code sent successfully");
    }
    catch (PDOException $e)
    {
        return sendError($res, $e, "Error occured");
    }
});