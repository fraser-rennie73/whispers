<?php declare(strict_types=1); // strict requirement

class Article {

	public ?int $Id;
	public ?string $Name;
	public ?string $Text;
	public ?string $Summary;
    public ?string $SummaryImage;
	public ?int $CommentsCount;
	public ?int $ArticleTypeId;
	public ?string $ArticleTypeClass;
	public array $Categories;
	public array $Comments;
	public ?int $CreatedAt;
	public ?int $CreatedById;
	public ?int $PostedById;
	public ?string $CreatedBy;
    public ?string $PostedBy;
	public ?string $CreatedByPicture;
	public ?int $UpdatedAt;
    public ?int $GameDate;

	public function __construct() {
		$this->Id = null;
		$this->Name = "";
		$this->Text = "";
		$this->Summary = "";
        $this->SummaryImage= null;
		$this->CommentsCount = 0;
		$this->ArticleTypeId = 1; //Normal
		$this->ArticleTypeClass = "";
		
		$this->Categories = array();
		$this->Comments = array();
		
		$this->CreatedAt = null;
		$this->CreatedById = null;
		$this->PostedById = null;
		
		$this->CreatedBy = "";
        $this->PostedBy = "";
		$this->CreatedByPicture = "";
		$this->UpdatedAt = null;
        $this->GameDate = null;
	}
	
	public function FromRow(array $row)
	{
		$this->Id = (int)$row["ArticleID"];
		$this->Name = $row["ArticleName"];
		$this->Text = $row["ArticleText"];
		$this->Summary = $row["ArticleSummary"];
        
        if( !empty($row["SummaryImage"]) )
        {
            $this->SummaryImage = $row["SummaryImage"];
        }
        
		$this->ArticleTypeId = (int)$row["ArticleTypeID"];
		$this->ArticleTypeClass = $row["ArticleTypeClass"];

		if( !empty($row["CommentsCount"]) )
		{
			$this->CommentsCount = (int)$row["CommentsCount"];
		}
		
		if( !empty($row["CreatedAt"]) )
		{
			$created = strtotime( $row["CreatedAt"] );
			$this->CreatedAt = $created;
		}
        
        if( !empty($row["UpdatedAt"]) )
        {
            $updated = strtotime( $row["UpdatedAt"]);
            $this->UpdatedAt = $updated;
        }
        
        if( !empty($row["GameDate"]) ) {
            $gd = strtotime( $row["GameDate"] );
            $this->GameDate = $gd;
        }
		
		$this->CreatedById = (int)$row["CreatedByPlayerID"];
		if( !empty($row["PlayerCharacterID"]) )
		{
			$this->PostedById = (int)$row["PlayerCharacterID"];
		}
		
		$this->CreatedBy = $row["CreatedByPlayer"];
        $this->PostedBy = $row["PostedBy"];
		$this->CreatedByPicture = $row["CreatedByPicture"];
	}
	
	public function ClearCategories()
	{
		$this->Categories = array();
	}
	public function AddCategory(int $categoryId)
	{
		array_push($this->Categories, $categoryId);
	}
	
	public function ClearComments()
	{
		$this->Comments = array();
		$this->CommentsCount = 0;
	}
	public function AddComments(array $comments)
	{
		foreach($comments as $comment)
		{
			array_push($this->Comments, $comment);
		}
		$this->CommentsCount = count($comments);
	}
}

class ArticleType {

	public ?int $Id;
	public ?string $Name;
	public ?string $CssClass;

	public function FromRow(array $row)
	{
		$this->Id = (int)$row["ArticleTypeID"];
		$this->Name = $row["ArticleTypeName"];
		$this->CssClass = $row["ArticleTypeClass"];
	}
}

class ArticleBus {

	private mysqli $conn;

	public function __construct(mysqli $conn) 
    {
		$this->conn = $conn;
    }
    
