<?php
    require './vendor/autoload.php';
    header('Content-Type: application/json');
    $config = json_decode(file_get_contents('config.json'), true);
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("HTTP/1.1 405 Method Not Allowed");
        echo(json_encode(array(
            "code" => 405,
            "message" => "Request must be POST"
        )));
    }
    else if (hash('sha256', $_POST['token']) === $config['token'])
    {
        if (isset($_POST['action']) && isset($_POST['name']))
        {
            if ($_POST['action'] === 'get')
            {
                $conn = r\connect('localhost');
                echo(json_encode(array(
                    "code" => 200,
                    "message" => json_decode(json_encode(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]), true)
                )));
            }
            else if ($_POST['action'] === 'add')
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
                    if (isset($_POST['serverCount'])) {
                        $arr['serverCount'] = intval($_POST['serverCount']);
                    }
                    if (isset($_POST['nbMsgs'])) {
                        $date = date('ymdH');
                        $arr['nbMsgs'][$date] = intval($_POST['nbMsgs']) + intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['nbMsgs'][$date]);
                    }
                    if (isset($_POST['modules'])) {
                        $date = date('ym');
                        $arr['modules'][$date][$_POST['modules']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['modules'][$date][$_POST['modules']]) + 1;
                        $date = date('ymdH');
                        $arr['serverModules'][$date][$_POST['modules']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['serverModules'][$date][$_POST['modules']]) + 1;
                    }
                    if (isset($_POST['serversBiggest'])) {
                        $i = 0;
                        foreach (explode('$', $_POST['serversBiggest']) as $e1) {
                            $y = 0;
                            foreach (explode('|', $e1) as $e2) {
                                $arr['serversBiggest'][$i][$y] = $e2;
                                $y++;
                            }
                            $i++;
                        }
                    }
                    if (isset($_POST['errors'])) {
                        $date = date('ymd');
                        $arr['errors'][$date][$_POST['errors']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['errors'][$date][$_POST['errors']]) + 1;
                    }
                    if (isset($_POST['booru'])) {
                        $date = date('ym');
                        $arr['booru'][$date][$_POST['booru']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['booru'][$date][$_POST['booru']]) + 1;
                    }
                    if (isset($_POST['games'])) {
                        $date = date('ym');
                        $arr['games'][$date][$_POST['games']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['games'][$date][$_POST['games']]) + 1;
                    }
                    if (isset($_POST['commandServs'])) {
                        $date = date('ymdH');
                        $arr['commandServs'][$date][$_POST['commandServs']] = intval(r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->run($conn)->ToArray()[0]['commandServs'][$date][$_POST['commandServs']]) + 1;
                    }
                    r\db('zirk_bots')->table('ping')->filter(array('name' => $_POST['name']))->update($arr)->run($conn);
                    echo(json_encode(array(
                        "code" => 200,
                        "message" => 'Element updated'
                    )));
                }
            }
            else
            {
                header("HTTP/1.1 400 Bad Request");
                echo(json_encode(array(
                    "code" => 400,
                    "message" => "Invalid action"
                )));
            }
        }
        else
        {
            header("HTTP/1.1 400 Bad Request");
            echo(json_encode(array(
                "code" => 400,
                "message" => "You must specify an action and a name"
            )));
        }
    }
    else
    {
        header("HTTP/1.1 401 Unauthorized");
        echo(json_encode(array(
            "code" => 401,
            "message" => "You must provide a valid authentification token"
        )));
    }
?>