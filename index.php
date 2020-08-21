<?php

$action = $_GET['action'];
$config = json_decode(file_get_contents('config.json'), true);

function getCard($name, $link, $icon, $color)
{
    return <<<HTML
        <section class="item-container">
            <div class="item" style="background-color: $color">
                <img class="app-icon" src="$icon">
                <div class="details">
                    <div class="title white">$name</div>
                </div>
                <a class="link white" href="$link" target="_blank">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </section>
    HTML;
}

function getAllCards($config)
{
    $content = "";
    $color = null;
    if(array_key_exists('globalColor', $config['general']))
        $color = $config['general']['globalColor'];
    foreach($config['items'] as $item)
    {
        if($color == null)
            $color = $item['color'];
        $c = getCard($item['name'], $item['link'], 'assets/icons/'.$item['icon'], $color);
        $content .= $c;
    }
    return $content;
}

function getBackgroundFile($config)
{
    switch($config['general']['bgMode'])
    {
        case 'bing':
            return 'https://www.bing.com'.json_decode(file_get_contents('https://www.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1&mkt=en-US'),true)['images'][0]['url'];
        break;
        case 'unsplash':
            //bgParam is a search, like 'nature'
            return 'https://source.unsplash.com/random/1920x1080/?'.$config['general']['bgParam'];
        break;
        case 'static':
            //bgParam is the file path
            return $config['general']['bgParam'];
        break;
        case 'random':
            //bgParam is an image folder path
            $files = scandir($config['general']['bgParam']);
            return $config['general']['bgParam'].'/'.$files[array_rand($files, 1)];
        break;
        default:
            return 'default.jpg';
    }
}

if($action == 'getBackground')
{
    $file = getBackgroundFile($config);
    if(substr($file, 0, 4) == 'http')
    {
        header('Location: '.$file);
    }
    else
    {
        header('Content-Type: '.mime_content_type($file));
        header('Content-Length: '.filesize($file));
        fpassthru(fopen($file, 'rb'));
    }
}
else
{
    $date = date('F j, Y');
    $content = getAllCards($config);
    $new = "";
    if($config['general']['newTab'])
        $new = "target=\"_blank\"";
        
    echo <<<HTML
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>{$config['general']['name']}</title>
                <meta name="mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-capable" content="yes">
                <link rel="stylesheet" href="assets/css/index.css" type="text/css">
                <link rel="stylesheet" href="assets/css/fontawesome.min.css" type="text/css">
            </head>
            <body>
                <div id="app">
                    <div class="content">
                        <main>
                            <div class="head">
                                <h1 class="date">$date</h1>
                                <form id="searchForm" action="https://www.google.com/search" method="get" $new autocomplete="off">
                                    <input id="search" type="text" name="q" placeholder="Search">
                                </form>
                            </div>
                            <div id="sortable">
                                $content
                            </div>
                        </main>
                    </div>
                </div>
                <script>
                    document.querySelector('#searchForm').addEventListener('submit', function() {
                        setTimeout(
                            function() {
                                document.querySelector('#search').value = "";
                            }, 1000
                        );
                    });
                </script>
            </body>
        </html>
HTML;
}