<?php
if (!defined('DXMA_VERSION')) die();

require_once "schema.php";
require_once "paths.php";

function prepareSearch(string $q) {
    $q = str_replace("\\", "\\\\", $q);
    $q = str_replace("%", "\\%", $q);
    $q = str_replace("_", "\\_", $q);
    // TODO not inside quotes?
    $q = str_replace("*", "%", $q);
    $q = str_replace("?", "_", $q);
    return "%" . $q . "%";
}

function parseSearchOrder(string $q, bool &$desc) {
    if (preg_match('/([a-z]+)_([ad])/', $q, $matches) !== 1)
        return NULL;
    $desc = $matches[2] === "d";
    return $matches[1];
}

class DatabaseModel {
    protected $db;
    private $has_version;

    public function __construct($db) {
		$this->db = $db;
    }
    
    public function version() {
        $result = $this->db->query("SELECT version FROM SchemaVersion")->one();
        $this->has_version = !is_null($result);
        return is_null($result) ? 0 : $result["version"];
    }

    private function migrate(int $v) {
        global $migrations;
        $stmt = $migrations[$v];
        $this->db->migrateQuery($stmt);
    }
    
    private function setVersion(int $v) {
        if ($this->has_version)
            $this->db->query("UPDATE SchemaVersion SET version=?", $v);
        else
            $this->db->query("INSERT INTO SchemaVersion (version) VALUES (?)", $v);
    }
    
    public function prepare() {
        $this->migrate(0);
        $version = $this->version();
        $update = $version < DBVERSION;
        while ($version < DBVERSION) {
            $this->migrate(++$version);
        }
        if ($update)
            $this->setVersion($version);
    }

    public function getUserByName(string $uname) {
        return $this->db->query("SELECT * FROM User WHERE username=?", $uname)->one();
    }
    
    public function getUserById(int $uid) {
        return $this->db->query("SELECT * FROM User WHERE id=?", $uid)->one();
    }

    public function forgotAllowed($uid, $ticket) {
        if (!is_numeric($uid)) return FALSE;
        $uid = intval($uid);
        $user = $this->db->query("SELECT * FROM User WHERE id=? AND forgotexpiry >= now()", $uid)->one();
        if (is_null($user)) return FALSE;
        if (is_null($user["forgotcode"])) return FALSE;
        return hash_equals($user["forgotcode"], $ticket);
    }
    
    public function getMissionById(int $mid) {
        return $this->db->query("SELECT Mission.*, User.username as `username` FROM Mission JOIN User ON Mission.user = User.id WHERE Mission.id=?", $mid)->one();
    }
    
    public function numberOfMembers() {
        return $this->db->query("SELECT COUNT(*) AS `count` FROM User")->one()['count'];
    }
    
    public function numberOfMissions() {
        return $this->db->query("SELECT COUNT(*) AS `count` FROM Mission")->one()['count'];
    }

    public function hasFavorite(int $uid, int $mid) {
        return $this->db->query("SELECT id FROM Favorite WHERE user=? AND mission=?", $uid, $mid)->exists();
    }

    public function getRatingData(?int $uid, int $mid) {
        $ratings = $this->db->query("SELECT user, rating FROM Rating WHERE mission=?", $mid)->all();
        $scores = array_column($ratings, "rating");
        $you = NULL;

        if (!is_null($uid)) {
            foreach ($ratings as &$rating) {
                if ($rating["user"] === $uid) {
                    $you = $rating["rating"];
                    break;
                }
            }
        }
        
        return array("count" => count($ratings), "average" => count($scores) > 0 ? array_sum($scores) / count($scores) : NULL, "you" => $you);
    }

