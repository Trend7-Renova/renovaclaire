<?php

/* 
vérification de la configuration wordpress (en cas de migration) 
apeller /wp-adsmin/?config=force pour mettre à jour l'url du site en base de données
*/
function check_current_config(){
  wp_cache_init();
  $config = isset($_GET['config']) ? $_GET['config'] : false;
  if($config) {
    if($config == 'force' && !isset($_GET['old']) && !isset($_GET['siteurl'])){
      $new = get_option('siteurl');

      $new = rtrim(current_site_url().current(explode('wp-admin',$_SERVER['REQUEST_URI'])), '/');
      ?>
      <form method="get">
      <strong>Attention: videz le cache redis avant de valider(redis-cli && FLUSHALL)</strong>
        <input type="hidden" name=config value=force>
        <p>Ancienne Url<br><input type="text" name="old" size="100" placeholder="Ancienne url" value="<?php echo get_option('siteurl');?>"></p>

        <p>Nouvelle Url<br><input type="text" name="siteurl" size="100" placeholder="Nouvelle url" value="<?php echo $new;?>"></p>
        <input type="submit">
      </form>
      <?php
      exit;
    }

    $path = realpath('.');

    $siteurl = addslashes($_GET['siteurl']) ;
    $old = addslashes($_GET['old']) ;

    if($config == 'force' && $old && $siteurl){

      /* mise à jour de l'url du site dans les options */
      update_option('home',$siteurl);
      update_option('siteurl',$siteurl);


      /* mise à jour de l'url du site dans les posts */
      $GLOBALS['wpdb']->get_results('UPDATE '.$GLOBALS['wpdb']->prefix.'options SET option_value = REPLACE(option_value,"'.$old.'","'.$siteurl.'")');

      $GLOBALS['wpdb']->get_results('UPDATE '.$GLOBALS['wpdb']->prefix.'posts SET post_content = REPLACE(post_content,"'.$old.'","'.$siteurl.'")');

      $GLOBALS['wpdb']->get_results('UPDATE '.$GLOBALS['wpdb']->prefix.'posts SET guid = REPLACE(guid,"'.$old.'","'.$siteurl.'")');

      ?><p>update terminated : <?php echo $old;?> -> <?php echo $siteurl;?></p><a href="<?php echo $siteurl;?>/wp-admin/">Continue</a><?php 
      exit;
    }
  }
}
add_action( 'init', 'check_current_config' );

/**
 * Obtient l'URL du site actuel
 *
 * @param bool|string $host Le nom d'hôte
 * @param bool $request Inclure la chaîne de requête ou non
 * @return string Retourne l'URL complète
 */
function current_site_url($host = false, $request = false)
{
    $host = $host ? $host : $_SERVER['HTTP_HOST'];
    $https = false;
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
        $https = true;
    }

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $https = true;
    }
    $url = ($https ? 'https' : 'http') . '://' . $host;
    if ($request) {
        $url .= $_SERVER['REQUEST_URI'];
    }
    return $url;
}
