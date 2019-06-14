<?php

use Notion\NotionClient;
use Notion\Records\Blocks\CollectionRowBlock;

require './_bootstrap.php';

$client = new NotionClient(getenv('MADEWITHLOVE_NOTION_TOKEN'));
$sprints = $client->getBlock(getenv('URL_PROJECT_PAGE'))->getRows();

/** @var CollectionRowBlock $nextSprint */
$nextSprint = $sprints
    ->sortBy(function (CollectionRowBlock $block) {
        return $block->ends;
    })
    ->first(function (CollectionRowBlock $block) {
        return $block->status === 'Future';
    });

$issuesGroomed = $nextSprint
    ->getChildren()
    ->first(function (Notion\Records\Blocks\BasicBlock $block) {
        return $block->getCollection() ? $block->getCollection()->title : false;
    })
    ->getCollection()
    ->getRows();

$pendingProposals = $client
    ->getBlock('https://www.notion.so/madewithlove/af6f022f76144cc08b78c5b1842c4846?v=bf2d808edb8f4a4c88c12140f41a2552')
    ->getRows()
    ->filter(function (CollectionRowBlock $block) {
        return $block->status === 'Review';
    });

$sprint = $sprints->first(function (CollectionRowBlock $block) {
    return $block->status === 'Current';
});
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>B.OS dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/darkly/bootstrap.min.css">
</head>
<body style="padding: 2rem">
<div class="container-full">
    <h1>Project Dashboard</h1>
    <section class="card-deck">
        <article class="card">
            <div class="card-header">
                Current Sprint
            </div>
            <div class="card-body text-center">
                <h1><?= $sprint->title; ?></h1>
            </div>
        </article>
        <article class="card">
            <div class="card-header">
                Sprint deadline
            </div>
            <div class="card-body text-center">
                <h1><?= $sprint->ends->format('Y-m-d'); ?></h1>
            </div>
        </article>
        <article class="card">
            <div class="card-header">
                Issues groomed for <?= $nextSprint->title; ?>
            </div>
            <div class="card-body text-center">
                <h1><?= $issuesGroomed->count(); ?></h1>
            </div>
        </article>
        <article class="card">
            <div class="card-header">
                Pending proposals
            </div>
            <div class="card-body">
                <ul>
                    <?php foreach ($pendingProposals as $proposal) { ?>
                        <li>

                            <a href=" <?= $proposal->getUrl(); ?>" target="_blank   ">
                                <?= $proposal->title; ?>
                            </a> by <strong><?= $proposal->author; ?></strong>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </article>
    </section>
</div>
<script
    src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
    crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