    public function searchMissions(array $params, int &$total = NULL) {
        $wheres = array();
        $r = array();
        $joins = array();
        $groupby = array();
        $fields = array("Mission.*", "User.username as `username`");
        $table = "Mission";
        if (!empty($params["favs"])) {
            $table = "Favorite";
            $wheres[] = "Favorite.user = ?";
            $r[] = $params["favs"];
            $joins[] = "JOIN Mission ON Favorite.mission = Mission.id";
        }
        $joins[] = "JOIN User ON Mission.user = User.id";
        $query = "FROM " . $table . " ";
        if (!empty($params["q"])) {
            $wheres[] = "Mission.title LIKE ?";
            $r[] = prepareSearch($params["q"]);
        }
        if (!empty($params["user"])) {
            $wheres[] = "Mission.user = ?";
            $r[] = $params["user"];
        }
        if (!empty($params["players"])) {
            $wheres[] = "Mission.playersMin <= ?";
            $r[] = $params["players"];
            $wheres[] = "Mission.playersMax >= ?";
            $r[] = $params["players"];
        }
        if (!empty($params["modes"])) {
            $wheres[] = "Mission.mode IN (" . implode(",", array_fill(0, count($params["modes"]), "?")) . ")";
            $r = array_merge($r, $params["modes"]);
        }
        if (!empty($params["games"])) {
            $wheres[] = "Mission.game IN (" . implode(",", array_fill(0, count($params["games"]), "?")) . ")";
            $r = array_merge($r, $params["games"]);
        }
        $orderby = "Mission.title";
        $orderad = "ASC";
        $desc = false;
        if (!empty($params["order"])) {
            $givenorder = parseSearchOrder($params["order"], $desc);
            if (!is_null($givenorder)) {
                $orderad = $desc ? "DESC" : "ASC";

                if ($givenorder === "rdate") {
                    $orderby = "Mission.released";
                } elseif ($givenorder === "udate") {
                    $orderby = "Mission.updated";
                } elseif ($givenorder === "rating") {
                    $divisor = 8;
                    $fields[] = "IFNULL(SUM(Rating.rating - 5), 0) * (EXP(COUNT(Rating.rating)) - EXP(-COUNT(Rating.rating))) / (EXP(COUNT(Rating.rating)) + EXP(-COUNT(Rating.rating))) / $divisor AS score";
                    $joins[] = "LEFT JOIN Rating ON Mission.id = Rating.mission";
                    $groupby[] = "GROUP BY Mission.id";
                    $orderby = "score";
                } // else = name
            }
        }
        $query .= implode(" ", $joins) . " ";
        if (!empty($groupby)) {
            $query .= implode(" ", $groupby) . " ";
        }
        if (!empty($wheres)) {
            $query .= "WHERE " . implode(" AND ", $wheres) . " ";
        }

        $tmpr = array_merge(array("SELECT COUNT(*) AS `count` " . $query), $r);
        $total = call_user_func_array(array($this->db, "query"), $tmpr)->one()['count'];

        $query .= "ORDER BY $orderby $orderad ";
        $query = "SELECT " . implode(", ", $fields) . " " . $query;
        if (!empty($params["page"])) {
            $page = intval($params["page"]);
            if ($page > 0) $page = $page - 1;
            $query .= "LIMIT ? OFFSET ?";
            $r[] = PERPAGE;
            $r[] = PERPAGE * $page;
        }
        array_unshift($r, $query);
        return call_user_func_array(array($this->db, "query"), $r)->all();
    }

    public function searchMembers(array $params, int &$total = NULL) {
        $wheres = array();
        $r = array();
        $joins = array();
        $fields = array("User.*");
        $table = "User";
        $query = "FROM " . $table . " ";
        if (!empty($params["q"])) {
            $wheres[] = "User.username LIKE ? OR User.realname LIKE ?";
            $r[] = prepareSearch($params["q"]);
            $r[] = prepareSearch($params["q"]);
        }
        $orderby = "User.username";
        $orderad = "ASC";
        $desc = false;
        if (!empty($params["order"])) {
            $givenorder = parseSearchOrder($params["order"], $desc);
            if (!is_null($givenorder)) {
                $orderad = $desc ? "DESC" : "ASC";
                if ($givenorder === "jdate") {
                    $orderby = "User.joined";
                } // else = name
            }
        }
        $query .= implode(" ", $joins) . " ";
        if (!empty($wheres)) {
            $query .= "WHERE " . implode(" AND ", $wheres) . " ";
        }

        $tmpr = array_merge(array("SELECT COUNT(*) AS `count` " . $query), $r);
        $total = call_user_func_array(array($this->db, "query"), $tmpr)->one()['count'];

        $query .= "ORDER BY $orderby $orderad ";
        $query =  "SELECT " . implode(", ", $fields) . " " . $query;
        if (!empty($params["page"])) {
            $page = intval($params["page"]);
            if ($page > 0) $page = $page - 1;
            $query .= "LIMIT ? OFFSET ?";
            $r[] = PERPAGE;
            $r[] = PERPAGE * $page;
        }
        array_unshift($r, $query);
        return call_user_func_array(array($this->db, "query"), $r)->all();
    }
    
	public function close() {
		return $this->db->close();
	}
}
?>
