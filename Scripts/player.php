<?php declare(strict_types=1); // strict requirement

class Player {

	public ?int $Id;
	public ?string $OauthProvider;
	public ?string $OauthUid;
	public ?string $FirstName;
	public ?string $LastName;
	public ?string $Picture;
	public ?string $Email;
	public ?int $RoleId;
	public ?date $FirstLogIn;
	public ?date $LatestLogIn;

	public function __construct() {
		$this->Id = null;
		$this->OauthProvider = "";
		$this->OauthUid = "";
		$this->FirstName = "";
		$this->LastName = "";
		$this->Picture = "";
		$this->Email = "";
		$this->RoleId = null;
		$this->FirstLogIn = null;
		$this->LatestLogIn = null;
	}
	
	public function FromOauth(string $provider, array $userDetail)
	{
		$this->Id = null;
		$this->FirstLogIn = null;
		$this->LatestLogIn = null;
		$this->RoleId = null;
		
		$this->OauthProvider = $provider;
		$this->OauthUid = $userDetail["id"];
		$this->FirstName = $userDetail["first_name"];
		$this->LastName = $userDetail["last_name"];
		$this->Picture = $userDetail['picture'];
		$this->Email = $userDetail['email'];
	}
	
	public function FromRow(array $row)
	{
		$this->Id = (int)$row["PlayerID"];
		$this->OauthProvider = $row["OauthProvider"];
		$this->OauthUid = $row["OauthUid"];
		$this->FirstName = $row["FirstName"];
		$this->LastName = $row["LastName"];
		$this->Picture = $row["Picture"];
		$this->Email = $row['Email'];
		
		$this->RoleId = (int)$row["RoleID"];
		//$this->FirstLogIn = strtotime( $row["FirstLogIn"] );
		//$this->LatestLogIn = strtotime( $row["LatestLogIn"] );
	}
}

class PlayerBus {

	private mysqli $conn;

	public function __construct(mysqli $conn) {
		$this->conn = $conn;
	}
	
	public function AddGuest( string $sid ) : int
	{
		// prepare and bind
		$sql = "INSERT INTO Guest ( SessionID ) VALUES ( ? )";
		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("s", $sessId);
		
		$sessId = $sid;
		
		$result = $stmt->execute();
		$guestId = $stmt->insert_id;
		
		$stmt->close();

		return $guestId;
	}
	
	public function Login( int $playerId ) 
	{
		$sql = "UPDATE Player ";
		$sql .= "SET FirstLogIn = CASE WHEN FirstLogIn IS NULL THEN NOW() ELSE FirstLogIn END, LatestLogIn = NOW() ";
		$sql .= "WHERE PlayerID = $playerId";
		
		$result = $this->conn->query($sql);
	}
	
	public function Add( Player $player ) : int
	{
		$playerId = 0;
		
		// prepare and bind
		$sql = "INSERT INTO Player ";
		$sql .= "( OauthProvider, OauthUid, FirstName, LastName, Picture, Email, FirstLogIn, LatestLogIn ) ";
		$sql .= "VALUES ( ?, ?, ?, ?, ?, ?, NOW(), NOW() )";
		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("ssssss", $provider, $uid, $firstName, $lastName, $picture, $email);

		// set parameters and execute
		$provider = $player->OauthProvider;
		$uid = $player->OauthUid;
		$firstName = $player->FirstName;
		$lastName = $player->LastName;
		$picture = $player->Picture;
		$email = $player->Email;
		
		$result = $stmt->execute();
		$playerId = $stmt->insert_id;
		
		$stmt->close();

		return $playerId;
	}

	public function Edit( int $playerId, Player $player ) : int
	{
		// prepare and bind
		$sql = "UPDATE Player SET ";
		$sql .= "OauthProvider = ?, OauthUid = ?, FirstName = ?, LastName = ?, Picture = ?, Email = ?, ";
		$sql .= "LatestLogIn = NOW() ";
		$sql .= "WHERE PlayerID = ?";
		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("ssssssi", $provider, $uid, $firstName, $lastName, $picture, $email, $id);

		// set parameters and execute
		$provider = $player->OauthProvider;
		$uid = $player->OauthUid;
		$firstName = $player->FirstName;
		$lastName = $player->LastName;
		$picture = $player->Picture;
		$email = $player->Email;		
		$id = $playerId;
		
		$result = $stmt->execute();
		
		$stmt->close();
		
		return $playerId;
	}

	public function Read( string $email ) : Player
	{
		$sql = "SELECT * FROM Player ";
		$sql .= "WHERE Email = '$email'";
		
		$result = $this->conn->query($sql);
		$player = new Player();
		
		if ($result->num_rows > 0) {

			$row = $result->fetch_assoc();
			$player->FromRow($row);
		}
		
		return $player;
	}
	
	public function ReadAll( ) : array
	{
		$sql = "SELECT * FROM Player ORDER BY LastName, FirstName";
		
		$result = $this->conn->query($sql);
		$players = array();
		
		if ($result->num_rows > 0) {

			// output data of each row
			while($row = $result->fetch_assoc()) {
				
				$player = new Player();
				$player->FromRow($row);
				
				array_push($players, $player);
			}
		}
		
		return $players;
	}

