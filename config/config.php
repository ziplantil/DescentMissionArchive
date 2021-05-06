<?php
if (!defined('I_AM_DXMA')) {
    die();
}

// root path of frontend (without trailing slash)
// the path must be visible to the web browser
define('FRONTEND', '/dxma');

// where files will be stored on the disk (under subdirs). no trailing slash
// make sure to create the directory and ensure that the user under which
// php is running has write permissions to it
// note: DXMA does not place dummy index files. the dir might be browseable
define('FILEPATH', '/home/dxma/files');
// the above path but from the point of view of the web browser
// this is NOT relative to FRONTEND. no trailing slash
define('FILEURL', '/dxma/files');

define('MB', 1048576);
// maximum mission file size in bytes
define('MAXFILESIZE', 50 * MB);
// maximum screenshot file size in bytes
define('MAXIMGSIZE', 10 * MB);
// make sure to also adjust PHP max upload size!

// whether new account registrations are allowed
define('ALLOW_REGISTRATIONS', true);

// results per page
define('PERPAGE', 25);

// SQL database host
define('DBHOST', 'localhost');
// SQL database user name
define('DBUSER', 'testuser');
// SQL database password
define('DBPASS', 'testpass');
// SQL database name
define('DBNAME', 'dxma');

// for the email message; the public URL to access this instance.
// this one DOES need a trailing slash
define('PUBLIC_URL', 'https://example.com/dxma/');
// whether email is configured (for "forgot my password")
// PHP mail/sendmail must be configured if TRUE!!
define('CAN_EMAIL', FALSE);

// max length for description
define('DESC_MAXLENGTH', 5000);

// allowed file extensions for missions
define('ALLOWED_MISSION_EXTS', ['.zip', '.7z', '.rar', '.gz', '.bz2']);
// allowed file extensions for screenshots
define('ALLOWED_SCREENSHOT_EXTS', ['.bmp', '.png', '.jpg', '.jpeg', '.webp']);

define('MODE_ENUM', [["SP", "Singleplayer/co-op"], ["MP", "Multiplayer"], ["CTF", "Capture the Flag"], ["T", "Team"], ["H", "Hoard"], ["TH", "Team Hoard"]]);
define('GAME_ENUM', [["D1", "Descent 1"], ["D2", "Descent 2"], ["D3", "Descent 3"], ["XL", "D2X-XL"], ["OL", "Overload"]]);

// must have at least one
define('REGISTER_CHECKS', [
    ["The Descent trilogy has ___ (number) games", "3"],
    ["In 2018, a spiritual successor to Descent called ___ was released", "overload"],
    ["How many degrees of freedom? (as a number less than one hundred)", "6"],
    ["How many levels in total in both Descent I and II (not including secret levels or add-ons?)", "51"],
    ["The first word of the weapon in Descent II that fires bouncing shots?", "phoenix"],
    ["Descent 3 came out in... (four-digit year)", "1999"],
    ["The four-letter megacorporation featured in Descent games is?", "ptmc"],
    ["You should head to the exit after causing a ___ meltdown", "reactor"]
]);

define('DEBUG', 0);
