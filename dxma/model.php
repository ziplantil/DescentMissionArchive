<?php
if (!defined('DXMA_VERSION')) {
    die();
}

require_once "schema.php";
require_once "paths.php";

function prepareSearch(string $q)
{
    $q = str_replace("\\", "\\\\", $q);
    $q = str_replace("%", "\\%", $q);
    $q = str_replace("_", "\\_", $q);
    // TODO not inside quotes?
    $q = str_replace("*", "%", $q);
    $q = str_replace("?", "_", $q);
    return $q;
}

function parseSearchOrder(string $q, bool &$desc)
{
    if (preg_match('/([a-z]+)_([ad])/', $q, $matches) !== 1) {
        return null;
    }
    $desc = $matches[2] === "d";
    return $matches[1];
}

class DatabaseModel
{
    protected $db;
    private $has_version;

    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function version()
    {
        $result = $this->db->query("SELECT version FROM SchemaVersion")->one();
        $this->has_version = !is_null($result);
        return is_null($result) ? null : $result["version"];
    }

    private function migrate(int $v)
    {
        global $migrations;
        $migrations[$v]($this->db);
    }
    
    private function setVersion(int $v)
    {
        if ($this->has_version) {
            $this->db->query("UPDATE SchemaVersion SET version=?", $v);
        } else {
            $this->db->query("INSERT INTO SchemaVersion (version) VALUES (?)", $v);
        }
    }
    
    public function prepare()
    {
        $version = $this->version();
        if (is_null($version)) {
            $this->migrate(0);
            $version = 0;
        }
        $update = $version < DBVERSION;
        while ($version < DBVERSION) {
            $this->migrate(++$version);
        }
        if ($update) {
            $this->setVersion($version);
        }
    }

    public function getUserByName(string $uname)
    {
        return $this->db->query("SELECT * FROM User WHERE username=?", $uname)->one();
    }
    
    public function getUserById(int $uid)
    {
        return $this->db->query("SELECT * FROM User WHERE id=?", $uid)->one();
    }

    public function getUserByIdLite(int $uid)
    {
        return $this->db->query("SELECT id, username FROM User WHERE id=?", $uid)->one();
    }
    
    public function getAuthorById(int $aid)
    {
        return $this->db->query("SELECT * FROM Author WHERE id=?", $aid)->one();
    }

    public function forgotAllowed($uid, $ticket)
    {
        if (!is_numeric($uid)) {
            return false;
        }
        $uid = intval($uid);
        $user = $this->db->query("SELECT * FROM User WHERE id=? AND forgotexpiry >= now()", $uid)->one();
        if (is_null($user)) {
            return false;
        }
        if (is_null($user["forgotcode"])) {
            return false;
        }
        return hash_equals($user["forgotcode"], $ticket);
    }
    
    public function getMissionById(int $mid, bool $getAuthors)
    {
        $result = $this->db->query("SELECT Mission.*, User.username as `username` FROM Mission JOIN User ON Mission.user = User.id WHERE Mission.id=?", $mid)->one();
        if ($getAuthors && !is_null($result)) {
            $result["authors"] = $this->db->query("SELECT Author.id, IFNULL(User.username, Author.`name`) as `name`, Author.userid FROM MissionAuthor JOIN Author ON MissionAuthor.author = Author.id LEFT JOIN User on Author.userid = User.id WHERE MissionAuthor.mission=? ORDER BY MissionAuthor.order ASC", $mid)->all();
        }
        return $result;
    }
    
    public function numberOfMembers()
    {
        return $this->db->query("SELECT COUNT(*) AS `count` FROM User")->one()['count'];
    }
    
    public function numberOfAuthors()
    {
        return $this->db->query("SELECT COUNT(*) AS `count` FROM Author")->one()['count'];
    }
    
    public function numberOfMissions()
    {
        return $this->db->query("SELECT COUNT(*) AS `count` FROM Mission")->one()['count'];
    }

    public function hasFavorite(int $uid, int $mid)
    {
        return $this->db->query("SELECT id FROM Favorite WHERE user=? AND mission=?", $uid, $mid)->exists();
    }

    public function getRatingData(?int $uid, int $mid)
    {
        $ratings = $this->db->query("SELECT user, rating FROM Rating WHERE mission=?", $mid)->all();
        $scores = array_column($ratings, "rating");
        $you = null;

        if (!is_null($uid)) {
            foreach ($ratings as &$rating) {
                if ($rating["user"] === $uid) {
                    $you = $rating["rating"];
                    break;
                }
            }
        }
        
        return array("count" => count($ratings), "average" => count($scores) > 0 ? array_sum($scores) / count($scores) : null, "you" => $you);
    }

