<?php
    require './vendor/autoload.php';
    header('Content-Type: application/json');
    header("Access-Control-Allow-Origin: *");
    $config = json_decode(file_get_contents('config.json'), true);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $conn = r\connect('localhost');
        if (isset($_GET['name'])) {
            if ($_GET['online'] === "true") {
                if ((new DateTime())->getTimestamp() - (DateTime::createFromFormat("Y-m-d H:i:s", json_decode(json_encode(r\db('zirk_bots')->table('ping')->filter(array('name' => $_GET['name']))->run($conn)->ToArray()[0]), true)["date"]))->getTimestamp() < 120) {
                    header("HTTP/1.1 204 No Content");
                } else {
                    header("HTTP/1.1 503 Service Unavailable");
                }
            } else if ($_GET['shield'] === "true") {
                echo(json_encode(array(
                    "schemaVersion" => 1,
                    "label" => "Server count",
                    "message" => strval(json_decode(json_encode(r\db('zirk_bots')->table('ping')->filter(array('name' => $_GET['name']))->run($conn)->ToArray()[0]), true)["serverCount"]),
                    "color" => "green"
                )));
            } else if ($_GET['small'] === "true") {
                $commands = 0;
                foreach (end(json_decode(json_encode(r\db('zirk_bots')->table('ping')->filter(array('name' => $_GET['name']))->run($conn)->ToArray()[0]), true)["modules"]) as $key => $value){
                    $commands += $value;
                }
                echo(json_encode(array(
                    "serverCount" => json_decode(json_encode(r\db('zirk_bots')->table('ping')->filter(array('name' => $_GET['name']))->run($conn)->ToArray()[0]), true)["serverCount"],
                    "commandsLastMonth" => $commands
                )));
            } else {
                echo(json_encode(array(
                    "message" => json_decode(json_encode(r\db('zirk_bots')->table('ping')->filter(array('name' => $_GET['name']))->run($conn)->ToArray()[0]), true)
                )));
            }
        } else {
            header("HTTP/1.1 400 Bad Request");
            echo(json_encode(array(
                "message" => "You must specify a name"
            )));
        }
    }
    else if (hash('sha256', $_POST['token']) === $config['token'])
    {
        if (isset($_POST['name'])) // Update bot stats
        {
            $conn = r\connect('localhost');
            if (intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->count()->run($conn)) === 0) {
                r\db('zirk_bots')->table('ping')->insert(array('name' => $_POST['name'], 'date' => date("Y-m-d H:i:s")))->run($conn);
                echo(json_encode(array(
                    "code" => 200,
                    "message" => 'Element inserted'
                )));
            } else {
                $arr = array('date' => date("Y-m-d H:i:s"));
                if (isset($_POST['serverCount'])) { // Nb of servers the bot is in
                    $arr['serverCount'] = intval($_POST['serverCount']);
                }
                if (isset($_POST['nbMsgs'])) { // Nb of msg sent
                    $date = date('ymdH');
                    $arr['nbMsgs'][$date] = intval($_POST['nbMsgs']) + intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['nbMsgs'][$date]);
                }
                /*if (isset($_POST['modules'])) { // (Sanara) Message permodules
                    $date = date('ym');
                    $arr['modules'][$date][$_POST['modules']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['modules'][$date][$_POST['modules']]) + 1;
                    $date = date('ymdH');
                    $arr['serverModules'][$date][$_POST['modules']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['serverModules'][$date][$_POST['modules']]) + 1;
                }
                if (isset($_POST['commands'])) { // (Sanara) Message per commands
                    $date = date('ym');
                    $arr['commands'][$date][$_POST['commands']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['commands'][$date][$_POST['commands']]) + 1;
                    $date = date('ymdH');
                    $arr['serverCommands'][$date][$_POST['commands']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['serverCommands'][$date][$_POST['commands']]) + 1;
                }
                if (isset($_POST['serversBiggest'])) { // (Sanara) Most populated servers
                    $i = 0;
                    foreach (explode('$', $_POST['serversBiggest']) as $e1) {
                        $y = 0;
                        foreach (explode('|', $e1) as $e2) {
                            $arr['serversBiggest'][$i][$y] = $e2;
                            $y++;
                        }
                        $i++;
                    }
                }*/
                if (isset($_POST['bestScores'])) { // (Sanara) Best scores
                    $tmpArr = explode('$', $_POST['bestScores']);
                    $arr['bestScores']['general'] = explode('|', $tmpArr[0]);
                    $arr['bestScores']['shiritori'] = explode('|', $tmpArr[1]);
                    $arr['bestScores']['anime'] = explode('|', $tmpArr[2]);
                    $arr['bestScores']['booru'] = explode('|', $tmpArr[3]);
                    $arr['bestScores']['kancolle'] = explode('|', $tmpArr[4]);
                    $arr['bestScores']['azurlane'] = explode('|', $tmpArr[5]);
                    $arr['bestScores']['fatego'] = explode('|', $tmpArr[6]);
                    $arr['bestScores']['pokemon'] = explode('|', $tmpArr[7]);
                    $arr['bestScores']['girlsfrontline'] = explode('|', $tmpArr[8]);
                    $arr['bestScores']['arknights'] = explode('|', $tmpArr[9]);
                    $arr['bestScores']['arkaudio'] = explode('|', $tmpArr[10]);
                }
                if (isset($_POST['errors'])) { // (Sanara) Last command answer (ok or exception)
                    $date = date('ymd');
                    $arr['errors'][$date][$_POST['errors']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['errors'][$date][$_POST['errors']]) + 1;
                }
                /*if (isset($_POST['booru'])) { // (Sanara) Booru command
                    $date = date('ym');
                    $arr['booru'][$date][$_POST['booru']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['booru'][$date][$_POST['booru']]) + 1;
                }*/
                if (isset($_POST['games'])) { // (Sanara) Game command
                    $date = date('ym');
                    $arr['games'][$date][$_POST['games']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['games'][$date][$_POST['games']]) + 1;
                }
                if (isset($_POST['commands'])) { // (Sanara) Command per server
                    $date = date('ymdH');
                    $arr['commands'][$date][$_POST['commands']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['commands'][$date][$_POST['commands']]) + 1;
                }
                r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->update($arr)->run($conn);
                echo(json_encode(array(
                    "message" => 'Element updated'
                )));
            }
        }
        else if (isset($_POST['url'])) // Upload image
        {
            if (isset($_POST['action']))
            {
                function generateHash($url) {
                    $value = 0;
                    foreach(str_split($url) as $c) {
                        $value += intval($c);
                    }
                    return $value;
                }
                $url = $_POST['url'];
                $arr = explode('.', $url);
                $extension = end($arr);
                $fileName = generateHash($url) . "." . $extension;
                if ($_POST['action'] == 'upload') // Upload a file
                {
                    file_put_contents($fileName, file_get_contents($url));
                    echo(json_encode(array(
                        "url" => 'https://api.zirk.eu/' . $fileName
                    )));
                }
                else if ($_POST['action'] == 'delete') // Delete a previously uploaded file
                {
                    if (file_exists($fileName))
                    {
                        unlink($fileName);
                        echo(json_encode(array(
                            "message" => 'Element deleted'
                        )));
                    }
                    else
                    {
                        header("HTTP/1.1 400 Bad Request");
                        echo(json_encode(array(
                            "message" => "Invalid URL"
                        )));
                    }
                }
                else
                {
                    header("HTTP/1.1 400 Bad Request");
                    echo(json_encode(array(
                        "message" => "Action must be upload or delete"
                    )));
                }
            }
            else
            {
                header("HTTP/1.1 400 Bad Request");
                echo(json_encode(array(
                    "message" => "You must specify an action"
                )));
            }
        }
        else
        {
            header("HTTP/1.1 400 Bad Request");
            echo(json_encode(array(
                "message" => "You must specify a name or an url"
            )));
        }
    }
    else
    {
        header("HTTP/1.1 401 Unauthorized");
        echo(json_encode(array(
            "message" => "You must provide a valid authentification token"
        )));
    }
?>