    private function GetSearchSummary(string $text, string $searchQuery) : string
{
    $result = [];
    $obj = json_decode($text);
    $replacement = "<b>$searchQuery</b>";
    $len = strlen($searchQuery);
    $wrap = 40;
    $matchLen = ($wrap * 2) + $len;
    $dropTags = '/<[^>]+>/';
    $lookFor = '/' . preg_quote($searchQuery) . '/i';
    $matchCount = 0;
    array_push($result, "x Matches");
    
    foreach($obj->blocks as $block) {
        $blockText = "";
        if ($block->type == "image") {
            $blockText = $block->data->caption;
        } elseif ($block->type == "table") {
            $rows = [];
            foreach($block->data->content as $row)
                array_push($rows, implode(" | ", $row));
            $blockText = implode(" || ", $rows);
        } else {
            $blockText = $block->data->text;
        }
    
        if( !$blockText ) continue;
        $blockText = preg_replace( $dropTags, "", $blockText );
        
        $offset = 0;
        $finished = false;
        $blockLen = strlen($blockText);
        $lastStart = max(0, $blockLen - $matchLen);
        
        try {
        $m = preg_match_all($lookFor, $blockText, $matches, PREG_OFFSET_CAPTURE);
            
        if( $m ) {
            $matchCount += count($matches[0]);
            if( count($result) < 6 ) {
            foreach($matches[0] as $match) {
                $pos = $match[1];
                $start = max(0, $pos - $wrap);
                $replStart = ( $start == 0 ? $pos : $wrap );
                
                if($start > $lastStart) {
                    $replStart += ($start - $lastStart);
                    $start = $lastStart;
                }

            $match = substr($blockText, $start, $matchLen);
            $match = substr_replace($match, $replacement, $replStart, $len);

            array_push($result, "<span>...$match...</span>");
            if( count($result) >= 6 ) break;
              }
            }
        }
      }
      //catch exception
      catch(Exception $e) {
        array_push($result, 'Message: ' .$e->getMessage());
      }
    }
    
    $matches = ($matchCount == 1 ? "match" : "matches" );
    $result[0] = "$matchCount $matches.";
    return implode( "<br>", $result );
}
    
    Private function GetSummaryImage( string $text ) : string
    {
        $obj = json_decode($text);
        $image = "";
        
        foreach($obj->blocks as $block)
        {
            if( $block->type == "image" ) {
                $image = $block->data->url;
                break;
            }
        }
        return( $image );
    }
    
    private function GetFullText(string $text) : string 
{
    $obj = json_decode($text);
    $fullText = "";

    foreach($obj->blocks as $block) {
        if ($block->type == "image") {
            $fullText .= $block->data->caption;
        } elseif ($block->type == "table") {
            $rows = [];
            foreach($block->data->content as $row)
                array_push($rows, implode(" | ", $row));
            $fullText .= implode(" || ", $rows);
        } else {
            $fullText .= $block->data->text;
        }
        $fullText .= " ";

        if ($block->type == "investigator") {
            $fullText .= $block->data->description . " ";
        }
    }
    return strip_tags( $fullText );
}

    
    Private function GetSummary( string $text ) : string
	{
        $full = $this->GetFullText($text);
        
        $sumariser = new ArticleSummariser();
        $summary = $sumariser->summarise( $full );
        
        /*
		$obj = json_decode($text);
		$summary = "";
		$joiner = "";
		$toAdd = "";
		
		foreach($obj->blocks as $block)
		{
			if( $block->type == "image" )
			{
				$toAdd = $block->data->caption;
			}
			else
			{
				$toAdd = $block->data->text;
			}
			
			$toAdd = preg_replace('/<[^>]*>/', '', $toAdd);
			
			if( !empty( $toAdd ) )
			{
				$summary .= $joiner . $toAdd;
				if( $joiner == "" ) $joiner = "<br>";
			}
			
			if( strlen( $summary ) >= 250 )
			{
				$summary = substr( $summary, 0, 247 ) . "...";
				break;
			}
		}
		
		//$lines .= "<br/></br/><p>" . htmlspecialchars($text) . "</p>";
        */
		return( $summary );
	}
	
