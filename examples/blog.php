<?php

use Notion\NotionClient;

require './_bootstrap.php';
$client = new NotionClient(getenv('MADEWITHLOVE_NOTION_TOKEN'));
$blogpostsPage = $client
    ->getBlock('https://www.notion.so/f0dd308b371c4885a1feca986e7b6fa3?v=784d3dcaafda4bf69d0682efc378a319')
    ->getCollection();
$blogposts = $blogpostsPage->getRows();

if ($article = $_GET['article'] ?? null) {
    $article = $client->getBlock($article);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/sketchy/bootstrap.min.css">
    <title>My Notion-Powered Blog</title>
</head>
<body style="padding: 5rem">
<h1>My Blog</h1>
<aside>
    <h2><?= $blogpostsPage->title ?></h2>
    <ul>
        <?php foreach ($blogposts as $blogpost): ?>
            <li><a href="?article=<?= $blogpost->id ?>"><?= $blogpost->title ?></a></li>
        <?php endforeach; ?>
    </ul>
</aside>
<?php if ($article): ?>
<main>
    <h2><?= $article->title ?></h2>
    <h3>Length: <?= $article->length ?></h3>
    <?= $article->toHtml() ?>
</main>
<?php endif; ?>
</body>
</html>
