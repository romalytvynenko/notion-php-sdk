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

/** @var \Illuminate\Support\Collection $issuesGroomed */
$issuesGroomed = $nextSprint
    ->getChildren()
    ->first(function (Notion\Records\Blocks\BasicBlock $block) {
        return $block->getCollection() ? $block->getCollection()->title : false;
    })
    ->getCollection()
    ->getRows();

/** @var CollectionRowBlock[] $pendingProposals */
$pendingProposals = $client
    ->getBlock(getenv('URL_PROPOSALS_PAGE'))
    ->getRows()
    ->filter(function (CollectionRowBlock $block) {
        return $block->status === 'Review';
    });

$sprint = $sprints->first(function (CollectionRowBlock $block) {
    return $block->status === 'Current';
});

function statistic(string $title, $value): void
{
    if (is_callable($value)) {
        ob_start();
        $value();
        $value = ob_get_clean();
    } ?>
    <article class="card">
        <div class="card-body text-center d-flex p-0">
            <h3 class="card-title text-uppercase text-right bg-primary text-white p-3 font-weight-lighter m-0 " style="width: 25%">
                <?= $title; ?>
            </h3>
            <h2 class="p-3 m-0 font-weight-lighter text-left "><?= $value; ?></h2>
        </div>
    </article>
    <?php
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Project dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/litera/bootstrap.min.css">
</head>
<body style="padding: 2rem">
<section class="container-full">
    <h1>Project Dashboard</h1>
    <section class="card-grid">
        <?php statistic('Current sprint', $sprint->title); ?>
        <?php statistic('Deadline', $sprint->ends->format('Y-m-d')); ?>
        <?php statistic('Issues groomed', $issuesGroomed->count()); ?>
        <?php statistic('Pending proposals', function () use ($pendingProposals): void {
    ?>
            <ul class="list-unstyled">
                <?php foreach ($pendingProposals as $proposal) { ?>
                    <li>
                        <a href=" <?= $proposal->getUrl(); ?>" target="_blank   ">
                            <?= $proposal->title; ?>
                        </a> by <strong><?= $proposal->author; ?></strong>
                    </li>
                <?php } ?>
            </ul>
            <?php
}); ?>
    </section>
</div>
<script
    src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
    crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
