<?php

use Illuminate\Support\Collection;
use Notion\Records\Blocks\CollectionRowBlock;
use Notion\Records\Blocks\CollectionViewBlock;
use Notion\NotionClient;
use Symfony\Component\Dotenv\Dotenv;

require '../vendor/autoload.php';
(new Dotenv())->load(__DIR__ . '/../.env');

/** @var CollectionViewBlock $todoPage */
$client = new NotionClient(getenv('NOTION_TOKEN'));
$todoPage = $client
    ->getBlock('https://www.notion.so/anahkiasen/3d13a98be599441485953749b4dbc8ad?v=f3c354c0549e4589adf10d7eff46a512')
    ->getCollection();

/** @var Collection|CollectionRowBlock[] $todos */
$todos = $todoPage->getRows()->sortBy(function (CollectionRowBlock $child) {
    return $child->done;
});

if ($title = $_POST['title'] ?? '') {
    $block = $todoPage->addRow(['title' => $title]);
} elseif ($id = $_GET['mark_as_done'] ?? null) {
    $row = $todoPage->getRow($id);
    $row->done = true;
}
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
<h1><?= $todoPage->title ?></h1>
<h2><?= $todoPage->description ?></h2>
<form method="POST">
    <div class="form-group">
        <label for="title">Task</label>
        <input id="title" name="title" type="text" class="form-control" placeholder="Do something">
    </div>
    <button type="submit" class="btn btn-primary">Add task</button>
</form>
<table class="table table-striped table-hover mt-3">
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
    <?php foreach ($todos as $child): ?>
        <tr>
            <td><?= $child->id ?></td>
            <td><?= $child->title ?></td>
            <td style="background-color: <?= $child
                ->getProperty('Priority')
                ->getOptionAttribute('color') ?>"><?= $child->priority ?></td>
            <td style="background-color: <?= $child
                ->getProperty('Effort')
                ->getOptionAttribute('color') ?>"><?= $child->effort ?></td>
            <td><?= $child->tags ?></td>
            <td>
                <div class="custom-control custom-checkbox">
                    <a href="?mark_as_done=<?= $child->id ?>">
                    <?php if ($child->done): ?>
                        <input type="checkbox" class="custom-control-input" name="done" checked>
                    <?php else: ?>
                        <input type="checkbox" class="custom-control-input" name="done">
                    <?php endif; ?>
                    <label class="custom-control-label"></label>

                    </a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
