<?php

function ReadCategories() {
    $conn = GetConnection();
	  $bus = new CategoryBus($conn);
	  $cats = $bus->ReadAll();
    CloseConnection( $conn );

    return $cats;
}
function ReadArticles($catId) {
    $conn = GetConnection();
	  $artBus = new ArticleBus($conn);
	  $articles = $artBus->ReadAllForCategory($catId);
	  CloseConnection( $conn );

    return $articles;
}

function ReadArticle($artId) {
    $conn = GetConnection();
    $artBus = new ArticleBus($conn);
    $article = $artBus->Read($artId);
    CloseConnection( $conn );

    $list = array();
    $obj = json_decode($article->Text);
    $headerIndex = 0;	

  	foreach($obj->blocks as $block)
  	{
        if ( $block->type == "header" || $block->type == "investigator" )
    	{
            $headerIndex++;
            $c = new Category();
            $c->Id = $headerIndex;
            $c->Name = $block->data->text;
            $list[] = $c;
        }
    }
    
    return $list;
}

function BuildList() {
    
    $qCat = (int)$_GET["cat"];
    $qId = (int)$_GET["id"];
    $qPage = $_GET["page"];

    $root = "https://fraser-rennie73.github.io/whispers-redirect/";

    $pages = array( "whispers", "cabin", "dream-walking", "dream-walking-la", "magic" );
    if ($qPage == $pages[1]) {
        $page = "cabin.html";
    } elseif ($qPage == $pages[2]) {
        $page = "dream-walking.html";
    } elseif ($qPage == $pages[3]) {
        $page = "dream-walking-la.html";
    } elseif ($qPage == $pages[4]) {
        $page = "magic.html";
    } else {
        $page = "index.html";
        $qPage = $pages[0];
    }

    $q = "";
  	if( !empty($_SERVER['QUERY_STRING']) )
  	{
	    	$q .= $_SERVER['QUERY_STRING'] . "&";
  	}

    $content = "";
    
    $content .= "id = " . $qId;
    
    $content .= "<ul>";
    foreach($pages as $p) {
        $content .= "<li>";
        if ( $p == $qPage ) {
            $content .= $p;
        } else {
            $content .= "<a href=\"?" . $q . "page=" . $p . "\">" . $p . "</a>";
        }
        $content .= "</li>";
    }
    $content .= "</ul>";
    $content .= "<hr />";

    $query = "?cat=";
    if(!empty($qId)) {
         $query .= $qCat . "&id=" . $qId . "#header-";
         $list = ReadArticle($qId);
    } elseif(!empty($qCat)) {
         $query .= $qCat . "&id=";
         $list = ReadArticles($qCat);
    } else {
         $list = ReadCategories();
    }

    $content .= "<ul>";
    foreach($list as $item)
    {
        $url = $root . $page . $query . $item->Id;

        $content .= "<li>";
        $content .= "<h2>" . $item->Name . "</h2>";
        $content .= "<p>" . $url . "</p>";
        
        $wa = "https://wa.me/?text=" .  urlencode($url . "\n" . $item->Name);
        
        $content .= "<a href=\"$wa\">WhatsApp</a><br /><br />";
        
        if (empty($qId)) {
            $content .= "<a href=\"" . $query . $item->Id . "&page=" . $qPage . "\">" . $item->Name . "</a>";
        }
        $content .= "</li>";
    }
    $content .= "</ul>";
    return $content;
}

?>