	public function Add( Article $article, int $playerId ) : int
	{
		$articleId = 0;
		
		$sql = "INSERT INTO Article ";
		$sql .= "( ArticleName, ArticleText, ArticleSummary, GameDate, ArticleTypeID, CreatedByPlayerID, PlayerCharacterID ) ";
		$sql .= "VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $summaryText = $this->GetSummary( $article->Text );

		// prepare and bind
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("ssssiii", $name, $text, $summary, $gameDate, $typeId, $byId, $charId);

		// set parameters and execute
		$name = $article->Name;
		$text = $article->Text;
        //$summary = $this->GetSummary($article->Text);
        $summary = $summaryText;
		$typeId = $article->ArticleTypeId;
		$byId = $playerId;
		
		$charId = null;
		if( isSet($article->PostedById) ) 
            $charId = $article->PostedById;
		
        $gameDate = null;
        if( isSet($article->GameDate) )
            $gameDate = date("Y-m-d", $article->GameDate);
        
		$result = $stmt->execute();
		$articleId = $stmt->insert_id;
		
		$stmt->close();

		/*
		$sql = "INSERT INTO Article ";
		$sql .= "( ArticleName, ArticleText ) ";
		$sql .= "VALUES ( '$article->Name', '$article->Text' )";
		
		$result = $this->conn->query($sql);
		*/
		
		if( $result === True )
		{
			//$articleId = $this->conn->insert_id;
			
			$sql = "INSERT INTO ArticleCategory ";
			$sql .= "( ArticleID, CategoryID ) ";
			$sql .= "VALUES ";
			
			$joiner = "";
			
			foreach( $article->Categories as $cat )
			{
				$sql .= "$joiner( $articleId, $cat )";
				$joiner = ", ";
			}
		
			$result = $this->conn->query($sql);
		}
		
		return $articleId;
	}

	public function Edit( Article $article ) : int
	{
		// prepare and bind
		$sql = "UPDATE Article SET ";
		$sql .= "ArticleName = ?, ArticleText = ?, ArticleSummary = ?, SummaryImage = ?, GameDate = ?, ArticleTypeID = ?, PlayerCharacterID = ?";
        $sql .= ", UpdatedAt = CURRENT_TIMESTAMP()";
		$sql .= " WHERE ArticleID = ?";
        
        $summaryText = $this->GetSummary( $article->Text );
		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("sssssiii", $name, $text, $summary, $summaryImage, $gameDate, $typeId, $charId, $articleId);

		// set parameters and execute
		$name = $article->Name;
		$text = $article->Text;
        //$summary = $this->GetSummary($article->Text);
        $summary = $summaryText;
		$typeId = $article->ArticleTypeId;
		$articleId = $article->Id;
		
		$charId = null;
		if( isSet($article->PostedById) ) 
            $charId = $article->PostedById;
        
        $summaryImage = null;
        $si = $this->GetSummaryImage($article->Text);
        if( $si != "" ) {
            $summaryImage = $si;
        }
        
        $gameDate = null;
        if( isSet($article->GameDate) ) 
            $gameDate = date( "Y-m-d", $article->GameDate);
		
		$result = $stmt->execute();
		$stmt->close();

		if( $result === True )
		{
			$existing = $this->GetCategories( $article->Id );
			
			$toRemove = array_diff( $existing, $article->Categories );
			$toAdd = array_diff( $article->Categories, $existing );
			
			if( count($toRemove) > 0 )
			{
				$sql = "DELETE FROM ArticleCategory WHERE ArticleID = ? AND CategoryID = ?";
				$stmt = $this->conn->prepare($sql);
				$stmt->bind_param("ii", $artId, $catId);
				
				$artId = $article->Id;
				foreach( $toRemove as $id )
				{
					$catId = $id;
					$result = $stmt->execute();
				}
				$stmt->close();
			}
			if( count($toAdd) > 0 )
			{
				$sql = "INSERT INTO ArticleCategory ( ArticleID, CategoryID ) VALUES ( ?, ? )";
				$stmt = $this->conn->prepare($sql);
				$stmt->bind_param("ii", $artId, $catId);
				
				$artId = $article->Id;
				foreach( $toAdd as $id )
				{
					$catId = $id;
					$result = $stmt->execute();
				}
				$stmt->close();
			}
			
			/*
			$sql = "DELETE FROM ArticleCategory WHERE ArticleID = $article->Id";
			$result = $this->conn->query($sql);
			
			$sql = "INSERT INTO ArticleCategory ";
			$sql .= "( ArticleID, CategoryID ) ";
			$sql .= "VALUES ";
			
			$joiner = "";
			
			foreach( $article->Categories as $cat )
			{
				$sql .= "$joiner( $article->Id, $cat )";
				$joiner = ", ";
			}
		
			$result = $this->conn->query($sql);
			*/
		}
		
		return $articleId;
	}

