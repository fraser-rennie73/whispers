<?php 

//declare(strict_types=1); // strict requirement

class PageResult {
	
	public string $Content;
	public string $PageTitle;
	public string $PageClass;
	
	public array $Scripts;
	public array $Styles;
	
	public function __construct(string $c, string $t, string $css) 
	{
		$this->Content = $c;
		$this->PageTitle = $t;
		$this->PageClass = $css;
		
		$this->Scripts = array();
		$this->Styles = array();
	}
	
	public function ScriptsHtml() : string
	{
		$html = "";
		
		if( count($this->Scripts) > 0 )
		{
			foreach( $this->Scripts as $script )
			{
				$html .= "<script src=\"$script\"></script>\n";
			}
		}
		
		return $html;
	}
	
	public function StylesHtml() : string
	{
		$html = "";
		
		if( count($this->Styles) > 0 )
		{
			foreach( $this->Styles as $style )
			{
				$html .= "<link href=\"$style\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\"/>\n";
			}
		}
		
		return $html;
	}
}

/* **************************************************************************************************** */

function ProcessForm( string $queryAction, int $queryCat, int $queryId, string $queryImage ) : PageResult
{
	$result = null;
	
	if( !empty($queryId) && $queryAction == "delete" )
	{
		$result = ProcessDeleteArticle($queryAction, $queryCat, $queryId);
	}
	elseif( ( !empty($queryCat) && $queryAction == "add" ) || ( !empty($queryId) && $queryAction == "edit" ) )
	{
		$result = ProcessUpdateArticle($queryAction, $queryCat, $queryId);
	}
	elseif( ( empty($queryCat) && $queryAction == "add" ) || ( empty($queryId) && $queryAction == "edit" ) )
	{
		$result = ProcessUpdateCategory($queryAction, $queryCat, $queryId);
	}
	elseif( $queryAction == "comment" )
	{
		$result = ProcessPostComment($queryAction, $queryCat, $queryId, $queryImage);
	}
    elseif( $queryAction == "search" )
	{
		$result = ProcessSearch();
	}
	
	return( $result );
}

function ProcessSearch() {
    
    $nm = SearchBoxName();
    $searchQuery = htmlspecialchars( $_POST[$nm] );
    
    $url = $_SERVER["PHP_SELF"];
	$url .= "?t=" . idate("U");
	$url .= "&action=search";
	$url .= "&q=$searchQuery";
    
    $content = "<script>";
    $content .= "window.location.replace( \"$url\" )";
    $content .= "</script>";
	
	$result = new PageResult($content, "Zoomthulhu Docs", "edit");
	return( $result );
}

function ProcessPostComment(string $queryAction, int $queryCat, int $queryId, string $queryImage) : PageResult
{
	$comment = ReadComment($queryId, $queryImage);
	
	$conn = GetConnection();

	$bus = new CommentBus( $conn );
	$id = $bus->Add( $comment );
	
	CloseConnection( $conn );
	
	// **********
    
    $queryIndex = (int)$_GET["inum"];
    
    // **********
	
	$url = $_SERVER["PHP_SELF"];
	$url .= "?t=" . idate("U");
	$url .= "&cat=$queryCat&id=$queryId";
    if( !empty( $queryIndex ) ) 
        $url .= "&inum=$queryIndex";
    else if( !empty( $queryImage ) ) 
        $url .= "&image=$queryImage";
	$url .= "#comment-$id";
	
	//$url = htmlspecialchars($url);
	
	$content = "<script>";
	$content .= "window.location.replace( \"$url\" )";
	$content .= "</script>";
	
	/*
	$content = "<p>Article ID:$comment->ArticleId</p>";
	$content .= "<p>Player ID:$comment->PlayerId</p>";
	$content .= "<p>Text:$comment->Text</p>";
	*/
	
	$result = new PageResult($content, "Zoomthulhu Docs", "edit");
	return( $result );
}

function ProcessUpdateCategory(string $queryAction, int $queryCat, int $queryId) : PageResult
{
	$category = ReadCategory();
	
	// ValidateCategory( $category );
	// if( $isValid )
		
	// **********
	
	$conn = GetConnection();

	$bus = new CategoryBus( $conn );
	
	if( $queryAction == "add" )
	{
		$css = strtolower( $category->Name );
		$css = preg_replace( '/[^a-z ]/i', '', $css );
		$css = preg_replace( '/\s/i', '-', $css );
		
		$category->CssClass = $css;
				
		$queryCat = $bus->Add( $category );
		//$queryCat = 1;
	}
	elseif( $queryAction == "edit" )
	{
		$category->Id = $queryCat;
		$bus->Edit( $category );
	}
	
	CloseConnection( $conn );
	
	// **********
	
	$url = $_SERVER["PHP_SELF"];
	$url .= "?t=" . idate("U");
	$url .= "&cat=$queryCat";
	
	//$url = htmlspecialchars($url);
	
	$content = "<script>";
	//$content .= "window.location.replace( \"$url\" )";
	$content .= "history.go(-2);";
	$content .= "</script>";

	$result = new PageResult($content, "Zoomthulhu Docs", "edit");
	return( $result );
}

function ProcessUpdateArticle(string $queryAction, int $queryCat, int $queryId) : PageResult
{
	$article = ReadArticle();
	
	// ValidateArticle( $article );
	// if( $isValid )
		
	// **********
	
	$conn = GetConnection();

	$bus = new ArticleBus( $conn );
	
	if( $queryAction == "add" )
	{
		//$queryId = $bus->Add( $article, $_SESSION['player']['Id'] );
		$queryId = $bus->Add( $article, $_SESSION['player-id'] );
	}
	elseif( $queryAction == "edit" )
	{
		$article->Id = $queryId;
		$bus->Edit( $article );
	}
	
	CloseConnection( $conn );
	
	// **********
	
	$url = $_SERVER["PHP_SELF"];
	$url .= "?t=" . idate("U");
	$url .= "&cat=$queryCat&id=$queryId";
	
	//$url = htmlspecialchars($url);
	
	$content = "<script>";
	//$content .= "window.location.replace( \"$url\" )";
	$content .= "history.go(-2);";
	$content .= "</script>";

	$result = new PageResult($content, "Zoomthulhu Docs", "edit");
	return( $result );
}

function ProcessDeleteArticle(string $queryAction, int $queryCat, int $queryId) : PageResult
{
	
	$conn = GetConnection();

	$bus = new ArticleBus( $conn );
	$bus->Delete( $queryId );
	
	CloseConnection( $conn );
	
	// **********
	
	$url = $_SERVER["PHP_SELF"];
	$url .= "?t=" . idate("U");
	$url .= "&cat=$queryCat";
	
	//$url = htmlspecialchars($url);
	
	$content = "<script>";
	//$content .= "window.location.replace( \"$url\" )";
	$content .= "history.go(-3);";
	$content .= "</script>";

	$result = new PageResult($content, "Zoomthulhu Docs", "edit");
	return( $result );
}

/* **************************************************************************************************** */