	/*
	*/
	public function ReadAllForCategory( int $categoryId, int $playerId ) : array
	{
		$sql = "SELECT DISTINCT PC.PlayerCharacterID AS PlayerID, PC.FirstName, PC.LastName ";
		$sql .= "FROM PlayerCharacter PC ";
		$sql .= "INNER JOIN PlayerCharacterCategory PCC ON PCC.PlayerCharacterID = PC.PlayerCharacterID ";
		$sql .= "WHERE PCC.CategoryID = $categoryId ";
		$sql .= "UNION ALL ";
		$sql .= "SELECT -1, P.FirstName, P.LastName ";
		$sql .= "FROM Player P ";
		$sql .= "WHERE P.PlayerID = $playerId ";
		$sql .= "ORDER BY CASE WHEN PlayerID = -1 THEN 2 ELSE 1 END, LastName, FirstName ";
		
		$result = $this->conn->query($sql);
		$players = array();
		
		if ($result->num_rows > 0) {

			// output data of each row
			while($row = $result->fetch_assoc()) {
				
				$player = new Player();
				$player->FromRow($row);
				
				array_push($players, $player);
			}
		}
			
		return $players;
	}
	
	public function ReadWhoCouldIBe( int $playerId, int $categoryId ) : array
	{
		$sql = "SELECT PC.PlayerCharacterID AS PlayerID, PC.FirstName, PC.LastName, PC.Picture, PC.IsActive ";
		$sql .= "FROM PlayerCharacter PC ";
		$sql .= "INNER JOIN PlayerCharacterCategory PCC ";
		$sql .= "ON PCC.PlayerCharacterID = PC.PlayerCharacterID ";
		$sql .= "WHERE PCC.CategoryID = $categoryId AND PCC.PlayerID = $playerId ";
		$sql .= "UNION ALL ";
		$sql .= "SELECT -1 AS PlayerID, P.FirstName, P.LastName, P.Picture, -1 AS IsActive ";
		$sql .= "FROM Player P ";
		$sql .= "WHERE P.PlayerID = $playerId ";
		$sql .= "ORDER BY IsActive DESC ";
		
		$result = $this->conn->query($sql);
		$players = array();
		
		if ($result->num_rows > 0) {

			// output data of each row
			while($row = $result->fetch_assoc()) {
				
				$player = new Player();
				$player->FromRow($row);
				
				array_push($players, $player);
			}
		}
			
		return $players;
	}
	
	public function ReadWhoAmI( int $playerId, int $categoryId )
	{
		/*
		if( empty($categoryId) || $categoryId <= 0 )
		{
			$sql = "SELECT P.FirstName, P.LastName, P.Picture ";
			$sql .= "FROM Player P ";
			$sql .= "WHERE P.PlayerID = $playerId ";
		}
		else
		{
			$sql = "SELECT IFNULL(PC.FirstName, P.FirstName) AS FirstName, IFNULL(PC.LastName, P.LastName) AS LastName, IFNULL(PC.Picture, P.Picture) AS Picture ";
			$sql .= "FROM Player P ";
			$sql .= "LEFT JOIN ( ";
			$sql .= "  SELECT PCC.PlayerID, PC.PlayerCharacterID, PC.FirstName, PC.LastName, PC.Picture, PC.IsActive ";
			$sql .= "  FROM PlayerCharacter PC ";
			$sql .= "  INNER JOIN PlayerCharacterCategory PCC ON PCC.PlayerCharacterID = PC.PlayerCharacterID ";
			$sql .= "  WHERE PCC.CategoryID = $categoryId AND PC.IsActive = 1 ) PC ";
			$sql .= "ON PC.PlayerID = P.PlayerID ";
			$sql .= "WHERE P.PlayerID = $playerId ";
			$sql .= "LIMIT 1";
		}
		
		$result = $this->conn->query($sql);
		$player = new Player();
		
		if ($result->num_rows > 0) {

			$row = $result->fetch_assoc();
			$player->FromRow($row);
		}
		
		return $player;
		*/
		$possible = $this->ReadWhoCouldIBe( $playerId, $categoryId );
		return $possible[0];
	}
	
	public function DeleteUserData( $userId )
	{
		$sql = "SELECT PlayerID FROM Player WHERE OauthUid=?";
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("s", $uid);

		$uid = $userId;
		$result = $stmt->execute();
		
		$row = $result->fetch_assoc();
		$playerId = $row["PlayerID"];
		
		$stmt->close();
		
		/* ********** */
		
		$sql = "UPDATE Player SET ";
		$sql .= "OauthProvider = NULL, OauthUid = NULL, FirstName = 'Zoom', LastName = 'Thulhu', Picture = 'Images/Characters/man.jpg', Email = 'zoomthulhu@fraser.epizy.com', ";
		$sql .= "LatestLogIn = NOW() ";
		$sql .= "WHERE OauthUid = ?";
		
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param("s", $uid);

		// set parameters and execute
		$uid = $userId;
		$result = $stmt->execute();
		$stmt->close();
		
		return $playerId;		
	}
}

?>