<?php
// header.php
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <!-- wp:modfarm/site-background {"mode":"image","imageUrl":"https://example.com/path/bg.jpg","size":"cover","repeat":"no-repeat","attachment":"fixed"} /-->
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">