function SetAccessToken( array $fbObject )
{
	if( isset($_SESSION['facebook_access_token']) )
	{
		$fbObject['fb']->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}
	else
	{
		// Put short-lived access token in session
		$_SESSION['facebook_access_token'] = (string) $fbObject['accessToken'];

		// OAuth 2.0 client handler helps to manage access tokens
		$oAuth2Client = $fbObject['fb']->getOAuth2Client();

		// Exchanges a short-lived access token for a long-lived one
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;

		// Set default access token to be used in script
		$fbObject['fb']->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}

	// Redirect the user back to the same page if url has "code" parameter in query string
	if( isset($_GET['code']) )
	{
		//header('Location: ./');
		
		$url = FB_REDIRECT_URL;
		if( !empty( $_SESSION['fb-redirect-query'] ) )
		{
			$url .= "?" . $_SESSION['fb-redirect-query'];
		}
		
		header("Location: $url");
	}

	if( !isSet($_SESSION['player']) )
	{
		// Getting user's profile info from Facebook
		try 
		{
			//$graphResponse = $fb->get('/me?fields=name,first_name,last_name,email,link,gender,picture');
			$graphResponse = $fbObject['fb']->get('/me?fields=name,first_name,last_name,email,picture');
			$fbUser = $graphResponse->getGraphUser();
		} 
		catch(FacebookResponseException $e) 
		{
			echo 'Graph returned an error: ' . $e->getMessage();
			session_destroy();
			// Redirect user back to app login page
			header("Location: ./");
			exit;
		} 
		catch( FacebookSDKException $e ) 
		{
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		// Getting user's profile data
		$userData = array();
		
		$userData['id']  		= !empty($fbUser['id']) ? $fbUser['id'] : '';
		$userData['first_name'] = !empty($fbUser['first_name']) ? $fbUser['first_name'] : '';
		$userData['last_name']  = !empty($fbUser['last_name']) ? $fbUser['last_name'] : '';
		$userData['picture']    = !empty($fbUser['picture']['url']) ? $fbUser['picture']['url'] : '';
		$userData['email']  	= !empty($fbUser['email']) ? $fbUser['email'] : '';
		
		$player = new Player();
		$player->FromOauth('facebook', $userData);

		$id = SavePlayerToDb($player);
		$player->Id = $id;
		
		/*
		// Getting user's profile data
		$userData = array();
		$userData['oauth_uid']  = !empty($fbUser['id']) ? $fbUser['id'] : '';
		$userData['first_name'] = !empty($fbUser['first_name']) ? $fbUser['first_name'] : '';
		$userData['last_name']  = !empty($fbUser['last_name']) ? $fbUser['last_name'] : '';
		$userData['picture']    = !empty($fbUser['picture']['url']) ? $fbUser['picture']['url'] : '';
		
		// Store user data in the session
		$_SESSION['userData'] = $userData;
		*/

		$playerSummary = array();
		$playerSummary['Id'] = $player->Id;
		$playerSummary['FirstName'] = $player->FirstName;
		$playerSummary['LastName'] = $player->LastName;
		$playerSummary['Picture'] = $player->Picture;
		$playerSummary['Email'] = $player->Email;

		$_SESSION['player'] = $playerSummary;
		
		UpdatePermissions( $player->Id );
	}
}

function SavePlayerToDb(Player $player) : int
{
	$id = 0;
	$conn = GetConnection();

	$bus = new PlayerBus($conn);
	$dbPlayer = $bus->Read( $player->Email );
	
	if( !isSet($dbPlayer->Id) )
	{
		$id = $bus->Add($player);
	}
	else
	{
		$id = $bus->Edit($dbPlayer->Id, $player);
	}
	
	CloseConnection( $conn );
	return $id;
}

/* **************************************************************************************************** */

function AddGuest( ) : int
{
	$session_id = session_id();
	
	$conn = GetConnection();

	$bus = new PlayerBus($conn);
	$dbGuest = $bus->AddGuest( $session_id );
		
	CloseConnection( $conn );
	return $dbGuest;
}

/* **************************************************************************************************** */

//function BuildPage( array $fbObject ) : PageResult
function BuildPage( ) : PageResult
{
	//define('HELPER_IS_SET', (isSet($fb['helper']) ? "orig-is-set" : "orig-not-set" ));
	//define('HELPER_IS_SET', "xxx" ));
	
	try
	{
		$queryAction = $_GET["action"];
		$queryCat = (int)$_GET["cat"];
		$queryId = (int)$_GET["id"];
		$queryImage = $_GET["image"] ?? '';
		$queryCaption = $_GET["caption"] ?? '';
        $queryIndex = (int)$_GET["inum"];
		
		$result = null;
		
		InitialisePermissions();
		
		//if( $queryAction == "guest" || $queryAction == "login" )
		if( $queryAction == "login" )
		{
			if ($_SERVER["REQUEST_METHOD"] == "POST")
			{
				$playerId = ReadLogin();
				
				if( $playerId <= 0 )
				{
					$_SESSION['continue-as-a-guest'] = true;
					unset( $_SESSION['player-id'] );
					AddGuest();
				}
				else
				{
					unset( $_SESSION['continue-as-a-guest'] );
					$_SESSION['player-id'] = $playerId;
					
					$expire = 60 * 60 * 24 * 30; // 30 days
					setcookie( 'zoomthulhu-player-id', (string)$playerId, time() + $expire, "/" );
					
					$conn = GetConnection();
					$playerbus = new PlayerBus($conn);
					$playerbus->Login($playerId);
					CloseConnection( $conn );
				}
				
				$url = $_SERVER["PHP_SELF"];
				$url .= "?t=" . idate("U");
				if( !empty($queryCat) ) $url .= "&cat=$queryCat";
				if( !empty($queryId) ) $url .= "&id=$queryId";
				if( !empty($queryImage) ) $url .= "&image=$queryImage";
				if( !empty($queryCaption) ) $url .= "&caption=$queryCaption";
				
				header("Location: $url");
			}
			else
			{
				unset( $_SESSION['player-id'] );
				unset( $_SESSION['continue-as-a-guest'] );
			}
		}

		if( !isset($_SESSION['player-id']) && !isSet($_SESSION['continue-as-a-guest']) )
		{
			if( isset($_COOKIE['zoomthulhu-player-id']) )
				$_SESSION['player-id'] = (int)$_COOKIE['zoomthulhu-player-id'];
		}
		
		//if( !isset($fbObject['accessToken']) && !isSet($_SESSION['continue-as-a-guest']) )
		if( !isset($_SESSION['player-id']) && !isSet($_SESSION['continue-as-a-guest']) )
		{
			//$result = BuildLoginPage($fbObject);
			$result = BuildLoginPage();
		}
		else
		{
			//if( !isSet($_SESSION['continue-as-a-guest']) )
			//{
			//	SetAccessToken( $fbObject );
			//}
			
			if( isset($_SESSION['player-id']) )
			{
				UpdatePermissions( $_SESSION['player-id'] );
			}
			
			if ($_SERVER["REQUEST_METHOD"] == "POST")
			{
				$result = ProcessForm($queryAction, $queryCat, $queryId, $queryImage);
			}
			else
			{
				$thePage = "";
				
                if( $queryAction == "search" )  $thePage = "search";
                elseif( !empty($queryImage) || !empty($queryIndex) ) 	$thePage = "image";
				elseif( !empty($queryId) ) 	$thePage = "article";
				elseif( !empty($queryCat) ) $thePage = "category";
				else						$thePage = "home";
				
				if( !empty($queryAction) )
				{
					if( $queryAction == "delete" && $thePage == "article" )
					{
						$conn = GetConnection();

						$bus = new ArticleBus($conn);
						$article = $bus->Read($queryId);
										
						CloseConnection( $conn );
						
						$result = BuildDeleteArticlePage($article);
					}
					elseif( ( $queryAction == "add" && $thePage == "category" ) || ( $queryAction == "edit" && $thePage == "article" ) )
					{
						$article = new Article();
						
						if( $queryAction == "add" )
						{
							$conn = GetConnection();
							$bus = new PlayerBus($conn);
							$player = $bus->ReadWhoAmI($_SESSION['player-id'], $queryCat);
							CloseConnection( $conn );

							// **********
							
							if( !empty($queryCat) ) array_push( $article->Categories, $queryCat );
							$article->CreatedById = $_SESSION['player-id'];
							if( $player->Id > 0 ) $article->PostedById = $player->Id;
						}
						elseif( $queryAction == "edit" )
						{
							$conn = GetConnection();

							$bus = new ArticleBus($conn);
							$article = $bus->Read($queryId);
											
							CloseConnection( $conn );
						}
							
						$result = BuildEditArticlePage($article);
					}
					elseif( ( $queryAction == "add" && $thePage == "home" ) || ( $queryAction == "edit" && $thePage == "category" ) )
					{
						$category = new Category();
						if( $queryAction == "edit" )
						{
							$conn = GetConnection();

							$bus = new CategoryBus($conn);
							$category = $bus->Read($queryCat);
											
							CloseConnection( $conn );
						}
							
						$result = BuildEditCategoryPage($category);
					}
                    elseif( $queryAction == "search" )
                    {
                        $result = BuildSearchPage();
                    }
				}
						
				elseif( $thePage == "home" )
				{
					$result = BuildHomePage();
                }
				elseif( $thePage == "category" )
				{
					$result = BuildCategoryPage($queryCat);
				}
				elseif( $thePage == "article" )
				{
					$result = BuildArticlePage($queryCat, $queryId);
				}
				elseif( $thePage == "image" )
				{
					$result = BuildImagePage($queryCat, $queryId, $queryImage, $queryCaption);
				}
				else
				{
					$result = new PageResult("<h1>NOT FOUND</h1>", "Zoomthulhu Docs", "home");
				}
			}
		}
		return( $result );
	}
	catch( Exception $ex )
	{
		$message = $ex->getMessage();
		$file = $ex->getFile();
		$line = $ex->getLine();

		$content = "<h1>ERROR</h1>";
		$content .= "<p>Exception thrown in $file on line $line";
        $content .= "<p>$message</p>";
		
		$result = new PageResult($content, "Zoomthulhu Docs", "error");
		return( $result );		
	}
}

function ReadLogin() : int
{
	$id = -1;
	if( !empty($_POST["who-are-you"]) )
	{
		$id = (int)$_POST["who-are-you"];
	}
	
	return $id;
}

function ReadComment(int $articleId, string $queryImage) : Comment
{
	$comment = new Comment();
	
	$comment->Text = $_POST["comment-text"];
	//$comment->PlayerId = $_SESSION['player']['Id'];
	$comment->PlayerId = $_SESSION['player-id'];
	
	if( !empty($_POST["comment-post-as"]) )
	{
		$charId = (int)$_POST["comment-post-as"];
		if( $charId > 0 ) $comment->CharacterId = $charId;
	}
	
    if( !empty($_POST["hdn-image-url"]) )
        $comment->ImageUrl = $_POST["hdn-image-url"];
    elseif( !empty( $queryImage ) )
		$comment->ImageUrl = $queryImage;
	else
		$comment->ArticleId = $articleId;
	
	return $comment;
}

function ReadCategory() : Category
{
	$category = new Category();
	
	$category->Name = $_POST["category-title"];
	$category->IsCampaign = ( !empty($_POST["is-campaign"]) );
    $category->IsActive = ( !empty($_POST["is-active"]) );
    
    if ( !empty($_POST["category-tagline"]) ) {
        $category->Tagline = $_POST["category-tagline"];
    }
	return $category;
}

function ReadArticle() : Article
{
	$article = new Article();
	
	$article->Name = $_POST["article-title"];
	$article->Text = $_POST["article-text"];
	
    if( !empty($_POST["article-game-date"]) ) {
        $raw = $_POST["article-game-date"];
        $re = '/^(\d{2})\/(\d{2})\/(\d{4})$/';
        $fmt = preg_replace($re, '$3-$2-$1', $raw);
        $gd = strtotime( $fmt );
        
        $article->GameDate = $gd;
    }
    
	if( !empty($_POST["campaign"]) )
	{
		array_push( $article->Categories, (int)$_POST["campaign"] );
	}
	
	if( !empty($_POST["categories"]) )
	{
		foreach( $_POST["categories"] as $cat )
		{
			array_push( $article->Categories, (int)$cat );
		}
	}
	
	if( !empty($_POST["article-type"]) )
	{
		$article->ArticleTypeId = (int)$_POST["article-type"];
	}
	
	$postedBy = (int)$_POST["article-post-as"];
	if( $postedBy > 0 )
	{
		$article->PostedById = $postedBy;
	}
	
	return $article;
}

function BuildShowArticlePage( ) : PageResult
{
	$article = ReadArticle();
	
	// **********
	
	$conn = GetConnection();

	$bus = new CategoryBus($conn);
	$cats = $bus->ReadAll();
	
	CloseConnection( $conn );
		
	// **********
	
	$content = "<h1><span>Edit</span> <span>Article</span></h1>";
	$content .= "<div>";
	$content .= "<b>Title</b><br/>";
	$content .= "<p>$article->Name</p>";
	$content .= "<br/>";
	
	$content .= "<b>Categories</b><br/>";
	$content .= "<p>";
	foreach($cats as $cat)
	{
		if( in_array($cat->Id, $article->Categories) )
		{
			if( $cat->IsCampaign ) $content .= "*";
			$content .= "$cat->Name<br/>";
		}
	}
	$content .= "</p>";
	$content .= "<br/>";
	
	$text = str_replace("\n", "<br/>", $article->Text);
	
	$content .= "<b>Text</b><br/>";
	$content .= "<p>$text</p>";
	$content .= "</div>";
	
	$result = new PageResult($content, "Zoomthulhu Docs", "edit");
	return( $result );
}

function BuildDeleteArticlePage( Article $article ) : PageResult
{
	$postToSelf = $_SERVER["PHP_SELF"];
	
	if( !empty($_SERVER['QUERY_STRING']) )
	{
		$postToSelf .= "?" . $_SERVER['QUERY_STRING'];
	}
	
	$postToSelf = htmlspecialchars($postToSelf);
		
	// **********
	
	$cancelQuery = "";
	
	foreach( $_GET as $name => $value )
	{
		if( $name != 'action' )
		{
			if( $cancelQuery != "" ) $cancelQuery .= "&";
			$cancelQuery .= "$name=$value";
		}
	}
	
	$cancelLink = $_SERVER["PHP_SELF"];
	if( $cancelQuery != "" ) $cancelLink .= "?$cancelQuery";
	
	$cancelLink = htmlspecialchars($cancelLink);
	
	// **********

	$content = "<form method=\"post\" action=\"$postToSelf\">";

	$content .= "<h1>Delete</h1>";
	$content .= "<div class=\"confirmation\">Are you absolutely sure you want to delete &apos;$article->Name&apos;?</div>";
	
    $content .= "<div class=\"buttons-panel\">";
	$content .= "<button type=\"submit\">Delete</button>";	
	$content .= "<a href=\"$cancelLink\" onclick=\"history.go(-1); return false;\" class=\"cancel\">Cancel</a>";	
    $content .= "</div>";
    
	$content .= "</form>";

	// **********
	
	$result = new PageResult($content, "Zoomthulhu Docs", "edit");
	return( $result );
}

function BuildEditArticlePage( Article $article ) : PageResult
{
	$postToSelf = $_SERVER["PHP_SELF"];
	
	if( !empty($_SERVER['QUERY_STRING']) )
	{
		$postToSelf .= "?" . $_SERVER['QUERY_STRING'];
	}
	
	$postToSelf = htmlspecialchars($postToSelf);

	// **********
	
	$conn = GetConnection();

	$bus = new CategoryBus($conn);
	$cats = $bus->ReadAll();
	
	$artBus = new ArticleBus($conn);
	$articleTypes = $artBus->ReadTypes();
	
	$players = array();
	
	$chosenCampaign = -1;
	foreach( $cats as $cat ) 
		if( $cat->IsCampaign && in_array($cat->Id, $article->Categories) ) $chosenCampaign = $cat->Id;
		
	$playerBus = new PlayerBus($conn);
	$players = $playerBus->ReadAllForCategory($chosenCampaign, $article->CreatedById);
	
	CloseConnection( $conn );
		
	// **********
	
	$cancelQuery = "";
	
	foreach( $_GET as $name => $value )
	{
		if( $name != 'action' )
		{
			if( $cancelQuery != "" ) $cancelQuery .= "&";
			$cancelQuery .= "$name=$value";
		}
	}
	
	$cancelLink = $_SERVER["PHP_SELF"];
	if( $cancelQuery != "" ) $cancelLink .= "?$cancelQuery";
	
	$cancelLink = htmlspecialchars($cancelLink);

	// **********
	
	$currentId = -1;
	if( IsSet($article->PostedById) ) $currentId = $article->PostedById;
	
	// **********
	
	$content = "<form method=\"post\" action=\"$postToSelf\">";

	$content .= "<input id=\"article-title\" name=\"article-title\" type=\"text\" value=\"$article->Name\" placeholder=\"Enter the title...\" />";
    
    //if( IsSet($article->PostedById) ) $content .= "<p>".$article->PostedById."</p>";
	$content .= PlayersSelectHtml( "article-post-as", "By: ", $players, $currentId );
    
    $gd = "";
    if( isSet($article->GameDate) )
        $gd = date("d/m/Y", $article->GameDate);
    $content .= "<input id=\"article-game-date\" name=\"article-game-date\" type=\"text\" value=\"$gd\" placeholder=\"Date in the game (dd/mm/yyyy)...\" />";
    
	$content .= ChooseCampaignHtml( $cats, $article->Categories );
	$content .= ChooseCategoriesHtml( $cats, $article->Categories );
	$content .= ChooseArticleTypeHtml( $articleTypes, $article->ArticleTypeId );
		
	$content .= "<input type=\"hidden\" id=\"article-text\" name=\"article-text\" />";
	$content .= "<div id=\"article-text-editor\"></div>";
	
    $content .= "<div class=\"buttons-panel\">";
	$content .= "<button type=\"submit\" onclick=\"SaveEditor();\">Save</button>";	
	$content .= "<a href=\"$cancelLink\" onclick=\"history.go(-1); return false;\" class=\"cancel\">Cancel</a>";	
    $content .= "</div>";
    
	$content .= "</form>";

	// **********

	/*
	// config for Quill
	$content .= "<script>";
	
	$content .= "var quill = new Quill('#article-text-editor', {";
	$content .= "  modules: {";
	$content .= "    toolbar: [";
	$content .= "      [{ header: [2, false] }],";
	$content .= "      ['bold', 'italic', 'blockquote']";
	//$content .= "      ,[{ list: 'bullet' }]";
	$content .= "    ]";
	$content .= "  },";
	$content .= "	theme: 'bubble',";
	$content .= "	placeholder: 'Enter the text...'";
	
	$content .= " }); ";
	
	if( !empty( $article->Text ) )
	{
		$content .= "quill.setContents($article->Text); ";
	}
	
	$content .= "function SaveEditor(){";
    $content .= "    document.getElementById(\"article-text\").value = JSON.stringify(quill.getContents());";
	$content .= "}";
	
	$content .= "</script>";
	
	// **********
	
	$result = new PageResult($content, "Zoomthulhu Docs", "edit");
	array_push( $result->Scripts, 
		"//cdn.quilljs.com/1.3.6/quill.js" 
	);
	array_push( $result->Styles, 
		"//cdn.quilljs.com/1.3.6/quill.bubble.css"
	);
	//*/
	
	//*  
	// config for editor.js
	$content .= "<script>";
	
	$content .= "const editor = new EditorJS({ ";
    $content .= "holder: 'article-text-editor',";
    $content .= "minHeight: 0,";
	$content .= "inlineToolbar: ['bold', 'italic'],";
    //$content .= "tools:{ header: Header, quote: Quote, list: List },";
    //$content .= "tools:{ header: Header, quote: Quote },";
	
	$tools = array( "header"=>"Header", "quote"=>"Quote", "image"=>"SimpleImage", "delimiter"=>"Delimiter", "table"=>"Table", "signature"=>"Signature", "marking"=>"Marking", "investigator"=>"Investigator" );
	$joiner = "";
	$content .= "tools:{ ";
	
	foreach($tools as $tool=>$module)
	{
		$content .= "$joiner $tool: { class: $module, inlineToolbar: true }";
		$joiner = ",";
	}
	
    //$content .= "tools:{ header: { class: Header, inlineToolbar : true }, quote: { class: Quote, inlineToolbar : true }, image: { class: SimpleImage, inlineToolbar : true } },";
	$content .= "}, ";
	$content .= "placeholder: 'Enter the text...'";
	
	if( !empty( $article->Text ) )
	{
		$content .= ",data: $article->Text";
	}
	
	$content .= " });";
	
	$content .= "function SaveEditor(){";
    $content .= "editor.save().then((output) => {";
    $content .= "    console.log('output: ', output);";
    $content .= "    document.getElementById(\"article-text\").value = JSON.stringify(output);";
    $content .= "}).catch((error) => {";
    $content .= "    console.log('Saving failed: ', error)";
    $content .= "});";
	$content .= "}";
	
	$content .= "</script>";
	
	// **********
	
	$result = new PageResult($content, "Zoomthulhu Docs", "edit");
	array_push( $result->Scripts, 
		"https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest", 
		"https://cdn.jsdelivr.net/npm/@editorjs/header@latest", 
		"https://cdn.jsdelivr.net/npm/@editorjs/quote@latest",
		"https://cdn.jsdelivr.net/npm/@editorjs/simple-image@latest",
		"https://cdn.jsdelivr.net/npm/@editorjs/delimiter@latest",
        "https://cdn.jsdelivr.net/npm/@editorjs/table@latest",
		"Tools/signature.js?v=001",
		"Tools/marking.js?v=001",
		"Tools/investigator.js?v=005"
	);

	//"https://cdn.jsdelivr.net/npm/@editorjs/list@latest",  
	//*/
	
	return( $result );
}

function BuildEditCategoryPage(Category $category) : PageResult
{
	$postToSelf = $_SERVER["PHP_SELF"];
	
	if( !empty($_SERVER['QUERY_STRING']) )
	{
		$postToSelf .= "?" . $_SERVER['QUERY_STRING'];
	}
	
	$postToSelf = htmlspecialchars($postToSelf);
		
	// **********
	
	$cancelQuery = "";
	
	foreach( $_GET as $name => $value )
	{
		if( $name != 'action' )
		{
			if( $cancelQuery != "" ) $cancelQuery .= "&";
			$cancelQuery .= "$name=$value";
		}
	}
	
	$cancelLink = $_SERVER["PHP_SELF"];
	if( $cancelQuery != "" ) $cancelLink .= "?$cancelQuery";
	
	$cancelLink = htmlspecialchars($cancelLink);
	
	// **********

	$content = "<form method=\"post\" action=\"$postToSelf\">";

	//$content .= TextBoxHtml( "category-title", "Title", $category->Name );
	$content .= "<input id=\"category-title\" name=\"category-title\" type=\"text\" value=\"$category->Name\" placeholder=\"Enter the name...\" />";
	$content .= ChooseIsCampaignHtml($category);
    $content .= ChooseIsActiveHtml($category);
    $content .= "<textarea id=\"category-tagline\" name=\"category-tagline\" placeholder=\"Enter the Tagline...\" >$category->Tagline</textarea>";
	
    $content .= "<div class=\"buttons-panel\">";
	$content .= "<button type=\"submit\"\">Save</button>";	
	$content .= "<a href=\"$cancelLink\" onclick=\"history.go(-1); return false;\" class=\"cancel\">Cancel</a>";	
    $content .= "</div>";
    
	$content .= "</form>";

	// **********
	
	$result = new PageResult($content, "Zoomthulhu Docs", "edit");
		
	return( $result );
}

function ChooseCampaignHtml( array $categories, array $chosenCategories )
{
	$content = "";
	
	$campaigns = array_filter($categories, function($c) { return $c->IsCampaign; });
	$chosenCampaign = array();
	foreach( $campaigns as $x ) 
		if( in_array($x->Id, $chosenCategories) ) array_push( $chosenCampaign, $x );
	
	if( count( $chosenCampaign ) > 0 )
	{
		$campaigns = $chosenCampaign;
	}

	$content .= "<fieldset>";
	$content .= "<legend>Campaign</legend>";
	
	foreach( $campaigns as $cat)
	{
		$content .= "<input type=\"radio\" id=\"campaign-$cat->Id\" name=\"campaign\" value=\"$cat->Id\" ";
		
		if( in_array($cat->Id, $chosenCategories) )
		{
			$content .= "checked=\"checked\" ";
		}		
		$content .= ">";
		
		$content .= "<label for=\"campaign-$cat->Id\">$cat->Name</label>";
		//$content .= "<br/>";
	}
	
	$content .= "</fieldset>";
	return( $content );	
}

function ChooseCategoriesHtml( array $categories, array $chosenCategories )
{
	$others = array_filter($categories, function($c) { return !($c->IsCampaign); });

	$content = "<fieldset>";
	$content .= "<legend>Categories</legend>";
	
	foreach( $others as $cat)
	{
		$id = "category-$cat->Id";
		
		$content .= "<input type=\"checkbox\" id=\"$id\" name=\"categories[]\" value=\"$cat->Id\" ";
		
		if( in_array($cat->Id, $chosenCategories) )
		{
			$content .= "checked=\"checked\" ";
		}		
		$content .= ">";
		
		$content .= "<label for=\"$id\">$cat->Name</label>";
		//$content .= "<br/>";
	}
	
	$content .= "</fieldset>";
	return( $content );	
}

function ChooseIsCampaignHtml(Category $category) : string
{
	$content = "<div id=\"is_campaign-holder\">";
	
    $content .= "<input type=\"checkbox\" id=\"is-campaign\" name=\"is-campaign\" value=\"1\" ";
	if( $category->IsCampaign )
	{
		$content .= "checked=\"checked\" ";
	}		
	$content .= " />";
    $content .= "<label for=\"is-campaign\">This is a Campaign</label>";
	
	$content .= "</div>";

	return( $content );	
}

function ChooseIsActiveHtml(Category $category) : string
{
	$content = "<div id=\"is-active-holder\">";
	
    $content .= "<input type=\"checkbox\" id=\"is-active\" name=\"is-active\" value=\"1\" ";
	if( $category->IsActive )
	{
		$content .= "checked=\"checked\" ";
	}		
	$content .= " />";
    $content .= "<label for=\"is-active\">This is the Current Investigation</label>";
	
	$content .= "</div>";

	return( $content );	
}

function ChooseArticleTypeHtml( array $articleTypes, int $articleTypeId )
{
	$content = "<fieldset>";
	$content .= "<legend>Appearance</legend>";
	
	foreach( $articleTypes as $artType )
	{
		$content .= "<input type=\"radio\" id=\"article-type-$artType->Id\" name=\"article-type\" value=\"$artType->Id\" ";
		
		if( $artType->Id == $articleTypeId )
		{
			$content .= "checked=\"checked\" ";
		}		
		$content .= ">";
		
		$content .= "<label for=\"article-type-$artType->Id\">$artType->Name</label>";
		//$content .= "<br/>";
	}
	
	$content .= "</fieldset>";
	return( $content );	
}

//function BuildLoginPage( array $fbObject ) : PageResult
function BuildLoginPage( ) : PageResult
{
	//$_SESSION['fb-redirect-query'] = $_SERVER['QUERY_STRING'];

	// **********	
	
    // Get login url
    //$permissions = ['email']; // Optional permissions
    //$loginURL = $fbObject['helper']->getLoginUrl(FB_REDIRECT_URL, $permissions);
	
	//$content = "<h1><span>Zoomthulhu</span> <span>Docs</span></h1>";
	
	//$content .= "<div class=\"login\">";
	//$content .= "<p>You need to log in to Zoomthulhu using Facebook.</p>";
    
    // Render Facebook login button
    //$content .= '<a href="'.htmlspecialchars($loginURL).'"><img src="Images/fb-login-btn.png"></a><br/>';
    //$content .= '<a href="?action=guest">Continue as a guest</a>';

	//$content .= "</div>";

	$conn = GetConnection();

	$bus = new PlayerBus($conn);
	$players = $bus->ReadAll();
	
	CloseConnection( $conn );

	// **********

	$baseUrl = $_SERVER["PHP_SELF"] . "?";
	if( !empty($_SERVER['QUERY_STRING']) )
	{
		$baseUrl .= $_SERVER['QUERY_STRING'] . "&";
	}
		
	$postToSelf = $baseUrl . "action=login";
	$postToSelf = htmlspecialchars($postToSelf);

	// **********
	
	$guest = new Player();
	
	$guest->Id = -1;
	$guest->FirstName = "None of these - ";
	$guest->LastName = "I must be a Guest";
	
	array_push( $players, $guest );
	
	// **********
	
	$content = "<h1><span>Whispers</span><span>from the</span><span>Void</span></h1>";
	$content .= "<form method=\"post\" action=\"$postToSelf\">";
	$content .= "<p>Please let me know who you are...</p>";

	/*
	$content .= "<select name=\"who-are-you\" id=\"who-are-you\">";
	foreach( $players as $player )
	{
		$content .= "<option value=\"$player->Id\">$player->FirstName $player->LastName</option>";
	}
	$content .= "<option value=\"-1\">None of these - I'm a Guest</option>";
	$content .= "</select>";
	*/
	$content .= PlayersSelectHtml( "who-are-you", "", $players, $players[0]->Id );
	
	$content .= "<button type=\"submit\">Continue</button>";	
	$content .= "</form>";
	
	$result = new PageResult($content, "Zoomthulhu Docs", "home login");
	return( $result );
}

function BuildWhoAmI( int $categoryId ) : string
{
	//if( isSet($_SESSION['player']) ) 
	if( isSet($_SESSION['player-id']) ) 
	{
		$conn = GetConnection();

		$bus = new PlayerBus($conn);
		//$player = $bus->ReadWhoAmI($_SESSION['player']['Id'], $categoryId);
		$player = $bus->ReadWhoAmI($_SESSION['player-id'], $categoryId);
		
		CloseConnection( $conn );

		// **********
		
		//$player = $_SESSION['player'];
		
		$content = "<div class=\"user-data\">";
		//$content .= "<span>".$player['FirstName']." ".$player['LastName']."</span>";
		//$content .= "<img src=\"".$player['Picture']."\" />";
		$content .= "<span>$player->FirstName $player->LastName</span>";
		$content .= "<img src=\"$player->Picture\" />";
		$content .= "</div>";
	}
	else
	{
		$url = $_SERVER["PHP_SELF"] . "?";
		if( !empty($_SERVER['QUERY_STRING']) )
		{
			$url .= $_SERVER['QUERY_STRING'] . "&";
		}
		$url .= "action=login";
		
		$content = "<div class=\"user-data\"><a href=\"$url\">Log In</a></div>";
	}
	return $content;
}

function SummaryLink(int $categoryId, int $articleId = -1, string $caption = "Review the case file...") : string
{
    /*
    if( $categoryId < 0 ) {
        $categoryId = $article->Categories[0];
    }
    */
    
    //$caption = "Review the case file...";
    
    $u = "?t=" . idate("U");
    $u .= "&cat=" . $categoryId;
    if( $articleId > 0 ) {
        $u .= "&id=" . $articleId;
    }
    
    $content = "<a href=\"$u\" class=\"summary-link\">";
	$content .= "<span>$caption</span>";
	$content .= "</a>";
    
    return $content;
}

function BuildHomePage() : PageResult
{
	$conn = GetConnection();

	$bus = new CategoryBus($conn);
	$cats = $bus->ReadAll();
    
    $artBus = new ArticleBus($conn);
    $articles = $artBus->ReadLatest();
	
	CloseConnection( $conn );

	// **********
	
	$baseUrl = $_SERVER["PHP_SELF"] . "?";
	if( !empty($_SERVER['QUERY_STRING']) )
	{
		$baseUrl .= $_SERVER['QUERY_STRING'] . "&";
	}

	// *****
	
	$addUrl = $baseUrl . "action=add";
	$addUrl = htmlspecialchars($addUrl);
	
	// **********
    
    $currCamp = null;
    foreach( $cats as $c ) {
        if( $c->IsActive ) {
            $currCamp = $c;
            break;
        }
    }
    
    $campaigns = array_filter($cats, function($c) { return $c->IsCampaign; });
	$others = array_filter($cats, function($c) { return !($c->IsCampaign); });
    
    // *********"
	
	$content = BuildWhoAmI(-1);
	$content .= "<h1><span>Whispers</span><span>from the</span><span>Void</span></h1>";
	
    $content .= "<div id=\"hero-bar\" class=\"hero-bar\">";
    
    $content .= "<div class=\"home-summary\">";
    $content .= "<p class=\"tagline\">The Forbidden Archive of The Sons of Monas</p>";
    
    $content .= "<p>What follows is not meant for the unprepared mind. ";
    $content .= "These pages recount the discoveries and terrors faced by the Sons and Daughters of Monas between 1932 and 1935 - ";
    $content .= "when the veil between our world and the cosmic abyss grew perilously thin.</p>";
    
    //$content .= "<p>Version: " . phpversion() . "</p>";
    // Version: 8.3.19
    
    //$content .= "<div class=\"gallery\"><img src=\"Images/group.jpg\" /></div>";
    $url = "Images/group.jpg";
    $caption = "New York 1935";
	$imageUrl = $baseUrl . "image=" . $url . "&caption=" . $caption;
	$captionElement = "<span>$caption</span>";
			
	$imageUrl = htmlspecialchars( $imageUrl );
			
	$content .= "<div class=\"gallery\"><div><a href=\"$imageUrl\"><img src=\"$url\" />";
	$content .= $captionElement;
	$content .= "</a></div></div>";
    $content .= "</div>";
    
    if( $currCamp ) {
        $content .= "<div class=\"home-summary\">";
        
        $content .= "<p>Current Investigation</p>";
        
        //$content .= "<p class=\"tagline\">$currCamp->Name</p>";
        $content .= "<p class=\"tagline\">" . SummaryLink($currCamp->Id, caption: $currCamp->Name) . "</p>";
        
        $content .= "<p>$currCamp->Tagline</p>";
        
        $currLink = new Category();
        $currLink->Id = $currCamp->Id;
        $currLink->Name = "Current Investigation";

        //$content .= "<div class=\"curr-link\">" . CategoryLink($currLink) . "</div>";
        //$content .= "<p>Review the Case File...</p>";
        
        $content .= "<p>" . SummaryLink($currCamp->Id) . "</p>";
        $content .= "</div>";
    }
    //$content .= "<div class=\"home-summary\">Article</div>";
    foreach($articles as $art) {
        $content .= "<div class=\"home-summary\">";
        
        $updated = date("j F Y", $art->UpdatedAt);
        
        $content .= "<p>Updated: $updated</p>";
        //$content .= "<p class=\"tagline\">$art->Name</p>";
        $content .= "<p class=\"tagline\">" . SummaryLink($art->Categories[0], $art->Id, $art->Name) . "</p>";
        
        $content .= BuildCategoryTags(array_merge($campaigns, $others), $art->Categories);
        
        if ( !empty($art->SummaryImage) ) {
            //$content .= "<p><img class=\"article-link-image\" src=\"$art->SummaryImage\" /></p>";
            $content .= "<img class=\"article-link-image\" src=\"$art->SummaryImage\" />";
        } else {
            $content .= "<p>$art->Summary</p>";
        }
        //$content .= ArticleLink(-1, $art, true, $campaigns + $others);
        
        //$content .= "<p>Review the Case File...</p>";
        $content .= "<p>" . SummaryLink($art->Categories[0], $art->Id) . "</p>";
        $content .= "</div>";
    }
    
    $content .= "</div>";
    
    //$campaigns = array_filter($cats, function($c) { return $c->IsCampaign; });
    //$others = array_filter($cats, function($c) { return !($c->IsCampaign); });

    $content .= SearchFormHtml();
    
    /*
    $currCamp = end($campaigns);
    $currLink = new Category();
    $currLink->Id = $currCamp->Id;
    $currLink->Name = "Current Investigation";
    */
    
    /*
    if( $currCamp != null ) {
        $currLink = new Category();
        $currLink->Id = $currCamp->Id;
        $currLink->Name = "Current Investigation";

        $content .= "<div class=\"curr-link\">" . CategoryLink($currLink) . "</div>";
     }
     */
        
    //$content .= "<div class=\"curr-link\">" . CategoryLink($currLink) . "</div>";
    
	$content .= BuildCategoryList(array_reverse($campaigns));
	$content .= BuildCategoryList($others);
	
	if( $_SESSION['Permissions']['CanUpdateCategories'] )
	{
		$content .= "<div class=\"controls\">";
		$content .= "<a href=\"$addUrl\" title=\"Add Category\" class=\"add\"><span>Add</span></a>";
		$content .= "</div>";
	}
	
	$result = new PageResult($content, "Whispers From the Void", "home");
    array_push( $result->Scripts, 
        "Scripts/autoScroll.js?ver=006"
    );
    
    return( $result );
}

function SearchBoxName() : string {
    return "search-for-query-box";
}

function SessionsName() : string {
    return "Reports";
}

function SearchFormHtml(string $searchQuery = "") : string
{
    $postToSelf = $_SERVER["PHP_SELF"];
    /*
	if( !empty($_SERVER['QUERY_STRING']) ) 	{
        $postToSelf .= "?" . $_SERVER['QUERY_STRING'];
    }
    */
    $postToSelf .= "?action=search";
	$postToSelf = htmlspecialchars($postToSelf);

    // **********

    $nm = SearchBoxName();

    $content = "<form id=\"search-form\" method=\"post\" action=\"$postToSelf\">";
  	$content .= "<input id=\"$nm\" name=\"$nm\" type=\"text\" value=\"$searchQuery\" placeholder=\"Search the Whispers...\" />";
  	$content .= "<button type=\"submit\"\">Search</button>";	
  	$content .= "</form>";

    // **********

    return $content;
}

function BuildSearchPage() : PageResult
{
    $searchQuery = "";
    $articles = [];
    $cats = [];
    
    /*
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nm = SearchBoxName();
        $searchQuery = htmlspecialchars( $_POST[$nm] );
    */
    $searchQuery = $_GET["q"] ?? "";

    if (!empty( $searchQuery )) {
        $conn = GetConnection();
        
        $artBus = new ArticleBus($conn);
        $articles = $artBus->Search($searchQuery);

        $bus = new CategoryBus($conn);
        $cats = $bus->ReadAll();

        CloseConnection( $conn );
        
        // Order array so that campaigns come first
        $campaigns = array_filter($cats, function($c) {return $c->IsCampaign; });
        $others = array_filter($cats, function($c) { return !($c->IsCampaign); });
        $cats = array_merge($campaigns, $others);
    }

    // **********
    
    $content = "";

    $content .= BuildWhoAmI(-1);
    $content .= "<div class=\"links\">";	
    $content .= HomeLink();
    $content .= "</div>";
    
   	$content .= "<div>";	
   	$content .= "<h1>Search</h1>";

    $content .= SearchFormHtml($searchQuery);
    //*
    if ( !empty($searchQuery) ) {
        $artCount = count($articles);
        $content .= "<h2 id=\"search-for-heading\">Search for: $searchQuery</h2>";
        if ($artCount == 0) {
            $content .= "<p>No matches found</p>";
        }
        else {
            $matchCount = 0;
            $getMatches = '/^\d+/';
            foreach( $articles as $article ) {
                preg_match($getMatches, $article->Summary, $matches);
                $matchCount += (int)$matches[0];
            }
            
            $matches = ( $matchCount == 1 ? "match" : "matches" );
            $documents = ( $artCount == 1 ? "document" : "documents" );
            
            $content .= "<p>$matchCount $matches in $artCount $documents</p>";
            $content .= "<ul class=\"column-$artCount\">";
            foreach( $articles as $article ) {
                $content .= "<li data-articleid=\"$article->Id\">";
                $content .= ArticleLink( -1, $article, true, $cats, $searchQuery );
                $content .= "</li>";
            }
            $content .= "</ul>";
        }
    }
    // */
    $content .= "</div>";

    // **********

    $result = new PageResult($content, "Search", "category");
	return( $result );
}

function BuildCategoryPage(int $categoryId) : PageResult
{
	$conn = GetConnection();

	$bus = new CategoryBus($conn);
    //$cat = $bus->Read($categoryId);
    $cats = $bus->ReadAll();
	
	$artBus = new ArticleBus($conn);
	$articles = $artBus->ReadAllForCategory($categoryId);
	//$articles = array();
	
	CloseConnection( $conn );
	
	// **********
    
    $cat = null;
    foreach( $cats as $c ) {
        if( $c->Id == $categoryId ) {
            $cat = $c;
            break;
        }
    }
    
    $campaigns = array_filter($cats, function($c) { return $c->IsCampaign; });
	$others = array_filter($cats, function($c) { return !($c->IsCampaign); });
    $cats = array_merge($campaigns, $others);
    
    // **********
	
	$baseUrl = $_SERVER["PHP_SELF"] . "?";
	
	if( !empty($_SERVER['QUERY_STRING']) )
	{
		$baseUrl .= $_SERVER['QUERY_STRING'] . "&";
	}

	// *****
	
	$addUrl = $baseUrl . "action=add";
	$addUrl = htmlspecialchars($addUrl);
	
	$editUrl = $baseUrl . "action=edit";
	$editUrl = htmlspecialchars($editUrl);
	
	// **********
	
	$content = BuildWhoAmI($categoryId);
	
	/*
	$content .= "<p>Player ID: " . $_SESSION['player-id'] . "</p>";
	$content .= "<p>Is Set: " . ( isSet($_SESSION['player-id']) ? "YES" : "NO" ) . "</p>";
	$content .= "<p>Guest: " . $_SESSION['continue-as-a-guest'] . "</p>";
	$content .= "<p>Is Set: " . ( isSet($_SESSION['continue-as-a-guest']) ? "YES" : "NO" ) . "</p>";
	*/
    
    $isSessions = $_GET["p"];
    $thisName = $cat->Name;
	
	$content .= "<div class=\"links\">";	
	$content .= HomeLink();	
    
    if( $isSessions == "sessions" ) {
        $content .= "<span>&gt;</span>";
        $content .= CategoryLink( $cat );
        $thisName = SessionsName();
    } 
    
	$content .= "</div>";
    
	$content .= "<div>";	
	$content .= "<h1>$thisName</h1>";
    
    if($cat->Tagline) {
        $content .= "<div class=\"home-summary\">";
        $content .= "<p>$cat->Tagline</p>";
        $content .= "</div>";
    }
    if( $isSessions == "sessions" ) {
        $content .= BuildSessionsList($cat, $articles, $cats);
    } else {
        $content .= BuildArticleList($cat, $articles, $cats);
    }
    $content .= "</div>";

	$content .= "<div class=\"controls\">";
	if( $_SESSION['Permissions']['CanUpdateArticles'] || $_SESSION['Permissions']['CanUpdateAllArticles'] )
	{
		$content .= "<a href=\"$addUrl\" title=\"Add Article\" class=\"add\"><span>Add</span></a>";
	}
	if( $_SESSION['Permissions']['CanUpdateCategories'] )
	{
		$content .= "<a href=\"$editUrl\" title=\"Edit Category\" class=\"edit\"><span>Edit</span></a>";
	}
	$content .= "</div>";
	
	$result = new PageResult($content, $cat->Name, "category " . $cat->CssClass);
	
	if( $_SESSION['Permissions']['CanUpdateCategories'] )
	{
		array_push( $result->Scripts, 
			"Scripts/reorderCategories.js?ver=004"
		);
	}
	
	return( $result );
}

function BuildImagePage(int $categoryId, int $articleId, string $imageUrl, string $caption) : PageResult
{
	$conn = GetConnection();

    if ( $articleId ) {
        $bus = new ArticleBus($conn);
        $article = $bus->Read($articleId);
    }
    if ( $categoryId ) {
        $catBus = new CategoryBus($conn);
        $category = $catBus->Read($categoryId);
    }
    
    $qq = "inum";

    $imageIndex = (int)$_GET["inum"];
    $isIndex = false;
    $imageCount = 0;
    $commentImage = null;

    if ( !empty( $imageIndex ) ) {
      $isIndex = true;
      $obj = json_decode($article->Text);

      foreach($obj->blocks as $block) {
        if ($block->type == "image") {
            $imageCount++;
            if ( $imageCount == $imageIndex ) {
              $imageUrl = $block->data->url;
              $caption = $block->data->caption;
            }
        }
        else if ($block->type == "investigator") {
            $imageCount++;
            if ( $imageCount == $imageIndex ) {
              $imageUrl = $block->data->imageUrl;
              $caption = $block->data->text;
            }
        }
      }
      $imageUrl = str_ireplace( 'https://fraser.epizy.com/php/', '', $imageUrl );
      $commentImage = $imageUrl;
    }
	
	$commentBus = new CommentBus($conn);
	$comments = $commentBus->ReadAllForImage($imageUrl);
	
	CloseConnection( $conn );
	
	// **********
	
	$isPage = ( $articleId && !empty($article->ArticleTypeClass) );

	// **********
	
	$baseUrl = $_SERVER["PHP_SELF"] . "?";
	
	if( !empty($_SERVER['QUERY_STRING']) )
	{
		$baseUrl .= $_SERVER['QUERY_STRING'] . "&";
	}
		
	// **********

	$content = BuildWhoAmI($categoryId);
	$content .= "<div class=\"links\">";	
	$content .= HomeLink();	
    if ( $categoryId ) {
        $content .= "<span>&gt;</span>";
        $content .= CategoryLink( $category );
        if ( $articleId ) {
            $content .= "<span>&gt;</span>";
            $content .= ArticleLink( $categoryId, $article, false, array() );
        }
    }
	$content .= "</div>";
	
	if( empty($caption) ) $caption = $article->Name;
	
	$content .= "<div>";	
	$content .= "<h1>$caption</h1>";	

	/*
	$c = count($comments);
	$s = ( $c == 1 ? "" : "s" );
	$content .= "<p class=\"comments links\"><a href=\"#comment-text\">$c Comment$s</a></p>"; 
	*/
	$content .= CommentLinks( $comments );
	
	$content .= "<img id=\"image-page\" src=\"$imageUrl\"/>";
	$content .= "</div>";
    
    if ($isIndex ) {
      $hasPrev = ($imageIndex > 1);
      $hasNext = ($imageIndex < $imageCount);

      $u = "?t=" . idate("U");
      $u .= "&cat=" . $categoryId;
      $u .= "&id=" . $articleId . "&";

      if ( $imageIndex > 1 ) {
        $prevNum = $imageIndex - 1;
        $prevUrl = $u . "$qq=$prevNum";
        $content .= "<a href=\"$prevUrl\" onclick=\"window.location.replace('$prevUrl'); return false;\" title=\"Previous Image\" id=\"prev-image\"><span>&lt;</span></a>";
      }
      if ( $imageIndex < $imageCount ) {
        $nextNum = $imageIndex + 1;
        $nextUrl = $u . "$qq=$nextNum";
        $content .= "<a href=\"$nextUrl\" onclick=\"window.location.replace('$nextUrl'); return false;\" title=\"Next Image\" id=\"next-image\"><span>&gt;</span></a>";
      }

      $content .= "<div id=\"jump-links\">";
      for($i = 1; $i <= $imageCount; $i++) {
        if( $i == $imageIndex ) {
          $content .= "<p><span>$i</span></p>";
        } else {
          $jumpUrl = $u . "$qq=$i";
          $content .= "<a href=\"$jumpUrl\" onclick=\"window.location.replace('$jumpUrl'); return false;\" title=\"Image $i\"><span>$i</span></a>";
        }
      }
      $content .= "</div>";
    }

	// **********

	$postToSelf = $baseUrl . "action=comment";
	$postToSelf = htmlspecialchars($postToSelf);

	// **********
	
	foreach($comments as $comment)
	{
		$t = FormatPostedTime($comment->CreatedMinutesAgo, $comment->CreatedAt);
		
		$text = preg_replace( '/\n/', '<br/>', $comment->Text );
		
		$content .= "<div class=\"comment\" id=\"comment-$comment->Id\">";
		
		if( !empty($comment->Picture) )
			$content .= "<img src=\"$comment->Picture\" />";
		
		$content .= "<span>$text</span>";
		$content .= "<span>$comment->PostedBy</span>";
		$content .= "<span>$t</span>";
		$content .= "</div>";
	}

	// **********
	
	/*
	if( $_SESSION['Permissions']['CanPostComments'] )
	{
		$content .= "<form method=\"post\" action=\"$postToSelf\">";

		$content .= "<textarea id=\"comment-text\" name=\"comment-text\" placeholder=\"Add a comment...\"></textarea>";
		$content .= "<button type=\"submit\">Post</button>";	
		
		$content .= "</form>";
	}
	*/
	$content .= BuildCommentForm($categoryId, $commentImage);
	
	// **********
		
	$result = new PageResult($content, $caption, "attachment $category->CssClass imagePage");
    array_push( $result->Scripts, 
		"Scripts/imageSwipe.js?v=032"
	);
    
    return( $result );
}

function CommentLinks( array $comments ) : string
{
	$c = count($comments);
	$s = ( $c == 1 ? "" : "s" );

	$content = "<p class=\"comments links\"><span>"; 
	
	if( $c == 0 )
		$content .= "<span>$c Comment$s</span>";
	else
	{
		$href = $comments[0]->Id;
		$content .= "<a href=\"#comment-$href\">$c Comment$s</a>"; 
	}
	
	$content .= "<a class=\"add-comment\" href=\"#comment-text\"><span>Add Comment</span></a>"; 
	$content .= "</span></p>";
	
	return $content;
}

function BuildCommentForm(int $categoryId, string $imageUrl = null) : string
{
	$content = "";
	
	if( $_SESSION['Permissions']['CanPostComments'] )
	{
		/*
		$conn = GetConnection();

		$bus = new PlayerBus($conn);
		//$players = $bus->ReadWhoCouldIBe($_SESSION['player']['Id'], $categoryId);
		$players = $bus->ReadWhoCouldIBe($_SESSION['player-id'], $categoryId);
		
		CloseConnection( $conn );
		*/
		
		// **********
		
		$baseUrl = $_SERVER["PHP_SELF"] . "?";
		if( !empty($_SERVER['QUERY_STRING']) )
		{
			$baseUrl .= $_SERVER['QUERY_STRING'] . "&";
		}
			
		$postToSelf = $baseUrl . "action=comment";
		$postToSelf = htmlspecialchars($postToSelf);

		// **********
		
		$content = "<form method=\"post\" action=\"$postToSelf\">";
        $content .= "<input type=\"hidden\" id=\"hdn-image-url\" name=\"hdn-image-url\" value=\"$imageUrl\" />";
        //$content .= "<p>Image URL: '$imageUrl'<p>";
        $content .= "<textarea id=\"comment-text\" name=\"comment-text\" placeholder=\"Add a comment...\"></textarea>";

		/*
		$content .= "<select name=\"comment-post-as\" id=\"comment-post-as\">";
		foreach( $players as $player )
		{
			$content .= "<option value=\"$player->Id\">From: $player->FirstName $player->LastName</option>";
		}
		$content .= "</select>";
		*/
		$content .= PostAsSelect( "comment-post-as", $categoryId );
		
		$content .= "<button type=\"submit\">Post</button>";	
		$content .= "</form>";
	}
	
	return $content;
}

function PostAsSelect( string $selectId, int $categoryId )
{
	$conn = GetConnection();

	$bus = new PlayerBus($conn);
	//$players = $bus->ReadWhoCouldIBe($_SESSION['player']['Id'], $categoryId);
	$players = $bus->ReadWhoCouldIBe($_SESSION['player-id'], $categoryId);
	
	CloseConnection( $conn );
	
	// **********

	return PlayersSelectHtml( $selectId, "From: ", $players, $players[0]->Id );
}
function PlayersSelectHtml( string $selectId, string $itemPrefix, array $players, int $currentId )
{		
	$content = "<select name=\"$selectId\" id=\"$selectId\">";
	foreach( $players as $player )
	{
		$content .= "<option value=\"$player->Id\"";
		
		if( $player->Id == $currentId ) $content .= " selected=\"selected\"";
		
		$content .= ">$itemPrefix$player->FirstName $player->LastName</option>";
	}
	$content .= "</select>";
	
	return $content;
}

function BuildArticlePage(int $categoryId, int $articleId) : PageResult
{
	$conn = GetConnection();

	$bus = new ArticleBus($conn);
	$article = $bus->Read($articleId);
	
	$catBus = new CategoryBus($conn);
    // $category = $catBus->Read($categoryId);
    $cats = $catBus->ReadAll();
    //$category = array_find($cats, function($c) { return $c->Id == $categoryId; });
	
	CloseConnection( $conn );
    
    $category = null;
    foreach($cats as $c) {
        if( $c->Id == $categoryId ) {
            $category = $c;
            break;
        }
    }
	
	// **********
	
	$isPage = ( !empty($article->ArticleTypeClass) );

	// **********
	
	$baseUrl = $_SERVER["PHP_SELF"] . "?";
    $qs = $_SERVER["QUERY_STRING"];
	
	if( !empty($_SERVER['QUERY_STRING']) )
	{
		$baseUrl .= $_SERVER['QUERY_STRING'] . "&";
	}
		
	// **********

	//$content = BuildWhoAmI($categoryId);
	$content = "<div class=\"links\">";	
	$content .= HomeLink();	
	$content .= "<span>&gt;</span>";
	$content .= CategoryLink( $category );
    
	if ( $category->IsCampaign and stripos($article->Name, $category->Name) !== false ) {
	    $content .= "<span>&gt;</span>";
        $content .= CategorySessionsLink( $category );
    }
    
	$content .= "</div>";	
	$content .= "<div id=\"article-wrapper\" class=\"$article->ArticleTypeClass\">";
    
    $dropTags = '/<[^>]+>/';
    $summary = preg_replace($dropTags, "", $article->Summary);
    
    $content .= "<input type=\"hidden\" id=\"hdn-article-title\" value=\"$article->Name\" />";
    $content .= "<input type=\"hidden\" id=\"hdn-article-text\" value=\"$summary\" />";
    $content .= "<input type=\"hidden\" id=\"hdn-article-url\" value=\"$qs\" />";
    $content .= "<input type=\"hidden\" id=\"hdn-category-id\" value=\"$categoryId\" />";
    
	$content .= "<h1>$article->Name</h1>";
    
	$content .= "<p class=\"author\">";
	if( !empty($article->CreatedByPicture) )
	{
		$content .= "<img src=\"$article->CreatedByPicture\" />";
	}
    if( !empty($article->PostedBy) ) {
        $content .= "by $article->PostedBy";
    } else {
        $content .= "by $article->CreatedBy";
    }
    if( !empty($article->GameDate) ) {
        $gd = date("l, j F Y", $article->GameDate);
        $content .= "<br/>$gd";
    }
    $content .= "</p>";
    
    if( $article->Name !== 'Gallery' ) {
        $content .= "<p class=\"article-summary\">$article->Summary</p>";
    }
    
    $content .= BuildCategoryTags($cats, $article->Categories);
    
	/*
	$c = count($article->Comments);
	$s = ( $c == 1 ? "" : "s" );

	$content .= "<p class=\"comments links\"><a href=\"#comment-text\">$c Comment$s</a></p>"; 
	*/
	$content .= CommentLinks( $article->Comments );
	
	if( $isPage ) $content .= "<div class=\"page\">";
	$content .= ParseArticleText( $article->Text, $article->ArticleTypeClass, $baseUrl );
	if( $isPage )$content .= "</div>";
	$content .= "</div>";

	// **********

	foreach($article->Comments as $comment)
	{
		$t = FormatPostedTime($comment->CreatedMinutesAgo, $comment->CreatedAt);
		
		$text = preg_replace( '/\n/', '<br/>', $comment->Text );
		
		$content .= "<div class=\"comment\" id=\"comment-$comment->Id\">";
		
		if( !empty($comment->Picture) )
			$content .= "<img src=\"$comment->Picture\" />";
		
		$content .= "<span>$text</span>";
		$content .= "<span>$comment->PostedBy</span>";
		$content .= "<span>$t</span>";
		$content .= "</div>";
	}

	// **********
	
	/*
	if( $_SESSION['Permissions']['CanPostComments'] )
	{
		$content .= "<form method=\"post\" action=\"$postToSelf\">";

		$content .= "<textarea id=\"comment-text\" name=\"comment-text\" placeholder=\"Add a comment...\"></textarea>";
		$content .= "<button type=\"submit\" onclick=\"SaveEditor()\">Post</button>";	
		
		$content .= "</form>";
	}
	*/
	$content .= BuildCommentForm($categoryId);
	
	// **********
    
    $content .= "<div class=\"footer\">";
    
    // l, j F Y
    $created = date("j F Y", $article->CreatedAt);
	$updated = date("j F Y", $article->UpdatedAt);
    
    $content .= "<span>Published By: $article->CreatedBy</span>";
    $content .= "<span>Created: $created</span>";
    if ( $updated != $created ) {
        $content .= "<span>Updated: $updated</span>";
    }
    $content .= "</div>";
	
	$canUpdate = false;
	if( $_SESSION['Permissions']['CanUpdateAllArticles'] )
	{
		$canUpdate = true;
	}
	elseif( $_SESSION['Permissions']['CanUpdateArticles'] )
	{
		//$canUpdate = ( $article->CreatedById == $_SESSION['player']['Id'] );
		$canUpdate = ( $article->CreatedById == $_SESSION['player-id'] );
	}

	/*
	if( $canUpdate )
	{
		$editUrl = $baseUrl . "action=edit";
		$editUrl = htmlspecialchars($editUrl);
		
		$deleteUrl = $baseUrl . "action=delete";
		$deleteUrl = htmlspecialchars($deleteUrl);
		
		// **********

		$content .= "<div class=\"controls\">";
		$content .= "<a href=\"$editUrl\" title=\"Edit Article\" class=\"edit\"><span>Edit</span></a>";
		$content .= "<a href=\"$deleteUrl\" title=\"Delete Article\" class=\"delete\"><span>Delete</span></a>";
		$content .= "</div>";
	}
	*/
	
	$content .= BuildSideBar( $category, $article->Name, $article->Text, $canUpdate, $baseUrl );
	
	$result = new PageResult($content, $article->Name, "attachment $category->CssClass");
	array_push( $result->Scripts, 
		"Scripts/articleSearch.js?v=067"
	);
    
    return( $result );
}

function FormatPostedTime(int $minutesAgo, int $createdAt)
{
	$result = "";
	
	if( $minutesAgo <= 3 ) $result = "Just Now";
	elseif( $minutesAgo <= 15 ) $result = "$minutesAgo Minutes Ago";
	elseif( $minutesAgo <= 60 )
	{
		$m = round( $minutesAgo / 15) * 15;
		$result = "$m Minutes Ago";
	}
	else
	{
		$h = round( $minutesAgo / 60 );
		$s = ( $h == 1 ? "" : "s" );
		if( $h <= 24 ) $result = "$h Hour$s Ago";
		else
		{
			$d = round( $h / 24 );
            if( $d <= 28 )
            {
                $s = ( $d == 1 ? "" : "s" );
                $result = "$d Day$s Ago";
            }
            else 
            {
                $result = date('F Y', $createdAt);
            }
		}
	}
	
	return $result;
}

function ParseArticleText( string $text, string $articleTypeClass, string $baseUrl ) : string
{
	/*
    $obj = json_decode($text);
	$lines = array();
	
    foreach($obj->ops as $element)
	{
		if ( $element->insert != "\n" ) 
		{
			$theseLines = explode( "\n", $element->insert );
			$joiner = "";
			
			foreach( $theseLines as $line )
			{
				array_push( $lines, $joiner.$line );
				$joiner = "<br/>";
			}
		}

		if( !empty( $element->attributes ) )
		{
			$last = count( $lines ) - 1;
			$result = $lines[ $last ];
			
			if( !empty( $element->attributes->header ) ) 
			{
				$tag = "h" . $element->attributes->header;
				$result = "<$tag>$result</$tag>";
			}
			elseif( !empty( $element->attributes->blockquote ) ) 
				$result = "<blockquote>$result</blockquote>";
			else
			{
				if( !empty( $element->attributes->bold ) ) $result = "<b>$result</b>";
				if( !empty( $element->attributes->italic ) ) $result = "<i>$result</i>";
			}
			
			$lines[ $last ] = $result;
		}
    }
	
	$parsed = implode( $lines );
	$parsed .= "<br/></br/><p>" . htmlspecialchars($text) . "</p>";
	
	return $parsed;
	//*/
	
	//*
	//editor js parser
	$obj = json_decode($text);
	$images = "";
	$markings = "";
	$investigators = "";
	$headerIndex = 0;
	$index = 0;
	
	$isPage = !empty($articleTypeClass);
	$isFormalLetter = ( $articleTypeClass == "letter palfry-letter" );
    $imageIndex = 0;
    
	foreach($obj->blocks as $block)
	{
		if( $images != "" && $block->type != "image" )
		{
			$lines .= "<div class=\"gallery\">$images</div>";
			$images = "";
		}

		if( $markings != "" && $block->type != "marking" )
		{
			$lines .= "<div class=\"markings\">$markings</div>";
			$markings = "";
		}

		if( $investigators != "" && $block->type != "investigator" )
		{
			$lines .= "<div class=\"investigators\">$investigators</div>";
			$investigators = "";
		}

		if( $block->type == "image" )
		{
            $imageIndex++;
            $imageUrl = $baseUrl . "inum=$imageIndex";
            $url = $block->data->url;
			$caption = $block->data->caption;
			
			$captionElement = "";
			
            //$relative = str_ireplace( 'https://fraser.epizy.com/php/', '', $url );
            //$imageUrl = $baseUrl . "image=" . $relative;
			if( !empty( $caption ) )
			{
                //$imageUrl .= "&caption=" . $caption;
				$captionElement = "<span>$caption</span>";
			}
			
			$imageUrl = htmlspecialchars( $imageUrl );
			
			$images .= "<div><a href=\"$imageUrl\"><img src=\"$url\" />";
			$images .= $captionElement;
			$images .= "</a></div>";			
		}
		
		elseif( $block->type == "marking" )
		{
			$markName = $block->data->text;
			$markValue = $block->data->value;

			$markings .= "<div class=\"marking\" style=\"--data-pc:$markValue\" >";
			$markings .= "<span>$markName</span>";		
			$markings .= "</div>";
		}
		
		elseif( $block->type == "investigator" )
		{
			$invName = $block->data->text;
			$invDesc = $block->data->description;
			$invImage = $block->data->imageUrl;
			$invLink = $block->data->linkUrl;
			$invPlayer = $block->data->player;
			
            $headerIndex++;
            
			$investigators .= "<div id=\"header-$headerIndex\" class=\"investigator\" >";
			$investigators .= "<div>";
			if( $invName ) { $investigators .= "<span>$invName</span>"; }
			if( $invDesc ) { $investigators .= "<span>$invDesc</span>"; }
			$investigators .= "</div>";
			if( $invImage ) { 
                $imageIndex++;
                $imageUrl = $baseUrl . "inum=$imageIndex";
                
                $relative = str_ireplace( 'https://fraser.epizy.com/php/', '', $invImage );
                //$imageUrl = $baseUrl . "image=" . $relative;
                //$imageUrl .= "&caption=" . $invName;
                $imageUrl = htmlspecialchars( $imageUrl );
                
                $investigators .= "<a href=\"$imageUrl\">";
                $investigators .= "<img src=\"$invImage\" />"; 
                $investigators .= "</a>";
            }
			$investigators .= "<div>";
			if( $invLink ) { $investigators .= "<a href=\"$invLink\">Character Sheet</a>"; }
			if( $invPlayer ) { $investigators .= "<span>$invPlayer</span>"; }
			$investigators .= "</div>";
			$investigators .= "</div>";
		}
		
		elseif( $block->type == "list" )
		{
			$a = "";
			foreach( $block->data->items as $item )
			{
				$a .= "<li>$item</li>";
			}
			$lines .= "<ul>$a</ul>";		
		}
		
		elseif( $block->type == "delimiter" )
		{
			if( !$isPage )
				$lines .= "<div class=\"delimiter\">* * *</div>";
			else
				$lines .= "</div><div class=\"page\">";
		}
        
        elseif( $block->type == "table" )
        {
            $tbl = "<table>";
            $cellTag = "td";
            if( $block->data->withHeadings )
            {
                $cellTag = "th";
            }
            
            foreach( $block->data->content as $row )
            {
                $tbl .= "<tr>";
                if( !$block->data->withHeadings )
                {
                    $cellTag = "th";
                }
                foreach( $row as $cell )
                {
                    $tbl .= "<" . $cellTag . ">";
                    $tbl .= $cell;
                    $tbl .= "</" . $cellTag . ">";
                    
                    if( !$block->data->withHeadings )
                    {
                        $cellTag = "td";
                    }
                }
                $tbl .= "</tr>";
                $cellTag = "td";
            }
            $tbl .= "</table>";
            $lines .= $tbl;
        }
		
		else
		{
			$tag = "p";
			$insert = "";
		
			if ( $block->type == "header" )
			{
				$tag = "h" . $block->data->level;
				
				$headerIndex++;
				$insert = " id=\"header-$headerIndex\"";
			}
			elseif ( $block->type == "quote" )
			{
				$tag = "blockquote";
			}
			elseif ( $block->type == "signature" )
			{
				$insert = " class=\"signature\"";
			}
			elseif ( $index == 0 && $isFormalLetter && $block->type == "paragraph" )
			{
				$start = strtolower( substr( $block->data->text, 0, 4 ) );
				if( $start != "dear" ) $insert = " class=\"letter-head\"";
			}
			
			$lines .= "<$tag$insert>" . $block->data->text . "</$tag>";		
		}
		
		$index++;		
	}
	
	if( $images != "" )
	{
		$lines .= "<div class=\"gallery\">$images</div>";
	}
	if( $markings != "" )
	{
		$lines .= "<div class=\"markings\">$markings</div>";
	}
	if( $investigators != "" )
	{
		$lines .= "<div class=\"investigators\">$investigators</div>";
	}
		
	//$lines .= "<br/></br/><p>" . htmlspecialchars($text) . "</p>";
	return $lines;
	//*/	
}

/* **************************************************************************************************** */

function BuildSideBar( Category $category, string $pageName, string $text, bool $canUpdate, string $baseUrl ) : string
{
	$obj = json_decode($text);
	
	$content = "<input type=\"checkbox\" id=\"toggle-side-bar\" />"; 
	$content .= "<label for=\"toggle-side-bar\" ><span>Options</span></label>"; 
	
	$content .= "<div id=\"side-bar\">";
	$content .= BuildWhoAmI($category->Id);
	$content .= "<div class=\"breadcrumbs\">";
	
	$content .= HomeLink();	
	$content .= CategoryLink( $category );

	//$content .= "<span>$pageName</span>";
	$content .= "<a class=\"this-page\" href=\"#top\" onclick=\"window.location.replace('#top'); document.getElementById('toggle-side-bar').click(); return false;\">$pageName</a>";
	
	$headerIndex = 0;	
	foreach($obj->blocks as $block)
	{
		if ( $block->type == "header" || $block->type == "investigator" )
		{
			$headerIndex++;
			$text = $block->data->text;
			//$content .= "<a href=\"#header-$headerIndex\">$text</a>";
			$content .= "<a href=\"#header-$headerIndex\" onclick=\"window.location.replace('#header-$headerIndex'); document.getElementById('toggle-side-bar').click(); return false;\">$text</a>";
		}
	}

	//$content .= "<a href=\"#comment-text\">Comments</a>";
	$content .= "</div>";
	
	if( $canUpdate )
	{
		$editUrl = $baseUrl . "action=edit";
		$editUrl = htmlspecialchars($editUrl);
		
		$deleteUrl = $baseUrl . "action=delete";
		$deleteUrl = htmlspecialchars($deleteUrl);
		
		// **********

		$content .= "<div class=\"controls\">";
		$content .= "<a href=\"$editUrl\" title=\"Edit Article\" class=\"edit\"><span>Edit</span></a>";
		$content .= "<a href=\"$deleteUrl\" title=\"Delete Article\" class=\"delete\"><span>Delete</span></a>";
		$content .= "</div>";
	}
	
	$content .= "</div>";

	return $content;
}

/* **************************************************************************************************** */

function TextBoxHtml( string $id, string $caption, string $value ) : string
{
	$content = "<label for=\"$id\">$caption</label>";
	$content .= "<input id=\"$id\" name=\"$id\" type=\"text\" value=\"$value\" />";	
	
	return $content;
}

function TextAreaHtml( string $id, string $caption, string $value ) : string
{
	$content = "<label for=\"$id\">$caption</label>";
	$content .= "<textarea id=\"$id\" name=\"$id\">$value</textarea>";
	
	return $content;
}

/* **************************************************************************************************** */

function HomeLink() : string
{
	$u = "?t=" . idate("U");
	
	$content = "<a href=\"$u\">";
	$content .= "<span>Home</span>";
	$content .= "</a>";
	
	return( $content );
}

function CategoryLink( Category $category ) : string
{
	$u = "?t=" . idate("U");
	$u .= "&cat=" . $category->Id;
	
	$content = "<a href=\"$u\">";
	$content .= "<span>$category->Name</span>";
	$content .= "</a>";
	
	return( $content );
}
function CategorySessionsLink( Category $category ) : string
{
	$u = "?t=" . idate("U");
	$u .= "&cat=" . $category->Id;
	$u .= "&p=sessions";
	
	$content = "<a href=\"$u\">";
	$content .= "<span>Sessions</span>";
	$content .= "</a>";
	
	return( $content );
}

function ArticleLink( int $categoryId, Article $article, bool $showSumary, array $categories, string $searchQuery = "" ) : string
{
    if( $categoryId < 0 ) {
        $categoryId = $article->Categories[0];
    }
    
	$u = "?t=" . idate("U");
	$u .= "&cat=" . $categoryId;
	$u .= "&id=" . $article->Id;
    
    if( !empty($searchQuery) ) {
        $u .= "&q=$searchQuery";
    }
	
	//$summary = preg_replace( ['/\n/', '/<br>/'], "<br/>", $article->Summary );
    $c = strtolower($article->Name);
    $c = str_replace( " ", "-", $c );
    if($c == "reports") {
        $c = "sessions";
    } elseif ($c == "gallery") {
        $c = "gallery-link";
    }
    $cap = $article->Name;
    
	$content = "<a href=\"$u\" class=\"article-link $c\">";
	
    if ( $showSumary and !empty($article->SummaryImage) ) {
        $content .= "<img src=\"$article->SummaryImage\" />";
    } 
    $content .= "<span>$cap</span>";
	if( $showSumary ) 
	{
        //$content .= "<span>$article->CreatedBy | $created</span>";
        
        //$content .= BuildCategoryTags($categories, $article->Categories);
        //*
        $content .= "<div class=\"tags summary\">";
        foreach( $categories as $cat)
        {
            //$content .= "<span>$cat->Id $cat->Name</span>";
            if( in_array($cat->Id, $article->Categories) ) {
                $content .= "<span>$cat->Name</span>";
            }
        }
        $content .= "</div>";
        //*/
        
        if ( empty($article->SummaryImage) ) {
            $content .= "<span>$article->Summary</span>";
        }
        
        //if ( !empty($article->GameDate) ) {
        //    $created = date("j F Y", $article->GameDate);
        //} else {
        //    $created = date("j F Y", $article->CreatedAt);
        //}
        $content .= "<div class=\"posted-by\">";
        if( !empty($article->CreatedByPicture) )
        {
            $content .= "<img src=\"$article->CreatedByPicture\" />";
        }
        $content .= "<div>";
        $content .= "<span class=\"posted-name\">$article->CreatedBy</span>";
        
        if ( !empty($article->GameDate) ) {
            $created = date("j F Y", $article->GameDate);
            $content .= "<span>$created</span>";
        }
        $content .= "</div>";
        $content .= "</div>";
        
		if( $article->CommentsCount > 0 )
		{
			$s = ( $article->CommentsCount == 1 ? '' : 's' );
			$content .= "<span class=\"comments-count\">$article->CommentsCount Comment$s</span>";
		}
	}
	$content .= "</a>";
	
	return( $content );
}

function SessionsLink( int $categoryId ) : string
{
	$u = "?t=" . idate("U");
	$u .= "&cat=" . $categoryId;
	$u .= "&p=sessions";
	
	$content = "<a href=\"$u\" class=\"sessions\">";
	$content .= "<span>".SessionsName()."</span>";
	$content .= "</a>";
	
	return( $content );
}

/* **************************************************************************************************** */

function BuildCategoryList(array $cats) : string
{
	$content = "<ul>";

	foreach ($cats as $cat) {

		$content .= "<li>";
		$content .= CategoryLink( $cat );
		$content .= "</li>";
	}
	
	$content .= "</ul>";
	return( $content );	
}

function BuildArticleList(Category $category, array $articles, array $categories) : string
{
	$content = "";
    $important = "";
	$columns = count($articles);
	
	if( $columns > 0 )
	{
		$content .= "<ul class=\"column-$columns\">";
        $sess = array();
        $inv = NULL;
        $gall = NULL;
        $isCamp = $category->IsCampaign;
        
		foreach($articles as $article)
		{
            $nm = $article->Name;
            if( $isCamp and $nm == "Gallery" ) {
                $gall = $article;
            } elseif ( $isCamp and $nm == "Investigators" ) {
                $inv = $article;
            } elseif ( $isCamp and stripos($nm, $category->Name) !== false ) {
                $sess[] = $article;
            } else {
                $content .= "<li data-articleid=\"$article->Id\">";
                $content .= ArticleLink( $category->Id, $article, true, $categories );
                $content .= "</li>";
            }
		}
		$content .= "</ul>";
        
        if( !is_null($gall) or !is_null($inv) ) {
            $important = "<div class=\"important curr-link\">";
            if( count($sess) == 1 ) {
                $sessId = $sess[0]->Id;
                $art = new Article();
                $art->Name = SessionsName();
                $art->Id = $sessId;
                
                $important .= ArticleLink( $category->Id, $art, false, $categories );
            } elseif ( count($sess) > 1 ) {
                $important .= SessionsLink( $category->Id );
            }
            if( !is_null($inv) ) {
                $important .= ArticleLink( $category->Id, $inv, false, $categories );
            }
            if( !is_null($gall) ) {
                $important .= ArticleLink( $category->Id, $gall, false, $categories );
            }
            $important .= "</div>";
        }
	}
	else
	{
		$content .= "<p>There&apos;s nothing here yet.</p>";
		$content .= "<p>Click &apos;Add&apos; to get the adventure started.</p>";
	}
	return( $important . $content );	
}


function BuildSessionsList(Category $category, array $articles, array $categories) : string
{
	$content = "";
    $important = "";
	$columns = count($articles);
	
	if( $columns > 0 )
	{
		$content .= "<ul class=\"column-$columns\">";
        
		foreach($articles as $article)
		{
            $nm = $article->Name;
            if( stripos($nm, $category->Name) !== false ) {
                $content .= "<li data-articleid=\"$article->Id\">";
                $content .= ArticleLink( $category->Id, $article, true, $categories );
                $content .= "</li>";
            }
		}
		$content .= "</ul>";
        
        $content .= "<div class=\"important curr-link back\">";
        /*
        $cat = new Category();
        $cat->Name = "Back";
        $cat->Id = $category->Id;
        
        $important .= CategoryLink( $cat );
        */
        $content .= "<a href=\"#\" class=\"back\" onclick=\"history.go(-1); return false;\">Back</a>";
        $content .= "</div>";
	}
	else
	{
		$content .= "<p>There&apos;s nothing here yet.</p>";
		$content .= "<p>Click &apos;Add&apos; to get the adventure started.</p>";
	}
	return( $important . $content );	
}

function BuildCategoryTags( array $categories, array $chosenCategories )
{
    //$ch = array_filter($categories, function($c) { return in_array($c->Id, $chosenCategories); });

    $campaigns = array_filter($categories, function($c) { return $c->IsCampaign; });
	$others = array_filter($categories, function($c) { return !($c->IsCampaign); });
    
    $content = "<div class=\"tags\">";
    foreach( array_merge($campaigns, $others) as $cat)
    {
        if( in_array($cat->Id, $chosenCategories) ) {
            $content .= CategoryLink( $cat );
        }
    }
    $content .= "</div>";
    return( $content );	
}

/* **************************************************************************************************** */

function InitialisePermissions()
{
	//if( !isSet($_SESSION['Permissions']) )
	//{
		$permissions = array(
			'CanUpdateCategories'=>false,
			'CanUpdateArticles'=>false,
			'CanUpdateAllArticles'=>false,
			'CanPostComments'=>false
		);
		
		$_SESSION['Permissions'] = $permissions;
	//}
}
function UpdatePermissions(int $playerId)
{
	$perm = new Permission();
	
	$conn = GetConnection();

	$bus = new PermissionBus($conn);
	$perm = $bus->ReadForPlayer( $playerId );
	
	CloseConnection( $conn );
	
	// **********

	$_SESSION['Permissions'] = $perm->ToArray();
	
	/*
	$permissions = array(
		'CanUpdateCategories'=>false,
		'CanUpdateArticles'=>true,
		'CanUpdateAllArticles'=>false,
		'CanPostComments'=>true
	);
	
	$_SESSION['Permissions'] = $permissions;
	*/
}

?>