	public function Delete(int $id) : bool
	{
		$sql = "UPDATE Article SET ";
		$sql .= "IsVisible = 0 ";
		$sql .= "WHERE ArticleID = $id";
		
		$result = $this->conn->query($sql);
		return $result;
	}
	
	public function Read(int $id) : Article
	{
		$sql = "SELECT A.ArticleID, A.ArticleName, A.ArticleText, A.ArticleTypeID, T.ArticleTypeClass, A.ArticleSummary, ";
		$sql .= "A.CreatedAt, A.CreatedByPlayerID, A.PlayerCharacterID, A.UpdatedAt, A.GameDate, ";
		$sql .= "CONCAT(P.FirstName, ' ', P.LastName) AS CreatedByPlayer, CONCAT(PC.FirstName, ' ', PC.LastName) AS PostedBy, ";
		$sql .= "IFNULL(PC.Picture,P.Picture) AS CreatedByPicture ";
		$sql .= "FROM Article A ";
		$sql .= "INNER JOIN ArticleType T ON T.ArticleTypeID = A.ArticleTypeID ";
		$sql .= "INNER JOIN Player P ON P.PlayerID = A.CreatedByPlayerID ";
		$sql .= "LEFT JOIN PlayerCharacter PC ON PC.PlayerCharacterID = A.PlayerCharacterID ";
		$sql .= "WHERE A.ArticleId = $id";
		
		$result = $this->conn->query($sql);
		
		$article = new Article();
		
		if ($result->num_rows > 0) {

			// output data of the row
			$row = $result->fetch_assoc();
			$article->FromRow($row);
			
			//$this->SetCategories($article);
			$categories = $this->GetCategories( $id );
			foreach( $categories as $catId )
			{
				$article->AddCategory( $catId );				
			}
			
			$bus = new CommentBus($this->conn);
			$comments = $bus->ReadAllForArticle($id);
			
			$article->AddComments($comments);
		}
		
		return $article;
	}

