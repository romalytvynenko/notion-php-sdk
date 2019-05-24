<?php

use Notion\Blocks\CollectionViewBlock;
use Notion\NotionClient;
use Symfony\Component\Dotenv\Dotenv;

require '../vendor/autoload.php';
(new Dotenv())->load(__DIR__.'/../.env');

/** @var CollectionViewBlock $collectionView */
$client = new NotionClient(getenv('NOTION_TOKEN'));
$collectionView = $client->getBlock('https://www.notion.so/anahkiasen/3d13a98be599441485953749b4dbc8ad?v=f3c354c0549e4589adf10d7eff46a512');
$collection = $collectionView->getCollection();
dd($collection, $collection->getChildren());
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Todo App</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/minty/bootstrap.min.css">
</head>
<body style="padding: 2rem">
<h1><?= $collection->getTitle() ?></h1>
<h2><?= $collection->getDescription() ?></h2>
<table class="table">
    <thead>
    <tr>
        <td>ID</td>
        <td>Name</td>
        <td>Done</td>
    </tr>
    </thead>
    <?php foreach ($collection->getChildren() as $child): ?>
    <tr>
        <td><?= $child->getId()->toString() ?></td>
        <td><?= $child->getTitle() ?></td>
        <td><?= $child->getProperty('Done') ? 'Y' : 'N' ?></td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
