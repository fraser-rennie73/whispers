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

function BuildList() {
    
    $qCat = (int)$_GET["cat"];
    $qId = (int)$_GET["id"];
    $qPage = $_GET["page"];
    
    $content = "cat = '" . $qCat . "'";
    $content .= "empty = " . ( empty($qCat) ? "YES" : "NO" );

    $root = "https://fraser-rennie73.github.io/whispers-redirect/";
    $page = "index.html";
    $query = "?cat=";

    if(!empty($qCat)) {
         $query .= $qCat . "&Id=";
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
        $content .= "<a href=\"" . $query . $item->Id . "\">" . $item->Name . "</a>";
        $content .= "</li>";
    }
    $content .= "</ul>";
    return $content;
}

?>