<?php

use Notion\NotionClient;

require './_bootstrap.php';

$client = new NotionClient(getenv('MADEWITHLOVE_NOTION_TOKEN'));
$locations = $client
    ->getBlock('https://www.notion.so/madewithlove/f61f3cbb2a454251aebf562b8880ec28?v=5bd8eb0d4eb14f54af432076253371a8')
    ->getRows()
    ->filter(function ($block) {
        return $block->where;
    })
    ->sortBy(function ($block) {
        return $block->where;
    });

$properties = $locations
    ->first()
    ->getProperties()
    ->filter(function ($_, $key) {
        return (bool) $key;
    })
    ->keys()
    ->values()
    ->filter(function (string $property, $key) {
        return $property !== 'More information';
    });
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RetreatReviews</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/minty/bootstrap.min.css">
</head>
<form method="POST">
    <div class="form-group">
        <?php foreach ($properties as $property) { ?>
            <label for="<?= $property; ?>"><?= $property; ?></label>
            <input id="<?= $property; ?>" name="<?= $property; ?>?" type="text" class="form-control">
        <?php } ?>
    </div>
    <button type="submit" class="btn btn-primary">Add Location</button>
</form>
<body style="padding: 2rem">
    <div class="container-full">
        <table class="table">
        <thead>
        <tr>
        <?php foreach ($properties as $property) { ?>
            <th><?= $property; ?></th>
        <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($locations as $location) { ?>
            <tr>
                <?php foreach ($properties as $property) { ?>
                    <td><?= $location->getProperty($property); ?></td>
                <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
