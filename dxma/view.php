<?php
if (!defined('DXMA_VERSION')) die();

require_once "db.php";
require_once "model.php";
require_once "controller.php";
require_once "auth.php";
require_once "route.php";
require_once "get.php";

class DescentMissionArchive {
    protected $db;
    protected $model;
    protected $ctrl;
    protected $auth;

    public function __construct() {
        $this->db = DatabaseConnection::get();
        $this->model = new DatabaseModel($this->db);
        $this->model->prepare();
        $this->ctrl = new DatabaseController($this->db, $this->model);
        $this->auth = new AuthSystem($this->model, $this->ctrl);
    }

    public function checkCSRF() {
        if (!hash_equals($_POST["csrf"] ?? "", $_SESSION["token"])) {
            $this->serveError("403");
            die();
        }
    }

    public function serve(string $page, string $title, array $data) {
        $logged_in = $this->auth->ok;
        $userid = $this->auth->uid;
        $username = $this->auth->uname;
        extract($data);
        $content = "$page.php";
        require "template/frame.php";
    }

    public function serveError(string $page) {
        $logged_in = $this->auth->ok;
        $userid = $this->auth->uid;
        $username = $this->auth->uname;
        require "template/$page.php";
    }

    public function mainPage() {
        $title = "List";
        $pagenum = getNumber('page', 0);
        if (empty($pagenum)) $pagenum = 1;
        $array = array("q" => $_GET['q'] ?? '', "page" => $pagenum);
        $total = 0;
        if (isset($_GET["mode"]))
            $array["modes"] = getNumberArray($_GET["mode"]);
        if (isset($_GET["game"]))
            $array["games"] = getNumberArray($_GET["game"]);
        if (isset($_GET["players"]))
            $array["players"] = getNumber('players', NULL);
        if (!empty($_GET["order"]))
            $array["order"] = $_GET["order"];
        $missions = $this->model->searchMissions($array, $total);
        $this->serve("list", $title, array("missions" => $missions, "total" => $total, "pageNum" => $pagenum, "pageCount" => ceil($total / PERPAGE)));
    }

    public function loginPage() {
        $title = "Log in";
        if (hasAllPost('uname', 'upass')) {
            $this->checkCSRF();
            if ($this->auth->login($_POST['uname'], $_POST['upass']))
                redirect(route());
            else
                $this->serve("login", $title, array("fail" => 1));
        } else {
            $this->serve("login", $title, array());
        }
    }

    public function logout() {
        $this->auth->logout();
        redirect(route());
    }
    
    public function registerPage() {
        $title = "Register";
        $i = rand(0, count(REGISTER_CHECKS) - 1);
        $check = array("checkkey" => $i, "checkquestion" => REGISTER_CHECKS[$i][0]);
        if (hasAllPost('uname', 'upass', 'upassc', 'checkkey', 'check')) {
            $this->checkCSRF();
            if (!is_numeric($_POST['checkkey'])) {
                $this->serve("register", $title, $check);
                die();
            }
            $key = intval($_POST['checkkey']);
            if (strtolower(REGISTER_CHECKS[$key][1]) !== strtolower($_POST['check'])) {
                $this->serve("register", $title, array_merge($check, array("error" => "You failed the trivia question. Try again")));
                die();
            }

            if ($this->auth->register($_POST['uname'], $_POST['upass'], $_POST['upassc'], $_POST['email']))
                redirect(route());
            else
                $this->serve("register", $title, array_merge($check, array("error" => $this->auth->error)));
        } else {
            $this->serve("register", $title, $check);
        }
    }
    
    public function forgotPasswordPage() {
        $title = "Forgot password";
        $i = rand(0, count(REGISTER_CHECKS) - 1);
        $check = array("checkkey" => $i, "checkquestion" => REGISTER_CHECKS[$i][0]);
        if (hasAllPost('uid', 'ticket', 'upass', 'upassc')) {
            $this->checkCSRF();
            if (!$this->model->forgotAllowed($_POST['uid'], $_POST['ticket'])) {
                $this->serveError('403');
                die();
            }
            if ($this->ctrl->setPassword($_POST['uid'], arrayget($_POST, 'upass', 'upassc'), $this->auth))
                $this->serve("forgotsetok", $title, array());
            else
                $this->serve("forgotnewpass", $title, array("uid" => $_POST['uid'], "ticket" => $_POST['ticket'], "error" => $this->ctrl->error));
        } else if (hasAllGet('u', 't')) {
            if (!$this->model->forgotAllowed($_GET['u'], $_GET['t'])) {
                $this->serveError('403');
                die();
            }
            $this->serve("forgotnewpass", $title, array("uid" => $_GET['u'], "ticket" => $_GET['t'], "error" => $this->auth->error));
        } else if (hasAllPost('uname', 'checkkey', 'check')) {
            $this->checkCSRF();
            if (!is_numeric($_POST['checkkey'])) {
                $this->serve("forgot", $title, $check);
                die();
            }
            $key = intval($_POST['checkkey']);
            if (strtolower(REGISTER_CHECKS[$key][1]) !== strtolower($_POST['check'])) {
                $this->serve("forgot", $title, array_merge($check, array("error" => "You failed the trivia question. Try again")));
                die();
            }

            if ($this->auth->forgot($_POST['uname']))
                $this->serve("forgotok", $title, array());
            else
                $this->serve("forgot", $title, array_merge($check, array("error" => $this->auth->error)));
        } else {
            $this->serve("forgot", $title, $check);
        }
    }
    
