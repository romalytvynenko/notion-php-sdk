<?php

use Illuminate\Support\Str;
use Notion\NotionClient;
use Notion\Records\Blocks\CollectionRowBlock;
use Notion\Records\Blocks\PageBlock;

require './_bootstrap.php';
$client = new NotionClient(getenv('MADEWITHLOVE_NOTION_TOKEN'));
$blogpostsPage = $client
    ->getBlock('https://www.notion.so/f0dd308b371c4885a1feca986e7b6fa3?v=784d3dcaafda4bf69d0682efc378a319')
    ->getCollection();

$blogposts = $blogpostsPage->getRows()->sortByDesc(function (CollectionRowBlock $block) {
    return $block->created_time->format('Y-m-d');
});

if ($article = $_GET['article'] ?? null) {
    /** @var PageBlock $article */
    $article = $client->getBlock($article);
}

function icon(PageBlock $block)
{
    return !Str::contains($block->icon, 'http') ? $block->icon : '<img src="' . $block->icon . '" class="icon">';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/litera/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/themes/prism-coy.min.css">
    <title>My Notion-Powered Blog</title>
    <style>
        .icon {
            width: 1.25em;
        }
    </style>
</head>
<body style="padding: 5rem">
<div class="container-full">
    <h1>My Notion-Powered Blog</h1>
    <div class="row">
        <aside class="col-3">
            <h2><?= $blogpostsPage->title ?></h2>
            <ul>
                <?php foreach ($blogposts as $blogpost): ?>
                    <li>
                        <?= icon($blogpost) ?>
                        <a href="?article=<?= $blogpost->id ?>"><?= $blogpost->title ?></a><br>
                        <small class="text-muted">
                            <?= $blogpost->created_time->format('Y-m-d') ?>
                        </small></li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <?php if ($article): ?>
            <main class="col">
                <img src="<?= $article->cover ?>" class="img-fluid">
                <h2>
                    <?= icon($article) ?>
                    <?= $article->title ?><br />
                    <small class="text-muted">Length: <?= $article->length ?></small><br />
                    <small class="text-muted">Status: <?= $article->status ?></small>
                </h2>
                <hr>
                <?= $article->toHtml() ?>
            </main>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/components/prism-bash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/components/prism-markup-templating.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.16.0/components/prism-php.min.js"></script>
</body>
</html>
