<?php

require './_bootstrap.php';

$blocks = array(
    0 => array(
        0 => 'At madewithlove we currently use (mainly but not only) '
    ),
    1 => array(
        0 => 'PHP CS Fixer',
        1 => array(
            0 => array(
                0 => 'a',
                1 => 'https://cs.sensiolabs.org/'
            )
        )
    ),
    2 => array(
        0 => ', however a quick glance at the list of the things it fixes puts into light the main reason I still use Prettier in addition to it: PCS conflate'
    )
);

echo \Notion\Utils::notionToMarkdown($blocks);