    public function missionPage() {
        $mid = getNumber('m', NULL);
        if (is_null($mid))
            return $this->serveError("404");
        $mission = $this->model->getMissionById($mid);
        if (is_null($mission))
            return $this->serveError("404");
        $fav = $this->auth->ok && $this->model->hasFavorite($this->auth->uid, $mid);
        $ratings = $this->model->getRatingData($this->auth->ok ? $this->auth->uid : NULL, $mid);
        $title = "Mission: " . $mission["title"];
        $this->serve("mission", $title, array("m" => $mission, "fav" => $fav, "ratings" => $ratings));
    }
    
    public function userPage() {
        $uid = getNumber('u', NULL);
        if (is_null($uid))
            return $this->serveError("404");
        $user = $this->model->getUserById($uid);
        if (is_null($user))
            return $this->serveError("404");
        $missions = $this->model->searchMissions([ "user" => $uid ]);
        $title = "User: " . $user["username"];
        $this->serve("user", $title, array("u" => $user, "missions" => $missions));
    }
    
    public function addMissionPage() {
        $title = "Upload mission";
        if (!$this->auth->ok)
            redirect(route("login"));
        if (!hasAllPost('title', 'version', 'description', 'author', 'game', 'mode', 'levels', 'playersMin', 'playersMax', 'released')) {
            $arr = array();
            if (isset($_GET["upload"])) {
                $arr['error'] = "One of your files was too large";
            }
            $this->serve("upload", $title, $arr);
        } else {
            $this->checkCSRF();
            $arr = array_merge(arrayget($_POST, 'title', 'version', 'description', 'author', 'game', 'mode', 'levels', 'playersMin', 'playersMax', 'released'), arrayget($_FILES, 'file', 'screenshot'));
            $mid = $this->ctrl->createMission($this->auth->uid, $this->auth->uname, $arr);
            if (is_null($mid))
                $this->serve("upload", $title, array("error" => $this->ctrl->error));
            else
                redirect(route("mission", array("m" => $mid)));
        }
    }
    
    public function editUserPage() {
        if (!$this->auth->ok)
            redirect(route("login"));
        $title = "Edit user page";
        $userid = $this->auth->uid;
        $user = $this->model->getUserById($userid);
        if (!hasAllPost('realname', 'email', 'website', 'description', 'upass', 'upassc')) {
            $this->serve("usermod", $title, array("user" => $user));
        } else {
            $this->checkCSRF();
            $arr = arrayget($_POST, 'realname', 'email', 'website', 'description', 'upass', 'upassc');
            if (!$this->ctrl->editUser($this->auth->uid, $arr, $this->auth))
                $this->serve("usermod", $title, array("user" => $user, "error" => $this->ctrl->error));
            else
                redirect(route("user", array("u" => $this->auth->uid)));
        }
    }
    
    public function favoritesPage() {
        if (!$this->auth->ok)
            redirect(route("login"));
        $title = "Favorites";
        $uid = $this->auth->uid;
        $pagenum = getNumber('page', 0);
        $array = array("q" => getString('q', 0), "page" => $pagenum, "favs" => $uid);
        $total = 0;
        $missions = $this->model->searchMissions($array, $total);
        $this->serve("favorites", $title, array("missions" => $missions, "total" => $total, "pageNum" => $pagenum, "pageCount" => ceil($total / PERPAGE)));
    }

    public function editMissionPage() {
        if (!$this->auth->ok)
            redirect(route("login"));
        $mid = getNumber('m', NULL);
        if (is_null($mid))
            return $this->serveError("404");
        $mission = $this->model->getMissionById($mid);
        if (is_null($mission))
            return $this->serveError("404");
        if ($mission["user"] != $this->auth->uid)
            return $this->serveError("403");
        
        $title = "Edit mission: " . $mission["title"];
        if (!hasAllPost('title', 'version', 'author', 'description', 'game', 'mode', 'levels', 'playersMin', 'playersMax', 'released')) {
            $this->serve("edit", $title, array("mission" => $mission));
        } else {
            $this->checkCSRF();
            $arr = arrayget($_POST, 'title', 'version', 'author', 'description', 'game', 'mode', 'levels', 'playersMin', 'playersMax', 'released');
            if (!$this->ctrl->editMission($this->auth->uid, $mid, $this->auth->uname, $arr))
                $this->serve("edit", $title, array("mission" => $mission, "error" => $this->ctrl->error));
            else {
                redirect(route("mission", array("m" => $mid)));
            }
        }
    }
    
