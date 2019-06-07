<?php

use Notion\Entities\Blocks\CollectionRowBlock;
use Notion\Entities\Blocks\CollectionViewBlock;
use Notion\NotionClient;
use Symfony\Component\Dotenv\Dotenv;

require '../vendor/autoload.php';
(new Dotenv())->load(__DIR__ . '/../.env');

$client = new NotionClient(getenv('MADEWITHLOVE_NOTION_TOKEN'));

$coffees = collect([
    'Ghent' => 'a61eb783a20940b59652fbdedb9a0292?v=c99913c2de5548af8c56c2337406fbe4',
    'Leuven' => '602c2098ceac4816bf5a27ce5f2d237d?v=702bd45ffc1c4c378c6f91d6e90a36a5'
])
    ->map(function (string $url) use ($client) {
        return $client->getBlock('https://www.notion.so/madewithlove/' . $url);
    })
    ->map(function (CollectionViewBlock $view) {
        return $view->getRows()->sortBy(function (CollectionRowBlock $row) {
            return $row->name;
        });
    });
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>madewithlove.coffee</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/minty/bootstrap.min.css">
</head>
<body style="padding: 2rem">
<div class="container">
    <div class="row">
        <?php foreach ($coffees as $location => $rows): ?>
            <div class="col">
                <h1><?= $location ?> Coffee</h1>
                <hr>
                <?php /** @var CollectionRowBlock $row */
                foreach ($rows as $row): ?>
                    <div class="card mb-2">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= $row->name ?>
                            </h5>
                            <?php if ($row->country || $row->region): ?>
                                <h6 class="card-subtitle mb-2 text-muted">
                                    from
                                    <strong><?= trim(
                                        sprintf('%s, %s', $row->country, $row->region),
                                        ' ,'
                                    ) ?></strong>
                                </h6>
                            <?php endif; ?>
                            <p class="card-text">
                                <strong>Roaster:</strong> <?= $row->roaster ?><br />
                                <?php if ($row->tasting_notes): ?>
                                    <strong>Tasting Notes:</strong><br />
                                    <?= $row->tasting_notes ?><br />
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
