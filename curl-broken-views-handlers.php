<?php
// First we need to Log into drupal and set the cookies
    $crl = curl_init();
    $site_url = "http://SITE URL";
    $login_path = "/user/login";
    $url = $site_url.$login_path; 
    curl_setopt($crl, CURLOPT_URL, $url);
    curl_setopt($crl, CURLOPT_COOKIEFILE, "/tmp/cookie.txt");
    curl_setopt($crl, CURLOPT_COOKIEJAR, "/tmp/cookie.txt");
    curl_setopt($crl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($crl, CURLOPT_POST, 1);

    // This array will hold the field names and values.
    $postdata=array(
      "name"=>"USER NAME",
      "pass"=>"PASSWORD",
      "form_build_id"=>"form-FORM ID",
      "form_id"=>"user_login",
      "op"=>"Log in"
    );
    // Tell curl we're going to send $postdata as the POST data
    curl_setopt ($crl, CURLOPT_POSTFIELDS, $postdata);

    $result=curl_exec($crl);
    $headers = curl_getinfo($crl);
    curl_close($crl);
    if ($headers['url'] == $url) {
        die("Cannot login.");
    }

      // access views to find broken handlers

    $views_path = "/admin/structure/views";
    $views_uri = $site_url.$views_path;
    $crl = curl_init();
    curl_setopt($crl,CURLOPT_URL, $views_uri);
    curl_setopt($crl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($crl, CURLOPT_COOKIEFILE, "/tmp/cookie.txt");
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($crl);
   curl_close($crl);

// Get only views edit links
  $regex='|<li class="edit first"><a.*?href="(.*?)"|';
  preg_match_all($regex,$result,$parts);
  $links=$parts[1];

  foreach ($links as $view) {
    $uri = $site_url.$views_path.$view;
   $crl = curl_init();
    curl_setopt($crl,CURLOPT_URL, $uri);
    curl_setopt($crl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($crl, CURLOPT_COOKIEFILE, "/tmp/cookie.txt");
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    $curl_scraped_page = curl_exec($crl);
    $filename = 'brokenviews_log';
    curl_close($crl);

if (strpos($curl_scraped_page,'broken/missing') !== false) {
   $time = date("D M d, Y G:i a");
   echo '<br>'. $time . ' Broken view found here: <a href=" '. $uri . '">'. $uri . '</a>';
   file_put_contents("brokenviews_log/$filename.html",'<br>'. $time . ' Broken view found here: <a href=" '. $uri . '">'. $uri . '</a>', FILE_APPEND | LOCK_EX);
    }
    else
      { echo '<br>No Broken views found: <a href=" '. $uri . '">'. $uri . '</a>';}
  }
?>