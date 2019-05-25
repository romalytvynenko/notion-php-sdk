<?php

use Notion\Entities\Blocks\CollectionRowBlock;
use Notion\Entities\Blocks\CollectionViewBlock;
use Notion\NotionClient;
use Symfony\Component\Dotenv\Dotenv;

require '../vendor/autoload.php';
(new Dotenv())->load(__DIR__ . '/../.env');

/** @var CollectionViewBlock $todoPage */
$client = new NotionClient(getenv('NOTION_TOKEN'));
$todoPage = $client
    ->getBlock(
        'https://www.notion.so/anahkiasen/3d13a98be599441485953749b4dbc8ad?v=f3c354c0549e4589adf10d7eff46a512'
    )
    ->getCollection();

/** @var CollectionRowBlock[] $rows */
$rows = $todoPage->getRows()->sortBy(function (CollectionRowBlock $child) {
    return $child->getProperty('Done')->getValue();
});

$routinePage = $client->getBlock(
    'https://www.notion.so/anahkiasen/764e98e89e1b4b2da097fb5705ebd518?v=98149a197e31481099deb7143012336e'
)->getCollection();
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
<h1><?= $routinePage->getTitle() ?></h1>
<ul>
    <?php echo $routinePage
        ->getRows()
        ->map(function (CollectionRowBlock $block) {
            ?>
        <li><strong> <?= $block->hour ?>:</strong> <?= $block->name ?></li>
        <?php
        }); ?>
</ul>
<h1><?= $todoPage->getTitle() ?></h1>
<h2><?= $todoPage->getDescription() ?></h2>
<table class="table table-striped table-hover">
    <thead>
    <tr>
        <td>ID</td>
        <td>Name</td>
        <td>Priority</td>
        <td>Effort</td>
        <td>Tags</td>
        <td>Done</td>
    </tr>
    </thead>
    <?php foreach ($rows as $child): ?>
        <tr>
            <td><?= $child->getId()->toString() ?></td>
            <td><?= $child->getTitle() ?></td>
            <td style="background-color: <?= $child
                ->getProperty('Priority')
                ->getOptionAttribute('color') ?>"><?= $child->priority ?></td>
            <td style="background-color: <?= $child
                ->getProperty('Effort')
                ->getOptionAttribute('color') ?>"><?= $child->effort ?></td>
            <td><?= $child->tags ?></td>
            <td>
                <div class="custom-control custom-checkbox">
                    <?php if (
                        $child->getProperty('Done')->getValue() === 'Yes'
                    ): ?>
                        <input type="checkbox" class="custom-control-input" name="done" checked>
                    <?php else: ?>
                        <input type="checkbox" class="custom-control-input" name="done">
                    <?php endif; ?>
                    <label class="custom-control-label"></label>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