	public function ReadLatest() : array
	{
		$sql = "SELECT A.ArticleID, A.ArticleName, A.ArticleSummary, A.SummaryImage, A.ArticleTypeID, IFNULL(C.CommentsCount,0) AS CommentsCount, ";
		$sql .= "A.CreatedAt, A.CreatedByPlayerID, A.PlayerCharacterID, A.UpdatedAt, A.GameDate, ";
		$sql .= "CONCAT(IFNULL(PC.FirstName,P.FirstName), ' ', IFNULL(PC.LastName,P.LastName)) AS CreatedByPlayer, ";
		$sql .= "IFNULL(PC.Picture,P.Picture) AS CreatedByPicture ";
		$sql .= "FROM Article A ";
		$sql .= "INNER JOIN Player P ON P.PlayerID = A.CreatedByPlayerID ";
		$sql .= "LEFT JOIN PlayerCharacter PC ON PC.PlayerCharacterID = A.PlayerCharacterID ";
		$sql .= "LEFT JOIN ";
		$sql .= "( SELECT ArticleID, COUNT('x') AS CommentsCount ";
		$sql .= "  FROM ArticleComment WHERE ArticleID IS NOT NULL ";
		$sql .= "  GROUP BY ArticleID ) AS C ON C.ArticleID = A.ArticleID ";
        $sql .= "LEFT JOIN (";
        $sql .= "  SELECT ArticleId, MAX(CreatedAt) AS CommentedAt ";
        $sql .= "  FROM ArticleComment WHERE ArticleId IS NOT NULL ";
        $sql .= "  GROUP BY ArticleId ) AS CMT ON CMT.ArticleId = A.ArticleID ";
		$sql .= "WHERE A.IsVisible = 1 ";
		$sql .= "ORDER BY CASE WHEN CMT.CommentedAt > A.UpdatedAt THEN CMT.CommentedAt ELSE A.UpdatedAt END DESC ";
		$sql .= "LIMIT 5 ";
		$result = $this->conn->query($sql);
		
		$articles = array();
		
		if ($result->num_rows > 0) {

			// output data of each row
			while($row = $result->fetch_assoc()) {
				
				$article = new Article();
				$article->FromRow($row);
                
                $categories = $this->GetCategories( $article->Id );
                foreach ($categories as $catId ) {
                    $article->AddCategory( $catId );
                }
				
				array_push($articles, $article);
			}
		}
		
		return $articles;
	}
    
	public function ReadAllForCategory(int $categoryId) : array
	{
		$sql = "SELECT A.ArticleID, A.ArticleName, A.ArticleSummary, A.SummaryImage, A.ArticleTypeID, IFNULL(C.CommentsCount,0) AS CommentsCount, ";
		$sql .= "A.CreatedAt, A.CreatedByPlayerID, A.PlayerCharacterID, A.UpdatedAt, A.GameDate, ";
		$sql .= "CONCAT(IFNULL(PC.FirstName,P.FirstName), ' ', IFNULL(PC.LastName,P.LastName)) AS CreatedByPlayer, ";
		$sql .= "IFNULL(PC.Picture,P.Picture) AS CreatedByPicture ";
		$sql .= "FROM Article A ";
		$sql .= "INNER JOIN ArticleCategory AC ON AC.ArticleID = A.ArticleID ";
		$sql .= "INNER JOIN Player P ON P.PlayerID = A.CreatedByPlayerID ";
		$sql .= "LEFT JOIN PlayerCharacter PC ON PC.PlayerCharacterID = A.PlayerCharacterID ";
		$sql .= "LEFT JOIN ";
		$sql .= "( SELECT ArticleID, COUNT('x') AS CommentsCount ";
		$sql .= "  FROM ArticleComment WHERE ArticleID IS NOT NULL ";
		$sql .= "  GROUP BY ArticleID ) AS C ON C.ArticleID = A.ArticleID ";
		$sql .= "WHERE AC.CategoryID = $categoryId AND A.IsVisible = 1 ";
		$sql .= "ORDER BY CASE WHEN AC.CategoryOrder IS NULL THEN 2 ELSE 1 END, AC.CategoryOrder, A.ArticleID";
		$result = $this->conn->query($sql);
		
		$articles = array();
		
		if ($result->num_rows > 0) {

			// output data of each row
			while($row = $result->fetch_assoc()) {
				
				$article = new Article();
				$article->FromRow($row);
                
                $categories = $this->GetCategories( $article->Id );
                foreach ($categories as $catId ) {
                    $article->AddCategory( $catId );
                }
				
				array_push($articles, $article);
			}
		}
		
		return $articles;
	}
        