    public function searchMissions(array $params, int &$total = null)
    {
        $wheres = array();
        $r = array();
        $joins = array();
        $groupby = array();
        $fields = array("Mission.*", "IFNULL(User.username, Author.name) AS `authorname`", "Author.id AS `authorid`", "Author.userid AS `authoruid`");
        $table = "Mission";
        if (!empty($params["favs"])) {
            $table = "Favorite";
            $wheres[] = "Favorite.user = ?";
            $r[] = $params["favs"];
            $joins[] = "JOIN Mission ON Favorite.mission = Mission.id";
        }
        $joins[] = "LEFT JOIN MissionAuthor ON Mission.id = MissionAuthor.mission";
        $joins[] = "JOIN Author ON MissionAuthor.author = Author.id";
        $joins[] = "LEFT JOIN User ON Author.userid = User.id";
        $groupby[] = "GROUP BY Mission.id";
        $query = "FROM " . $table . " ";
        if (!empty($params["q"])) {
            $wheres[] = "Mission.title LIKE ?";
            $r[] = prepareSearch("*" . $params["q"] . "*");
        }
        if (!empty($params["user"])) {
            $wheres[] = "Mission.user = ?";
            $r[] = $params["user"];
        }
        if (!empty($params["author"])) {
            $wheres[] = "User.username LIKE ? OR Author.name LIKE ?";
            $r[] = prepareSearch($params["author"]);
            $r[] = prepareSearch($params["author"]);
            $fields[] = "(SELECT COUNT(MissionAuthor.author) <> 1 FROM MissionAuthor WHERE MissionAuthor.mission = Mission.id) AS `multiauthor`";
        } elseif (!empty($params["authorid"])) {
            $wheres[] = "Author.id = ?";
            $r[] = $params["authorid"];
            $fields[] = "(SELECT COUNT(MissionAuthor.author) <> 1 FROM MissionAuthor WHERE MissionAuthor.mission = Mission.id) AS `multiauthor`";
        } elseif (!empty($params["authoruserid"])) {
            $wheres[] = "Author.userid = ?";
            $r[] = $params["authoruserid"];
            $fields[] = "(SELECT COUNT(MissionAuthor.author) <> 1 FROM MissionAuthor WHERE MissionAuthor.mission = Mission.id) AS `multiauthor`";
        } else {
            $fields[] = "COUNT(Author.id) <> 1 AS `multiauthor`";
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
        $orderby = "Mission.title ASC";
        $desc = false;
        if (!empty($params["order"])) {
            $givenorder = parseSearchOrder($params["order"], $desc);
            if (!is_null($givenorder)) {
                $orderad = $desc ? "DESC" : "ASC";

                if ($givenorder === "rdate") {
                    $orderby = "Mission.released $orderad";
                } elseif ($givenorder === "udate") {
                    $orderby = "Mission.updated $orderad";
                } elseif ($givenorder === "rating") {
                    $divisor = 8;
                    $fields[] = "CASE WHEN COUNT(Rating.rating) = 0 THEN 0 ELSE SUM(Rating.rating - 5) / $divisor * (EXP(COUNT(Rating.rating)) - EXP(-COUNT(Rating.rating))) / (EXP(COUNT(Rating.rating)) + EXP(-COUNT(Rating.rating))) END AS score";
                    $joins[] = "LEFT JOIN Rating ON Mission.id = Rating.mission";
                    $orderby = "score $orderad, Mission.title ASC";
                } else {
                    $orderby = "Mission.title $orderad";
                }
            }
        }
        $query .= implode(" ", $joins) . " ";
        if (!empty($wheres)) {
            $query .= "WHERE " . implode(" AND ", $wheres) . " ";
        }

        $tmpr = array_merge(array("SELECT COUNT(*) AS `count` " . $query), $r);
        $total = call_user_func_array(array($this->db, "query"), $tmpr)->one()['count'];
        
        if (!empty($groupby)) {
            $query .= implode(" ", $groupby) . " ";
        }

        $query .= "ORDER BY $orderby ";
        $query = "SELECT " . implode(", ", $fields) . " " . $query;
        if (!empty($params["page"])) {
            $page = intval($params["page"]);
            if ($page > 0) {
                $page = $page - 1;
            }
            $query .= "LIMIT ? OFFSET ?";
            $r[] = PERPAGE;
            $r[] = PERPAGE * $page;
        }
        array_unshift($r, $query);
        return call_user_func_array(array($this->db, "query"), $r)->all();
    }

    public function searchMembers(array $params, int &$total = null)
    {
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
            if ($page > 0) {
                $page = $page - 1;
            }
            $query .= "LIMIT ? OFFSET ?";
            $r[] = PERPAGE;
            $r[] = PERPAGE * $page;
        }
        array_unshift($r, $query);
        return call_user_func_array(array($this->db, "query"), $r)->all();
    }

    public function searchAuthors(array $params, int &$total = null)
    {
        $wheres = array();
        $r = array();
        $joins = array("LEFT JOIN User ON Author.userid = User.id");
        $fields = array("Author.id AS `id`", "Author.userid AS `userid`", "IFNULL(User.username, Author.`name`) AS `name`");
        $table = "Author";
        $query = "FROM " . $table . " ";
        if (!empty($params["q"])) {
            $wheres[] = "Author.name LIKE ? OR User.username LIKE ?";
            $r[] = prepareSearch($params["q"]);
            $r[] = prepareSearch($params["q"]);
        }
        $orderby = "`name`";
        $orderad = "ASC";
        $desc = false;
        if (!empty($params["order"])) {
            $givenorder = parseSearchOrder($params["order"], $desc);
            if (!is_null($givenorder)) {
                $orderad = $desc ? "DESC" : "ASC";
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
            if ($page > 0) {
                $page = $page - 1;
            }
            $query .= "LIMIT ? OFFSET ?";
            $r[] = PERPAGE;
            $r[] = PERPAGE * $page;
        }
        array_unshift($r, $query);
        return call_user_func_array(array($this->db, "query"), $r)->all();
    }
    
    public function close()
    {
        return $this->db->close();
    }
}