    public function updateMissionPage() {
        if (!$this->auth->ok)
            redirect(route("login"));
        $mid = getNumber('m', NULL);
        if (is_null($mid))
            return $this->serveError("404");
        $mission = $this->model->getMissionById($mid);
        if (is_null($mission))
            return $this->serveError("404");
        if ($mission["user"] != $this->auth->uid)
            return $this->serveError("403");
        
        $title = "Update mission: " . $mission["title"];
        if (isset($_POST['updatefile']) && isset($_POST['version'])) {
            $this->checkCSRF();
            $file = $_FILES['file'];
            if (!$this->ctrl->updateMissionFile($this->auth->uid, $mid, $file, $_POST['version']))
                $this->serve("update", $title, array("mission" => $mission, "error" => $this->ctrl->error));
            else {
                redirect(route("mission", array("m" => $mid)));
            }
        } else if (isset($_POST['updatescreenshot'])) {
            $this->checkCSRF();
            $file = $_FILES['screenshot'];
            if (!$this->ctrl->updateMissionScreenshot($this->auth->uid, $mid, $file))
                $this->serve("update", $title, array("mission" => $mission, "error" => $this->ctrl->error));
            else {
                redirect(route("mission", array("m" => $mid)));
            }
        } else {
            $arr = array("mission" => $mission);
            if (isset($_GET["upload"])) {
                $arr['error'] = "One of your files was too large";
            }
            $this->serve("update", $title, $arr);
        }
    }
    
    public function deleteMissionPage() {
        if (!$this->auth->ok)
            redirect(route("login"));
        $mid = getNumber('m', NULL);
        if (is_null($mid))
            return $this->serveError("404");
        $mission = $this->model->getMissionById($mid);
        if (is_null($mission))
            return $this->serveError("404");
        if ($mission["user"] != $this->auth->uid)
            return $this->serveError("403");
        
        $title = "Delete mission: " . $mission["title"];
        if (!hasAllPost('confirm')) {
            $this->serve("delete", $title, array("mission" => $mission));
        } else {
            $this->ctrl->deleteMission($this->auth->uid, $mid);
            redirect(route());
        }
    }
    
    public function favoriteMission() {
        if (!$this->auth->ok)
            redirect(route("login"));
        $mid = getNumber('m', NULL);
        if (is_null($mid))
            return $this->serveError("404");
        $mission = $this->model->getMissionById($mid);
        if (is_null($mission))
            return $this->serveError("404");
        
        $this->ctrl->toggleFavorite($this->auth->uid, $mid);
        redirect(route("mission", array("m" => $mid)));
    }
    
    public function memberListPage() {
        $title = "Member list";
        $pagenum = getNumber('page', 0);
        if (empty($pagenum)) $pagenum = 1;
        $array = array("q" => getString('q', 0), "page" => $pagenum);
        if (!empty($_GET["order"]))
            $array["order"] = $_GET["order"];
        $total = 0;
        $members = $this->model->searchMembers($array, $total);
        $this->serve("members", $title, array("members" => $members, "total" => $total, "pageNum" => $pagenum, "pageCount" => ceil($total / PERPAGE)));
    }

    public function rateMission() {
        if (!$this->auth->ok)
            redirect(route("login"));
        $mid = getNumber('m', NULL);
        if (is_null($mid))
            return $this->serveError("404");
        $mission = $this->model->getMissionById($mid);
        if (is_null($mission))
            return $this->serveError("404");
        
        if (!empty($_POST["rating"])) {
            $this->checkCSRF();
            $this->ctrl->setRating($this->auth->uid, $mid, $_POST["rating"]);
        } else if (isset($_POST["delete"])) {
            $this->checkCSRF();
            $this->ctrl->deleteRating($this->auth->uid, $mid);
        }
        redirect(route("mission", array("m" => $mid)));
    }

    public function aboutPage() {
        $title = "About";
        $this->serve("about", $title, array());
    }

    public function statsPage() {
        $title = "Statistics";
        $data = array();
        $data["memberTotal"] = $this->model->numberOfMembers();
        $data["missionTotal"] = $this->model->numberOfMissions();
        $this->serve("stats", $title, $data);
    }
}
?>
