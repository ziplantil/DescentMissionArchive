<?php
if (!defined('DXMA_VERSION')) {
    die();
}

require_once "schema.php";
require_once "paths.php";

function validateDate(string $date, string $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function getExtension(string $fn)
{
    $info = pathinfo($fn);
    return isset($info['extension']) ? '.' . $info['extension'] : '';
}

function swap(&$x, &$y)
{
    $tmp = $x;
    $x = $y;
    $y = $tmp;
}

class DatabaseController
{
    protected $db;
    protected $model;
    public $error;

    public function __construct($db, $model)
    {
        $this->db = $db;
        $this->model = $model;
    }
    
    protected function fail(string $msg)
    {
        $this->error = $msg;
        return null;
    }
    
    public function createUser(string $uname, string $phash, ?string $email)
    {
        $ok = $this->db->execute("INSERT INTO User (username, realname, passhash, email) VALUES (?, ?, ?, ?)", $uname, $uname, $phash, $email);
        if (!$ok) {
            return null;
        }
        return $this->db->newId();
    }

    public function editUser(int $uid, array $data, $auth)
    {
        $realname = $data["realname"];
        if (empty($realname)) {
            $realname = $username;
        }
        $email = $data["email"];
        if (empty($email)) {
            $email = "";
        }
        $website = $data["website"];
        if (empty($website)) {
            $website = "";
        }
        $desc = $data["description"];
        if (empty($desc)) {
            $desc = "";
        }

        $arr = array();

        if (!empty($data["upass"])) {
            if ($data["upass"] !== ($data["upassc"] ?? "")) {
                return fail("passwords do not match");
            }
            $phash = $auth->changePassword($uid, $data["upass"]);
            if (!$this->db->execute("UPDATE User SET passhash=?, forgotcode=NULL WHERE id=?", $phash, $uid)) {
                return fail("Could not update user");
            }
        }

        if (!$this->db->execute(
            "UPDATE User SET realname=?, email=?, website=?, description=?, forgotcode=NULL WHERE id=?",
            $realname,
            $email,
            $website,
            $desc,
            $uid
        )) {
            return fail("Could not update user");
        }
        return true;
    }

    public function setPassword(int $uid, array $data, $auth)
    {
        if (empty($data["upass"])) {
            return $this->fail("password must not be empty");
        }
        if ($data["upass"] !== ($data["upassc"] ?? "")) {
            return $this->fail("passwords do not match");
        }
        $phash = $auth->changePassword($uid, $data["upass"]);

        if (!$this->db->execute(
            "UPDATE User SET passhash=?, forgotcode=NULL WHERE id=?",
            $phash,
            $uid
        )) {
            return $this->fail("Could not update user");
        }
        return true;
    }

    public function setUpForgot(int $uid, string $ticket)
    {
        return $this->db->execute(
            "UPDATE User SET forgotcode=?, forgotexpiry=DATE_ADD(NOW(), INTERVAL 24 HOUR) WHERE id=?",
            $ticket,
            $uid
        );
    }

    public function createMission(int $uid, string $uname, array $data)
    {
        if ($data['file']['error'] === UPLOAD_ERR_NO_FILE) {
            return $this->fail("Mission file is required!");
        }
        if ($data['file']['error'] !== UPLOAD_ERR_OK) {
            return $this->fail("There was an upload error: " . $data['file']['error']);
        }
        if ($data['screenshot']['error'] !== UPLOAD_ERR_OK && $data['screenshot']['error'] !== UPLOAD_ERR_NO_FILE) {
            return $this->fail("There was an upload error: " . $data['screenshot']['error']);
        }
        if ($data['file']['size'] > MAXFILESIZE) {
            return $this->fail("Mission file is too large");
        }
        if ($data['screenshot']['size'] > MAXIMGSIZE) {
            return $this->fail("Screenshot file is too large");
        }
        if ($data['file']['size'] === 0) {
            return $this->fail("Mission file is required");
        }
        $fname = $data['file']['name'];
        if (empty($fname)) {
            $fname = "file.zip";
        }
        $fext = strtolower(getExtension($fname));
        if (!in_array($fext, ALLOWED_MISSION_EXTS)) {
            return $this->fail("Invalid mission file type. Allowed types are: " . implode(", ", ALLOWED_MISSION_EXTS));
        }
        if ($data['screenshot']['error'] !== UPLOAD_ERR_NO_FILE) {
            $sname = $data['screenshot']['name'];
            if (empty($sname)) {
                $sname = "image.png";
            }
            $sext = strtolower(getExtension($sname));
            if (!in_array($sext, ALLOWED_SCREENSHOT_EXTS)) {
                return $this->fail("Invalid screenshot file type. Allowed types are: " . implode(", ", ALLOWED_SCREENSHOT_EXTS));
            }
        } else {
            $sname = null;
        }

        $title = trim($data["title"]);
        if (empty($title)) {
            return $this->fail("Empty title not permitted");
        }
        $version = $data["version"];
        if (empty($version)) {
            return $this->fail("Empty version not permitted");
        }
        $author = $data["author"];
        if (empty($author)) {
            $author = $uname;
        }
        $desc = $data["description"];
        if (empty($desc)) {
            $desc = "";
        }
        if (strlen($desc) > DESC_MAXLENGTH) {
            return fail("Description is too long");
        }
        $mode = $data["mode"];
        if (!is_numeric($mode)) {
            return $this->fail("Invalid mode");
        }
        $mode = intval($mode);
        if (!isset(MODE_ENUM[$mode])) {
            return $this->fail("Invalid mode");
        }
        $game = $data["game"];
        if (!is_numeric($game)) {
            return $this->fail("Invalid game");
        }
        $game = intval($game);
        if (!isset(GAME_ENUM[$game])) {
            return $this->fail("Invalid game");
        }
        $levels = $data["levels"];
        if (!is_numeric($levels)) {
            return $this->fail("Invalid level count");
        }
        $levels = intval($levels);
        if ($levels < 1 || $levels > 255) {
            return $this->fail("Invalid level count");
        }
        $playersMin = $data["playersMin"];
        if (!is_numeric($playersMin)) {
            return fail("Invalid player count");
        }
        $playersMin = intval($playersMin);
        if ($playersMin < 1 || $playersMin > 255) {
            return fail("Invalid player count");
        }
        $playersMax = $data["playersMax"];
        if (!is_numeric($playersMax)) {
            return fail("Invalid player count");
        }
        $playersMax = intval($playersMax);
        if ($playersMax < 1 || $playersMax > 255) {
            return fail("Invalid player count");
        }
        if ($playersMin > $playersMax) {
            swap($playersMin, $playersMax);
        }
        $released = $data["released"];
        if (empty($released) || !validateDate($released)) {
            $released = date("Y-m-d");
        }

        $tempdir = bin2hex(random_bytes(16));
        $fpath = getMissionFilePath($uid, $tempdir, $fname);
        if (!move_uploaded_file($data['file']['tmp_name'], $fpath)) {
            return $this->fail("Could not upload mission file");
        }
        if ($data['screenshot']['size'] > 0) {
            $spath = getScreenshotFilePath($uid, $tempdir, $sname);
            if (!move_uploaded_file($data['screenshot']['tmp_name'], $spath)) {
                deltree(getMissionFilePath($uid, $tempdir));
                return $this->fail("Could not upload screenshot file");
            }
        }

        $ok = $this->db->execute(
            "INSERT INTO Mission (title, version, author, user, description, mode, game, levels, playersMin, playersMax, released, filename, screenshot, created, updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now(), now())",
            $title,
            $version,
            $author,
            $uid,
            $desc,
            $mode,
            $game,
            $levels,
            $playersMin,
            $playersMax,
            $released,
            $fname,
            $sname
        );
        if (!$ok) {
            deltree(getMissionFilePath($uid, $tempdir));
            deltree(getScreenshotFilePath($uid, $tempdir));
            return $this->fail("Could not add mission");
        }
        $mid = $this->db->newId();
        renameOrMerge(getMissionFilePath($uid, $tempdir), getMissionFilePath($uid, $mid));
        renameOrMerge(getScreenshotFilePath($uid, $tempdir), getScreenshotFilePath($uid, $mid));
        return $mid;
    }

    public function editMission(int $uid, int $mid, string $uname, array $data)
    {
        $title = trim($data["title"]);
        if (empty($title)) {
            return fail("Empty title not permitted");
        }
        $version = $data["version"];
        if (empty($version)) {
            return fail("Empty version not permitted");
        }
        $author = $data["author"];
        if (empty($author)) {
            $author = $uname;
        }
        $desc = $data["description"];
        if (empty($desc)) {
            $desc = "";
        }
        if (strlen($desc) > DESC_MAXLENGTH) {
            return fail("Description is too long");
        }
        $mode = $data["mode"];
        if (!is_numeric($mode)) {
            return fail("Invalid mode");
        }
        $mode = intval($mode);
        if (!isset(MODE_ENUM[$mode])) {
            return fail("Invalid mode");
        }
        $game = $data["game"];
        if (!is_numeric($game)) {
            return fail("Invalid game");
        }
        $game = intval($game);
        if (!isset(GAME_ENUM[$game])) {
            return fail("Invalid game");
        }
        $levels = $data["levels"];
        if (!is_numeric($levels)) {
            return fail("Invalid level count");
        }
        $levels = intval($levels);
        if ($levels < 1 || $levels > 255) {
            return fail("Invalid level count");
        }
        $playersMin = $data["playersMin"];
        if (!is_numeric($playersMin)) {
            return fail("Invalid player count");
        }
        $playersMin = intval($playersMin);
        if ($playersMin < 1 || $playersMin > 255) {
            return fail("Invalid player count");
        }
        $playersMax = $data["playersMax"];
        if (!is_numeric($playersMax)) {
            return fail("Invalid player count");
        }
        $playersMax = intval($playersMax);
        if ($playersMax < 1 || $playersMax > 255) {
            return fail("Invalid player count");
        }
        if ($playersMin > $playersMax) {
            swap($playersMin, $playersMax);
        }
        $released = $data["released"];
        if (empty($released) || !validateDate($released)) {
            $released = date("Y-m-d");
        }

        $arr = array();

        $mission = $this->model->getMissionById($mid);
        if (is_null($mission)) {
            return fail("No such mission");
        }
        if ($mission["user"] !== $uid) {
            return fail("Not permitted");
        }

        if (!$this->db->execute(
            "UPDATE Mission SET title=?, version=?, author=?, description=?, mode=?, game=?, levels=?, playersMin=?, playersMax=?, released=? WHERE id=?",
            $title,
            $version,
            $author,
            $desc,
            $mode,
            $game,
            $levels,
            $playersMin,
            $playersMax,
            $released,
            $mid
        )) {
            return fail("Could not update mission");
        }
        return true;
    }

    public function updateMissionFile(int $uid, int $mid, array $data, string $version)
    {
        if ($data['error'] === UPLOAD_ERR_NO_FILE) {
            return $this->fail("You must specify a file!");
        }
        if ($data['error'] !== UPLOAD_ERR_OK) {
            return $this->fail("There was an upload error: " . $data['error']);
        }
        if ($data['size'] > MAXFILESIZE) {
            return $this->fail("Mission file is too large");
        }
        if ($data['size'] === 0) {
            return $this->fail("Mission file is required");
        }

        $fname = $data['name'];
        if (empty($fname)) {
            $fname = "file.zip";
        }
        $fext = strtolower(getExtension($fname));
        if (!in_array($fext, ALLOWED_MISSION_EXTS)) {
            return $this->fail("Invalid mission file type. Allowed types are: " . implode(", ", ALLOWED_MISSION_EXTS));
        }
        
        $mission = $this->model->getMissionById($mid);
        if (is_null($mission)) {
            return fail("No such mission");
        }
        if ($mission["user"] !== $uid) {
            return fail("Not permitted");
        }

        $fpathold = getMissionFilePath($uid, $mid, $mission['filename']);
        $fpath = getMissionFilePath($uid, $mid, $fname);
        if (!move_uploaded_file($data['tmp_name'], $fpath)) {
            return $this->fail("Could not upload mission file");
        }

        $ok = $this->db->execute("UPDATE Mission SET version=?, filename=?, updated=now() WHERE id=?", $version, $fname, $mid);
        if (!$ok) {
            if ($fpath === $fpathold) {
                return true;
            }
            unlink($fpath);
            return $this->fail("Could not update mission");
        }
        if ($fpath !== $fpathold) {
            unlink($fpathold);
        }
        return true;
    }

    public function updateMissionScreenshot(int $uid, int $mid, array $data)
    {
        if ($data['error'] === UPLOAD_ERR_NO_FILE) {
            return $this->fail("You must specify a file!");
        }
        if ($data['error'] !== UPLOAD_ERR_OK) {
            return $this->fail("There was an upload error: " . $data['error']);
        }
        if ($data['size'] > MAXIMGSIZE) {
            return $this->fail("Screenshot file is too large");
        }
        if ($data['size'] === 0) {
            return $this->fail("Screenshot file is required");
        }

        $fname = $data['name'];
        if (empty($fname)) {
            $fname = "image.png";
        }
        $fext = strtolower(getExtension($fname));
        if (!in_array($fext, ALLOWED_SCREENSHOT_EXTS)) {
            return $this->fail("Invalid screenshot file type. Allowed types are: " . implode(", ", ALLOWED_MISSION_EXTS));
        }
        
        $mission = $this->model->getMissionById($mid);
        if (is_null($mission)) {
            return fail("No such mission");
        }
        if ($mission["user"] !== $uid) {
            return fail("Not permitted");
        }

        $hadscreenshot = !is_null($mission['screenshot']);
        $fpathold = getScreenshotFilePath($uid, $mid, $mission['screenshot']);
        $fpath = getScreenshotFilePath($uid, $mid, $fname);
        if (!move_uploaded_file($data['tmp_name'], $fpath)) {
            return $this->fail("Could not upload screenshot file");
        }

        if ($fpath !== $fpathold) {
            $ok = $this->db->execute("UPDATE Mission SET screenshot=? WHERE id=?", $fname, $mid);
            if (!$ok) {
                unlink($fpath);
                return $this->fail("Could not update screenshot");
            }
            if ($hadscreenshot) {
                unlink($fpathold);
            }
        }
        return true;
    }

    public function deleteMissionScreenshot(int $uid, int $mid)
    {
        $mission = $this->model->getMissionById($mid);
        if ($mission === null) {
            return fail("No such mission");
        }
        if ($mission["user"] !== $uid) {
            return fail("Not permitted");
        }

        if (empty($mission['screenshot'])) {
            return true;
        }

        $spath = getScreenshotFilePath($uid, $mid, $mission['filename']);
        $ok = $this->db->execute("UPDATE Mission SET screenshot=NULL WHERE id=?", $mid);
        if ($ok) {
            unlink($spath);
        }
        return $ok;
    }

    public function deleteMission(int $uid, int $mid)
    {
        $mission = $this->model->getMissionById($mid);
        if ($mission === null) {
            return fail("No such mission");
        }
        if ($mission["user"] !== $uid) {
            return fail("Not permitted");
        }

        $fpath = getMissionFilePath($uid, $mid, $mission['filename']);
        $spath = null;
        if (!empty($mission['screenshot'])) {
            $spath = getScreenshotFilePath($uid, $mid, $mission['filename']);
        }
        $ok = $this->db->execute("DELETE FROM Mission WHERE id=?", $mid);
        if ($ok) {
            unlink($fpath);
            if (!is_null($spath)) {
                unlink($spath);
            }
        }
        return $ok;
    }
    
    public function toggleFavorite(int $uid, int $mid)
    {
        $this->db->begin();
        if ($this->model->hasFavorite($uid, $mid)) {
            $ok = $this->db->execute("DELETE FROM Favorite WHERE user=? AND mission=?", $uid, $mid);
        } else {
            $ok = $this->db->execute("INSERT INTO Favorite (user, mission) VALUES (?, ?)", $uid, $mid);
        }
        $this->db->commit();
        return $ok;
    }
    
    public function setRating(int $uid, int $mid, int $rating)
    {
        if (!is_int($rating)) {
            return false;
        }
        if ($rating < 0 || $rating > 10) {
            return false;
        }
        $this->db->begin();
        $hasRating = $this->db->query("SELECT user, rating FROM Rating WHERE user=? AND mission=?", $uid, $mid)->exists();
        if ($hasRating) {
            $ok = $this->db->execute("UPDATE Rating SET rating=? WHERE user=? AND mission=?", $rating, $uid, $mid);
        } else {
            $ok = $this->db->execute("INSERT INTO Rating (user, mission, rating) VALUES (?, ?, ?)", $uid, $mid, $rating);
        }
        $this->db->commit();
        return $ok;
    }
    
    public function deleteRating(int $uid, int $mid)
    {
        return $this->db->execute("DELETE FROM Rating WHERE user=? AND mission=?", $uid, $mid);
    }

    public function close()
    {
        return $this->db->close();
    }
}
