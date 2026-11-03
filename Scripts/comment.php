<?php declare(strict_types=1); // strict requirement

class Comment {

	public ?int $Id;
	public ?int $CreatedAt;
	public ?int $CreatedMinutesAgo;
	
	public ?int $PlayerId;
	public ?int $CharacterId;
	public ?string $PostedBy;	
	public ?string $Picture;
	
	public ?int $ArticleId;
	public ?string $ImageUrl;
	public ?string $Text;

	public function __construct() {
		$this->Id = null;
		$this->CreatedAt = null;
		$this->CreatedMinutesAgo = null;
		
		$this->PlayerId = null;
		$this->CharacterId = null;
		$this->PostedBy = "";
		$this->Picture = "";
		
		$this->ArticleId = null;
		$this->ImageUrl = null;
		$this->Text = "";
	}
	
	public function FromRow(array $row)
	{
		$this->Id = (int)$row["ArticleCommentID"];
		
		if( !empty($row["CreatedAt"]) )
		{
			$created = strtotime( $row["CreatedAt"] );
			$this->CreatedAt = $created;
		}

		$this->CreatedMinutesAgo = (int)$row["CreatedMinutesAgo"];
		$this->PlayerId = (int)$row["PlayerID"];
		$this->CharacterId = (int)$row["CharacterID"];
		$this->PostedBy = $row["PostedBy"];
		$this->Picture = $row["Picture"];
		
		$this->ArticleId = (int)$row["ArticleID"];
		$this->ImageUrl = $row["ImageUrl"];
		$this->Text = $row["CommentText"];
	}
}

class CommentBus {

	private mysqli $conn;

	public function __construct(mysqli $conn) {
		$this->conn = $conn;
	}
	
	public function Add( Comment $comment ) : int
	{
		$commentId = 0;

		$sql = "INSERT INTO ArticleComment ";
		$sql .= "( PlayerID, CharacterID, ArticleID, ImageUrl, CommentText ) ";
		$sql .= "VALUES (?, ?, ?, ?, ?)";

		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("iiiss", $playerId, $characterId, $articleId, $imageUrl, $text);

		// set parameters and execute
		$playerId = $comment->PlayerId;
		
		if( isSet($comment->CharacterId) )
		{
			$characterId = $comment->CharacterId;
		}
		if( isSet($comment->ArticleId) )
		{
			$articleId = $comment->ArticleId;
		}
		if( isSet($comment->ImageUrl) )
		{
			$imageUrl = $comment->ImageUrl;
		}
		
		$text = $comment->Text;
		
		$result = $stmt->execute();
		$commentId = $stmt->insert_id;
		
		$stmt->close();
		
		return $commentId;
	}

	/*
	ArticleCommentID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
	PlayerID INT NOT NULL,
	CharacterID INT NULL,
	ArticleID NULL,
	ImageUrl VARCHAR(250) NULL,
	CommentText VARCHAR(250) NOT NULL
	*/

	public function ReadAllForArticle(int $articleId) : array
	{
		$sql = "SELECT C.ArticleCommentID, C.CreatedAt, C.PlayerID, C.CharacterID, C.ArticleID, ";
		$sql .= "C.CommentText, CONCAT(IFNULL(PC.FirstName,P.FirstName), ' ', IFNULL(PC.LastName,P.LastName)) AS PostedBy, IFNULL(PC.Picture,P.Picture) AS Picture, ";
		$sql .= "TIMESTAMPDIFF(MINUTE, C.CreatedAt, NOW()) AS CreatedMinutesAgo  ";
		$sql .= "FROM ArticleComment C ";
		$sql .= "INNER JOIN Player P ON P.PlayerID = C.PlayerID ";
		$sql .= "LEFT JOIN PlayerCharacter PC ON PC.PlayerCharacterID = C.CharacterID ";
		$sql .= "WHERE C.ArticleID = $articleId";
		
		/*
		SELECT C.ArticleCommentID, C.CreatedAt, C.PlayerID, C.CharacterID, C.ArticleID, 
		C.CommentText, CONCAT(IFNULL(PC.FirstName,P.FirstName), ' ', IFNULL(PC.LastName,P.LastName)) AS PostedBy, IFNULL(PC.Picture,P.Picture) AS Picture, 
		TIMESTAMPDIFF(MINUTE, C.CreatedAt, NOW()) AS CreatedMinutesAgo  
		FROM ArticleComment C 
		INNER JOIN Player P ON P.PlayerID = C.PlayerID 
		LEFT JOIN PlayerCharacter PC ON PC.PlayerCharacterID = C.CharacterID
		WHERE C.ArticleID = 35;
		*/
		
		$result = $this->conn->query($sql);
		
		$comments = array();
		
		if ($result->num_rows > 0) {

			// output data of each row
			while($row = $result->fetch_assoc()) {
				
				$comment = new Comment();
				$comment->FromRow($row);
				
				array_push($comments, $comment);
			}
		}
		
		return $comments;
	}
	public function ReadAllForImage(string $imageUrl) : array
	{
		$sql = "SELECT C.ArticleCommentID, C.CreatedAt, C.PlayerID, C.CharacterID, C.ImageUrl, ";
		$sql .= "C.CommentText, CONCAT( IFNULL( PC.FirstName, P.FirstName ), ' ', IFNULL( PC.LastName, P.LastName )) AS PostedBy, IFNULL( PC.Picture, P.Picture ) AS Picture, ";
		$sql .= "TIMESTAMPDIFF(MINUTE, C.CreatedAt, NOW()) AS CreatedMinutesAgo  ";
		$sql .= "FROM ArticleComment C ";
		$sql .= "INNER JOIN Player P ON P.PlayerID = C.PlayerID ";
        $sql .= "LEFT JOIN PlayerCharacter PC ON PC.PlayerCharacterID = C.CharacterID ";
		$sql .= "WHERE C.ImageUrl = '$imageUrl'";
		
		$result = $this->conn->query($sql);
		
		$comments = array();
		
		if ($result->num_rows > 0) {

			// output data of each row
			while($row = $result->fetch_assoc()) {
				
				$comment = new Comment();
				$comment->FromRow($row);
				
				array_push($comments, $comment);
			}
		}
		
		return $comments;
	}
	
}

?>