    public function Search(string $searchFor) : array
    {
		$sql = "SELECT A.ArticleID, A.ArticleName, A.ArticleSummary, A.ArticleText, A.ArticleTypeID, IFNULL(C.CommentsCount,0) AS CommentsCount, ";
		$sql .= "A.CreatedAt, A.CreatedByPlayerID, A.PlayerCharacterID, A.GameDate, ";
		$sql .= "CONCAT(IFNULL(PC.FirstName,P.FirstName), ' ', IFNULL(PC.LastName,P.LastName)) AS CreatedByPlayer, ";
		$sql .= "IFNULL(PC.Picture,P.Picture) AS CreatedByPicture ";
		$sql .= "FROM Article A ";
		$sql .= "INNER JOIN Player P ON P.PlayerID = A.CreatedByPlayerID ";
		$sql .= "LEFT JOIN PlayerCharacter PC ON PC.PlayerCharacterID = A.PlayerCharacterID ";
		$sql .= "LEFT JOIN ";
		$sql .= "( SELECT ArticleID, COUNT('x') AS CommentsCount ";
		$sql .= "  FROM ArticleComment WHERE ArticleID IS NOT NULL ";
		$sql .= "  GROUP BY ArticleID ) AS C ON C.ArticleID = A.ArticleID ";
		$sql .= "WHERE ";
        $sql .= "IsVisible = 1 AND ";
        $sql .= "(A.ArticleName LIKE '%$searchFor%' OR A.ArticleText LIKE '%$searchFor%')";
		$sql .= "ORDER BY ";
        $sql .= "  CASE WHEN A.ArticleName = '$searchFor' THEN 1 ELSE 2 END, ";
        $sql .= "  CASE WHEN A.ArticleName LIKE '$searchFor%' THEN 1 ELSE 2 END, ";
        $sql .= "  CASE WHEN A.ArticleName LIKE '%$searchFor%' THEN 1 ELSE 2 END, ";
        $sql .= "  LENGTH(A.ArticleText) - LENGTH(REGEXP_REPLACE(A.ArticleText, '(?i)$searchFor', '')) DESC, ";
        $sql .= "  A.ArticleName";

        $result = $this->conn->query($sql);
		$articles = array();
		
		if ($result->num_rows > 0) {
			// output data of each row
			while($row = $result->fetch_assoc()) {
				$article = new Article();
				$article->FromRow($row);
                
                $article->Summary = $this->GetSearchSummary( $article->Text, $searchFor );
                $article->Text = "";
                //$article->Summary = "<span>Search for $searchFor</span>";
                
                $categories = $this->GetCategories( $article->Id );
                foreach( $categories as $catId ) {
                    $article->AddCategory( $catId );
                }
                
				array_push($articles, $article);
			}
		}
		
		return $articles;
    }
	
	private function SetCategories(Article $article)
	{
		$sql = "SELECT CategoryID FROM ArticleCategory ";
		$sql .= "WHERE ArticleID = $article->Id";
		$result = $this->conn->query($sql);
		
		$article->ClearCategories();
		
		if ($result->num_rows > 0) {

			// output data of each row
			while($row = $result->fetch_assoc()) {
				
				$catId = (int)$row["CategoryID"];
				$article->AddCategory( $catId );
			}
		}
	}

	private function GetCategories(int $id) : Array
	{
		$sql = "SELECT CategoryID FROM ArticleCategory ";
		$sql .= "WHERE ArticleID = $id";
		$result = $this->conn->query($sql);
		
		$categories = array();
		if ($result->num_rows > 0) {

			// output data of each row
			while($row = $result->fetch_assoc()) {
				
				$catId = (int)$row["CategoryID"];
				array_push( $categories, $catId );
			}
		}
		
		return $categories;
	}
	
	public function ReadTypes() : array
	{
		$sql = "SELECT ArticleTypeID, ArticleTypeName, ArticleTypeClass FROM ArticleType";
		$result = $this->conn->query($sql);
		
		$articleTypes = array();
		
		if ($result->num_rows > 0) {

			// output data of each row
			while($row = $result->fetch_assoc()) {
				
				$articleType = new ArticleType();
				$articleType->FromRow($row);
				
				array_push($articleTypes, $articleType);
			}
		  		  
		}
		
		return $articleTypes;
	}
}

?>