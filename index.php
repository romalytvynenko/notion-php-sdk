<?php

use Notion\NotionClient;
use Symfony\Component\Dotenv\Dotenv;

require 'vendor/autoload.php';

// Load dotfiles
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$client = new NotionClient(getenv('NOTION_TOKEN'));
$client->getBlock('https://www.notion.so/anahkiasen/Life-693febf12aa74a6283ea2cdd3ec50939');
