<?php
if (!defined('ABSPATH')) exit;

/** @var array $card */
$card = isset($card) && is_array($card) ? $card : [];

require_once get_stylesheet_directory() . '/template-parts/book/ui.php';

echo mfb_ui_card($card);